<?php
/**
 * [WECHAT 2017]
 * [WECHAT  a free software]
 */
load()->func('communication');
load()->model('cloud');
$r = cloud_prepare();
if (is_error($r)) {
	message($r['message'], url('cloud/profile'), 'error');
}

$step = $_GPC['step'];
$steps = array('files', 'schemas', 'scripts');
$step = in_array($step, $steps) ? $step : 'files';

if ($step == 'files' && $_W['ispost']) {
	$post = $_GPC['__input'];
	$ret = cloud_download($post['path'], $post['type']);
	if (!is_error($ret)) {
		exit('success');
	}
	exit();
}

if ($step == 'scripts' && $_W['ispost']) {
	$post = $_GPC['__input'];
	$fname = $post['fname'];
	$entry = IA_ROOT . '/data/update/' . $fname;
	if (is_file($entry) && preg_match('/^update\(\d{12}\-\d{12}\)\.php$/', $fname)) {
		$evalret = include $entry;
		if (!empty($evalret)) {
			cache_build_users_struct();
			cache_build_setting();
			@unlink($entry);
			exit('success');
		}
	}
	exit('failed');
}

if (!empty($_GPC['m'])) {
	$m = $_GPC['m'];
	$type = 'module';
	$module = pdo_fetch('SELECT * FROM ' . tablename('modules') . ' WHERE `name`=:name', array(':name' => $m));
	$is_upgrade = intval($_GPC['is_upgrade']);
	$packet = cloud_m_build($_GPC['m']);
	if(!empty($packet['files']) && $is_upgrade){
		$mdir=IA_ROOT .'/data/modulesbak/'.$m;
		if(!is_dir($mdir)){
			load()->func('file');
			mkdirs($mdir);
		}
		$bak = $mdir.'/'.$module['version'].'.'.time();
		$zip= new ZipArchive();
		$zip->open($bak, ZipArchive::CREATE);
		foreach($packet['files'] as $file) {
			if(is_file(IA_ROOT . '/addons'.$file)){
				$zip->addFile(IA_ROOT . '/addons'.$file,str_replace('/'. $m,'',$file));
			}
		}
		$zip->close();
	}
} elseif (!empty($_GPC['t'])) {
	$m = $_GPC['t'];
	$type = 'theme';
	$is_upgrade = intval($_GPC['is_upgrade']);
	$packet = cloud_t_build($_GPC['t']);
} else {
	$packet = cloud_build();
	if(!empty($packet['files'])){
		if(!is_dir(IA_ROOT .'/data/systembak')){
			load()->func('file');
			mkdirs(IA_ROOT .'/data/systembak');
		}
		$bak=IA_ROOT .'/data/systembak/'.time();
		$zip= new ZipArchive();
	    $zip->open($bak, ZipArchive::CREATE);
		foreach($packet['files'] as $file){
			if(is_file(IA_ROOT .$file)){
				$zip->addFile(IA_ROOT .$file,$file);
			}
		}
		$zip->close();
	}
}
if ($step == 'schemas' && $_W['ispost']) {
	$post = $_GPC['__input'];
	$tablename = $post['table'];
	foreach ($packet['schemas'] as $schema) {
		if (substr($schema['tablename'], 4) == $tablename) {
			$remote = $schema;
			break;
		}
	}
	if (!empty($remote)) {
		load()->func('db');
		$local = db_table_schema(pdo(), $tablename);
		$sqls = db_table_fix_sql($local, $remote);
		$error = false;
		foreach ($sqls as $sql) {
			if (pdo_query($sql) === false) {
				$error = true;
				$errormsg .= pdo_debug(false);
				break;
			}
		}
		if (!$error) {
			exit('success');
		}
	}
	exit;
}

if (!empty($packet) && (!empty($packet['upgrade']) || !empty($packet['install']))) {
	$schemas = array();
	if (!empty($packet['schemas'])) {
		foreach ($packet['schemas'] as $schema) {
			$schemas[] = substr($schema['tablename'], 4);
		}
	}
	$scripts = array();
	if (empty($packet['install'])) {
		$updatefiles = array();
		if (!empty($packet['scripts']) && empty($packet['type'])) {
			$updatedir = IA_ROOT . '/data/update/';
			load()->func('file');
			rmdirs($updatedir, true);
			mkdirs($updatedir);
			$cversion = IMS_VERSION;
			$crelease = IMS_RELEASE_DATE;
			foreach ($packet['scripts'] as $script) {
				if ($script['release'] <= $crelease) {
					continue;
				}
				$fname = "update({$crelease}-{$script['release']}).php";
				$crelease = $script['release'];
				$script['script'] = @base64_decode($script['script']);
				if (empty($script['script'])) {
					$script['script'] = <<<DAT
<?php
load()->model('setting');
setting_upgrade_version('{$packet['family']}', '{$script['version']}', '{$script['release']}');
return true;
DAT;
				}
				$updatefile = $updatedir . $fname;
				file_put_contents($updatefile, $script['script']);
				$updatefiles[] = $updatefile;
				$s = array_elements(array('message', 'release', 'version'), $script);
				$s['fname'] = $fname;
				$scripts[] = $s;
			}
		}
	}
} else {
	if (is_error($packet)) {
		message($packet['message'], '', 'error');
	} else {
		cache_delete('checkupgrade:system');
		message('更新已完成. ', url('cloud/upgrade'), 'info');
	}
}
template('cloud/process');