<?php
session_start();
require('dbconnect.php');

// サインインしているユーザーの情報取得
$sql = 'SELECT * FROM `users` WHERE `id` = ?';
$data = [$_SESSION['49_LearnSNS']['id']];
$stmt = $dbh->prepare($sql);
$stmt -> execute($data);
$signin_user = $stmt->fetch(PDO::FETCH_ASSOC);

// ユーザーの一覧取得
$sql = 'SELECT * FROM `users`';
$stmt = $dbh->prepare($sql);
$stmt -> execute();

$users = [];
while(true) {
    $record = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($record == false) {
        break;
    }

    // 各ユーザーの投稿情報を取得
    $feed_sql = 'SELECT COUNT(*) AS `cnt` FROM `feeds` WHERE `user_id` = ?';
    // user_id = ?に入れるべき値はusersテーブルの角レコードのid
    $feed_data = [$record['id']];
    $feed_stmt = $dbh->prepare($feed_sql);
    $feed_stmt -> execute($feed_data);
    $result = $feed_stmt->fetch(PDO::FETCH_ASSOC);
    // $recordはusersテーブルの各レコード
    // その連想配列にfeed_cntというキーと値を新しく追加
    $record['feed_cnt'] = $result['cnt'];

    $users[] = $record;
}

echo '<pre>';
echo '</pre>';


?>
<?php include('layouts/header.php'); ?>
<body style="margin-top: 60px; background: #E4E6EB;">
    <?php include('navbar.php'); ?>
    <div class="container">
        <?php foreach ($users as $user): ?>
            <div class="row">
                <div class="col-xs-12">
                    <div class="thumbnail">
                        <div class="row">
                            <div class="col-xs-2">
                                <img src="user_profile_img/<?php echo $user['img_name']; ?>" width="80px">
                            </div>
                            <div class="col-xs-10">
                                名前 <a href="profile.php" style="color: #7f7f7f;"><?php echo $user['name']; ?></a>
                                <br>
                                <?php echo $user['created']; ?>からメンバー
                            </div>
                        </div>
                        <div class="row feed_sub">
                            <div class="col-xs-12">
                                <span class="comment_count">つぶやき数：<?php echo $user['feed_cnt']; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</body>
<?php include('layouts/footer.php'); ?>
</html>
