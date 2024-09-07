<?php
echo '<meta charset="UTF-8">';

phpinfo();

$servername = "rm-wz9ke1m803i4n2f2a.mysql.rds.aliyuncs.com"; // 服务器地址
$username = "linhai"; // 数据库用户名
$password = "Czr9889!"; // 数据库密码
$database = "linhai"; // 数据库名
 
// 创建连接
$conn = new mysqli($servername, $username, $password, $database);
 
// 检查连接
if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}
echo "连接成功";
$conn->close();
?>