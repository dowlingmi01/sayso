<?php
class Starbar_View_Helper_Text extends Zend_View_Helper_Abstract
{
	function text($key, $starbarId = null) {
		return Starbar_Content::getByStarbarAndKey($key, ($starbarId ? $starbarId : $this->view->starbar_id));
	}
}
