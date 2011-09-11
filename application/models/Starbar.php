<?php


class Starbar extends Record
{
    protected $_tableName = 'starbar';
    
    protected $_uniqueFields = array('short_name' => '');
    
    protected $_apiAuthKey = '';
    
    protected $_cssUrl = '';
    
    protected $_html = '';
    
    public function setApiAuthKey ($apiAuthKey) {
        $this->_apiAuthKey = $apiAuthKey;
    }
    
    public function setCssUrl ($cssUrl) {
        $this->_cssUrl = $cssUrl;
    }
    
    public function setHtml ($html) {
        $this->_html = $html;
    }
    
    public function exportProperties($parentObject = null) {
        $props = array(
            '_auth_key' => $this->_apiAuthKey,
            '_css_url' => $this->_cssUrl,
            '_html' => $this->_html
        );
        return array_merge(parent::exportProperties($parentObject), $props);
    }
}

