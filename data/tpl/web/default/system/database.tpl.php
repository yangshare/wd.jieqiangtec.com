<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/header-gw', TEMPLATE_INCLUDEPATH)) : (include template('common/header-gw', TEMPLATE_INCLUDEPATH));?>
<ol class="breadcrumb">
	<li><a href="./?refresh"><i class="fa fa-home"></i></a></li>
	<li><a href="<?php  echo url('system/welcome');?>">系统</a></li>
	<li><a href="<?php  echo url('system/database');?>">数据库</a></li>
	<li class="active"><?php  if($do == 'backup') { ?>备份<?php  } else if($do == 'restore') { ?>还原<?php  } else if($do == 'optimize') { ?>优化<?php  } else if($do == 'run') { ?>运行SQL<?php  } ?></li>
</ol>
<ul class="nav nav-tabs">
	<li<?php  if($do == 'backup') { ?> class="active"<?php  } ?>><a href="<?php  echo url('system/database');?>">备份</a></li>
	<li<?php  if($do == 'restore') { ?> class="active"<?php  } ?>><a href="<?php  echo url('system/database/restore');?>">还原</a></li>
	<li<?php  if($do == 'trim') { ?> class="active"<?php  } ?>><a href="<?php  echo url('system/database/trim');?>">数据库结构整理</a></li>
	<li<?php  if($do == 'optimize') { ?> class="active"<?php  } ?>><a href="<?php  echo url('system/database/optimize');?>">优化</a></li>
	<li<?php  if($do == 'run') { ?> class="active"<?php  } ?>><a href="<?php  echo url('system/database/run');?>">运行SQL</a></li>
</ul>
<div class="clearfix">
	<?php  if($do == 'backup') { ?>
	<form action="" method="post" class="form-horizontal" role="form">
		<h5 class="page-header">备份数据库</h5>
		<div class="form-group">
			<label class="col-xs-12 col-sm-2 col-md-2 col-lg-1 control-label">备份操作说明</label>
			<div class="col-sm-10 col-xs-12">
				<div class="help-block">使用本系统备份的备份数据, 只能使用本系统来进行还原. 如果使用其他工具, 或者自行导入sql, 可能造成数据不完整或不正确.</div>
				<div class="help-block"><strong>请在站点访问量比较低的时间段(如:深夜)操作, 或操作之前关闭站点. 来防止可能出现的意外数据丢失. </strong></div>
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-offset-2 col-md-offset-2 col-lg-offset-2 col-xs-12 col-sm-10 col-md-10 col-lg-10">
				<input name="submit" type="submit" value="开始备份" class="btn btn-primary span3" />
				<input type="hidden" name="token" value="<?php  echo $_W['token'];?>" />
			</div>
		</div>
	</form>
 	<?php  } ?>
	<?php  if($do == 'restore') { ?>
	<form action="" method="post" class="form-horizontal form">
		<h5 class="page-header">还原数据备份</h5>
		<div class="alert alert-info">
			<p>使用系统系统备份的备份数据, 只能使用系统系统来进行还原. 如果使用其他工具, 或者自行导入sql, 可能造成数据不完整或不正确.</p>
			<p><strong>请在站点访问量比较低的时间段(如:深夜)操作, 或操作之前关闭站点. 来防止可能出现的意外数据丢失. </strong></p>
		</div>
		<div class="panel panel-default">
		<div class="table-responsive panel-body">
			<table class="table table-hover">
				<thead>
				<tr>
					<th>备份名称</th>
					<th>备份时间</th>
					<th>分卷数量</th>
					<th>操作</th>
				</tr>
				</thead>
				<tbody>
				<?php  if(is_array($ds)) { foreach($ds as $row) { ?>
				<tr>
					<td><?php  echo $row['bakdir'];?></td>
					<td><?php  echo date('Y-m-d H:i:s', $row['time']);?></td>
					<td><?php  echo $row['volume'];?></td>
					<td><a href="<?php  echo url('system/database/restore', array('r' => $row['bakdir']));?>" onclick="return confirm('确认要恢复这条备份记录吗? 当前数据库的数据将会被覆盖.');">还原此备份</a> &nbsp; <a href="<?php  echo url('system/database/restore', array('d' => $row['bakdir']));?>" onclick="return confirm('确认要删除这条备份记录吗? ');">删除</a></td>
				</tr>
				<?php  } } ?>
				</tbody>
			</table>
		</div>
		</div>
		<?php  } ?>
	<?php  if($do == 'trim') { ?>
	<form action="" method="post" class="form-horizontal form">
		<h5 class="page-header">数据表差异</h5>
		<div class="alert alert-success table-info" style="display:none;">
			<p><strong>恭喜,您的数据表中已无冗余信息。</strong></p>
		</div>
		<?php  if(!empty($diff)) { ?>
		<div class="panel panel-default">
		<div class="panel-body table-responsive">
		<table class="table table-hover trim-container">
			<thead>
				<tr>
					<th style="width:400px;">表名称</th>
					<th style="width:200px;">字段</th>
					<th style="text-align:center">索引</th>
				</tr>
			</thead>
			<tbody>
			<?php  if(is_array($diff)) { foreach($diff as $row) { ?>
			<tr>
				<td class="tablename"><?php  echo $row['name'];?></td>
				<td>
					<?php  if(is_array($row['fields'])) { foreach($row['fields'] as $field) { ?>
						<div style="height:50px;" id="field<?php  echo $field;?>">
							<span><?php  echo $field;?></span>
							<a href="javascript:;" data-type="field" title="删除" class="btn btn-default btn-sm" style="float:right">删除</a>
						</div>
					<?php  } } ?>
				</td>
				<td>
					<?php  if(is_array($row['indexes'])) { foreach($row['indexes'] as $index) { ?>
						<div style="height:50px;float:right" id="index<?php  echo $index;?>">
							<span><?php  echo $index;?></span>
							<a href="javascript:;" data-type="index" title="删除" class="btn btn-default btn-sm" >删除</a>
						</div>
					<?php  } } ?>
				</td>
			</tr>
			<?php  } } ?>
			</tbody>
		</table>
		</div>
		</div>
		<?php  } ?>
		<script type="text/javascript">
				if ($(".tablename").text() == '') {
					$(".table-info").css('display', 'block');
				}
				$(".trim-container a").click(function() {
					if (!confirm('删除后将彻底改变数据库信息!')) {
						return false;
					}
					var type = $(this).attr('data-type');
					var data = $(this).prev().text();
					var table = $(this).parent().parent().parent().children("td.tablename").text();
					$.post('<?php  echo url('system/database/trim')?>', {'type' : type, 'data' : data, 'table' : table}, function(status){
						if (status == 'success') {
							$('#'+type+data).remove();
						}
					});
					var fields = $(this).parent().parent().children("div");
					var indexes = $(this).parent().parent().siblings().children("div");
					if (fields.length <= 1 && indexes.length < 1) {
						$(this).parent().parent().parent().remove();
					}
					if ($(".tablename").text() == '') {
						$(".table-info").css('display', 'block');
					}
				});
		</script>
	<?php  } ?>
	<?php  if($do == 'optimize') { ?>
	<form action="" method="post" class="form-horizontal form">
		<h5 class="page-header">优化数据表</h5>
		<div class="alert alert-info" style="margin-bottom:0">
			<strong>数据表优化可以去除数据文件中的碎片, 使记录排列紧密, 提高读写速度.</strong>
		</div>
		<br>
		<div class="panel panel-default">
		<div class="table-responsive panel-body">
		<?php  if(!empty($ds)) { ?>
		<table class="table table-hover">
			<thead>
				<tr>
					<th>操作</th>
					<th>表名</th>
					<th>表类型</th>
					<th>记录数</th>
					<th>数据尺寸</th>
					<th>索引尺寸</th>
					<th>碎片尺寸</th>
				</tr>
			</thead>
			<tbody>
			<?php  if(is_array($ds)) { foreach($ds as $row) { ?>
			<tr>
				<td><input type="checkbox" name="select[]" checked="checked" value="<?php  echo $row['title'];?>"></td>
				<td><?php  echo $row['title'];?></td>
				<td><?php  echo $row['type'];?></td>
				<td><?php  echo $row['rows'];?></td>
				<td><?php  echo $row['data'];?></td>
				<td><?php  echo $row['index'];?></td>
				<td><?php  echo $row['free'];?></td>
			</tr>
			<?php  } } ?>
			</tbody>
		</table>
		</div>
		</div>
		<table class="tb">
			<tr>
				<td>
					<button type="submit" class="btn btn-primary span3" name="submit" value="提交">开始优化</button>
					<input type="hidden" name="token" value="<?php  echo $_W['token'];?>" />
				</td>
			</tr>
		</table>
		<?php  } else { ?>
		<table class="tb">
			<tr>
				<th></th>
				<td>
					<div class="help-block"><strong>没有要优化的数据表</strong></div>
				</td>
			</tr>
		</table>
		<?php  } ?>
	</form>
	<?php  } ?>
	<?php  if($do == 'run') { ?>
	<form action="" method="post" class="form-horizontal form" onsubmit="return confirm('请确保你已经了解这些语句的作用, 并自愿承担风险.');">
		<h5 class="page-header">运行SQL语句</h5>
		<div class="form-group">
			<label class="col-xs-12 col-sm-2 col-md-2 col-lg-1 control-label"  >运行说明</label>
			<div class="col-sm-10 col-xs-12">
				<div class="help-block">通过此功能可以直接在数据库中执行特定语句, 用于调试错误. 或者系统管理员特定排错. 注意, 这里运行的语句不会有任何返回结果.</div>
				<div class="help-block"><strong>注意: 此功能可能造成数据破坏, 请谨慎使用. 如果你不清楚他的功能, 请不要使用.</strong></div>
			</div>
		</div>
		<div class="form-group">
			<label class="col-xs-12 col-sm-2 col-md-2 col-lg-1 control-label" >SQL</label>
			<div class="col-sm-10 col-xs-12">
					<textarea name="sql" class="form-control" rows="8"></textarea>
					<div class="help-block">多条语句请使用 ; 隔开</div>
			</div>
		</div>
		<div class="form-group">
			<label class="col-xs-12 col-sm-2 col-md-2 col-lg-1 control-label" ></label>
			<div class="col-sm-10 col-xs-12">
					<button type="submit" class="btn btn-primary span3" name="submit" value="提交">运行SQL</button>
					<input type="hidden" name="token" value="<?php  echo $_W['token'];?>" />
			</div>
		</div>
	<?php  } ?>
</div>
<?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/footer-gw', TEMPLATE_INCLUDEPATH)) : (include template('common/footer-gw', TEMPLATE_INCLUDEPATH));?>