<?php
session_start();
require('dbconnect.php');

// 一ページあたりの表示件数
// 定数はファイルの一番上に書くのがベター
const CONTENT_PER_PAGE = 5;

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
        //headerはGET送信
        header('Location: timeline.php');
        exit();

    } else {
        //エラー
        //「feed」が「空」というエラーを入れておく
        $errors['feed'] = 'blank';
    }
}

// 初期値
$page = 1;
if (isset($_GET['page'])){
    // ページの指定があった場合、指定地で上書き
    $page = $_GET['page'];
}

// -1などの不正な値を渡された時の対策
$page = max($page, 1);

$sql_count = 'SELECT COUNT(*) AS `cnt` FROM `feeds`';
$stmt_count = $dbh->prepare($sql_count);
$stmt_count->execute();

$record_count = $stmt_count->fetch(PDO::FETCH_ASSOC);

// 最後のページが何ページになるのか算出
// 最後のページ = (取得したページ数 ÷ 1ページあたりの表示件数)の切り上げ
$last_page = ceil($record_count['cnt'] / CONTENT_PER_PAGE);

// 最後のページより大きい値を渡された時の対策
$page = min($page, $last_page);

// スキップするレコード数を算出
// スキップするレコード数 = (指定ページ -1) * 表示件数
$start = ($page - 1) * CONTENT_PER_PAGE;



//投稿情報を全て取得する
$sql = 'SELECT `f` . * , `u` . `name` , `u` . `img_name` FROM `feeds` AS `f`
        LEFT JOIN `users` AS `u` ON `f` . `user_id` = `u` . `id` ORDER BY `f` . `created`DESC
        LIMIT ' . CONTENT_PER_PAGE . ' OFFSET ' . $start;
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
    // 各投稿に対するコメントを取得
    $comment_sql = 'SELECT `c`.*, `u`.`name`,`u`.`img_name`
                 FROM `comments` AS `C` LEFT JOIN `users` AS `u` ON `c`.`user_id` = `u`.`id` WHERE `feed_id` = ?';
    $comment_data = [$record['id']];
    $comment_stmt = $dbh->prepare($comment_sql);
    $comment_stmt->execute($comment_data);

    $comments = [];
    while (true) {
        $comment_record = $comment_stmt->fetch(PDO::FETCH_ASSOC);
        // if文の処理が一行の場合{}を省略できる
        if ($comment_record == false)break;
        $comments[] = $comment_record;
    }
    $record['comments'] = $comments;

    // 各投稿がいいね済みかどうか
    $like_flg_sql = 'SELECT * FROM `likes` WHERE `user_id` = ? AND `feed_id` = ?';
    $like_flg_data = [$signin_user['id'],$record['id']];
    $like_flg_stmt = $dbh->prepare($like_flg_sql);
    $like_flg_stmt->execute($like_flg_data);
    $is_liked = $like_flg_stmt->fetch(PDO::FETCH_ASSOC);

    // 三項演算子
    // 条件 ? 真の時の値 : 偽の時の値
    $record['is_liked'] = $is_liked ? true : false;

    // 投稿に対して何件いいねされているか
    // COUNT(カラム) 件数を取得
    // *は何かしら値があれば、の意味
    // SQLの関数
    $like_sql = 'SELECT COUNT(*) AS `like_cnt` FROM `likes` WHERE `feed_id` = ?';
    $like_data = [$record['id']];
    $like_stmt = $dbh->prepare($like_sql);
    $like_stmt->execute($like_data);
    $like = $like_stmt->fetch(PDO::FETCH_ASSOC);
    $record['like_cnt'] = $like['like_cnt'];

    // コメント数取得
    // $comment_cnt_sql = 'SELECT COUNT(*) AS `comment_cnt` FROM `comments` WHERE `feed_id` = ?';
    // $comment_cnt_data = [$record['id']];
    // $comment_cnt_stmt = $dbh->prepare($comment_cnt_sql);
    // $comment_cnt_stmt->execute($comment_cnt_data);
    // $comment_cnt_result = $comment_cnt_stmt->fetch(PDO::FETCH_ASSOC);
    // $record['comment_cnt'] = $comment_cnt_result['comment_cnt'];


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

    <!-- 誰がサインインしているかを出力 -->
    <span hidden class="signin-user"><?php echo $signin_user['id']; ?></span>
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
                            <!-- どの投稿にいいねがされているか -->
                            <span hidden class="feed-id"><?php echo $feed['id']; ?></span>
                            <?php if ($feed['is_liked']) :?>
                                <button class="btn btn-default js-unlike"><span>いいねを取り消す</span></button>
                            <?php else: ?>
                                <button class="btn btn-default js-like"><span>いいね！</span></button>
                            <?php endif; ?>
                            いいね数：
                            <span class="like-count"><?php echo $feed['like_cnt']?></span>
                            <a href="#collapseComment<?php echo $feed['id'] ;?>" data-toggle="collapse" aria-expanded="false"><span>コメントする</span></a>
                            <span class="comment-count">コメント数：<?php echo count($feed['comments']); ?></span>

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
                        <?php if($page == 1): ?>
                            <li class="previous disabled"><a><span aria-hidden="true">&larr;</span> Newer</a></li>
                        <?php else: ?>
                            <!-- GET送信のパラメータ URL ? キー = バリュー  -->
                            <li class="previous"><a href="timeline.php?page=<?php echo $page - 1; ?>"><span aria-hidden="true">&larr;</span> Newer</a></li>
                        <?php endif;?>
                            <!-- 押せない時 -->
                        <?php if($page == $last_page):?>
                            <li class="next disabled"><a>Older <span aria-hidden="true">&rarr;</span></a></li>
                        <?php else: ?>
                            <!-- 押せる時 -->
                            <li class="next"><a href="timeline.php?page=<?php echo $page + 1; ?>">Older <span aria-hidden="true">&rarr;</span></a></li>
                        <?php endif;?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
<?php include('layouts/footer.php'); ?>
</html>
