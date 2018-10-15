<?php
$link = mysqli_connect('localhost','root','123456','liaotianshi');
		mysqli_query($link,"set names 'utf8'");
$num1 = htmlspecialchars($_POST['num1']);
$num2 = htmlspecialchars($_POST['num2']);
//执行查询的SQL语句
$sql = "SELECT * FROM message where name='$num1' and to_name='$num2'";
$result = mysqli_query($link,$sql); //向MySQL服务器发出SQL请求
//取出记录总数
$records = mysqli_num_rows($result);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>聊天记录管理</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script type="text/javascript">

function confirmDel(tim,num1,num2)
{
if(window.confirm("你确认要删除吗？"))
{
	
//跳转到delete.php文件
location.href = "http://192.168.21.102:8080/liaotianshi/delete.php?type=1&tim="+tim+"&num1="+num1+"&num2="+num2;
}
}
</script>
</head>
<body>
<a href="http://192.168.21.102:8080/liaotianshi/zhuye.php">返回</a>
<table wnumth="1000" border="1" bordercolor="#eee" rules="all" align="center" cellpadding="5" id="abc">
<tr>
<th>发布时间</th>
<th>发送方账号</th>
<th>接收方账号</th>
<th>内容</th>
<th>操作</th>
</tr>
<?php
while($row=mysqli_fetch_row($result))
{
echo "<tr align='center'>
<td>$row[0]</td>
<td>$row[1]</td>
<td>$row[2]</td>
<td align='left'>$row[3]</td>
<td>
<a href='javascript:void(0)' onClick=confirmDel('$row[3]','$row[1]','$row[2]');>删除</a>
</td>
</tr>";
}
?>
</table>
</body>
</html>