<?php
session_start();
require('dbconnect.php');

$sql = 'SELECT * FROM `users` WHERE `id` = ?';
$data = [$_SESSION['49_LearnSNS']['id']];
$stmt = $dbh->prepare($sql);
$stmt->execute($data);

$signin_user = $stmt->fetch(PDO::FETCH_ASSOC);

if (isset($_GET['feed_id'])) {
    //編集する投稿情報の取得
    $feed_id = $_GET['feed_id'];

    $sql = 'SELECT `f`.*, `u`.`name`, `u`.`img_name` FROM `feeds` AS `f` LEFT JOIN `users` AS `u` ON `f`.`user_id` = `u`.`id` WHERE `f`.`id` = ?';
    $data = [$feed_id];
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);

    $feed = $stmt->fetch(PDO::FETCH_ASSOC);
}

//編集処理
if (!empty($_POST)) {
    //UPDATE文
    //UPDATE テーブル名 SET カラム名 = 値 WHERE 条件
    $sql = 'UPDATE `feeds` SET `feed` = ? WHERE `id` = ?';
    $data = [$_POST['feed'],$_POST['feed_id']];
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);

    //タイムライン画面へ遷移
    header('Location: timeline.php');
    exit();


}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>Learn SNS</title>
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="assets/font-awesome/css/font-awesome.css">
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
</head>
<body style="margin-top: 60px;">
    <?php include('navbar.php'); ?>
    <div class="container">
        <div class="row">
            <div class="col-xs-4 col-xs-offset-4">
                <form class="form-group" method="post" action="edit.php">
                    <img src="user_profile_img/<?php echo $feed['img_name'];?>" width="60">
                    <?php echo $feed['name'];?><br>
                    <?php echo $feed['created'];?><br>
                    <textarea name="feed" class="form-control"><?php echo $feed['feed'];?></textarea>
                    <input type="hidden" name="feed_id" value="<?php echo $feed['id']; ?>">
                    <input type="submit" value="更新" class="btn btn-warning btn-xs">
                </form>
            </div>
        </div>
    </div>
</body>
<?php include('layouts/footer.php'); ?>
</html>