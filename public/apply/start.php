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
            <div class="wrapper-ttl pt20"><h2 class="center">LOGIN</h2></div>
			</div><!--page-ttl -->


            <div class="login-container">

                <form action="index.php" method="post">
                    
                    <div class="form-content">
                        
                        <div class="item">
                            <div class="title">代理店ID</div>
                            <input type="text" id="corp" name="corp" placeholder="代理店ID">
                        </div>
                        <div class="item">
                            <div class="title">ユーザーID</div>
                            <input type="text" id="user" name="user" placeholder="ユーザーID">
                        </div>

                        <div class="item">
                            <div class="title">パスワード</div>
                            <input type="password" id="password" name="password" placeholder="password">
                        </div>

                        <button type="submit">Log in</button>

                    </div>
    
                </form>

            </div>




            <div id="footer_out">
                <div id="footer">




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
    <div id="page-top">
        <div>
            <a href="#wrapper">
            <p id="img">PAGE TOP</p>
            </a>
        </div>
    </div>


</body>
</html>