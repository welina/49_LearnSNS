<?php
require('dbconnect.php');

// app.jsからAjaxでPOST送信された値の取得
$feed_id = $_POST['feed_id'];
$user_id = $_POST['user_id'];

if(isset($_POST['is_unliked'])){
$sql = 'DELETE FROM `likes` WHERE `feed_id` = ? AND `user_id` = ?';
}else{
$sql = 'INSERT INTO `likes`(`feed_id`,`user_id`)VALUES(?,?)';
}

$data = [$feed_id, $user_id];
$stmt = $dbh->prepare($sql);
$res = $stmt->execute($data);

// 一番最後の出力がレスポンスとして返される
echo json_encode($res);