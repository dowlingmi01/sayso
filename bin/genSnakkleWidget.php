<?php
	$basepath = realpath(dirname(__FILE__) . '/../public/client/snakkle' );
  	$widgetData = 'SaySo.data = ' . json_encode(array('html' => file_get_contents( $basepath . '/widget.html' )
  	, 'json' => json_decode( file_get_contents( $basepath . '/widget-quiz.json' )))) . ';';
  	file_put_contents($basepath . '/js/widget-data.js', $widgetData);
