<?php
/* vim: set noai expandtab tabstop=4 softtabstop=4 shiftwidth=4: */
/**
 * System_Daemon turns PHP-CLI scripts into daemons.
 *
 * PHP version 5
 *
 * @category  System
 * @package   System_Daemon
 * @author	Kevin van Zonneveld <kevin@vanzonneveld.net>
 * @copyright 2008 Kevin van Zonneveld (http://kevin.vanzonneveld.net)
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD Licence
 * @version   SVN: Release: $Id$
 * @link	  http://trac.plutonia.nl/projects/system_daemon
 */

/**
 * A System_Daemon_OS driver for RedHat based Operating Systems
 *
 * @category  System
 * @package   System_Daemon
 * @author	Kevin van Zonneveld <kevin@vanzonneveld.net>
 * @author	Igor Feghali <ifeghali@php.net>
 * @copyright 2008 Kevin van Zonneveld (http://kevin.vanzonneveld.net)
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD Licence
 * @version   SVN: Release: $Id$
 * @link	  http://trac.plutonia.nl/projects/system_daemon
 * *
 */
class System_Daemon_OS_RedHat extends System_Daemon_OS_Linux
{
	/**
	 * On Linux, a distro-specific version file is often telling us enough
	 *
	 * @var string
	 */
	protected $_osVersionFile = "/etc/redhat-release";

	/**
	 * Path of init.d scripts
	 *
	 * @var string
	 */
	protected $_autoRunDir = '/etc/rc.d/init.d';

	/**
	 * Template path
	 *
	 * @var string
	 */
	protected $_autoRunTemplatePath = '#datadir#/template_RedHat';

	/**
	 * Replace the following keys with values to convert a template into
	 * a read autorun script
	 *
	 * @var array
	 */
	protected $_autoRunTemplateReplace = array(
		"@author_name@"  => "{PROPERTIES.authorName}",
		"@author_email@" => "{PROPERTIES.authorEmail}",
		'@name@'		 => '{PROPERTIES.appName}',
		'@desc@'		 => '{PROPERTIES.appDescription}',
		'@bin_file@'	 => '{PROPERTIES.appDir}/{PROPERTIES.appExecutable}',
		'@bin_name@'	 => '{PROPERTIES.appExecutable}',
		'@pid_file@'	 => '{PROPERTIES.appPidLocation}',
		'@chkconfig@'	=> '{PROPERTIES.appChkConfig}',
	);

}