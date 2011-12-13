<?php


class Study_Survey extends Record
{
	protected $_tableName = 'study_survey';
	
	protected $_uniqueFields = array('url' => '');
	
	/**
	 * @var Study
	 */
	protected $_study;
	
	/**
	 * @var Study_SurveyCriteriaCollection
	 */
	protected $_criteria;
	
	public function setStudy (Study $study) {
		$this->_study = $study;
	}
	
	public function addCriterion (Study_SurveyCriterion $criterion) {
		if (!$this->_criteria) {
			$this->_criteria = new Study_SurveyCriteriaCollection();
		}
		$this->_criteria->addItem($criterion);
	}
	
	public function save() {
		parent::save();
		
		// map this Survey to the Study
		if ($this->_study) {
			$surveyMap = new Study_SurveyMap();
			$surveyMap->study_id = $this->_study->getId();
			$surveyMap->survey_id = $this->getId();
			$surveyMap->save();

			// save each Criterion 
			if ($this->_criteria) {
				$this->_criteria->save();
				
				// map each Criterion to the SurveyMap
				// why map to a map? 1. if we tie criteria to surveys directly,
				// then the criteria will not be study specific, instead the criteria
				// will follow a particular survey around and apply on any study
				// the survey shows up in (this does not give the admin control
				// on a study-by-study basis over the criteria applied to a >re-used< survey). 
				// 2. if we tie criteria to the study, it creates a weak db relationship, 
				// because it may exist even if a survey doesn't exist (essentially it would
				// be orphan data). By mapping to a mapping, we are saying for the
				// survey for this particular study, we want these criteria; if that >same<
				// survey is used for another study, we want different criteria.
				foreach ($this->_criteria as $criterion) {
					/* @var $criterion Study_SurveyCriterion */
					$criterionMap = new Study_SurveyCriterionMap();
					$criterionMap->study_survey_map_id = $surveyMap->getId();
					$criterionMap->survey_criterion_id = $criterion->getId();
					$criterionMap->save();
				}
			}
		}
	}
}

