<?php
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Config;
use Destiny\Common\Utils\Date;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($model->title)?></title>
<meta charset="utf-8">
<?php include Tpl::file('seg/commontop.php') ?>
</head>
<body id="admin" class="thin">

	<?php include Tpl::file('seg/top.php') ?>
	<?php include Tpl::file('admin/seg/top.php') ?>
	
	<section class="container">
	
		<div class="content content-dark stream-grids clearfix">
			<div id="userlist" data-size="<?=Tpl::out($model->size)?>" data-page="<?=Tpl::out($model->page)?>" class="stream stream-grid" style="width:100%;">
				
				<div style="margin:15px;" class="clearfix">
					<div class="pull-right" style="margin: 0 0 0 0;">
						<button id="resetuserlist" class="btn btn-warning">Reset</button>
					</div>
					<div class="pull-right">
						<select name="size" style="margin: 0 15px 0 0; width: 120px;">
							<option value="">Show up to</option>
							<option value="20">20</option>
							<option value="40">40</option>
							<option value="60">60</option>
							<option value="80">80</option>
							<option value="100">100</option>
							<option value="200">200</option>
						</select>
					</div>
					<?php if($model->users['totalpages'] > 1): ?>
					<div class="pull-left">
						<div class="pagination" style="margin: 0 15px 0 0; height: 30px;">
							<ul>
								<li><a data-page="1" href="?page=0">Start</a></li>
								<?php for($i = max(1, $model->users['page'] - 2); $i <= min($model->users['page'] + 2, $model->users['totalpages']); $i++): ?>
								<li><a data-page="<?=$i?>" href="?page=<?=$i+1?>"><?=$i?></a></li>
								<?php endfor; ?>
								<li><a data-page="<?=$model->users['totalpages']?>" href="?page=<?=$model->users['totalpages']?>">End</a></li>
							</ul>
						</div>
					</div>
					<?php endif; ?>
				</div>
				
				<table class="grid">
					<thead>
						<tr>
							<td style="width: 100%;">User <small>(<?=$model->users['total']?>)</small></td>
							<td style="width: 100px;">Subscription</td>
							<td style="width: 80px;">Created on</td>
						</tr>
					</thead>
					<tbody>
					<?php foreach($model->users['list'] as $user): ?>
					<?php $subType = (isset(Config::$a['commerce']['subscriptions'][$user['subscriptionType']])) ? Config::$a['commerce']['subscriptions'][$user['subscriptionType']] : null;?>
					<tr>
						<td><a href="/admin/user/<?=$user['userId']?>/edit"><?=Tpl::out($user['username'])?></a></td>
						<td>
							<div>
								<?php if(empty($subType)): ?>
								<span>None</span>
								<?php endif; ?>
								<span><?=Tpl::out($subType['tierLabel'])?></span>
								<?=($user['recurring'] == 1) ? '<small>Recurring</small>':''?>
							</div>
						</td>
						<td><?=Tpl::moment(Date::getDateTime($user['createdDate']), Date::STRING_FORMAT)?></td>
					</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				
			</div>
		</div>
		
	</section>
	
	<br /><br />
	
	<?php include Tpl::file('seg/commonbottom.php') ?>
	<script src="<?=Config::cdnv()?>/web/js/admin.js"></script>
	
</body>
</html>