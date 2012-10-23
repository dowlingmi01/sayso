<?php

require_once APPLICATION_PATH . '/modules/api/controllers/GlobalController.php';

class Api_StudyController extends Api_GlobalController
{

	public function indexAction()
	{

	}

	public function getAction () {
		$this->_acceptIdParameter('study_id');
		$this->_validateRequiredParameters(array('study_id'));

		// .. get a specific study
	}

	public function validateAction () {
		$this->_validateRequiredParameters(array('user_id', 'study_id'));
		// purpose of this is to validate a study to ensure it is
		// still valid for the current user (date or otherwise)
		// @todo check if study date (other?) is still valid
		return $this->_resultType(true);
	}

	public function getAllAction () {
		$studyAds = new Study_AdCollection();
		$studyAds->loadAllStudyAds($this->user_id);
		return $this->_resultType($studyAds);
	}

	public function getAllQualifiedAction () {
		$this->_validateRequiredParameters(array('user_id'));

		// check if current study exists for this user
		$activeCellId = Study_CellAssignment::getActiveCellIdByUser($this->user_id);
		if ($activeCellId) {
			// user is part of an active study
		} else {
			// user not part of an active study
			// so find one to assign them to
			$builder = new Sql_GetQualifyingStudies();
			$builder->setUserId($this->user_id);
			$studies = $builder->run();
			// where I got to: this is the initial query object that finds qualifying studies
			// for the current user .. there is much to add to it, as well as some post
			// processing of the result collection, and possibly some further queries
			// in order to determine the correct study.
			//
			// test URL is at http://local.sayso.com/api/study/get-all-qualified/user_id/1 (assumes user id 1 exists)
			return $this->_resultType($studies);
		}
	}
}

