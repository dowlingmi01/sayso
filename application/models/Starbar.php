<?php
/**
 * Class representing a Starbar 
 * 
 * @author davidbjames
 *
 */
class Starbar extends Record
{
    protected $_tableName = 'starbar';
    
    protected $_uniqueFields = array('short_name' => '');
    
    protected $_apiAuthKey = '';
    
    protected $_cssUrl = '';
    
    protected $_html = '';
    
    protected $_user;
    
    /**
     * Each Starbar has it's own API key 
     * 
     * @param string $apiAuthKey
     */
    public function setApiAuthKey ($apiAuthKey) {
        $this->_apiAuthKey = $apiAuthKey;
    }
    
    /**
     * Each Starbar has custom CSS which should
     * be loaded ahead of injection of the markup.
     * This is handled in the browser app
     * 
     * @param string $cssUrl
     */
    public function setCssUrl ($cssUrl) {
        $this->_cssUrl = $cssUrl;
    }
    
    /**
     * The actual markup which gets injected into
     * the DOM of the containing page
     * 
     * @param string $html
     */
    public function setHtml ($html) {
        $this->_html = $html;
    }
    
    public function setUser (User $user) {
        $this->_user = $user;
    }
    
    public function exportProperties($parentObject = null) {
        $props = array(
            '_auth_key' => $this->_apiAuthKey,
            '_css_url' => $this->_cssUrl,
            '_html' => $this->_html,
            '_user' => $this->_user
        );
        return array_merge(parent::exportProperties($parentObject), $props);
    }
}

