<?
class Log_Event {
	private $_userId;
	private $_userSession;
	private $_sql;
	private $_baseTimestamp;
	private $_lastInsertedIndex;
	private $_assetDataLoaded;
	private $_assetData;


	public function __construct($userId, $userSession) {
		$this->_userId = $userId;
		$this->_userSession = $userSession;
	}


	public function insert ($eventTree) {
		if (!is_object($eventTree) || !property_exists($eventTree, 'base_ts') || !$eventTree->base_ts || !property_exists($eventTree, 'events') || !count($eventTree->events)) return;

		$this->_lastInsertedIndex = 0;
		$this->_sql = "SET @base_event_time := now();";

		$this->_baseTimestamp = (int) $eventTree->base_ts; // base timestamp... this is the timestamp when the event tree is sent on the client javascript
		if (!$this->_baseTimestamp) return;

		$this->_appendInsertSqlForEventTree($eventTree);

		try {
			Db_Pdo::execute($this->_sql);
		} catch(PDOException $e) {
			echo $e->getMessage();
			return false;
		}

		return true;
	}


	public static function removeAssetCache() {
		Api_Cache::quickRemove('Log_Asset_Data');
	}


	private function _appendInsertSqlForEventTree ($eventTree, $parentPageViewIndex = null, $topPageViewIndex = null) {
		if (!property_exists($eventTree, 'events') || !count($eventTree->events)) return;

		foreach ($eventTree->events as $event) {
			switch ($event->type) {
				case 'page_view':
					$this->_appendInsertSqlForPageView($event, $parentPageViewIndex, $topPageViewIndex);
					break;

				case 'social_action':
					$this->_appendInsertSqlForSocialAction($event, $parentPageViewIndex, $topPageViewIndex);
					break;

				case 'search':
					$this->_appendInsertSqlForSearch($event, $parentPageViewIndex, $topPageViewIndex);
					break;

				case 'asset':
					$this->_appendInsertSqlForAsset($event, $parentPageViewIndex, $topPageViewIndex);
					break;
			}
		}
	}


	private function _appendInsertSqlForPageView ($pageView, $parentPageViewIndex, $topPageViewIndex) {
		$myIndex = ++$this->_lastInsertedIndex;

		// we are at the top of the tree
		if (!$topPageViewIndex) {
			$topPageViewIndex = $myIndex;
		}

		/* INSERT URL */
		if (!property_exists($pageView, 'url') || !$pageView->url || !$this->_appendInsertSqlForUrl($pageView->url, $myIndex)) {
			/* no url/page view to insert, or insert failed... try to insert the sub-events anyway */
			$this->_appendInsertSqlForEventTree($pageView, null, null);
			return;
		}

		/* INSERT THIS PAGE VIEW */
		// sql to insert log_event_page_view...
		$this->_sql .= "INSERT INTO log_event_page_view (created, user_id, user_session_id, parent_log_event_page_view_id, top_log_event_page_view_id, log_url_id) ";
		$this->_sql .= " VALUES (".$this->_getCreatedSql($pageView).", ".$this->_userId.", ".$this->_userSession.", ".($parentPageViewIndex ? "@log_event_page_view_id_".$parentPageViewIndex : "NULL").", ".($topPageViewIndex ? "@log_event_page_view_id_".$topPageViewIndex : "NULL").", @log_url_id_".$myIndex.");";

		// ... and put its id in the mysql session variable @log_event_page_view_id_$myIndex, e.g. @log_event_page_view_id_1
		$this->_sql .= "SET @log_event_page_view_id_".$myIndex ." := last_insert_id();";

		/* INSERT SUB EVENTS */
		$this->_appendInsertSqlForEventTree($pageView, $myIndex, $topPageViewIndex);
	}


	private function _appendInsertSqlForSocialAction ($socialAction, $parentPageViewIndex, $topPageViewIndex) {
		if (!property_exists($socialAction, 'social_network') || !in_array($socialAction->social_network, array("Facebook", "Twitter", "Google+")) || !property_exists($socialAction, 'action') || !in_array($socialAction->action, array("Share", "Like"))) return;

		$myIndex = ++$this->_lastInsertedIndex;

		/* INSERT TARGET URL */
		$targetUrlSql = "NULL";
		if (property_exists($socialAction, 'target_url') && $socialAction->target_url && $this->_appendInsertSqlForUrl($socialAction->target_url, $myIndex))
			$targetUrlSql = "@log_url_id_".$myIndex;

		/* INSERT THIS SOCIAL ACTION*/
		// sql to insert log_event_social_action
		$this->_sql .= "INSERT INTO log_event_social_action (created, user_id, user_session_id, parent_log_event_page_view_id, top_log_event_page_view_id, target_log_url_id, social_network, action, message) ";
		$this->_sql .= " VALUES (".$this->_getCreatedSql($socialAction).", ".$this->_userId.", ".$this->_userSession.", ".($parentPageViewIndex ? "@log_event_page_view_id_".$parentPageViewIndex : "NULL").", ".($topPageViewIndex ? "@log_event_page_view_id_".$topPageViewIndex : "NULL").", ".$targetUrlSql.", '".$socialAction->social_network."', '".$socialAction->action."', ".sqlString(property_exists($socialAction, 'message') ? $socialAction->message : "").");";

		// don't need its id later, though we can set it if we do
		// $this->_sql .= "SET @log_event_social_action_id_".$myIndex." := last_insert_id();";
	}


	private function _appendInsertSqlForSearch ($search, $parentPageViewIndex, $topPageViewIndex) {
		if (!$search->query || !in_array($search->engine, array("Google", "Bing", "Yahoo", "Amazon"))) return;

		$myIndex = ++$this->_lastInsertedIndex;

		/* INSERT THIS SEARCH EVENT */
		// sql to insert log_event_search...
		$this->_sql .= "INSERT INTO log_event_search (created, user_id, user_session_id, parent_log_event_page_view_id, top_log_event_page_view_id, search_engine) ";
		$this->_sql .= " VALUES (".$this->_getCreatedSql($search).", ".$this->_userId.", ".$this->_userSession.", ".($parentPageViewIndex ? "@log_event_page_view_id_".$parentPageViewIndex : "NULL").", ".($topPageViewIndex ? "@log_event_page_view_id_".$topPageViewIndex : "NULL").", '".$search->engine."');";

		// ... and put its id in the mysql session variable @log_event_search_id_$myIndex, e.g. @log_event_search_id_1
		$this->_sql .= "SET @log_event_search_id_".$myIndex." := last_insert_id();";

		/* INSERT THIS SEARCH QUERY */
		// sql to insert log_event_search_query...
		$this->_sql .= "INSERT INTO log_event_search_query (log_event_search_id, query) ";
		$this->_sql .= " VALUES (@log_event_search_id_".$myIndex.", ".sqlString($search->query).");";

		$keywords = $this->_splitSearchQueryIntoKeywords($search->query);

		foreach ($keywords as $keyword) {
			$this->_sql .= "INSERT INTO log_event_search_keyword (log_event_search_id, log_search_keyword_id) VALUES (@log_event_search_id_".$myIndex.", func_get_log_search_keyword_id(".sqlString($keyword)."));";
		}
	}


	private function _appendInsertSqlForAsset ($assetEvent, $parentPageViewIndex, $topPageViewIndex) {
		if (!$assetEvent->provider || !$assetEvent->asset_type || !$assetEvent->action || !$assetEvent->asset_id) return;

		if (!$this->_assetDataLoaded) {
			$this->_loadAssetData();
		}

		if (
			!isset($this->_assetData['providers'][$assetEvent->provider])
			|| !isset($this->_assetData['types'][$assetEvent->asset_type])
			|| !isset($this->_assetData['types'][$assetEvent->asset_type]['actions'][$assetEvent->action])
		) return;

		/* error checking complete, begin processing */

		$myIndex = ++$this->_lastInsertedIndex;

		$assetType = $this->_assetData['types'][$assetEvent->asset_type];
		$assetTypeId = $assetType['id'];
		$assetProvider = $this->_assetData['providers'][$assetEvent->provider];
		$assetProviderId = $assetProvider['id'];
		$assetAction = $assetType['actions'][$assetEvent->action];
		$assetActionId = $assetAction['id'];

		$assetTitle = null;
		foreach ($assetEvent->props as $prop) {
			if (property_exists($prop, 'title') && $prop->title == 'title') {  // this is the property that contains the asset's title
				$assetTitle = $prop->value;
			}
		}

		/* INSERT THIS ASSET */
		// insert or update log_asset record...
		$this->_sql .= "CALL proc_get_log_asset_id(".$assetProviderId.", ".$assetTypeId.", ".sqlString($assetEvent->asset_id).", ".sqlString($assetTitle).", @log_asset_id_".$myIndex.");";

		/* INSERT THIS ASSET EVENT */
		// sql to insert log_event_asset...
		$this->_sql .= "INSERT INTO log_event_asset (created, user_id, user_session_id, parent_log_event_page_view_id, top_log_event_page_view_id, log_asset_id, log_asset_type_id, log_asset_type_action_id) ";
		$this->_sql .= " VALUES (".$this->_getCreatedSql($assetEvent).", ".$this->_userId.", ".$this->_userSession.", ".($parentPageViewIndex ? "@log_event_page_view_id_".$parentPageViewIndex : "NULL").", ".($topPageViewIndex ? "@log_event_page_view_id_".$topPageViewIndex : "NULL").", @log_asset_id_".$myIndex.", ".$assetTypeId.", ".$assetActionId.");";

		// ... and put its id in the mysql session variable @log_event_asset_id_$myIndex, e.g. @log_event_asset_id_1
		$this->_sql .= "SET @log_event_asset_id_".$myIndex." := last_insert_id();";

		/* INSERT THIS ASSET EVENT'S PROPERTIES */
		if (property_exists($assetEvent, 'props')) {
			foreach ($assetEvent->props as $prop) {
				if (!property_exists($prop, 'title') || !$prop->title) continue; // skip properties with no title
				if (isset($assetAction['properties'][$prop->title])) { // existing properties
					$propertyId = $assetAction['properties'][$prop->title]['id'];
					$propertyType = $assetAction['properties'][$prop->title]['type'];

					switch ($propertyType) {
						case "provider_category":
							if (!$prop->value) continue; // no category id set
							$title = (property_exists($prop, 'category_title') ? $prop->category_title : null);
							$this->_sql .= "INSERT INTO log_event_asset_property_provider_category (log_event_asset_id, log_asset_id, log_asset_type_action_id, log_asset_type_property_id, log_asset_provider_category_id) ";
							$this->_sql .= " VALUES (@log_event_asset_id_".$myIndex.", @log_asset_id_".$myIndex.", ".$assetActionId.", ".$propertyId.", func_get_log_asset_provider_category_id(".$assetProviderId.", ".$assetTypeId.", ".$propertyId.", ".sqlString($prop->value).", ".sqlString($title)."));";
							break;
						case "integer":
							$this->_sql .= "INSERT INTO log_event_asset_property_integer (log_event_asset_id, log_asset_id, log_asset_type_action_id, log_asset_type_property_id, value) ";
							$this->_sql .= " VALUES (@log_event_asset_id_".$myIndex.", @log_asset_id_".$myIndex.", ".$assetActionId.", ".$propertyId.", ".((int) $prop->value).");";
							break;
						case "decimal":
							$this->_sql .= "INSERT INTO log_event_asset_property_decimal (log_event_asset_id, log_asset_id, log_asset_type_action_id, log_asset_type_property_id, value) ";
							$this->_sql .= " VALUES (@log_event_asset_id_".$myIndex.", @log_asset_id_".$myIndex.", ".$assetActionId.", ".$propertyId.", ".sqlString(floatval($prop->value)).");";
							break;
						case "string":
							$this->_sql .= "INSERT INTO log_event_asset_property_string (log_event_asset_id, log_asset_id, log_asset_type_action_id, log_asset_type_property_id, value) ";
							$this->_sql .= " VALUES (@log_event_asset_id_".$myIndex.", @log_asset_id_".$myIndex.", ".$assetActionId.", ".$propertyId.", ".sqlString($prop->value).");";
							break;
						case "url":
							if (!$prop->value) continue; // no url
							// insert url
							$propIndex = ++$this->_lastInsertedIndex;

							if (!$this->_appendInsertSqlForUrl($prop->value, $propIndex)) continue; // try to insert url... if it fails, don't insert the property (skip to next property)

							// insert property
							$this->_sql .= "INSERT INTO log_event_asset_property_url (log_event_asset_id, log_asset_id, log_asset_type_action_id, log_asset_type_property_id, log_url_id) ";
							$this->_sql .= " VALUES (@log_event_asset_id_".$myIndex.", @log_asset_id_".$myIndex.", ".$assetActionId.", ".$propertyId.", @log_url_id_".$propIndex.");";
							break;
					}
				} else { // unknown properties
					$this->_sql .= "INSERT INTO log_event_asset_property_unknown (log_event_asset_id, log_asset_id, log_asset_type_action_id, title, value) ";
					$this->_sql .= " VALUES (@log_event_asset_id_".$myIndex.", @log_asset_id_".$myIndex.", ".$assetActionId.", ".sqlString($prop->title).", ".sqlString($prop->value).");";
				}
			}
		} // done inserting properties
	}


	private function _appendInsertSqlForUrl ($urlString, $myIndex) {
		if (!$urlString) return;
		list($url, $params) = $this->_splitUrlString($urlString);

		if (!$url) return;

		$this->_sql .= "CALL proc_add_log_url_no_parameters(".sqlString($url['protocol']).", ".sqlString($url['hostname']).", ".sqlString($url['path']).", @log_url_id_".$myIndex.", @hostname_id_".$myIndex.", @hostname_path_id_".$myIndex.");";

		foreach ($params as $paramTitle => $paramValue) {
			$this->_sql .= "INSERT INTO log_url_parameter(log_url_id, hostname_parameter_id, value) VALUES (@log_url_id_".$myIndex.", func_get_hostname_parameter_id(@hostname_id_".$myIndex.", ".sqlString($paramTitle)."), ".sqlString($paramValue).");";
		}

		return true;
	}


	private function _loadAssetData () {
		if ($this->_assetDataLoaded) return;

		$cache = Api_Cache::getInstance('Log_Asset_Data', Api_Cache::LIFETIME_WEEK);

		if ($cache->test()) {

			$this->_assetData = $cache->load();

		} else {

			$this->_assetData = array();

			/* load providers */
			$sql = "SELECT id, lookup FROM log_asset_provider";
			$data = Db_Pdo::fetchAll($sql);

			$this->_assetData['providers'] = array();
			foreach ($data as $row) {
				$this->_assetData['providers'][$row['lookup']] = array();
				$this->_assetData['providers'][$row['lookup']]['id'] = $row['id'];
			}

			/* load asset types and their actions */
			$sql = "
				SELECT lat.id AS type_id, lat.lookup AS type_lookup, lata.id AS action_id, lata.lookup AS action_lookup
				FROM log_asset_type_action lata
				INNER JOIN log_asset_type lat
					ON lat.id = lata.log_asset_type_id
			";
			$data = Db_Pdo::fetchAll($sql);

			$this->_assetData['types'] = array();
			foreach ($data as $row) {
				if (!isset($this->_assetData['types'][$row['type_lookup']])) {
					$this->_assetData['types'][$row['type_lookup']] = array();
					$this->_assetData['types'][$row['type_lookup']]['id'] = (int) $row['type_id'];
					$this->_assetData['types'][$row['type_lookup']]['actions'] = array();
				}

				$this->_assetData['types'][$row['type_lookup']]['actions'][$row['action_lookup']] = array();
				$this->_assetData['types'][$row['type_lookup']]['actions'][$row['action_lookup']]['id'] = (int) $row['action_id'];
				$this->_assetData['types'][$row['type_lookup']]['actions'][$row['action_lookup']]['properties'] = array();
			}

			/* load properties for each action */
			$sql = "
				SELECT lat.lookup AS type_lookup, lata.lookup AS action_lookup, latp.id AS prop_id, latp.lookup AS prop_lookup, latp.type AS prop_type
				FROM log_asset_type_property latp
				INNER JOIN log_asset_type_action_property_map latapm
					ON latapm.log_asset_type_property_id = latp.id
				INNER JOIN log_asset_type_action lata
					ON lata.id = latapm.log_asset_type_action_id
				INNER JOIN log_asset_type lat
					ON lata.log_asset_type_id = lat.id
			";
			$data = Db_Pdo::fetchAll($sql);

			foreach ($data as $row) {
				$this->_assetData['types'][$row['type_lookup']]['actions'][$row['action_lookup']]['properties'][$row['prop_lookup']] = array();
				$this->_assetData['types'][$row['type_lookup']]['actions'][$row['action_lookup']]['properties'][$row['prop_lookup']]['id'] = (int) $row['prop_id'];
				$this->_assetData['types'][$row['type_lookup']]['actions'][$row['action_lookup']]['properties'][$row['prop_lookup']]['type'] = $row['prop_type'];
			}

			$cache->save($this->_assetData);
		}

		$this->_assetDataLoaded = true;
	}


	private function _getCreatedSql ($event) {
		if (!property_exists($event, 'ts') || !$event->ts) return " NULL ";
		return " @base_event_time - interval " . (($this->_baseTimestamp - (int) $event->ts))  . " second ";
	}


	private function _splitUrlString ($s) {
		$url = array();
		$params = array();
		$parsed = parse_url($s);

		if (count($parsed) < 2) return array(null, null);

		$url['protocol'] = $parsed['scheme'];
		$url['hostname'] = $parsed['host'];
		$url['path'] = substr($parsed['path'], 1);
		$url['query'] = array_key_exists('query', $parsed) ? $parsed['query'] : '';

		$paramStrings = $url['query'] ? explode("&", $url['query']) : array();
		foreach ($paramStrings as $paramString) {
			$splitParamString = explode("=", $paramString, 2);
			if (count($splitParamString) == 2)
				$params[$splitParamString[0]] = urldecode($splitParamString[1]);
			else $params[$splitParamString[0]] = "";
		}

		return array($url, $params);
	}


	private function _splitSearchQueryIntoKeywords ($s) {
		$s = strtolower(preg_replace("/[\.\,\!\?]+/", " ", $s)); // treat punctuation like whitespace
		$s = strtolower(preg_replace("/\s+/", " ", $s)); // remove multiple spaces
		$numQuotes = substr_count($s, "\"");
		if ($numQuotes % 2) { // if the string has an odd number of quotes, ignore the last one
			$lastQuotePos = strnpos($s, "\"", $numQuotes);
			$s = substr($s, 0, $lastQuotePos - 1) . substr($s, $lastQuotePos + 1);
		}
		$keywords = array();
		$parts = array_map('trim', explode("\"", $s));
		for ($i = 0; $i < count($parts); $i++) {
			if ($i % 2 == 0) {
				// split words into spaces
				foreach (explode(" ", $parts[$i]) as $keyword) $keywords[] = $keyword;
			} else {
				// phrases (i.e. words in quotes) should not be broken up into words
				$keywords[] = $parts[$i];
			}
		}
		$keywords = array_unique(array_filter($keywords)); // remove duplicates and empty values (e.g. if user uses double  spaces or empty quotes "")
		return $keywords;
	}

}
?>
