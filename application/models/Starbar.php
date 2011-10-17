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
    
    protected $_user;
    
    protected $_userMap;
    
    protected $_cssUrl = '';
    
    protected $_visibility = '';
    
    protected $_html = '';
    
    public function init() {
        if (Registry::isRegistered('starbar')) {
            throw new Exception('Starbar already created and registered in Registry as \'starbar\'');
        }
        Registry::set('starbar', $this);
        parent::init();
    }
    
    public function setUser (User $user) {
        $this->_user = $user;
    }
    
    /**
     * @return User
     */
    public function getUser () {
        return $this->_user;
    }
    
    /**
     * @param Starbar_UserMap $userMap
     */
    public function setUserMap (Starbar_UserMap $userMap) {
        $this->_userMap = $userMap;
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
    
    
    
    public function setVisibility ($visibility) {
        $this->_visibility = $visibility;
    }
    
    public function getVisibility () {
        return $this->_visibility;
    }
    
    public function exportData() {
        $fields = array(
            'short_name',
            'label',
            'description',
            'user_pseudonym',
            'domain',
            'auth_key'
        );
        return array_intersect_key($this->getData(), array_flip($fields));
    }
    
    public function exportProperties($parentObject = null) {
        $props = array(
            '_user' => $this->_user,
            '_user_map' => $this->_userMap,
            '_css_url' => $this->_cssUrl,
            '_html' => $this->_html
        );
        return array_merge(parent::exportProperties($parentObject), $props);
    }
}

