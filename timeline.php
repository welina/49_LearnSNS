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

// echo'<pre>';
// var_dump($signin_user);
// echo '</pre>';

//エラー内容を入れておく配列定義
$errors = [];

//投稿ボタンが押されたら
// = POST送信だったら
if (!empty($_POST)){
    //textareaの値を取り出し
    //$_POSTのキーはtextareaタグのname属性を使う
    $feed = $_POST['feed'];

    //投稿が空かどうか
    if ($feed != '') {
        //投稿処理
        //INSERT INTO テーブル名(カラム名1，カラム名2，…) VALUES(値1，値2，…);
        $sql = 'INSERT INTO `feeds` (`feed`, `user_id`, `created`)VALUES(?, ?, NOW())';
        //?に入る値を列挙
        $data = [$feed, $signin_user['id']];
        //実行するSQLを準備
        $stmt = $dbh->prepare($sql);
        //SQL実行
        $stmt->execute($data);

        //投稿しっぱなしになるのを防ぐため
        header('Location: timeline.php');
        exit();

    } else {
        //エラー
        //「feed」が「空」というエラーを入れておく
        $errors['feed'] = 'blank';
    }
}

//投稿情報を全て取得する
$sql = 'SELECT `f` . * , `u` . `name` , `u` . `img_name` FROM `feeds` AS `f` LEFT JOIN `users` AS `u` ON `f` . `user_id` = `u` . `id` ORDER BY `f` . `created`DESC';
$stmt = $dbh->prepare($sql);
$stmt->execute();

//投稿情報を入れておく列挙
$feeds = [];
while (true) {
    //fetchは一行取得して次の行へ進む
    //取得できた場合は連想配列
    //取得できない場合はfalse
    $record = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($record == false) {
        break;
    }
    $feeds[] = $record;
}

// echo'<pre>';
// var_dump($feeds);
// echo'</pre>';

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
                    <!-- actionが空の時は自分自身にアクセス -->
                    <form method="POST" action="">
                        <div class="form-group">
                            <!--
                            textaraeaは複数行のテキスト
                            input type="text"は1行 -->
                            <textarea name="feed" class="form-control" rows="3" placeholder="Happy Hacking!" style="font-size: 24px;"></textarea><br>

                            <!--
                            条件式
                            ①「feed」にエラーはありますか
                            ②そのエラー内容は「blank」ですか-->
                            <?php if(isset($errors['feed']) && $errors['feed'] == 'blank'):?>
                                <p class="text-danger">投稿データを入力してください</p>
                            <?php endif;?>
                        </div>
                        <input type="submit" value="投稿する" class="btn btn-primary">
                    </form>
                </div>

                <!-- foreach 配列の個数分繰り返し処理が行われる
                     foreach(配列 as 取り出した変数)
                     foreach(複数形 as 単数形) -->
                <?php foreach ($feeds as $feed): ?>
                <div class="thumbnail">
                    <div class="row">
                        <div class="col-xs-1">
                            <img src="user_profile_img/<?php echo $feed['img_name']; ?>" width="40px">
                        </div>
                        <div class="col-xs-11">
                            <a href="profile.php" style="color: #7f7f7f;"><?php echo $feed['name']; ?></a>
                            <?php echo $feed['created']; ?>
                        </div>
                    </div>
                    <div class="row feed_content">
                        <div class="col-xs-12">
                            <span style="font-size: 24px;"><?php echo $feed['feed']; ?></span>
                        </div>
                    </div>
                    <div class="row feed_sub">
                        <div class="col-xs-12">
                            <button class="btn btn-default">いいね！</button>
                            いいね数：
                            <span class="like-count">10</span>
                            <a href="#collapseComment" data-toggle="collapse" aria-expanded="false"><span>コメントする</span></a>
                            <span class="comment-count">コメント数：5</span>

                            <?php if ($feed['user_id'] == $signin_user['id']): ?>
                            <a href="edit.php?feed_id=<?php echo $feed['id']; ?>" class="btn btn-success btn-xs">編集</a>
                            <a onclick="return confirm('ほんとに消すの？');" href="delete.php?feed_id=<?php echo $feed['id']; ?>" class="btn btn-danger btn-xs">削除</a>
                            <?php endif;?>

                        </div>
                        <?php include('comment_view.php'); ?>
                    </div>
                </div>
                <?php endforeach; ?>
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
