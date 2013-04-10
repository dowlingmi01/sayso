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
	  		<h2>What is Social Say.So?</h2>
	  		<p>Do you eagerly await new quests in <b>SimCity Social</b>, love building your social <br />
                           circle in <b> The Sims Social</b>, relentlessly work for new items in <b>Farmville</b>,<br />
	  		   or can't wait for a sequel to <b>Plants vs. Zombies</b>? <br />
                           Do you want to give us your thoughts on social gaming and earn great  rewards for doing so?<br />
	  		   If this sounds like you, then you're probably a perfect fit for Social Say.So!</p>
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
	  		<p>Earn redeemable points (Social PaySos) by taking polls, answering surveys and giving your opinion<br />
                           on social, casual and mobile gaming. Redeem Social PaySos for items in our Rewards Center.</p>
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
	  		<p>Great Prizes - Redeem your points for <b>games</b>, <b> game related goods</b>,<br />
                        <b>gift cards</b>, and a whole lot more!<br />
                           The Rewards Center is full of prizes designed specifically for social game fanatics.</p>
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
	  		<p>Start by creating your unique Say.So password so we can make sure your
points and rewards are saved. "Please allow the browser to install the
extension.</p>
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
