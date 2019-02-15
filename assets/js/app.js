// HTMLが全て読み込まれたら実行される
$(function(){
  // DOM.on(イベント,クラスorID,処理)
  // docomentoはHTML全体を指す
  $(document).on('click', '.js-like', function(){
    console.log('ボタンが押されました')
    // 誰がいいねを押したか
    let user_id = $('.signin-user').text()
    // どの投稿がいいねされたかを取得
    // this = 押されたボタン自身(いいねボタン)
    // DOM.siblings(クラス名)
    // DOMから見て兄弟要素の中で指定されたクラス名を持っている要素を取得する
    let feed_id = $(this).siblings('.feed-id').text()

    // ボタンが押されたタイミングでいいね数を増やす
    let like_btn = $(this)
    let like_count = $(this).siblings('.like-count').text()

    // 非同期通信(Ajax)
    // $.ajax(送信先や送信するデータ)
    // .done(成功時の処理)
    // .fail(失敗時の処理)
    $.ajax({
      url: 'like.php',     //送信先
      type: 'POST',        //送信先メソッド
      datatype: 'json',    //データのタイプ
      data: {              //送信するデータ
        'feed_id': feed_id,
        'user_id': user_id
      }
    }).done(function(data){
      // 成功時の処理
      // dataはサーバーからのレスポンス
      if (data){
        like_count++
        like_btn.siblings('.like-count').text(like_count)
      }
      console.log(data)
    }).fail(function(e){
      // 失敗時の処理
      // eはサーバーから返されたエラー
      console.log(e)
    })
  })
})