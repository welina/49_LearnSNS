<?php
session_start();
require('dbconnect.php');

//サインインしていなければsignin.phpへ強制遷移
if (!isset($_SESSION['49_LearnSNS']['id'])) {
    //signin.phpへ強制遷移
    header('Location: signin.php');
    exit();
}

$sql = 'SELECT * FROM `users` WHERE `id` = ?';
$data = [$_SESSION['49_LearnSNS']['id']];
$stmt = $dbh->prepare($sql);
$stmt->execute($data);

// -> アロー演算子
// インスタンスにのメンバメソッドを呼び出す
$signin_user = $stmt->fetch(PDO::FETCH_ASSOC);

echo'<pre>';
var_dump($signin_user);
echo '</pre>';

?>

<!--
    include(ファイル名);
    指定されたファイルが指定された箇所に組み込まれる
    Webサービス内で共通するような場所は他のファイルで定義をして、様々なページから利用可能にするべき

    includeとrequireの違い
    プロスラムに記述ミスがある場合
    requireはエラー(処理が止まる)
    includeは警告(処理は続行可能)

    includeされたファイル内では呼び出し元の変数が利用できる
-->

<?php include('layouts/header.php'); ?>
<body style="margin-top: 60px; background: #E4E6EB;">
    <?php include('navbar.php'); ?>
    <div class="container">
        <div class="row">
            <div class="col-xs-3">
                <ul class="nav nav-pills nav-stacked">
                    <li class="active"><a href="timeline.php?feed_select=news">新着順</a></li>
                    <li><a href="timeline.php?feed_select=likes">いいね！済み</a></li>
                </ul>
            </div>
            <div class="col-xs-9">
                <div class="feed_form thumbnail">
                    <form method="POST" action="">
                        <div class="form-group">
                            <textarea name="feed" class="form-control" rows="3" placeholder="Happy Hacking!" style="font-size: 24px;"></textarea><br>
                        </div>
                        <input type="submit" value="投稿する" class="btn btn-primary">
                    </form>
                </div>
                <div class="thumbnail">
                    <div class="row">
                        <div class="col-xs-1">
                            <img src="user_profile_img/misae.png" width="40px">
                        </div>
                        <div class="col-xs-11">
                            <a href="profile.php" style="color: #7f7f7f;">野原みさえ</a>
                            2018-10-14
                        </div>
                    </div>
                    <div class="row feed_content">
                        <div class="col-xs-12">
                            <span style="font-size: 24px;">LearnSNSの開発頑張ろう！</span>
                        </div>
                    </div>
                    <div class="row feed_sub">
                        <div class="col-xs-12">
                            <button class="btn btn-default">いいね！</button>
                            いいね数：
                            <span class="like-count">10</span>
                            <a href="#collapseComment" data-toggle="collapse" aria-expanded="false"><span>コメントする</span></a>
                            <span class="comment-count">コメント数：5</span>
                            <a href="edit.php" class="btn btn-success btn-xs">編集</a>
                            <a onclick="return confirm('ほんとに消すの？');" href="#" class="btn btn-danger btn-xs">削除</a>
                        </div>
                        <?php include('comment_view.php'); ?>
                    </div>
                </div>
                <div aria-label="Page navigation">
                    <ul class="pager">
                        <li class="previous disabled"><a><span aria-hidden="true">&larr;</span> Newer</a></li>
                        <li class="next disabled"><a>Older <span aria-hidden="true">&rarr;</span></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
<?php include('layouts/footer.php'); ?>
</html>
