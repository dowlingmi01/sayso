<?php

class Cli_IncrementalController extends Zend_Controller_Action
{
    /**
     * Need to do anything before the runAction is called?
     */
    public function init()
    {
        if (PHP_SAPI != 'cli')
        {
            throw new Exception("Unsupported call!");
        }
    }

    /**
     * All function calls should go there
     */
    public function runAction()
    {
        // .. start processing...

        // always do that at the end of action...
        exit(0);
    }

}