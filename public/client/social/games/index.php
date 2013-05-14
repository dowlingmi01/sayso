<?php
	$extids = array( 'development' => 'kcgjipkjdgakogjmbekhghlhdgacajbh'
		, 'sandbox' => 'fjgbjoknbfjhofpcdpfepjaicipncpob'
		, 'demo' => 'poipmplbjibkncgkiaomennpegokfjom'
		, 'staging' => 'dcdkmcnaenolmjcoijjggegpcbehgfkn'
		, 'testing' => 'dachmhjcknkhjkjpknneienbiolpoein'
		, 'production' => 'lpkeinfeenilbldefedbfcdhllhjnblc'
		);
    $extid = $extids[getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production' ];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>social.say.so Installation</title>
<link rel="stylesheet" href="landing.css" />
<link rel="chrome-webstore-item"
    href="https://chrome.google.com/webstore/detail/<?= $extid?>" />
<script src="js/config.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
<script src="js/jquery.cycle.all.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/functions.js"></script>
<style type="text/css">
a:link {
	color: #5e1682;
}
a:hover {
	color: #9413C4;
}
</style>
</head>
<body>
<div id="wrapper">
	<div id="header">
		<div id="logo"><img src="images/logo.png" alt="Social Say.So" /></div><!-- #logo -->
		<div class="community-link"><!--<a href="#">Other Communities</a>--></div><!-- .community-link -->
	</div><!-- #header -->
	<div id="main">

	<div id="sections" class="tab-content">
	  <div class="section tab-pane active" id="section-1">
	  	<div class="section-head">
	  		<h2>What is Say.So?</h2>
	  		<p>Say.so is a browser app that enables you to earn currency by giving your opinion on pop-culture and<br />
                           game-related topics. You can also take fun and challenging online missions for even bigger earnings.<br />
			   Spend your earned currency on awesome rewards and entries in drawings for monthly giveaways!</p>
			</div><!-- .section-head -->

			<div class="slideshow">
				<div class="slides">
					<div class="slide"><img src="images/img_slide_1a.png" alt="" /></div>
					<div class="slide"><img src="images/img_slide_1b.png" alt="" /></div>
                    <div class="slide"><img src="images/img_slide_1c.png" alt="" /></div>
					<div class="slide"><img src="images/img_slide_1d.png" alt="" /></div>
				</div><!-- .slides -->
				<div class="slide-nav">
					<span class="prev">Prev</span>
					<span class="next">Next</span>
				</div><!-- .slide-nav -->
			</div><!-- .slideshow -->

	  </div><!-- #section-1 -->
	  <div class="section tab-pane" id="section-2">
	  	<div class="section-head">
	  		<h2>What can I do?</h2>
	  		<p>Redeem your points (called PaySos) for fun prizes such<br />
                           as a Galaxy Tablet, fun games, or gift cards!</p>
			</div><!-- .section-head -->

			<div class="slideshow">
				<div class="slides">
					<div class="slide"><img src="images/img_slide_2a.png" alt="" /></div>
					<div class="slide"><img src="images/img_slide_2b.png" alt="" /></div>

				</div><!-- .slides -->
				<div class="slide-nav">
					<span class="prev">Prev</span>
					<span class="next">Next</span>
				</div><!-- .slide-nav -->
			</div><!-- .slideshow -->
	  </div><!-- #section-2 -->
	  <div class="section tab-pane" id="section-3">
	  	<div class="section-head">
	  		<h2>What do I get?</h2>
	  		<p>Great Prizes - Redeem your points for fun games, game related goods,<br />
                           gift cards, and a whole lot more! Get smaller items right away or save<br />
			   up your points for something really big, like a Galaxy Tablet!</p>
			</div><!-- .section-head -->

			<div class="slideshow">
				<div class="slides">
					<div class="slide"><img src="images/img_slide_3a.png" alt="" /></div>
					<div class="slide"><img src="images/img_slide_3b.png" alt="" /></div>
				</div><!-- .slides -->
				<div class="slide-nav">
					<span class="prev">Prev</span>
					<span class="next">Next</span>
				</div><!-- .slide-nav -->
			</div><!-- .slideshow -->

	  </div><!-- #section-3 -->
	  <div class="section tab-pane" id="section-4">
	  	<div class="section-head">
	  	<div id="create-password">
	  		<h2>Join Say.So!</h2>
	  		<p>Get the Say.So app! <br /> 
			   Start by creating your unique Say.So user account and password, <br /> 
			   so we can make sure your points and rewards are saved.</p>
	  		<br />
	  		<form>
	  			<p><input type="text" id="input-email" class="sso_fld" placeholder="Enter a valid email address" />
	  			<input type="text" id="input-email_txt" value="Enter a valid email address" /></p>
	  			<p><input type="password" id="input-password" class="sso_fld" placeholder="Enter your desired password. (6-12 characters)" />
	  			<input type="text" id="input-password_txt" value="Enter your desired password. (6-12 characters)" /></p>
	  			<p><input type="password" id="input-confirmation" class="sso_fld" placeholder="Verify your password" />
	  			<input type="text" id="input-confirmation_txt" value="Verify your password" /></p>
	  			<p><input type="checkbox" value="" id="agreeterms" /><label for="agreeterms">I agree to the <a href="http://app.saysollc.com/docs/social/Say.So_App_EULA.pdf" target="_blank">terms and conditions</a>.</label></p>
	  			<input type="submit" value="Grab It" disabled="" id="btn-submit" class="grab-it" />
	  		</form>
	  		<p><small>We don't share your email or password with anyone...period.</small><br />
			   <small> Please allow the browser to install the Say.So app.</small></p>
			
	  		</div>
			  <div id="password-created" style="display: none;">
	  			<p>Your Say.So account was created. You can now install the Say.So app.</p>
	  			<form>
	  				<input type="submit" value="Install" id="btn-install" class="grab-it" />
	  			</form>

			  </div>
			  <div id="after-redirect" style="display: none;">
	  			<p id="install-instructions">Please allow the browser to install the app.</p>
	  			<p>If your download didn't start, please click <a id="download-retry">here</a></p>
			  </div>
			  <div id="no-install" style="display: none;">
			  	<p></p>
			  </div>
	  	</div><!-- .section-head -->
	  </div><!-- #section-4 -->
	</div><!-- #sections -->

	<div id="jump"><a href="#section-4" data-class="section-4">Install the Say.so app now!</a></div>

	<div id="section-nav">
		<ul>
		  <li class="active"><a href="#section-1" data-class="section-1">What is Say.so</a></li>
		  <li><a href="#section-2" data-class="section-2">What Can I Do?</a></li>
		  <li><a href="#section-3" data-class="section-3">What Do I Get?</a></li>
		  <li><a href="#section-4" data-class="section-4">Get Started</a></li>
		</ul>
	</div><!-- #section-nav -->


	</div><!-- #main -->
</div><!-- #wrapper -->
</body>
</html>
