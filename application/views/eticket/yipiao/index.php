<div>合计：<?php echo $result['result']['total_results']; ?>条</div>
<div>
	<?php if ($result['result']['page'] - 1 > 0) { ?>
	<a href="?page=<?php echo $result['result']['page'] - 1; ?>">上一页</a>
	<?php } ?>
	<span>第<?php echo $result['result']['page']; ?>页 / 总<?php echo $result['result']['pages'];?>页 </span>
	<?php if ($result['result']['page'] + 1 <= $result['result']['pages']) { ?>
	<a href="?page=<?php echo $result['result']['page'] + 1; ?>">下一页</a>
	<?php } ?>
	</div>
<?php $status_list = array('1' => '已付款', '3' => '已核销', '9' => '已退款'); ?>
<table style="border: 1px solid #efefef;">
	<tr>
		<td>ID</td>
		<td>手机号码</td>
		<td>订单号</td>
		<td>金额</td>
		<td>状态</td>
		<td>创建时间</td>
	</tr>
	<?php foreach ($result['result']['rows']  as $row) { ?>
	<tr>
		<td><?php echo $row['id']; ?></td>
		<td><?php echo $row['mobile']; ?></td>
		<td><?php echo $row['order_id']; ?></td>
		<td><?php echo $row['amount']; ?></td>
		<td><?php echo $status_list[$row['status']]; ?></td>
		<td><?php echo $row['ctime']; ?></td>
	</tr>
	<?php } ?>
</table>
