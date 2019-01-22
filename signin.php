<?php
session_start();
require('dbconnect.php');

$errors = [];

if (!empty($_POST)){
    $email = $_POST['input_email'];
    $password = $_POST['input_password'];
    if ($email != '' && $password != ''){
        //正常系
        //両方入力されているとき
        //データベースとの照合処理

        //1.入力されたメールアドレスと一致する登録データを1件DBから取得する
        $sql = 'SELECT * FROM `users` WHERE `email` = ?';
        $data = [$email];
        $stmt = $dbh->prepare($sql);
        $stmt->execute($data);
        //$recordはDBの1レコード（横業）にあたいする
        //形式は連想配列
        //キーはカラムに依存する
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        //SERECT文の実行結果がある場合は連想配列
        //結果がない場合はfalseが入る
        if ($record == false){
            $errors['signin'] = 'failed';
        }

        //2.パスワード照合
        //password_verify(文字列，ハッシュ化された文字列)
        //指定した二つの文字列が合致する場合true
        if (password_verify($password, $record['password'])){
            //認証成功
            //3.セッションにユーザーのIDを格納
            $_SESSION['49_LearnSNS']['id'] = $record['id'];

            //4.タイムライン画面に遷移
            header('Location: timeline.php');
            exit();

        }else{
            //認証失敗
            $errors['signin'] = 'failed';
        }

    } else {
        $errors['signin'] = 'blank';
    }
}

?>
<?php include('layouts/header.php'); ?>
<body style="margin-top: 60px">
    <div class="container">
        <div class="row">
            <div class="col-xs-8 col-xs-offset-2 thumbnail">
                <h2 class="text-center content_header">サインイン</h2>
                <form method="POST" action="signin.php" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="email">メールアドレス</label>
                        <input type="email" name="input_email" class="form-control" id="email" placeholder="example@gmail.com">
                    <?php if(isset($errors['signin']) && $errors['signin'] == 'blank'): ?>
                        <p class="text-danger">メールアドレスとパスワードを正しく入力してください</p>
                    <?php endif; ?>

                    <?php if (isset($errors['signin']) && $errors['signin'] == 'failed'): ?>
                        <p class="text-danger">サインインに失敗しました</p>
                    <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="password">パスワード</label>
                        <input type="password" name="input_password" class="form-control" id="password" placeholder="4 ~ 16文字のパスワード">
                    </div>
                    <input type="submit" class="btn btn-info" value="サインイン">
                    <span style="float: right; padding-top: 6px;">
                        <a href="index.php">戻る</a>
                    </span>
                </form>
            </div>
        </div>
    </div>
</body>
<?php include('layouts/header.php'); ?>
</html>
