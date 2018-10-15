<?php
	$name=$_POST['zhanghao'];
	$password=$_POST['mima'];
	$link = mysqli_connect('localhost','root','123456','liaotianshi');
			mysqli_query($link,"set administrator 'utf8'");
		   
			

	//执行查询的SQL语句
	$sql = "SELECT * FROM administrator where Administrator='$name' AND password='$password'";
	$result = mysqli_query($link,$sql);//向MySQL服务器发出SQL请求
	//取出记录总数
	if($result)
	{
		echo "<script>window.alert('登录成功!'); window.location.href='http://192.168.21.102:8080/1/r.html?name=$name';</script>";
		
	}
?>