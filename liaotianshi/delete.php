
<?php

$link = mysqli_connect('http://192.168.21.102:3306/','root','123456','liaotianshi');
		mysqli_query($link,"set names 'utf8'");

//获取地址的num参数，并构建要删除的SQL语句
$tim = $_GET["tim"];
$num1 = $_GET["num1"];
$num2 = $_GET["num2"];
$type=$_GET["type"];

$sql = "DELETE FROM message WHERE name='$num1' and to_name='$num2' and content='$tim'";
//执行查询的SQL语句
if(mysqli_query($link,$sql))
{
	if($type==0){
//跳转到首页index.php
	header("Location:http://192.168.21.102:8080/liaotianshi/zhuye.php");}
	else if($type==1){
		header("Location:http://192.168.21.102:8080/liaotianshi/zhuye.php");}
}
else{
	echo "<script>window.alert('删除失败')</script>";
	header("http://192.168.21.102:8080/liaotianshi/zhuye.php");}
?>