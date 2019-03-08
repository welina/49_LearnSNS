<?php
session_start();
require('dbconnect.php');

// コメントの保存処理
// 必要な値
//   誰が：コメントしたユーザーのID = サインインしているユーザーのID
//   どれに：コメントする投稿のID
//   なんて：コメント内容

$user_id = $_SESSION['49_LearnSNS']['id'];
$feed_id = $_POST['feed_id'];
$comment = $_POST['write_comment'];

// DBへレコードを挿入
$sql = 'INSERT INTO `comments` (`user_id`, `feed_id`, `comment`, `created`) VALUES (?, ?, ?, NOW())';
$data = [$user_id, $feed_id, $comment];
$stmt = $dbh->prepare($sql);
$stmt->execute($data);

header('Location: timeline.php');
exit();