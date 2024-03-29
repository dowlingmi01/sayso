<?php

require_once APPLICATION_PATH . '/modules/admin/controllers/CommonController.php';

class Admin_StudyController extends Admin_CommonController
{

	public function init()
	{
		parent::init();

		if (!$this->_request->isXmlHttpRequest())
		{
			$this->setLayoutBasics();
		}

	}

	public function indexAction()
	{
		if(!$this->checkAccess(array('superuser')))
		{
			$this->_helper->viewRenderer->setNoRender(true);
		}

		$this->view->headScript()->appendFile('/modules/admin/study/index.js');
		$this->view->headLink()->appendStylesheet('/modules/admin/study/module.css', 'screen');
		$this->view->addLink = '<a href="' . $this->view->url(array('action' => 'add')) . '">Create A New Study</a>';

		$grid   = new Data_Markup_Grid();
		$select = Zend_Registry::get('db')->select()->from('study');
		$grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
		$grid->setGridColumns(
			array(
				'id', 'name', 'show_timeline', 'show_is_stopped',
				'show_status', 'progress',
				'edit', 'delete'
			)
		);


		/*$extraColumnBegin = new Bvb_Grid_Extra_Column();
		$extraColumnBegin
			->position('right')
			->name('show_begin_date')
			->title('Begin Date')
			->class('align-left important')
			->callback(
				array(
					'function'  => array($this, 'generateBeginDate'),
					'params'	=> array('{{id}}', '{{begin_date}}', '{{status}}')
				)
			);
		$grid->addExtraColumns($extraColumnBegin);

		$extraColumnEnd = new Bvb_Grid_Extra_Column();
		$extraColumnEnd
			->position('right')
			->name('show_end_date')
			->title('End Date')
			->class('align-left important')
			->callback(
				array(
					'function'  => array($this, 'generateEndDate'),
					'params'	=> array('{{id}}', '{{end_date}}', '{{status}}')
				)
			);
		$grid->addExtraColumns($extraColumnEnd);*/

		$extraColumnTimeline = new Bvb_Grid_Extra_Column();
		$extraColumnTimeline
			->position('right')
			->name('show_timeline')
			->title('Timeline')
			->class('align-left important')
			->callback(
				array(
					'function'  => array($this, 'generateTimeline'),
					'params'	=> array('{{id}}', '{{begin_date}}', '{{end_date}}', '{{status}}')
				)
			);
		$grid->addExtraColumns($extraColumnTimeline);

		$extraColumnProgress = new Bvb_Grid_Extra_Column();
		$extraColumnProgress
			->position('right')
			->name('progress')
			->title('Progress')
			->class('align-left')
			->callback(
				array(
					'function'  => array($this, 'generateProgressLinks'),
					'params'	=> array('{{id}}')
				)
			);
		$grid->addExtraColumns($extraColumnProgress);

// The following removed as requested on #383
//		$extraColumnStatus = new Bvb_Grid_Extra_Column();
//		$extraColumnStatus
//			->position('right')
//			->name('show_status')
//			->title('Status')
//			->callback(
//				array(
//					'function'  => array($this, 'generateStatusButtonLink'),
//					'params'	=> array('{{id}}', '{{status}}', '{{begin_date}}', '{{end_date}}')
//				)
//			);
//		$grid->addExtraColumns($extraColumnStatus);

		$extraColumnIsStopped = new Bvb_Grid_Extra_Column();
		$extraColumnIsStopped
			->position('right')
			->name('show_is_stopped')
			->title(' ')
			->class('align-left important')
			->callback(
				array(
					'function'  => array($this, 'generateStoppedControls'),
					'params'	=> array('{{id}}', '{{is_stopped}}')
				)
			);
		$grid->addExtraColumns($extraColumnIsStopped);

		/*$extraColumnProgress = new Bvb_Grid_Extra_Column();
		$extraColumnProgress
			->position('right')
			->name('progress')
			->title(' ')
			->class('td-progress')
			->callback(
				array(
					'function'  => array($this, 'generateProgress'),
					'params'	=> array('{{begin_date}}', '{{end_date}}', '{{status}}')
				)
			);
		$grid->addExtraColumns($extraColumnProgress);*/

		$extraColumnEdit = new Bvb_Grid_Extra_Column();
		$extraColumnEdit
			->position('right')
			->name('edit')
			->title(' ')
			->callback(
				array(
					'function'  => array($this, 'generateEditButtonLink'),
					'params'	=> array('{{id}}', '{{status}}')
				)
			);
		$grid->addExtraColumns($extraColumnEdit);

		$extraColumnDelete = new Bvb_Grid_Extra_Column();
		$extraColumnDelete
			->position('right')
			->name('delete')
			->title(' ')
			->callback(
				array(
					'function'  => array($this, 'generateDeleteButtonLink'),
					'params'	=> array('{{id}}', '{{status}}')
				)
			);
		$grid->addExtraColumns($extraColumnDelete);

		$grid->updateColumn('id',
			array(
				'class' => 'align-right'
			)
		);

		$grid->updateColumn('name',
			array(
				'callback' => array(
					'function'  => array($this, 'generateEditLink'),
					'params'	=> array('{{id}}', '{{name}}', '{{status}}')
				),
				'class' => 'align-left important'
			)
		);

		/**
		 * @see http://code.google.com/p/zfdatagrid/wiki/GridOptions
		 */

		$this->view->grid = $grid->deploy();
	}

	public function generateProgress($beginDate, $endDate, $status)
	{
		$progress = 0;
		if($status)
		{
			$progress = Study::getProgressPercentile($beginDate, $endDate);
		}
		return sprintf('<div class="button-show-progress" data-rel="%d" title="Total Progress: %d%%"></div>', $progress, $progress);
	}

	public function generateProgressLinks($id)
	{
		return
			'<a href="javascript:void(null)" class="study-progress">Quotas</a>'
			. ' <a href="javascript:void(null)" class="study-progress">Cells</a>'
			. ' <a href="javascript:void(null)" class="study-progress">Surveys</a>'
			. ' <a href="javascript:void(null)" class="study-progress">Reports</a>' ;
	}

	public function generateEditLink($id, $name, $status)
	{
		$filter = new Zend_Filter_Alnum(true);
		$name = $filter->filter($name);

		if($status)
		{
			return $name ? $name : '<span class="disabled">name malformed</span>';
		}

		return '<a href="' . $this->view->url(array('action' => 'edit', 'study_id' => intval($id))) . '">'.
			($name ? $name : '<span class="disabled">name malformed</span>') .'</a>';
	}

	public function generateEditButtonLink($id, $status)
	{
		if($status)
		{
			return '<div class="align-center">-</div>';
		}
		return  '<a href="' . $this->view->url(array('action' => 'edit', 'study_id' => intval($id)))
					. '" class="button-edit" title="Edit"></a>';
	}

	public function generateDeleteButtonLink($id, $status)
	{
		if($status)
		{
			return '<div class="align-center">-</div>';
		}
		return  '<a href="' . $this->view->url(array('action' => 'delete', 'study_id' => intval($id)))
					. '" class="button-delete" title="Delete"></a>';
	}

	public function generateBeginDate($id, $beginDate, $status)
	{
		return  '<span class="admin-date" data-id="'.$id.'">' . $beginDate . '</span>'
			. ($status ? '' : ' &nbsp; <input type="hidden" class="date-begin-visible button-datepicker" value="'.$beginDate.'" />');
	}

	public function generateEndDate($id, $endDate, $status)
	{
		return  '<span class="admin-date" data-id="'.$id.'">' . $endDate . '</span>'
			. ($status ? '' : ' &nbsp; <input type="hidden" class="date-end-visible button-datepicker" value="'.$endDate.'" />');
	}

	public function generateTimeline($id, $beginDate, $endDate, $status)
	{
		return  '<span><span class="admin-date" data-id="'.$id.'">' . substr($beginDate, 0, 10) . '</span>'
			. ($status ? '' : ' <input type="hidden" class="date-begin-visible button-datepicker" value="'.$beginDate.'" /></span>')
			. ' - <span><span class="admin-date" data-id="'.$id.'">' . substr($endDate, 0, 10) . '</span>'
			. ($status ? '' : ' <input type="hidden" class="date-end-visible button-datepicker" value="'.$endDate.'" /></span>');
	}

	public function generateStatusButtonLink($id, $status, $begin, $end)
	{
		$allStatuses = Study::getStatusArray();

		// check for non-database status
		if($status == Study::STATUS_LAUNCHED)
		{
			if(Study::hasStatusLive($begin, $end))
			{
				$status = Study::STATUS_LIVE;
			}
			if(Study::hasStatusComplete($end))
			{
				$status = Study::STATUS_COMPLETE;
			}
		}

		return  '<a href="javascript:void(null);" rel="'.$id.'" data-status="'.$status.'"'
					. ' class="'. $allStatuses[$status]['icon-class'] .' change-status" title="'
					. $allStatuses[$status]['label'] .'"></a>';
	}

	public function generateStoppedControls($id, $is_stopped)
	{
		return  '<div class="y-n">'
			. sprintf('<input type="checkbox" class="y-n-switch" id="yn-%d"%s '
				.'title="Click to toggle stopped status (currently %s)" value="%d" />',
					$id, ($is_stopped ? ' checked="checked"' : ''), ($is_stopped ? 'stopped' : 'live'), $id)
		.'</div>';
	}

	public function setTimeAction()
	{
		if(!$this->checkAccess(array('superuser')))
		{
			die('Access denied!');
		}
		$messages = array();

		/**
		 * @todo filter input
		 */

		$field	  = $this->_getParam('field');
		$value	  = $this->_getParam('date');
		$study_id   = $this->_getParam('study_id');

		if (in_array($field, array('begin_date', 'end_date')) && $value && $study_id)
		{
			Record::beginTransaction();
			try
			{
				$study  = new Study();
				$study->loadData($study_id);

				if($study->status)
				{
					throw new Exception('You cannot edit study with the status other than in-design!');
				}

				$study->$field = $value;
				$study->save();
				Record::commitTransaction();
			}
			catch(Exception $e)
			{
				$messages[] = 'Error: entry cannot be saved!';
				if(getenv('APPLICATION_ENV') != 'production')
				{
					$messages[] = $e->getMessage();
				}
			}
		}
		$content = array('messages' => $messages);
		echo json_encode($content);
		exit(0);
	}

	public function setStatusAction()
	{
		if(!$this->checkAccess(array('superuser')))
		{
			die('Access denied!');
		}

		$messages   = array();
		$result	 = false;

		/**
		 * @todo filter input
		 */

		// currently the status is set to 10, all the rest statuses are auto
		// left for scalability
		$status	 = $this->_getParam('status');
		$study_id   = $this->_getParam('study_id');
		$study	  = new Study();
		$study->loadData($study_id);

		if ($study->getId() > 0)
		{
			Record::beginTransaction();
			try
			{

				if($study->status)
				{
					throw new Exception('Status already set!');
				}

				// the below will throw a meaningful error
				// if the data in study is not good for launching
				$study->checkLaunchValidity();
				// all good, continue...
				$study->status = $status;
				$study->save();
				Record::commitTransaction();
				$result = true;
			}
			catch(Exception $e)
			{
				$messages[] = 'Error: entry cannot be saved!';
				if(getenv('APPLICATION_ENV') != 'production')
				{
					$messages[] = $e->getMessage();
				}
			}
		}
		$content = array('messages' => $messages, 'result' => $result);
		echo json_encode($content);
		exit(0);
	}

	public function setStoppedAction()
	{
		if(!$this->checkAccess(array('superuser')))
		{
			die('Access denied!');
		}

		$messages   = array();
		$result	 = false;

		/**
		 * @todo filter input
		 */

		$study_id   = $this->_getParam('study_id');
		$study	  = new Study();
		$study->loadData($study_id);

		if ($study->getId() > 0)
		{
			Record::beginTransaction();
			try
			{
				$study->is_stopped = !$study->is_stopped;
				$study->save();
				Record::commitTransaction();
				$result = true;
			}
			catch(Exception $e)
			{
				$messages[] = 'Error: entry cannot be saved!';
				if(getenv('APPLICATION_ENV') != 'production')
				{
					$messages[] = $e->getMessage();
				}
			}
		}
		$content = array('messages' => $messages, 'result' => $result);
		echo json_encode($content);
		exit(0);
	}

	public function addAction()
	{
		if(!$this->checkAccess(array('superuser')))
		{
			$this->_helper->viewRenderer->setNoRender(true);
		}

		$this->view->headLink()->appendStylesheet('/modules/admin/study/module.css', 'screen');
		$this->view->headScript()->appendFile('/modules/admin/study/study.js');
		$this->view->headScript()->appendFile('/modules/admin/study/add.js');
		$this->view->indexLink = '<a href="' . $this->view->url(array('action' => 'index')) . '">Back to All Studies</a>';

		$this->view->form = new Form_Study_AddEdit();
		$this->view->form->buildDeferred();

		if ($this->_request->isPost() && $this->view->form->isValid($_POST))
		{
			Record::beginTransaction();
			try
			{
				$study  = new Study();
				$values = $this->view->form->getValues();
				Study::saveStudyFromValues($study, $this->currentUser, $values);
				Record::commitTransaction();
				$this->msg->addMessage('Success: entry saved!');
				$this->rd->gotoSimple('index');
			}
			catch(Exception $e)
			{
				$this->msg->addMessage('Error: entry cannot be saved!');
				if(getenv('APPLICATION_ENV') != 'production')
				{
					$this->msg->addMessage($e->getMessage());
				}
			}
		}
	}

	public function editAction()
	{
		if(!$this->checkAccess(array('superuser')))
		{
			$this->_helper->viewRenderer->setNoRender(true);
		}

		$this->view->headScript()->appendFile('/js/jquery.form.min.js');
		$this->view->headScript()->appendFile('/modules/admin/study/study.js');
		$this->view->headScript()->appendFile('/modules/admin/study/edit.js');
		$this->view->headLink()->appendStylesheet('/modules/admin/study/module.css', 'screen');

		$this->view->indexLink = '<a href="' . $this->view->url(array('action' => 'index', 'study_id' =>null)) . '">Back to All Studies</a>';
		$this->view->addLink = '<a href="' . $this->view->url(array('action' => 'add')) . '">Create A New Study</a>';

		$study = new Study();

		$study->loadData(intval($this->_getParam('study_id')));

		if($study->status)
		{
			$this->msg->addMessage('You can edit studies with in-design status only!');
			$this->rd->gotoSimple('index');
		}

		if(false === $study->id > 0)
		{
			throw new Exception('Bad parameters, possibly a security issue..!');
			$this->rd->gotoSimple('index');
		}
		$this->view->study = $study;

		$this->view->form = new Form_Study_AddEdit();
		$this->view->form->setStudy($study);
		$this->view->form->setActionURL(
			$this->view->url(array('action' => 'edit', 'study_id' => $study->id))
		);
		$this->view->form->buildDeferred();

		if ($this->_request->isPost() && $this->view->form->isValid($_POST))
		{
			Record::beginTransaction();
			try
			{
				$values = $this->view->form->getValues();
				Study::saveStudyFromValues($study, $this->currentUser, $values, 'update');
				Record::commitTransaction();
				$this->msg->addMessage('Success: entry saved!');
				$this->rd->gotoSimple('index');
			}
			catch(Exception $e)
			{
				$this->msg->addMessage('Error: entry cannot be saved!');
				if(getenv('APPLICATION_ENV') != 'production')
				{
					$this->msg->addMessage($e->getMessage());
				}
			}
		}
		else
		{
			$details = array(
				// type
				'radioProduct'	  => $study->study_type,
				// basics
				'txtStudyName'	  => $study->name,
				'txtStudyId'		=> $study->study_id,
				'txtSampleSize'	 => $study->size,
				'txtMinThreshold'   => $study->size_minimum,
				'txtBegin'		  => Data_FormatTools::mysqlDateToDisplay($study->begin_date),
				'txtEnd'			=> Data_FormatTools::mysqlDateToDisplay($study->end_date),
				'radioIsSurvey'	 => $study->click_track,
			);
			$this->view->form->populate($details);
		}
	}

	public function deleteAction()
	{
		if(!$this->checkAccess(array('superuser')))
		{
			$this->_helper->viewRenderer->setNoRender(true);
		}

		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);

		try
		{
			$study = new Study();
			$study->loadData(intval($this->_getParam('study_id')));

			if($study->status)
			{
				$this->msg->addMessage('You can delete studies with in-design status only!');
				$this->rd->gotoSimple('index');
			}

			if(false === $study->id > 0)
			{
				throw new Exception('Bad parameters, possibly a security issue..!');
			}
			$study->delete();
		}
		catch(Exception $e)
		{
			$this->msg->addMessage('Operation caused exception!');
			if(getenv('APPLICATION_ENV') != 'production')
			{
				$this->msg->addMessage($e->getMessage());
			}
			$this->rd->gotoSimple('index');
		}

		$this->msg->addMessage('Entry deleted!');
		$this->rd->gotoSimple('index');
	}

	/**
	 * @deprecated To be deleted after addAction is done ...
	 */
	public function createNewAction ()
	{
		if(!$this->checkAccess(array('superuser')))
		{
			die('Access denied!');
		}

		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);

		$data	   = $this->_getParam('data');
		$type	   = $data['type'];

		$response   = array('result' => false, 'messages' => array());

		switch ($type) {
			case 'ADjuster Creative' :
				$tagsDomainsData = $data['domainAvail'];
				break;
			case 'ADjuster Campaign' :
			default :
				$tagsDomainsData = $data['tagdomain'];
				break;
		}

		// Transaction is closed within try{} block
		// and never comits in case of exception...
		Record::beginTransaction();

		try
		{
			// study

			$study = new Study();
			$study->user_id		 = $this->currentUser->id;
			$study->name			= $data['basic']['name'];
			$study->size			= $data['basic']['size'];
			$study->size_minimum	= $data['basic']['minimum'];
			$study->begin_date	  = convertLameDateFormat(isset($data['basic']['begindate']) ? strval($data['basic']['begindate']) : '' );
			$study->end_date		= convertLameDateFormat(isset($data['basic']['enddate']) ? strval($data['basic']['enddate']) : '');
			$study->click_track	 = $data['metrics']['clicktrack'] === 'Yes' ? 1 : 0;
			$study->save();

			// search engines

			if(isset($data['metrics']) && isset($data['metrics']['searchengineIds']) && !empty($data['metrics']['searchengineIds']))
			{
				foreach ($data['metrics']['searchengineIds'] as $searchEngineId)
				{
					$map					= new Study_SearchEnginesMap();
					$map->study_id		  = $study->getId();
					$map->search_engines_id = $searchEngineId;
					$map->save();
				}
			}

			// social activity types

			if(isset($data['metrics']) && isset($data['metrics']['socialIds']) && !empty($data['metrics']['socialIds']))
			{
				foreach ($data['metrics']['socialIds'] as $socialId)
				{
					$map							= new Study_SocialActivityTypeMap();
					$map->study_id				  = $study->getId();
					$map->social_activity_type_id   = $socialId;
					$map->save();
				}
			}

			// survey

			// for now only support surveys with urls
			if (isset($data['surveyinfo']) && isset($data['surveyinfo']['url']))
			{
				$survey			 = new Study_Survey();
				$survey->url		= $data['surveyinfo']['url'];
				$survey->setStudy($study);
				foreach ($data['surveyinfo']['deliverIf'] as $deliverIf)
				{
					$criterion				  = new Study_SurveyCriterion();
					$criterion->site			= $deliverIf['domain'];
					$criterion->timeframe_id	= $deliverIf['timeframeId'];
					$survey->addCriterion($criterion);
				}
				$survey->save();
			}

			// tags / domains

			$tags = new Study_TagCollection();
			$tagsByClientIds = array(); // Tag objects by client side guids
			if(!empty($tagsDomainsData))
			{
				foreach ($tagsDomainsData as $tagClientId => $tagDomainData)
				{
					$tag = new Study_Tag();
					$tag->name	  = $tagDomainData['label'];
					$tag->tag	   = $tagDomainData['tag'];
					$tag->type	   = $tagDomainData['type'];
					$tag->target_url = $tagDomainData['targetUrl'];
					$tag->user_id   = $this->currentUser->id;
					$tags->addItem($tag);

					// this is used below for ADjuster to grab the correct mapped tag
					$tagsByClientIds[$tagClientId] = $tag;
					if (!empty($tagDomainData['domain']))
					{
						// add domains for this Tag
						foreach ($tagDomainData['domain'] as $domainData)
						{
							$domain			 = new Study_Domain();
							$domain->domain	 = $domainData['name'];
							$domain->user_id	= $this->currentUser->id;
							$tag->addDomain($domain);
						}
					}
				}
				$tags->save();
			}

			// creatives

			if ($type === 'ADjuster Creative')
			{
				$creatives = new Study_CreativeCollection();
				// don't resave the tags, just the mappings see Study_Creative
				Study_Creative::$saveTagsOnSave = false;
				foreach ($data['creative'] as $creativeData)
				{
					$creative = new Study_Creative();
					$creative->user_id	  = $this->currentUser->id;
					$creative->mime_type_id = $creativeData['contentType'];
					$creative->name		 = $creativeData['name'];
					$creative->type		 = $creativeData['type'];
					$creative->url		  = $creativeData['creativeUrl'];
					$creative->target_url   = $creativeData['targetUrl'];
					$creative->ad_title		 = $creativeData['adTitle'];
					$creative->ad_description		 = $creativeData['adDescription'];
					// associate Study for this Creative
					$creative->setStudy($study);
					$creatives->addItem($creative);
					// add Tags mapped to this Creative
					if (!empty($creativeData['domainAvail'])) {
						foreach ($creativeData['domainAvail'] as $domainAvailClientId)
						{
							$creative->addTag($tagsByClientIds[$domainAvailClientId]);
						}
					}
				}
				$creatives->save();
			}

			// quotas

			foreach ($data['quota'] as $quotaData)
			{
				$quota				  = new Study_Quota();
				$quota->study_id		= $study->getId();
				$quota->percentile_id   = $quotaData['percentId'];
				$quota->gender_id	   = $quotaData['genderId'];
				$quota->age_range_id	= $quotaData['ageId'];
				$quota->ethnicity_id	= $quotaData['ethnicityId'];
				$quota->save();
			}

			// cells

			foreach ($data['cells'] as $cellData)
			{
				$cell			   = new Study_Cell();
				$cell->study_id	 = $study->getId();
				$cell->description  = $cellData['description'];
				$cell->size		 = $cellData['size'];
				$cell->cell_type	= $cellData['type'];
				$cell->save();

				foreach ($cellData['adtag'] as $adTagId)
				{
					$tag = $tagsByClientIds[$adTagId];
					/* @var $tag Study_Tag */
					$map			= new Study_CellTagMap();
					$map->cell_id   = $cell->getId();
					$map->tag_id	= $tag->getId();
					$map->save();
				}
				if (isset($cellData['qualifier']))
				{
					if (isset($cellData['qualifier']['browse']))
					{
						foreach ($cellData['qualifier']['browse'] as $browseQualifierData)
						{
							$browseQualifier			= new Study_CellBrowsingQualifier();
							$browseQualifier->cell_id   = $cell->getId();
							if ($browseQualifierData['include'] === 'Exclude')
							{
								$browseQualifier->exclude = 1;
							}
							$browseQualifier->site	  = $browseQualifierData['site'];
							if ($browseQualifierData['timeframeId'])
							{
								$browseQualifier->timeframe_id = $browseQualifierData['timeframeId'];
							}
							$browseQualifier->save();
						}
					}
					if (isset($cellData['qualifier']['search']))
					{
						foreach ($cellData['qualifier']['search'] as $searchQualifierData)
						{
							$searchQualifier			= new Study_CellSearchQualifier();
							$searchQualifier->cell_id   = $cell->getId();
							if ($searchQualifierData['include'] === 'Exclude')
							{
								$searchQualifier->exclude = 1;
							}
							$searchQualifier->term	  = $searchQualifierData['term'];
							if ($searchQualifierData['timeframeId'])
							{
								$searchQualifier->timeframe_id = $searchQualifierData['timeframeId'];
							}
							$searchQualifier->save();
							if ($searchQualifierData['whichIds'])
							{
								foreach ($searchQualifierData['whichIds'] as $searchEngineId)
								{
									$map							= new Study_CellSearchQualifierMap();
									$map->cell_qualifier_search_id  = $searchQualifier->getId();
									$map->search_engines_id		 = $searchEngineId;
									$map->save();
								}
							}
						}
					}
				}
			}

			Record::commitTransaction();
			$response['result'] = true;
			$response['messages'][] = 'Study saved!';
		}
		catch(Exception $e)
		{
			$response['result'] = false;
			$response['messages'][] = 'Exception occurred while saving new study';
			if(getenv('APPLICATION_ENV') != 'production')
			{
				$response['messages'][] = $e->getMessage();
			}
		}

		echo json_encode($response);
		exit(0);
	}
}

/**
 * Simple function to convert American-style dates
 * to dates that make sense. :)
 * (used in the above class)
 *
 * @param string $date
 *
 * @todo Create tools repo under application/models/Data/Tools, set as a static method and refactor usage
 *
 */
function convertLameDateFormat ($date)
{
	$parts = explode('/', $date);
	return
		count($parts) == 3
		? ($parts[2] . '-' . $parts[0] . '-' . $parts[1])
		: '0000-00-00';
}
