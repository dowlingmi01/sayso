<?php
/**
 *Browser class
 *
 * Hanldes browser related actions.
 */
class Browser extends Record
{
	/**
	 *
	 * @var type Table name
	 */
	protected $_tableName = 'browser';

	/**
	 * Process a $_SERVER["HTTP_USER_AGENT"] string
	 * to return existing database values or insert new values.
	 *
	 * @param string $httpAgentString if not provided,
	 * takes the value from $_SERVER["HTTP_USER_AGENT"]
	 */
	public function processAgentString($httpAgentString = FALSE)
	{
		if (!$httpAgentString)
			$httpAgentString = $_SERVER["HTTP_USER_AGENT"];

		//check if agent string exists

		$browser = Db_Pdo::fetch('SELECT id FROM ' . $this->_tableName . ' WHERE agent_string = ?', $httpAgentString);

		if (isset($browser["id"]))
			$this->loadData ($browser["id"]);
		else {
			//if this fails, google "browscap"
			$browscap = get_browser();

			$comment = NULL;
			if ($browscap->comment != $browscap->browser)
				$comment = $browscap->comment;

			//get browser type id
			$browserType = new Browser_Type();
			$browserType->processBrowser($browscap->browser);

			$this->browser_type_id = $browserType->id;
			$this->major_version = $browscap->majorver;
			$this->minor_version = $browscap->minorver;
			$this->agent_string = $httpAgentString;
			$this->comment = $comment;
			$this->save();
		}
	}
}