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
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>machinima.say.so Installation</title>
<link rel="stylesheet" href="machinima-landing.css" />
<link rel="chrome-webstore-item"
    href="https://chrome.google.com/webstore/detail/<?= $extid?>" />
<script src="js/config.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
<script src="js/jquery.cycle.all.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/functions.js"></script>
</head>
<body>
<div id="wrapper">
	<div id="header">
		<div id="logo"><img src="images/logo.png" alt="Machinima Say.So" /></div><!-- #logo -->
		<div class="community-link"><!--<a href="#">Other Communities</a>--></div><!-- .community-link -->
	</div><!-- #header -->
	<div id="main">

	<div id="sections" class="tab-content">
	  <div class="section tab-pane active" id="section-1">
	  	<div class="section-head">
	  		<h2>What is Machinima Recon?</h2>
	  		<p>You have been selected to join Machinma Recon, an exclusive community brought to you by Machinima! Explore Machinima's amazing content, give us your opinions, and earn rewards while you do it!</p>

			</div><!-- .section-head -->

			<div class="slideshow">
				<div class="slides">
               		 <div class="slide"><img src="images/img_slide_intro.png" alt="" /></div>
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
	  		<p>Earn Coins by taking polls, answering surveys and giving your opinion on Machinima content.  Redeem Coins for items in our Reward Center.</p>
			</div><!-- .section-head -->

			<div class="slideshow">
				<div class="slides">
					<div class="slide"><img src="images/img_slide_2a.png" alt="" /></div>
					<div class="slide"><img src="images/img_slide_2b.png" alt="" /></div>
					<div class="slide"><img src="images/img_slide_2c.png" alt="" /></div>
					<div class="slide"><img src="images/img_slide_2d.png" alt="" /></div>
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
	  		<p>Great Prizes - The Reward Center is full of prizes designed specifically for Machinima fans.</p>
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
	  		<h2>Let's get started</h2>
	  		<p>Start by creating your unique Say.So password so we can make sure your points and rewards and saved.</p>
	  		<br />
	  		<form>
	  			<p><input type="text" id="input-email" class="sso_fld" placeholder="Enter a valid email address" />
	  			<input type="text" id="input-email_txt" value="Enter a valid email address" /></p>
	  			<p><input type="password" id="input-password" class="sso_fld" placeholder="Enter your desired password. (6-12 characters)" />
	  			<input type="text" id="input-password_txt" value="Enter your desired password. (6-12 characters)" /></p>
	  			<p><input type="password" id="input-confirmation" class="sso_fld" placeholder="Verify your password" />
	  			<input type="text" id="input-confirmation_txt" value="Verify your password" /></p>
	  			<p><input type="checkbox" value="" id="agreeterms" /><label for="agreeterms">I agree to the <a href="http://app.saysollc.com/docs/machinima/Say.So_App_EULA.pdf" target="_blank">terms and conditions</a>.</label></p>
	  			<input type="submit" value="Grab It" disabled="" id="btn-submit" class="grab-it" />
	  		</form>
	  		<p><small>We don't share your email or password with anyone...period.</small></p>
	  		</div>
			  <div id="password-created" style="display: none;">
			  <p>Your Say.So password was created. You can now install the extension.</p>
	  			<form>
	  				<input type="submit" value="Install" id="btn-install" class="grab-it" />
	  			</form>

			  </div>
			  <div id="after-redirect" style="display: none;">
	  			<p id="install-instructions">Please allow the browser to install the extension.</p>
	  			<p>If your download didn't start, please click <a id="download-retry">here</a></p>
			  </div>
			  <div id="no-install" style="display: none;">
			  	<p></p>
			  </div>
	  	</div><!-- .section-head -->
	  </div><!-- #section-4 -->
	</div><!-- #sections -->
	<div id="jump"><a href="#section-4" data-class="section-4">I’m ready to start now!</a></div>

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
