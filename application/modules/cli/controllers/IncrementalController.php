<?php
/**
 * Controller to handle mysql development updates
 *
 * The script is aimed to produce any CLI output only in case of errors,
 * the succesful execution should end up silently
 *
 * @author alecksmart
 */
class Cli_IncrementalController extends Zend_Controller_Action
{

    const UPDATES_ONLY_AFTER = '2011-10-27';

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
        $logfile = dirname(APPLICATION_PATH) . sprintf('/log/incremental-%s.log', getenv('APPLICATION_ENV'));
        @touch($logfile);
        if(!file_exists($logfile) || !is_writable($logfile))
        {
            die(sprintf("Path %s not writable!\n", $logfile));
        }
        // printf("Using logfile %s", $logfile);

        $options = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOptions();

        // prepare array of former updates
        $existingUpdates    = file($logfile);
        foreach($existingUpdates as $k => $v)
        {
            $existingUpdates[$k] = trim($v);
        }

        // create command template autodetecting myqsl
        $mysqlBinary    = trim(`which mysql`);
        $command        = sprintf('%s -h %s --user=%s --password=%s %s < %%s',
            $mysqlBinary,
            $options['database']['params']['host'],
            $options['database']['params']['username'],
            $options['database']['params']['password'],
            $options['database']['params']['dbname']
        );

        // use nice SPL goodie to get needed files
        $files  = new GlobIterator(dirname(APPLICATION_PATH).'/scripts/sql/*.sql', FilesystemIterator::KEY_AS_FILENAME);
        $handle = fopen($logfile, 'a+');

        // do updates in a loop, break on error
        foreach ($files as $name => $path)
        {
            $fileDate = substr($name, 0, 10);
            if($fileDate <= self::UPDATES_ONLY_AFTER || in_array($name, $existingUpdates))
            {
                continue;
            }
            // ok, we can try your sql, dude...
            $output = array();
            $error  = 0;
            exec(sprintf($command, $path), $output, $error);
            if($error)
            {
                // something has gone wrong?
                // get out of here!
                fclose($handle);
                echo "UPDATE FAILED!\n";
                die($error . "\n");
            }
            fwrite($handle, $name."\n");
        }
        fclose($handle);

        // always do that at the end of action...
        exit(0);
    }
}