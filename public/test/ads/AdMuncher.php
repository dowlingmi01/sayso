<?php

// SEE test.php AND lib/ScriptAggregator FOR ADD'L PROGRESS WITH THIS EFFORT

set_include_path(get_include_path() . PATH_SEPARATOR . '/usr/local/zend/share/ZendFramework/library');

require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance();

// copied ad tag (probably comes from the database)
$ad = '<div id="bannerAd_rdr">
  <div id="bannerAd_fmt" class="loaded">
	<iframe width="728" scrolling="no" height="90" frameborder="0" src="http://as.webmd.com/html.ng/transactionID=712308489&amp;tile=1929093061&amp;tug=&amp;site=2&amp;affiliate=20&amp;hcent=947&amp;scent=&amp;pos=101&amp;xpg=1660&amp;sec=1660&amp;au1=1&amp;au2=1&amp;uri=%2fasthma%2fdefault&amp;artid=091e9c5e8010c6af&amp;inst=0&amp;leaf=&amp;cc=95&amp;tmg=" style="margin:0;" marginheight="0" marginwidth="0" title="Top Advertisement Frame" id="bannerAd_Iframe">
	  &lt;script language="JavaScript1.2" type="text/javascript" charset="ISO-8859-1" src="http://as.webmd.com/js.ng/Params.richmedia=yes&amp;amp;transactionID=712308489&amp;amp;tile=1929093061&amp;amp;tug=&amp;amp;site=2&amp;amp;affiliate=20&amp;amp;hcent=947&amp;amp;scent=&amp;amp;pos=101&amp;amp;xpg=1660&amp;amp;sec=1660&amp;amp;au1=1&amp;amp;au2=1&amp;amp;uri=%2fasthma%2fdefault&amp;amp;artid=091e9c5e8010c6af&amp;amp;inst=0&amp;amp;leaf=&amp;amp;cc=95&amp;amp;tmg="&gt;
	  &lt;/script&gt;
	</iframe>
	<script type="text/javascript">
					$("#bannerAd_Iframe").load(function() { $("#bannerAd_fmt").addClass("loaded"); $(".bannerAd_fmt").addClass("loaded"); });
				</script>
  </div>
</div>';

// grab the iframe URL
$matches = array();
preg_match('/iframe.+src="([^"]+)"/', $ad, $matches);
$iframeUrl = html_entity_decode($matches[1]);
out($iframeUrl);

// setup URI and HTTP client
$client = new Zend_Http_Client();
$client->setUri(Zend_Uri::factory($iframeUrl));


$test = preg_replace('/\s+/', ' ', '<script type="text/javascript">
		var pubId=26151;
		var siteId=26177;
		var kadId=21014;
		var kadwidth=728;
		var kadheight=90;
		var kadtype=1;
		alert("asdf");
		</script>');
$test = preg_replace('/\s+/', ' ', '
		var pubId=26151;
		var siteId=26177;
		var kadId=21014;
		var kadwidth=728;
		var kadheight=90;
		var kadtype=1;');
//$test = urlencode($test);
$url = 'http://localhost:8124';
//exit($url);
//$client->setUri(Zend_Uri::factory($url));
//$client->setParameterPost('js', $test);

// get the content
$response = $client->request('POST');
$body = $response->getBody();

//echo $body;


// check for different ad types
if (preg_match('/ads\.pubmatic/i', $body)) { // served via PubMatic
	echo 'Pubmatic';
	/*
	<script type="text/javascript">
		if ("html" == "html"){ 
		document.bgColor = 'transparent';
		}
	</script>
	<div style="text-align: center;"><!-- PubMatic ad tag (Javascript) : WebMD_Consumer_RON_728x90_ATF_INTL | http://www.WebMD.com/consumer/RON_INTL | 728 x 90 Leaderboard -->
		<script type="text/javascript">
		var pubId=26151;
		var siteId=26177;
		var kadId=21014;
		var kadwidth=728;
		var kadheight=90;
		var kadtype=1;
		</script>
		<script type="text/javascript" src="http://ads.pubmatic.com/AdServer/js/showad.js">
 		</script>
 	</div>
 */
	
	$matches2 = array();
	preg_match_all('#<script.*?type="text/javascript".*?>((?:.|\s)*?)</script>#', $body, $matches2);
	if (count($matches2[0]) && count($matches2[1])) {
		foreach ($matches2[1] as $key => $value) {
			if (trim($value)) {
				// JS script
				
			} else {
				// JS include
			}
		}
	}
} else if (false) { // continue checking for other ad sources
	
}

out($response->getBody());
function out($content) {
	echo "<pre>$content</pre>" . PHP_EOL;
}

