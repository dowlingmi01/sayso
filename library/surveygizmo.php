<?php
class SurveyGizmo
{

	/**
	* Places an API call to SurveyGizmo
	*
	* @author Peter Connolly
	* @param String $url
	*/
	private function _callAPI($url)
	{
		$config = Api_Registry::getConfig();
		$curl = curl_init();
		$url = sprintf("%s?user:pass=%s:%s",$url,$config->surveyGizmo->api->username,$config->surveyGizmo->api->password);

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$output = json_decode(curl_exec($curl));

		return $output;
	}

	/**
	* Returns a valid list of surveys
	*
	* @author Peter Connolly
	* @param string $resource Survey type required, e.g Poll, Survey
	*/
	public function getSurveyList($resource)
	{
		$surveylist = array();
		$usedsurveys = array();
		$allsurveys = $this->_callAPI("https://restapi.surveygizmo.com/v2/survey");

		$sql = "select external_id from survey";
		$usedsurveyslist = Db_Pdo::fetchAll($sql);
		// Gives us a list of external_ids already entered into the db
		foreach ($usedsurveyslist as $survey)
		{
			// SG can sometimes give us null external_ids. Need to filter them out.
			if (!empty($survey['external_id'])) {
				$usedsurveys[] = $survey['external_id'];
			}
		}

		foreach($allsurveys->data as $key=>$value)
		{
			// Run through all surveys returned from SurveyGizmo (in allsurveys->data),
			// remove those surveys which have already been used.
			if (!in_array($value->id, $usedsurveys)) {

				if (stripos($value->title, "wraparound")===false) { // we're not interested in Wraparounds

					if ((stripos($value->_subtype,$resource)!==false) && ($value->status=='Launched')) {

						$surveylist[$key]['external_id'] = $value->id;
						$surveylist[$key]['title'] = $value->title;
					}
				}
			}
		}

		return $surveylist;
	}

}


