<?php

class Starbar_RemoteController extends Api_AbstractController
{
    public function preDispatch() {
        Zend_Controller_Front::getInstance()
            ->getPlugin('Zend_Controller_Plugin_ErrorHandler')
            ->setErrorHandlerModule(Api_Bootstrap::$moduleName);
    }
    
    /**
     * Main Starbar action used for:
     * - determining which starbar is requested
     * - routing to the correct starbar 
     * - determing starbar based on origin
     */
    public function indexAction () {
        $this->_acceptIdParameter('starbar_id');
        $starbar = new Starbar();
        if ($this->starbar_id) {
            $starbar->loadData($this->starbar_id);
        } else if ($this->short_name) { 
            $starbar->loadDataByUniqueFields(array('short_name' => $this->short_name));
        } else { // starbar not determined yet
            
        }
        return $this->_forward(
            $starbar->short_name, 
            null, 
            null, 
            array('starbar' => $starbar)
        );
    }
    
    /**
     * Lady Gaga Starbar
     * @todo add to starbar table if we decide to use it
     */
    public function gagaAction () 
    {
        $this->render();
        return $this->_resultType(new Object(array('html' => $this->getResponse()->getBody())));
    }
    
    /**
     * Generic Starbar
     * @todo add to starbar table
     */
    public function genericAction () {
        
    }

    /**
     * Hello Music "BeatBar"
     */
    public function hellomusicAction () {
        
        $this->render();
        
        $starbar = $this->_getStarbarObject();
        $starbar->setApiAuthKey(Api_Registry::getConfig()->api->helloMusic->authKey);
        $starbar->setCssUrl('http://' . BASE_DOMAIN . '/css/starbar-hellomusic.css');
        $starbar->setHtml($this->getResponse()->getBody()); 
        return $this->_resultType($starbar);
    }
    
    /**
     * Setup renderer
     */
    public function postDispatch() {
        if ($this->_request->getActionName() !== 'index') {
            // for actual starbar, setup JSONP renderer
            $this->_enableRenderer(new Api_Plugin_JsonPRenderer());
        }
    }
    
    /**
     * Make sure Starbar has been determined in index
     * @return Starbar
     */
    private function _getStarbarObject () {
        if ($this->starbar && $this->starbar instanceof Starbar && $this->starbar->hasId()) { 
            return $this->starbar;
        } else {
            throw new Exception('Remote starbar actions cannot be accessed directly. Use /starbar/remote with id or short_name.');
        }
    }
    
}

