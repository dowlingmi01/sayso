<?php

require_once dirname(__FILE__) . '/ads/lib/ScriptAggregator.php';

set_include_path(get_include_path() . PATH_SEPARATOR . '/usr/local/zend/share/ZendFramework/library');

require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance();

// create a new ScriptAggregator with a chunk of DOM
$aggregator = new ScriptAggregator('<script type="text/javascript"> 
	if ("html" == "html"){ 
	document.bgColor = "transparent";
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
</div>');

echo $aggregator . PHP_EOL . PHP_EOL;

$client = new Zend_Http_Client();
$client->setUri(Zend_Uri::factory('http://localhost:8124'));

// iterate over the scripts (pulled from the aggregator)
foreach ($aggregator as $index => $script) {
	/* @var $script Script */
	// if it's an include type, then pull it via http request
	if ($script->getType() === Script::TYPE_INCLUDE) {
		$scriptClient = new Zend_Http_Client(Zend_Uri::factory((string) $script));
		$rawScript = $scriptClient->request('GET')->getBody();
		$script->setRawScript($rawScript);
	} else {
		// .. otherwise just print the script
		$rawScript = (string) $script;
	}
	$client->setParameterPost('js' . $index, $rawScript); 
	echo $script . PHP_EOL . PHP_EOL;
}

$response = $client->request('POST');
out($response->getBody());


$aggregator->replaceWithOutput(array('foo','bar','baz'));

echo $aggregator;



function out($content) {
	echo "<pre>$content</pre>" . PHP_EOL;
}
