<?php
class Zend_View_Helper_CssHelper extends Zend_View_Helper_Abstract
{
    /**
     * Automatically load CSS files
     * 
     * Locates any CSS files for the current controller/action in the 
     * public/media/css folder, and returns them as a headLink to
     * application/layouts/scripts/layout.phtml
     * @example when executing the addAction of the Surveys controller, this 
     * routine will search for public/media/css/surveys/add.css
     * 
     * @author Peter Connolly
     * @return type 
     */
    function cssHelper() {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $file_uri = 'media/css/' . $request->getControllerName() . '/' . $request->getActionName() . '.css';
        //$file_uri = 'media/css/' . $request->getControllerName() . '/' . $request->getActionName() . '.css';
       printf("<!-- Looking for [%s]-->",$file_uri);
        if (file_exists($file_uri)) {
            $this->view->headLink()->appendStylesheet('/' . $file_uri);
        }
       
        return $this->view->headLink();
       
    }
    
}