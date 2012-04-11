<?php
class Zend_View_Helper_JavascriptHelper extends Zend_View_Helper_Abstract
{
    /**
     * Automatically load Javascript files
     * 
     * Locates any Javascript files for the current controller/action in the 
     * public/media/js folder, and returns them as a headLink to
     * application/layouts/scripts/layout.phtml
     * @example when executing the addAction of the Surveys controller, this 
     * routine will search for public/media/js/surveys/add.js
     * 
     * @author Peter Connolly
     * @return type 
     */
    function javascriptHelper() {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $file_uri = 'media/js/' . $request->getControllerName() . '/' . $request->getActionName() . '.js';
       
        if (file_exists($file_uri)) {
            $this->view->headLink()->appendStylesheet('/' . $file_uri);
        }
       
        return $this->view->headLink();
       
    }
    
}