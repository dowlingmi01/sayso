<?php
class Starbar_Bootstrap extends Zend_Application_Module_Bootstrap
{
    public function _initSessionCustomParams()
    {
        Zend_Session::setOptions(array(
            'use_only_cookies'  => 0,
            'use_cookies'       => 0,
            'use_trans_sid'     => 0,
            'gc_probability'    => 0,
            'gc_maxlifetime'    => 31536000,
            'save_path'         => realpath(APPLICATION_PATH . '/../session')
        ));
        return null;
    }
}
