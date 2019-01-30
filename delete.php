<?php
require('dbconnect.php');

//削除する投稿IDを取得
$feed_id = $_GET['feed_id'];

//削除SQL
//DELETE FROM テーブル WHERE 条件
$sql = 'DELETE FROM `feeds` WHERE `id` = ?';
$data = [$feed_id];
$stmt = $dbh->prepare($sql);
$stmt->execute($data);


header("Location: timeline.php");
exit();