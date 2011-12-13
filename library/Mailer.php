<?php


class Mailer extends Zend_Mail
{
	public function setBodyMultilineText($txt, $charset = null, $encoding = Zend_Mime::ENCODING_QUOTEDPRINTABLE)
	{
		$txt = str_replace('					', '', $txt);
		parent::setBodyText($txt, $charset, $encoding);
	}
}

