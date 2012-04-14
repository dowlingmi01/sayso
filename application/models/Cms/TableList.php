<?php

class Cms_TableList extends Zend_Db_Table_Abstract
{
	protected $_name = 'cms_table_list';
    
    public function init()
    {
       
    }

	/**
	 * Locate a record in the Table List table by its ID
	 *
	 * @param int $id
	 * @return $this
	 */
	//public function find($id)
//        {
//            printf("Hello. Looking for [%s]",$id);
//            $result = $this->find($id);
//            
            //$result = $this->getDbTable()->find($id);
//          
//            if (0 == count($result)) {
//                return;
//            }
//            print_r($result);                     
//            /*$row = $result->current();
//            $this->setId($row->id)
//                    ->setTitle($row->email)
//                    ->setCreated($row->created);*/
//        }

        /**
         * Insert a new Survey record
         *
         * @author Peter Connolly
         * @param array $surveyValues - An array of values to be added to the Survey record
         * @param int $UserId - The userID this record should be associated with. Defaults to 1 (the admin user)
         * @return \Survey
         */                                              
        public function insert($surveyValues, $userId = 1)
        {
            // Assign default values
            $this->origin = "SurveyGizmo";

            // Pull variables from parameters
            $this->title = $surveyValues['title'];
            $this->user_id = $userId;
            $this->starbar_id = $surveyValues['starbar_id'];
            $this->premium = $surveyValues['premium'];
            $this->external_id = $surveyValues['external_id'];
	    $this->external_key = $surveyValues['external_key'];
	    $this->number_of_answers = $surveyValues['number_of_answers'];
	    $this->number_of_questions = $surveyValues['number_of_questions'];
	    $this->display_number_of_questions = $surveyValues['number_of_questions'];
	    $this->ordinal = $surveyValues['ordinal'];
	    $this->start_after = $surveyValues['start_after'];
	    $this->start_at = $surveyValues['start_at'];
	    $this->end_at = $surveyValues['end_at'];

            // Save the completed record
            $this->saveCMS();

            return $this;
        }

	/**
         * Insert a new Survey record
         *
         * @author Peter Connolly
         * @param array $surveyValues - An array of values to be updated on the required Survey record
         * @param int $Id - The ID of the record to be updated
         * @return \Survey
         */
        public function update($surveyValues, $Id)
        {
	    //printf("<p>In update, id is [%s]</p>",$Id);
	    // Set the ID - Required for an update
	    $this->setId ($Id);
//printf("<p>Id is [%s], set by [%s]</p>",$Id, $this->getID());
            // Assign values from the form
            //$this->origin = "SurveyGizmo";
            $this->title = $surveyValues['title'];
          //  $this->user_id = $userId;
            $this->starbar_id = $surveyValues['starbar_id'];
            $this->premium = $surveyValues['premium'];
            $this->external_id = $surveyValues['external_id'];
	    $this->external_key = $surveyValues['external_key'];
	    $this->number_of_answers = $surveyValues['number_of_answers'];
	    $this->number_of_questions = $surveyValues['number_of_questions'];
	    $this->display_number_of_questions = $surveyValues['number_of_questions'];
	    $this->ordinal = $surveyValues['ordinal'];
	    $this->start_after = $surveyValues['start_after'];
	    $this->start_at = $surveyValues['start_at'];
	    $this->end_at = $surveyValues['end_at'];

            // Save the completed record
            $this->saveCMS();

            return $this;
        }


	public static function getNextSurveyForUser($startSurvey, $userId) {
		// Figure out what the status of this survey is for this user
		$surveyUserMap = new Survey_UserMap();
		$surveyUserMap->loadDataByUniqueFields(array('survey_id' => $startSurvey->id, 'user_id' => $userId));
		if ($surveyUserMap->status) {
			$surveyUserStatus = $surveyUserMap->status;
		} else {
			$surveyUserStatus = 'new';
		}

		if ($surveyUserStatus == 'new' || $surveyUserStatus == 'archived') {
			$surveys = new SurveyCollection();
			$surveys->loadSurveysForStarbarAndUser($startSurvey->starbar_id, $userId, 'survey', $surveyUserStatus);
			$returnNextSurvey = false;
			foreach($surveys as $survey) {
				if ($returnNextSurvey) return $survey;
				if ($survey->id == $startSurvey->id) $returnNextSurvey = true;
			}
		}
		return new Survey();
	}


        /**
	 * @deprecated No longer used
	 * @param Study $study
	 * @param AdminUser $user
	 * @param array $values
	 */
	public static function savenew(Survey $survey, AdminUser $user, array $values, $action = 'save')
	{
		$merged = array();
		if(!empty($values))
		{
			foreach ($values as $data)
			{
				if(is_array($data) && !empty($data))
				{
					$merged = array_merge($merged, $data);
				}
			}
		}
		if(isset($_POST['criteria']) && is_array($_POST['criteria']) && !empty($_POST['criteria']))
		{
			$merged = array_merge($merged, array('criteria' => $_POST['criteria']));
		}
		if(isset($_POST['quotas']) && is_array($_POST['quotas']) && !empty($_POST['quotas']))
		{
			$merged = array_merge($merged, array('quotas' => $_POST['quotas']));
		}
		if(isset($_POST['cell']) && is_array($_POST['cell']) && !empty($_POST['cell']))
		{
			$merged = array_merge($merged, array('cell' => $_POST['cell']));
		}
		if(isset($_POST['tag']) && is_array($_POST['tag']) && !empty($_POST['tag']))
		{
			$merged = array_merge($merged, array('tag' => $_POST['tag']));
		}
		if(isset($_POST['creative']) && is_array($_POST['creative']) && !empty($_POST['creative']))
		{
			$merged = array_merge($merged, array('creative' => $_POST['creative']));
		}
		$values = $merged;

		//echo '<pre>';var_dump($_POST);exit(0);
		//echo '<pre>';var_dump($values);exit(0);
		/*echo '<pre>';
		foreach ($values['creative'] as $cell)
		{
			echo "------------------------------------\n";
			var_dump($cell);
		}
		exit(0);*/

		//Main
		if($action == 'save')
		{
			$study->user_id = $user->id;
		}
		$study->study_type	  = $values['radioProduct'];

		// Basic Tab
		$study->name			= $values['txtStudyName'];
		$study->study_id		= $values['txtStudyId'];
		$study->size			= $values['txtSampleSize'];
		$study->size_minimum	= $values['txtMinThreshold'];
		$study->begin_date	  = Data_FormatTools::displayDateToMysql($values['txtBegin']);
		$study->end_date		= Data_FormatTools::displayDateToMysql($values['txtEnd']);

		// Metrics tab
		$study->click_track	 = $values['radioOnline'];

		// Save before saving associations
		$study->saveCMS();

		// Associations
		if($action == 'update')
		{
			$error = Study_SearchEnginesMapCollection::dropForStudy($study->getId());
			if($error)
			{
				throw new Exception("PDO exception: " . $error);
			}
			$error = Study_SocialActivityTypeMapCollection::dropForStudy($study->getId());
			if($error)
			{
				throw new Exception("PDO exception: " . $error);
			}
			$error = Study_QuotaCollection::dropForStudy($study->getId());
			if($error)
			{
				throw new Exception("PDO exception: " . $error);
			}
			$error = Study_CellCollection::dropForStudy($study->getId());
			if($error)
			{
				throw new Exception("PDO exception: " . $error);
			}
			$error = Study_TagCollection::dropForStudy($study->getId());
			if($error)
			{
				throw new Exception("PDO exception: " . $error);
			}
			$error = Study_CreativeCollection::dropForStudy($study->getId());
			if($error)
			{
				throw new Exception("PDO exception: " . $error);
			}
		}
        }
}

