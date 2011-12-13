<?php
/**
 * Controller to handle mysql development updates
 *
 * The script is aimed to produce any CLI output only in case of errors,
 * the succesful execution should end up silently
 *
 * @author alecksmart
 */
class Cli_RebuildController extends Zend_Controller_Action
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
		$options = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOptions();		
		
		// create all files we need
		$logfile = dirname(APPLICATION_PATH) . sprintf('/log/incremental-%s.log', getenv('APPLICATION_ENV'));
		@touch($logfile);
		if(!file_exists($logfile) || !is_writable($logfile))
		{
			echo sprintf("Path %s not writable!\n", $logfile);
			exit(1);
		}

		$backupfile = dirname(APPLICATION_PATH) . sprintf('/log/before-rebuild-%s-%s.sql', getenv('APPLICATION_ENV'), date('YmdHis'));
		@touch($backupfile);
		if(!file_exists($backupfile) || !is_writable($backupfile))
		{
			echo sprintf("Backup %s not writable!\n", $backupfile);
			exit(1);
		}

		// create myqsl backup command autodetecting myqsldump binary
		$mysqlDumpBinary	= trim(`which mysqldump`);
		$command			= sprintf('%s -h %s --user=%s --password=%s --force --opt --routines --single-transaction --databases %s > %s',
			$mysqlDumpBinary,
			$options['database']['params']['host'],
			$options['database']['params']['username'],
			$options['database']['params']['password'],
			$options['database']['params']['dbname'],
			$backupfile
		);

		// create the backup ...
		$output = array();
		$error  = 0;
		exec($command, $output, $error);
		if($error)
		{
			// something has gone wrong?
			// get out of here!
			echo "BACKUP FAILED!\n";
			echo implode("\n", $output) . "\n";
			exit(1);
		}

		// drop tables
		$tables = Db_Pdo::fetchAll("SHOW TABLES;");
		$allSQL = array();
		Db_Pdo::execute("SET foreign_key_checks = 0;");
		for ($i = 0, $c = count($tables); $i < $c; $i++)
		{
			$allSQL[] = sprintf("DROP TABLE `%s`;", $tables[$i]['Tables_in_'.$options['database']['params']['dbname']]);
		}
		for ($i = 0, $c = count($allSQL); $i < $c; $i++)
		{
			fwrite(STDOUT, $allSQL[$i] . "\n");
			try
			{
				Db_Pdo::execute($allSQL[$i]);
			}
			catch(Exception $e)
			{
				echo "Exception trapped with message: ".$e->getMessage();
				exit(1);
			}
		}
		Db_Pdo::execute("SET foreign_key_checks = 1;");

		// create command template autodetecting myqsl
		$mysqlBinary	= trim(`which mysql`);
		$command		= sprintf('%s -h %s --user=%s --password=%s %s < %%s',
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
			// ok, we can try your sql, dude...
			$output = array();
			$error  = 0;
			exec(sprintf($command, $path), $output, $error);
			if($error)
			{
				// something has gone wrong?
				// get out of here!
				fclose($handle);
				echo "UPDATE FAILED in $name:\n";
				echo implode("\n", $output) . "\n";
				exit(1);
			}
			fwrite($handle, $name."\n");
			fwrite(STDOUT, "Updates in $name ............ SUCCESS\n");
		}

		// do updates in developer database

		// Drop tables from developer
		$dsn		= 'mysql:host=' . $options['database']['params']['host'] . ';dbname=' . 'developer';
		$pdo		= new PDO($dsn, $options['database']['params']['username'], $options['database']['params']['password']);
		$resultSet  = $pdo->query("SHOW TABLES;");
		$tables	 = $resultSet->fetchAll();

		$allSQL = array();
		$pdo->exec("SET foreign_key_checks = 0;");
		for ($i = 0, $c = count($tables); $i < $c; $i++)
		{
			$allSQL[] = sprintf("DROP TABLE `%s`;", $tables[$i][0]);
		}
		for ($i = 0, $c = count($allSQL); $i < $c; $i++)
		{
			fwrite(STDOUT, $allSQL[$i] . "\n");
			try
			{
				$pdo->exec($allSQL[$i]);
			}
			catch(Exception $e)
			{
				echo "Exception trapped with message: ".$e->getMessage();
				exit(1);
			}
		}
		$pdo->exec("SET foreign_key_checks = 1;");

		// create developer command pattern
		$command		= sprintf('%s -h %s --user=%s --password=%s %s < %%s',
			$mysqlBinary,
			$options['database']['params']['host'],
			$options['database']['params']['username'],
			$options['database']['params']['password'],
			'developer'
		);

		$files  = new GlobIterator(dirname(APPLICATION_PATH).'/scripts/sql/developer/*.sql', FilesystemIterator::KEY_AS_FILENAME);
		foreach ($files as $name => $path)
		{
			// ok, we can try your sql, dude...
			$output = array();
			$error  = 0;
			exec(sprintf($command, $path), $output, $error);
			if($error)
			{
				// something has gone wrong?
				// get out of here!
				fclose($handle);
				echo "UPDATE FAILED in $name:\n";
				echo implode("\n", $output) . "\n";
				exit(1);
			}
			fwrite($handle, $name."\n");
			fwrite(STDOUT, "Updates in $name ............ SUCCESS\n");
		}

		fclose($handle);
		echo "\nDatabase updates done...\n";

		// always do that at the end of action...
		exit(0);
	}
}