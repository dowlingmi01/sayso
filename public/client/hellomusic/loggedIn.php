<?php

// the following PHP code is for testing purposes only
// to simulate a hellomusic.com login
// do not deliver to customer

date_default_timezone_set('UTC');
ini_set('session.use_only_cookies', '0');
ini_set('session.use_cookies', '0');
ini_set('session.use_trans_sid', '0');

if (!isset($_COOKIE['CHOMPUID']) || !isset($_COOKIE['MyEmail'])) {
    $randomEmail = substr(str_shuffle('bcdfghjklmnpqrstvwxz'), 0, 1) . substr(str_shuffle('aeiouy'), 0, 1) . substr(str_shuffle('bcdfghjklmnpqrstvwxz'), 0, 2) . substr(str_shuffle('aeiouy'), 0, 1) . substr(str_shuffle('bcdfghjklmnpqrstvwxz'), 0, 1) . substr(str_shuffle('123456789'), 0, 1);
    $randomEmail .= '@hellomusic.com';
    setcookie('CHOMPUID', md5($randomEmail), mktime(0,0,0,12,31,2030), '/');
    setcookie('MyEmail', $randomEmail, mktime(0,0,0,12,31,2030), '/');
} else {
    $randomEmail = $_COOKIE['MyEmail'];
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Hello Music</title>
<link rel="stylesheet" type="text/css" href="css/sayso-onboard.css" />
</head>
<body style="margin: 0; padding: 0; background: url('images/HelloMusicScreenShot.png') top center no-repeat; background-size: 120%;">
    <div id="sayso-onboard">
    	<div id="sso_wrapper">
          	<div id="sso_logo">
            	<a href=""><img src="images/logo_hello-music.png" alt="HelloMusic&trade; Logo" /></a>
            </div><!-- #sso_logo -->
          	<div class="sso_content">
                <h1>Keep The Beat!<br />With the BeatBar</h1>
                <h4>You've been selected as a member of the rhythm<br />
                section keeping Hello Music products and deals in<br />
                sync with you, our customers.</h4>
                <h3><span class="sso_textHighlight">GRAB</span> the BeatBar. <span class="sso_textHighlight">GIVE</span> your opinion.</h3>
                <h2><span class="sso_textHighlight">GET</span> FREE GEAR.</h2>
                <h4 style="height: 50px;">
                <span class="sso_textError" style="display: none;">Whoops. Make sure you agree to the terms<br />
                and conditions before you download.</span>
                </h4>
                <form action="" name="" method="">
                    <p><input type="radio" /> I agree to the <a href="SaySo_TC.pdf" target="_blank">terms and conditions</a></p>
                    <p><a id="sayso-get-app" href="" class="sso_theme_button sso_theme_button_disabled sso_theme_buttonXL">GRAB IT</a></p>
                    <p id="sayso-install-tip" style="font-size: 0.8em; position:relative; top: -3px; display: none;"></p>
                    
                </form>
            </div><!-- .sso_content -->
            <div class="sso_main-image"></div><!-- .sso_main-image -->
        </div><!-- #sso_wraper -->
        <script type="text/javascript" src="js/starbar-onboard.js"></script>
    </div><!-- #sayso-onboard -->
    
    <!-- the following is for testing purposes only, do not deliver to customer -->
    <script type="text/javascript">
        setTimeout(function () { if (typeof window.console !== 'undefined' && typeof window.console.log === 'function') { console.log('<?= $randomEmail ?>');} }, 2000);
    </script>
</body>
</html>
