<?php
$name=$_GET['name'];
$link = mysqli_connect('localhost','root','123456','liaotianshi');
		mysqli_query($link,"set names 'utf8'");
       
		

//执行查询的SQL语句
$sql = "SELECT * FROM message where name='$name'";
$result = mysqli_query($link,$sql);//向MySQL服务器发出SQL请求
//取出记录总数
$records = mysqli_num_rows($result);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>聊天记录</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script type="text/javascript">

</script>
</head>
<body>
<table wnumth="1000" border="1" bordercolor="#eee" rules="all" align="center" cellpadding="5">
<tr>
<th>发布时间</th>
<th>发送方账号</th>
<th>接收方账号</th>
<th>内容</th>
</tr>
<?php
while($row=mysqli_fetch_row($result))
{
?>
<tr align="center">
<td><?php echo $row[0] ?></td>
<td><?php echo $row[1] ?></td>
<td><?php echo $row[2] ?></td>
<td align="left"><?php echo $row[3] ?></td>
</tr>
<?php
}
?>
</table>
</body>
</html>