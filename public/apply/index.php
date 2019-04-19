<?php

include_once("../../lib/table.class.php");
include_once("../../lib/class.php");
include_once("../../lib/validate.class.php");
include_once("../../lib/arr.class.php");
include_once("../../lib/form.class.php");
include_once("../../lib/convert.class.php");

//call pdo
$dbManager = new DbManager2();
$dbh = $dbManager->getDbh();

//初期化
$table = "Apply";
$columns = $dbManager->getColumns($table);
$columns[] = "generation";
$columns[] = "retype_instagram_pw";

//from mailaddressaddress
define("corp_name_mailaddressADDRESS","kobayashi@share-tree.com");

//フォーム部分の切り替え用
$mode = "input";

//error check
$error = array();

if(isset($_POST['confirm'])){
    $datas = [];

    //set value
    foreach ($columns as $column) {
      if( isset($_POST[$column]) ){
        $datas[$column] = getParam($column);
      }
    }

    $form = new ApplyForm($datas, $mode);

    //error check
    $valid = new Validator($datas);

    $set_validate_rules = [
      "mailaddress" => ["email"]
      ,"mobile_phone" => ["number"]
      ,"post_number" => ["number"]
      ,"instagram_pw" => ["retype"]
    ];
    setrules($form, $valid, $set_validate_rules);

    try {
      $valid->validate();
      $error = $valid->getErrmsg();
    } catch (Exception $e) {
      $error[] = "エラーチェックに失敗(".$e->getMessage().")";
    }

    //エラーがなければ確認画面へ
    if(empty($error)){
        //処理
        $mode = "confirm";
        $form->setmode($mode);
    }

}else if(isset($_POST['send'])){

    $mode = "confirm";
    $datas = [];

    //set value
    foreach ($columns as $column) {
      if( isset($_POST[$column]) ){
        $datas[$column] = getParam($column);
      }
    }

    $form = new ApplyForm($datas, $mode);

    //error check
    $valid = new Validator($datas);

    $set_validate_rules = [
      "mailaddress" => ["email"]
      ,"mobile_phone" => ["number"]
      ,"post_number" => ["number"]
    ];
    setrules($form, $valid, $set_validate_rules);

    try {
      $valid->validate();
      $error = $valid->getErrmsg();
    } catch (Exception $e) {
      $error[] = "エラーチェックに失敗(".$e->getMessage().")";
    }

    //エラーがなければ登録
    if(empty($error)){

      //call AutoUpdater
      $table = "Apply";
      $update_options = [];
      $code = "Apply insert";
      $datas = adjustDbColumns($datas);
      if( $dbManager->insert($table, $datas, $update_options, $code) ){
        $mode = "done";
      }else {
        $error[] = "登録に失敗しました。";
      }

    }

}else{
  $form = new ApplyForm($datas, $mode);
}



function h($string){
    return htmlspecialchars($string);
}

function getParam($column){
  $ret = "";
  if ( isset($_POST[$column]) && !empty($_POST[$column]) ) {
    if ( is_array($_POST[$column]) ) {
      foreach ($_POST[$column] as $key => $value) {
        $ret[$key] = Converter::trim_all($value);
      }
    }else{
      $ret = Converter::trim_all($_POST[$column]);
    }
  }
  return $ret;
}

function adjustDbColumns($datas)
{
  $ret = [];

  foreach ($datas as $key => $value) {
    switch ($key) {
      case 'agreement':
        $ret[$key] = $value[0];
      break;

      case 'generation':
        foreach ($value as $header) {
          $col_name = "{$header}0s";
          $ret[$col_name] = 1;
        }
        break;

      case 'instagram_pw':
        $ret[$key] = Converter::encrypt($value);;
        break;

      case 'mobile_phone':
      case 'post_number':
        $ret[$key] = implode("", $value);
        break;

      default:
        $ret[$key] = $value;
        break;
    }
  }

  return $ret;
}

function setrules($form, $valid, $set_rules){
  foreach ($form->getItemConditions() as $condition) {
    $rules = [];

    // 必須
    if(
      (isset($condition["required"]) && $condition["required"] == 1)
      || isset($_POST['required'][$condition["name"]])
    ){
      $rules[] = 'required';
    }

    //　個別ルール
    if(isset($set_rules[$condition["name"]])){
      $rules = array_merge($rules, $set_rules[$condition["name"]]);
    }

    //ルールのセット
    $valid->rule($condition["name"], $condition["label"], $rules);
  }
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-Type" content="text/html; charset=utf-8">
<title>instime</title>
<meta name="description" conten="">
<meta name="keywords" conten="instime, instime, instime">
<script type="text/javascript">
if ((navigator.userAgent.indexOf('iPhone') > 0) || navigator.userAgent.indexOf('iPod') > 0 || navigator.userAgent.indexOf('Android') > 0) {
        document.write('<meta name="viewport" content="width=device-width, initial-scale=1">');
    }else{
        document.write('<meta name="viewport" content="width=1000" />');
    }
</script>

<link rel="stylesheet" href="css/style.css" media="screen and (min-width : 769px)">
<link rel="stylesheet" href="css/style_sm.css" media="screen and (max-width : 768px)">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<link href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" rel="stylesheet">

  <style>

    input.button-submit{
      border-radius: 4px;
      color: #fff;
      box-shadow: 0 3px 3px rgba(0, 0, 0, .2), inset 0 1px 1px rgba(255, 255, 255, .7);
      margin: 0 auto;
      background-color: #ccaa66;
      font-family: "Lato", Helvetica, Arial, sans-serif;
    }
    input.button-submit:hover{
      background-color: rgba(255, 255, 255, .3);
      color: #333;
    }

    input.button-submit.button[disabled="disabled"] {
      opacity: 0.5;
    }
    input.button-submit.button[disabled="disabled"]:hover {
      background-color: #ccaa66;
      color: #fff;
    }

    .message{
        border: 1px solid #ffc9c9;
        border-radius: 5px;
        padding: 5px;
        background: #ffe4e4e6;
        color: #aa2424;
    }
    .message li{
        list-style-type: none;
        padding: 5px;
    }

    div.done{
        padding: 20px;
        text-align: center;
    }

    label {
      font-weight: normal;
    }

    input[type="checkbox"],
    input[type="radio"] {
      margin-left: 10px;
    }

    .width50 {
      width: 50px;
      display: inline;
    }

    .width60 {
      width: 60px;
      display: inline;
    }

    .table-no-border td span.loading {
      display: inline;
      vertical-align: text-top;
    }

    .policy_url {
      margin-left: 10px;
    }

    .policy_url:hover {
      text-decoration: underline;
      opacity: 0.7;
    }

  </style>
<script type="text/javascript">
$(function() {
    $(".nav").css("display","none");
    $(".menu_button").on("click", function() {
        $(".nav").slideToggle();
    });
});
</script>
<script src="js/search_addresses.js" charset="utf-8"></script>
</head>
<!-- HTML部分 -->
<body>
    <!-- all -->
    <div id="all">
        <!-- container -->
      <div id="container">

            <div id="header">
              <div id="header_in">
                <p>more fun, more instime</p>
                <!--<a href="#" class="header_btn">お問い合わせはこちら</a>-->
                <div id="nav">
                    <ul>
                        <li id="mm_4"><a href="index.php"><h1 style="margin-top: 22px;"><img src="img/logo.png" /></h1></a></li>
                        <li id="mm_1"><a href="index.php">申込フォーム</a></li>
                        <li id="mm_2"><a href="about.html">よくある質問</a></li>

                    </ul>

                    <div class="clear"></div>
                </div>
              </div>
            </div>
            <!-- #header -->

        <div id="header_sm">
            <p class="txt">more fun, more instime</p>
            <h1><a href="index.php"><img src="img/logo.png" /></a></h1>
            <p class="menu"><a href="javascript:void(0)" class="menu_button"><img src="img/header_sp_link03.png" /></a></p>
            <nav class="nav">
                <ul>
                        <li><a href="index.php">申込フォーム</a></li>
                        <li><a href="about.html">よくある質問</a></li>
                </ul>
            </nav>

        </div>
            <!-- #header -->
            <div id="second">
            <div id="page-ttl" class="about">
            <div class="wrapper-ttl pt20"><h2 class="center">instimeのお申込み</h2></div>
			</div><!--page-ttl -->

            <div id="form">
            <h3 class="">申込フォーム</h3>
                <div class="container">
                    <ul class="progressbar">
                        <li class="<?php echo ($mode == "input" || $mode == "confirm" || $mode =="done") ? "active" : "";  ?>">入力画面</li>
                        <li class="<?php echo ($mode == "confirm" || $mode =="done") ? "active" : "";  ?>">確認画面</li>
                        <li class="<?php echo ($mode =="done") ? "active" : "";  ?>">登録完了</li>
                    </ul>
                    <div class="clear mb10 mb5s"></div>
                    <!--message area-->

                        <?php
                            if(!empty($error)){
                                echo '<div class="message">';
                                foreach ($error as $key => $msg) {
                                    echo "<li>".$msg."</li>";
                                }
                                echo '</div>';
                            }
                        ?>

                </div><br>
                <!--タブここから-->
                <div class='panel panel-default tab-pane active'>
                    <div class='panel-body'>
                        <form class='form-horizontal' action="" method="post">
                            <?php if($mode == "input"){ ?>

                              <table width="0" cellspacing="0" cellpadding="0" class="table table-no-border" style="max-width:1000px; margin:0 auto;">
                                <?php
                                echo $form->getFormContext();
                                ?>
                                </table>
                                <div style="text-align: center;">
                                    <input type="submit" class="button button-submit" name="confirm" value="入力内容を確認する">
                                </div>

                            <?php  }else if($mode == "confirm"){ ?>

                            <p style="text-align: center; margin: 20px;">以下の内容で申込を完了します。よろしいですか？</p>
                              <table width="0" cellspacing="0" cellpadding="0" class="table table-no-border">
                                <?php
                                  echo $form->getFormContext();
                                ?>
                                </table>
                                <div style="text-align: center;">
                                    <?php
                                      $form->setmode("hidden");
                                      echo $form->getFormContext();
                                     ?>
                                    <input type="submit" class="button button-submit" name="send" value="送信する">
                                </div>

                            <?php }else if($mode == "done"){ ?>

                                <div class="done">

                                    お申込みを受付いたしました。
                                    ありがとうございました。<br><br>
                                    今後の流れ・・・

                                </div>

                             <?php } ?>

                        </form>
                    </div>
                </div>

            </div>


            </div>
            <div id="footer_out">
                <div id="footer">


            <div id="footer_menu">
                <ul class="footer_menu_in">
                    <li><a href="index.php">申込フォーム</a></li>
                    <li><a href="about.html">よくある質問</a></li>
                </ul>
            </div>

                </div>
                <!-- #footer -->
                <p id="copyright"><span>&copy; 2019- <a href="http://instime.jp">instime Inc.</a>, All rights reserved.</span></p>
            </div>
            <!-- #footer_out -->

        </div>
        <!-- /container -->
    </div>
    <!-- /all -->

	<script type="text/javascript">
    $(function() {
		//スクロールが300に達したらボタン表示
		var topBtn = $('#page-top');
			topBtn.hide();
			$(window).scroll(function () {
			if ($(this).scrollTop() > 300) {
			topBtn.fadeIn();
			} else {
			topBtn.fadeOut();
			}
		});
			//スクロールしてトップ
			topBtn.click(function () {
			$('body,html').animate({
			scrollTop: 0
			}, 800);
			return false;
		});
    });
    </script>
    <script>
	$(function(){
    $('a[href*=#], area[href*=#]').not(".noScroll").click(function() {

        var speed = 400, // ミリ秒(この値を変えるとスピードが変わる)
            href = $(this).prop("href"), //リンク先を絶対パスとして取得
            hrefPageUrl = href.split("#")[0], //リンク先を絶対パスについて、#より前のURLを取得
            currentUrl = location.href, //現在のページの絶対パスを取得
            currentUrl = currentUrl.split("#")[0]; //現在のページの絶対パスについて、#より前のURLを取得

        //#より前の絶対パスが、リンク先と現在のページで同じだったらスムーススクロールを実行
        if(hrefPageUrl == currentUrl){

            //リンク先の#からあとの値を取得
            href = href.split("#");
            href = href.pop();
            href = "#" + href;

            //スムースクロールの実装
            var target = $(href == "#" || href == "" ? 'html' : href),
                position = target.offset().top, //targetの位置を取得
                body = 'body',
                userAgent = window.navigator.userAgent.toLowerCase();
            if (userAgent.indexOf('msie') > -1 || userAgent.indexOf('trident') > -1 || userAgent.indexOf("firefox") > -1 ) { /*IE8.9.10.11*/
                body = 'html';
            }
            $(body).animate({
                scrollTop: position
            }, speed, 'swing', function() {
                //スムーススクロールを行ったあとに、アドレスを変更(アドレスを変えたくない場合はここを削除)
                if(href != "#top" && href !="#") {
                    location.href = href;
                }
            });

            return false;
        }

    });

    if( $('input[name="customer_type"]:checked').val() != "1" ){
      var tr_elm = $('input[name="customer_type"]:checked').parents('tbody');
      var target_elm = tr_elm.find('th label[for="corp_name"]');
      target_elm.find('span').remove();
      target_elm.find('input[type="hidden"]').remove();
      var html = '<span>必須</span><input type="hidden" name="required[corp_name]" value="1">';
      target_elm.append(html);
    }

    $('input[name="customer_type"]').click(function(){
      var tr_elm = $(this).parents('tbody');
      var target_elm = tr_elm.find('th label[for="corp_name"]');
      target_elm.find('span').remove();
      target_elm.find('input[type="hidden"]').remove();
      var html = '<span>必須</span><input type="hidden" name="required[corp_name]" value="1">';
      if($(this).val() != "1"){
        target_elm.append(html);
      }
    });

    $('input[name*="retype"]' + 'input[type="password"]').on("input", function(){
      var pw = $(this).prev();
      var parent = $(this).parent();
      var re_pw = $(this);
      var submit = $('input[type="submit"]' + 'input[name="confirm"]');
      var html = '<span style="color:crimson;float:left">確認用パスワードとパスワードが一致しません</span>';
      parent.find("span:last-of-type").remove();
      submit.attr("disabled", "disabled");
      if( pw.val() == re_pw.val() ){
        re_pw.css({"background-color":"white"});
        parent.find("span:last-of-type").remove();
        submit.removeAttr("disabled");
      }else if(pw.val() != ""){
        re_pw.css({"background-color":"#FFDDFF"});
        parent.append(html);
        submit.attr("disabled", "disabled");
      }
    });

});
</script>
    <div id="page-top">
        <div>
            <a href="#wrapper">
            <p id="img">PAGE TOP</p>
            </a>
        </div>
    </div>


</body>
</html>