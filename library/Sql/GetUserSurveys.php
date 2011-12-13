<?php
/**
 * SQL builder class to get all surveys for a user
 * 
 * @author davidbjames
 *
 */
class Sql_GetUserSurveys extends Sql_Abstract
{
	/**
	 * Create a UserCollection
	 */
	public function init ()
	{
		$this->_collection = new Survey_UserMapCollection();
	}
	
	/**
	 * Query by User id
	 * 
	 * @param int $userId
	 */
	public function setUserId ($userId)
	{
		$this->user_id = $userId;
	}
	
	/**
	 * @see SqlAbstract::build()
	 * @param array|Iterator $traversableData
	 * @return Survey_UserMap
	 */
	public function build (& $data, $builder = null)
	{
		// this method always returns a UserMap
		// object which goes into the UserMapCollection
		$userMap = new Survey_UserMap();
		$userMap->build($data);
		// each UserMap "row" corresponds to a Survey
		// in fact, UserMap is really an *instance* of a survey
		// completed (or possibly not completed) by user
		$survey = new Survey();
		$survey->build($data);
		// aggregate the Survey to the map
		$userMap->setSurvey($survey);
		return $userMap;
	}
}


