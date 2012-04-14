<?php

class Cms_TableColumn extends Zend_Db_Table_Abstract
{
	protected $_name = 'cms_table_column';
    private $_tableid;
    
  //  public function init($table)
//    {
//        $_tablename = $table;   
//        parent::init();
//    }

	/**
	* setter for the table id
	* @author Peter Connolly
	*/
	protected function setID($id)
	{
			$this->_tableid = $id;
	}
	
	/**
	* getter for the table id
	* @author Peter Connolly
	*/
	protected function getID()
	{
			return $this->_tableid;
	}
	/**
	* Count the number of records (i.e defined columns) for a table ID
	* 
	* @param mixed $tableid
	* @author Peter Connolly
	*/
	public function getCount() 
    {
        $select = $this->select();
        $select->from($this, array('count(*) as amount'));
        $select->where('cms_table_list_id = ?', $this->_tableid);
        $rows = $this->fetchAll($select);
        
        return($rows[0]->amount);        
    }
    

    /**
    * Test if this table is already defined
    * 
    * @param mixed $cms_table_list_id
    * @returns true if at least one row exists in the table
    * @author Peter Connolly
    */
    public function exists($cms_table_list_id)
    {
    	
         $this->setID($cms_table_list_id);
         
         $count = $this->getCount();
         
         if ($count > 0) {
             return true;
         } else {
             return false;
         }
    }
    
    /**
    * put your comment there...
    * 
    * @param mixed $field
    * @param mixed $type
    */
    public function newRow($field, $type,$displayorder=10) 
    {
    	//printf("<p>Inserting new row. Table is [%s], Key is [%s],Value is [%s]</p>",$this->getID(),$field,$type);
    	$id = $this->getID();
    	$row = $this->createRow();
    	$row->cms_table_list_id = $id;
    	$row->field_name = $field;
    	$row->field_label = ucwords(str_replace("_"," ",$field));
    	$row->field_type = $type;
    	$row->display_order = $displayorder;
    	$row->field_content = $this->deriveSaySoContent($field,$type);
    	$row->save();

	}
	
	/**
	* Determine the correct SaySo content type for validation on the form
	* 
	* @param mixed $field
	* @param mixed $type
	* @return string The valid name of a SaySo Content Type
	* @author Peter Connolly
	*/
	public function deriveSaySoContent($field, $type)
	{
		return $type;
	}

    /**
    * Load the table with a set of table and column definitions for this table
    * 
    * @param 
    * @author Peter Connolly
    */
    public function load($tablename)
    {
    
        // Table columns will be an array of something or other.
        // Get the name of the table
        $sql = sprintf('SHOW FULL COLUMNS FROM %s',$tablename);// . $this->quoteIdentifier("$schemaName.$tableName", true);
          
        $stmt = Db_Pdo::fetchAll($sql);
        
        $_displayorder = 10;
        foreach ($stmt as $key=>$value) {
        	$this->newRow($value['Field'],$value['Type'],$_displayorder);
        	$_displayorder = $_displayorder + 10;
		}

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

	public function displayMatrixAction()
	{
					 $grid = new Cms_Matrix();
     
    
	        //$grid->setSource(new Bvb_Grid_Source_Zend_Table(new Cms_TableColumn));
     $grid->setSource(new Bvb_Grid_Source_Zend_Table($this));
        $form = new Bvb_Grid_Form($class="Zend_Form",$options=array());
    //    $form->setEdit(true); // Add the edit button to our form
        $field_name = new Form_Element_Text('field_name');
        $field_name->setHelpText("The field name");
        $field_name->setReadonly();
        
        $form->addElements(array($field_name));
       // print_r($form);
        $grid->setForm($form);
     //   print_r($grid);
       // $grid->deploy();
        $this->view->grid = $grid->deploy();
 
        
        
        
	}
	
	public function displayMatrix2($grid)
	{
					 //$grid = new Cms_Matrix();
     
    printf("Display Matric 2");
	        //$grid->setSource(new Bvb_Grid_Source_Zend_Table(new Cms_TableColumn));
    // $grid->setSource(new Bvb_Grid_Source_Zend_Table($this));
        $form = new Bvb_Grid_Form($class="Zend_Form",$options=array());
    //    $form->setEdit(true); // Add the edit button to our form
        $field_name = new Form_Element_Text('field_name');
        $field_name->setHelpText("The field name");
        $field_name->setReadonly();
        
        $form->addElements(array($field_name));
       // print_r($form);
        $grid->setForm($form);
     //   print_r($grid);
       // $grid->deploy();
        $this->view->grid = $grid->deploy();
 
        
        
        
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

