<?php
/**
 * Router for serving command line interface calls
 * @see [project_root]/bin/README
 *
 * @author alecksmart
 */

class Task_Router_Cli extends Zend_Controller_Router_Abstract
{
    /**
     * Define route in cli module
     *
     * @param Zend_Controller_Request_Abstract $dispatcher
     * @return Zend_Controller_Request_Abstract
     */
    public function route(Zend_Controller_Request_Abstract $dispatcher)
    {
        $getopt = new Zend_Console_Getopt(array());
        $arguments = $getopt->getRemainingArgs();
        if ($arguments)
        {
            $command = array_shift($arguments);
            if (!preg_match('~\W~', $command))
            {
                $dispatcher->setModuleName('cli');
                $dispatcher->setControllerName($command);
                $dispatcher->setActionName('run');
                unset($_SERVER ['argv'][1]);
                return $dispatcher;
            }
            echo "Invalid command.\n", exit;
        }
        echo "No command given.\n", exit;
    }

    /**
     * Dummy method needed for ZF compatibility
     *
     * @param array $userParams
     * @param string $name
     * @param bool $reset
     * @param bool $encode
     */
    public function assemble($userParams, $name = null, $reset = false, $encode = true){}
}