<?php
    /**
     * controllers/CmsController.php
     * @author Peter Connolly, March 2012
     */

    require_once APPLICATION_PATH . '/modules/admin/controllers/CommonController.php';

    class Cms_AdminController extends Admin_CommonController
    {
	    /**
	     * @var Zend_Controller_Action_Helper_FlashMessenger
	     */
	    protected $msg;
	    /**
	     * @var Zend_Controller_Action_Helper_Redirector
	     */
	    protected $rd;


	    public function preDispatch() {
		    // i.e. for everything based on Generic Starbar, use these includes
		    $this->view->headLink()->appendStylesheet('/css/starbar-generic.css');
		    $this->view->headLink()->appendStylesheet('/css/cms.css');
		    $this->view->headScript()->appendFile('/js/starbar/jquery-1.7.1.min.js');
		    $this->view->headScript()->appendFile('/js/starbar/jquery-ui-1.8.16.custom.min.js');
		    $this->view->headScript()->appendFile('/js/starbar/jquery.jscrollpane.min.js');
		    $this->view->headScript()->appendFile('/js/starbar/jquery.cookie.js');
		    $this->view->headScript()->appendFile('/js/starbar/jquery.jeip.js');
		    $this->view->headScript()->appendFile('/js/starbar/jquery.cycle.lite.js');
		    $this->view->headScript()->appendFile('/js/cms/jquery.easyTooltip.js');
		    $this->view->headScript()->appendFile('/js/cms/jquery.ui.slider.js');
		    $this->view->headScript()->appendFile('/js/cms/jquery.ui.datepicker.js');
		    $this->view->headScript()->appendFile('/js/cms/jquery.ui.timepicker-addon.js');
		    $this->view->headScript()->appendFile('/js/cms/init.js');
		
	    }
        
        /**
         * Always called before actions
         */
        public function init()
        {

	        if (!$this->msg) {
			    $this->msg = $this->_helper->FlashMessenger;
	        }

	        if (!$this->rd) {
		        $this->rd = $this->_helper->Redirector;
	        }
        }

       
		
        /**
         * Display a list of all tables in the database records
         * 
	     * @author Peter Connolly
         */
        public function indexAction()
        {
	        $select = Zend_Registry::get('db')->select()->from('cms_table_list');
	        
            $grid   = new Cms_Matrix();
	        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
	        $grid->setGridColumns(array('table_name', 'table_alias', 'allowed','edit','editit'));

	        // To display a checkbox, we need to have a decorator, and we need to apply the format 'checkbox'
	        $grid->updateColumn('allowed',
				    array(
				        'title' => 'Allowed',
				        'align' => 'center',
				        'format' => 'checkbox',
				        'decorator'=>"<input type='checkbox' name='number[]' {{allowed}}  disabled='disabled' >"
				        ));

	        // Add a column which will give us the Edit Table Rows action
		    $extraColumnEdit = new Bvb_Grid_Extra_Column();
		    $extraColumnEdit
			    ->position('left')
			    ->name('editit')
			    ->title(' ')
			    ->callback(
				    array(
					    'function'  => array($this, 'generateEditButtonLink'),
					    'params'	=> array('{{id}}')
				    )
			    );
		    $grid->addExtraColumns($extraColumnEdit);

            
	        $form = new Bvb_Grid_Form($class='Zend_Form', $options=array());
	        $form->setEdit(true); // Add the edit button to our form

	        // Modify the fields. Note that the variable name is irrelevant. If the checkbox has the name of a boolean variable, that
	        // field will be turned into a checkbox.
	        $table_name = new Form_Element_Text('table_name');
	        $table_name->setHelpText("Internal table name");
	        $table_name->setReadonly();

	        $allowed = new Form_Element_Checkbox('allowed');
	        $allowed->setLabel('Allowed')
		        ->setAttrib('id','1-allowed')
		        ->setHelpText("Is this table shown in the CMS system?");

	        $enable_insert = new Form_Element_Checkbox('enable_insert');
	        $enable_insert->setAttrib('id','1-enable_insert')
		        ->setHelpText("Can we insert rows into this table?");

	        $enable_edit = new Form_Element_Checkbox('enable_edit');
	        $enable_edit->setAttrib('id','1-enable_edit')
		        ->setHelpText("Can we edit existing rows in this table?");

	        $enable_delete = new Form_Element_Checkbox('enable_delete');
	        $enable_delete->setAttrib('id','1-enable_delete')
		        ->setHelpText("Can we delete existing rows in this table?");

	        $enable_details = new Form_Element_Checkbox('enable_details');
	        $enable_details->setAttrib('id','1-enable_details')
		       ->setHelpText("Can we see details of this table? (might not use this)");

	        $enable_list = new Form_Element_Checkbox('enable_list');
	        $enable_list->setAttrib('id','1-enable_list')
		        ->setHelpText("Can we view this table as a list?");

	        $form->addElements(array($table_name, $allowed, $enable_insert, $enable_edit, $enable_delete, $enable_details, $enable_list));
	        $grid->setForm($form);

	        $this->view->grid = $grid->deploy();

        }
            
        /**
         * Edit a table
         * 
	     * @author Peter Connolly
         */
        public function editAction()
        {
			$id = $this->getRequest()->getParam('id');
			if ($id===null) {
				printf("<p>No ID found. Cannot edit.</p>");
			} else {
					
	            $tablename = strtolower($this->getRequest()->getParam('table'));
	            $tablenamepolite = ucwords(str_replace("_"," ",$tablename));
	            if ($tablename !== null) {
            		
					// Search for the json file
					// set file to read - Move relative from 'public' to find it.
					
					$userlevel = "superuser";
					// @todo Userlevel will be changed once logins and user level permissions are included.
					
					$file = sprintf('../application/modules/cms/models/%s.json',$userlevel);
					if (file_exists($file)) {
						
						$fh = fopen($file, 'r') or die('Could not open file!');
						$data = fread($fh, filesize($file)) or die('Could not read file!');
						// Process JSON file
						$json = Zend_Json::decode($data);
						fclose($fh);

					
						if (array_key_exists($tablename, $json['superuser'][0])) {
						
							$json = $json['superuser'][0][$tablename][0];
							
							// We need to get the details for the ID from this table
							// Find the columns we want to see on the grid
							$columnlist = $this->_getCMSColumns($json['columns'],"displaywhen","edit");
							
							$select = Zend_Registry::get('db')->select()->from($tablename,$columnlist)->where("id = ?",$id);
							
							$stmt = $select->query();
							$currentData = $stmt->fetchAll();
							if (count($currentData) == 1) {
								$currentData = $currentData[0];
							
								// currentData contains a list of fields and values from the JSON, which we can put in as initial values
						
								// Start with a blank formelements array, and add the array items as we go
								$formElements = array();
				
								foreach ($json['columns'] as $key=>$value) {
							
									// Process the form fields for this table
									// We know there will be a colname and a type
									$colname = $value['colname'];
									$coltype = $value['type'];
									
									if (array_key_exists("listoptions",$value)) {			
										$listoptions = array(); 
										
										foreach ($value['listoptions'] as $listkey=>$listvalue) {
											$listoptions[$listvalue] = $listvalue;
										}
									}
									
									$coloptions = array();
									foreach ($value as $colkey=>$colvalue) {
										if (($colkey!='colname') and ($colkey!='type')) {
											$coloptions[$colkey] = $colvalue;
										}	
									}
								
									// Build this form element
									switch (strtolower($coltype)) {
										case "checkbox":
											$formElements[$colname] = new Form_Element_Checkbox($colname);
											break;
										case "datetime":
											 $formElements[$colname] = new Form_Element_Date($colname,array('jQueryParams' => array('dateFormat' => 'yy-mm-dd')));
											break;
										case "fkey":
											$formElements[$colname] = new Form_Element_Fkey($colname);
											$formElements[$colname]->setParams($value);
											break;
										case "hidden":
											$formElements[$colname] = new Form_Element_Hidden($colname);
											break;
										case "list":
											$formElements[$colname] = new Form_Element_Select($colname);
											$formElements[$colname]->setMultiOptions($listoptions);
											break;
										case "number":
											$formElements[$colname] = new Form_Element_Number($colname);
											break;
										case "string":
											 $formElements[$colname] = new Form_Element_Text($colname);
											break;
										
									}
									// general aspects of a form element
									
									// Override the field label
									if (array_key_exists('label',$coloptions)) {
										$formElements[$colname]->setLabel($coloptions['label']);
									}
									
									// Assign a default value
									if (array_key_exists('value',$coloptions)) {
										$formElements[$colname]->setValue($coloptions['value']);
									}
									
									// Assign a value from the edited record - if there is one. This may override any default value
									if (array_key_exists($colname,$currentData)) {
										$formElements[$colname]->setValue($currentData[$colname]);
									}
									
									// Tooltip help
									if (array_key_exists('help',$coloptions)) {
										$formElements[$colname]->setAttrib("title", $coloptions['help']);
									}
									
									// Display Width
									if (array_key_exists('width',$coloptions)) {
										$formElements[$colname]->setAttrib("size", $coloptions['width']);
									}
								}
							
								// All column elements have been built. Add the standard form elements
								$formElements['submit'] = new Zend_Form_Element_Submit('submit');
            					$formElements['submit'] ->setLabel(sprintf('Save Changes')); // the button's value
				    										//->setIgnore(true); // very usefull -> it will be ignored before insertion
							    $form = new ZendX_JQuery_Form();
            					$form->setName($tablename);
	    						$form->addElements($formElements);
            					$form->addElement('hash', 'no_csrf_foo', array('salt' => 'uniquesay.so'));
	                			
			                // Find the record and populate the initial form values
			                
			               
								if ($this->getRequest()->isPost()) { //is it a post request ?
                					$postData = $this->getRequest()->getPost(); // getting the $_POST data
                					if ($form->isValid($postData)) {
                						
                    					$formData = $form->getValues(); // data filtered
                    					// Update the 'modified' field (don't update the 'Created' field)
                    					$formData += array('modified' => date('Y-m-d H:i:s'));
                   						
                   						unset($formData['no_csrf_foo']); // Remove the salt - we don't need it for an update
                   						// remove any data with null values - we don't need them.
                   						$formData = array_filter($formData,array('self','_notnull'));
                   						
                   						
                   						$db = new Zend_Db_Table($tablename);
                   					
                   						$where = sprintf('id = %s', $id);
	 
										$result = $db->update($formData, $where);
                   						
                   						$this->view->message = $result." record successfully updated ";
                   						            
                					} else {
                						$form->populate($postData); // show errors and populate form with $postData
									}
	    	        			}
								$this->view->tablename = $tablenamepolite;
    	    	    			$this->view->form = $form; // assigning the form to view
							} else {
								$this->view->message = sprintf("Invalid ID [%s] for %s",$id,$tablename);
							}
						} else {
							
							$this->view->message = sprintf("Table definition [%s] does not exist in the JSON file",$tablename);
							
						}
					}
					else {
						
						$this->view->message = sprintf("File [%s] is missing",$file);
					}
		        }
	        }
		}

		/**
		* View one record in detail
		* 
		* @author Peter Connolly
		*/
		public function detailAction()
        {
			$id = $this->getRequest()->getParam('id');
			if ($id===null) {
				printf("<p>No ID found. Cannot display.</p>");
			} else {
					
	            $tablename = strtolower($this->getRequest()->getParam('table'));
	            $tablenamepolite = ucwords(str_replace("_"," ",$tablename));
	            if ($tablename !== null) {
            		
					// Search for the json file
					// set file to read - Move relative from 'public' to find it.
					
					$userlevel = "superuser";
					// @todo Userlevel will be changed once logins and user level permissions are included.
					
					$file = sprintf('../application/modules/cms/models/%s.json',$userlevel);
					if (file_exists($file)) {
						
						$fh = fopen($file, 'r') or die('Could not open file!');
						$data = fread($fh, filesize($file)) or die('Could not read file!');
						// Process JSON file
						$json = Zend_Json::decode($data);
						fclose($fh);

					
						if (array_key_exists($tablename, $json['superuser'][0])) {
						
							$json = $json['superuser'][0][$tablename][0];
							
							// We need to get the details for the ID from this table
							// Find the columns we want to see on the grid
							$columnlist = $this->_getCMSColumns($json['columns'],"displaywhen","detail");
							
							$select = Zend_Registry::get('db')->select()->from($tablename,$columnlist)->where("id = ?",$id);
							
							$stmt = $select->query();
							$currentData = $stmt->fetchAll();
							if (count($currentData) == 1) {
								$currentData = $currentData[0];
							
								// currentData contains a list of fields and values from the JSON, which we can put in as initial values
						
								// Start with a blank formelements array, and add the array items as we go
								$formElements = array();
				
								foreach ($json['columns'] as $key=>$value) {
							
									// Process the form fields for this table
									// We know there will be a colname and a type
									$colname = $value['colname'];
									$coltype = $value['type'];
									
									if (array_key_exists("listoptions",$value)) {			
										$listoptions = array(); 
										
										foreach ($value['listoptions'] as $listkey=>$listvalue) {
											$listoptions[$listvalue] = $listvalue;
										}
									}
									
									$coloptions = array();
									foreach ($value as $colkey=>$colvalue) {
										if (($colkey!='colname') and ($colkey!='type')) {
											$coloptions[$colkey] = $colvalue;
										}	
									}
								
									// Build this form element
									switch (strtolower($coltype)) {
										case "checkbox":
											$formElements[$colname] = new Form_Element_Checkbox($colname);
											$formElements[$colname]->setAttrib("readonly","");
									$formElements[$colname]->setAttrib("class","readonly");
											break;
										case "datetime":
											 $formElements[$colname] = new Form_Element_Date($colname,array('jQueryParams' => array('dateFormat' => 'yy-mm-dd')));
											break;
										case "fkey":
											$formElements[$colname] = new Form_Element_Fkey($colname);
											$formElements[$colname]->setParams($value);
											break;
										case "hidden":
											$formElements[$colname] = new Form_Element_Hidden($colname);
											break;
										case "list":
											$formElements[$colname] = new Form_Element_Select($colname);
											$formElements[$colname]->setMultiOptions($listoptions);
											break;
										case "number":
											$formElements[$colname] = new Form_Element_Number($colname);
											$formElements[$colname]->setAttrib("readonly","");
									$formElements[$colname]->setAttrib("class","readonly");
											break;
										case "string":
											 $formElements[$colname] = new Form_Element_Text($colname);
											 $formElements[$colname]->setAttrib("readonly","");
											$formElements[$colname]->setAttrib("class","readonly");
											break;
										
									}
									// general aspects of a form element
									
									
									// Override the field label
									if (array_key_exists('label',$coloptions)) {
										$formElements[$colname]->setLabel($coloptions['label']);
									}
									
									// Assign a default value
									if (array_key_exists('value',$coloptions)) {
										$formElements[$colname]->setValue($coloptions['value']);
									}
									
									// Assign a value from the edited record - if there is one. This may override any default value
									if (array_key_exists($colname,$currentData) && array_key_exists($colname,$formElements)) {
										$formElements[$colname]->setValue($currentData[$colname]);
									}
									
									// Tooltip help
									if (array_key_exists('help',$coloptions)) {
										$formElements[$colname]->setAttrib("title", $coloptions['help']);
									}
									
									// Display Width
									if (array_key_exists('width',$coloptions)) {
										$formElements[$colname]->setAttrib("size", $coloptions['width']);
									}
								}
							
								// All column elements have been built. Add the standard form elements
							//	$formElements['submit'] = new Zend_Form_Element_Submit('submit');
            				//	$formElements['submit'] ->setLabel(sprintf('Save Changes')); // the button's value
				    										//->setIgnore(true); // very usefull -> it will be ignored before insertion
							    $form = new ZendX_JQuery_Form();
            					$form->setName($tablename);
	    						$form->addElements($formElements);
            					//$form->addElement('hash', 'no_csrf_foo', array('salt' => 'uniquesay.so'));
	                			
			                
			                
			               
							
								$this->view->tablename = $tablenamepolite;
    	    	    			$this->view->form = $form; // assigning the form to view
							} else {
								$this->view->message = sprintf("Invalid ID [%s] for %s",$id,$tablename);
							}
						} else {
							
							$this->view->message = sprintf("Table definition [%s] does not exist in the JSON file",$tablename);
							
						}
					}
					else {
						
						$this->view->message = sprintf("File [%s] is missing",$file);
					}
		        }
	        }
		}

        /**
        * View a table in column format, suitable for selecting records for editing/deleting
        * 
        * @author Peter Connolly
        */
        public function viewAction()
        {
        	$tablename = strtolower($this->getRequest()->getParam('table'));
            $tablenamepolite = ucwords(str_replace("_"," ",$tablename));
            if ($tablename != "") {
            	
				// Search for the json file
				// set file to read - Move relative from 'public' to find it.
				
				$userlevel = "superuser";
				// @todo Userlevel will be changed once logins and user level permissions are included.
				
				$file = sprintf('../application/modules/cms/models/%s.json',$userlevel);
				if (file_exists($file)) {
					
					$fh = fopen($file, 'r') or die('Could not open file!');
					$data = fread($fh, filesize($file)) or die('Could not read file!');
					// Process JSON file
					$json = Zend_Json::decode($data);
					fclose($fh);
					
					// Create the grid
					// Find the columns we want to see on the grid
					$columnlist = $this->_getCMSColumns($json['superuser'][0][$tablename][0]['columns'],"displaywhen","grid");
					$select = Zend_Registry::get('db')->select()->from($tablename,$columnlist)->order("id desc");
					$grid   = new Cms_Matrix();
					$grid->setJqgParams(array('altRows' => true));// rows will alternate color
	        		$grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
	        		
	        		//$grid->setDeleteConfirmationPage(true);
	        		
	        		// Add a column which will give us the Edit Table Rows action
				    $extraColumnEdit = new Bvb_Grid_Extra_Column();
				    $extraColumnEdit
					    ->position('left')
					    ->name('editit')
					    ->title(' ')
					    ->callback(
						    array(
							    'function'  => array($this, '_generateEditButtonLink'),
							    'params'	=> array('{{id}}')
						    )
					    );
				    $grid->addExtraColumns($extraColumnEdit);

 					$extraColumnDetails = new Bvb_Grid_Extra_Column();
				    $extraColumnDetails
					    ->position('left')
					    ->name('details')
					    ->title(' ')
					    ->callback(
						    array(
							    'function'  => array($this, '_generateDetailsButtonLink'),
							    'params'	=> array('{{id}}')
						    )
					    );
		   			$grid->addExtraColumns($extraColumnDetails);
		    		    
					$form = new Bvb_Grid_Form($class='Zend_Form', $options=array());
					
					$form->setDelete(true);
					$grid->setForm($form);
					
					$this->view->tablename = $tablenamepolite;
					$this->view->newRecordLink = sprintf('<span class="newlink"><a href="/cms/admin/add/table/%s/"><img src="/images/icons/add.png" style="width:16px;" alt="Add" Title="Add" /> Add New %s</a></span>',$tablename,$tablenamepolite);
			        $this->view->grid = $grid->deploy();
					
				} else {
					
					$this->view->message = sprintf("File [%s] is missing",$file);
				}
			} else {
					
				$this->view->message = sprintf("Table name is missing");
			}
		}
		
		/**
		* return an array of columns matching the required values
		* 
		* @example
		* $outputarray = getCMSColumns($inputarray,"displaywhen","list")
		* returns an array of one item, 'colnameoftype', given the following input array
		* Example input format:
		* [2] => Array
        *(
        *    [colname] => colnameoftype
        *    [type] => list
        *    [listoptions] => Array
        *        (
        *            [0] => poll
        *            [1] => survey
        *        )
		*
        *    [displaywhen] => Array
        *        (
        *           [0] => add
        *            [1] => list
        *            [2] => edit
        *        )
		*
        *)
        *
		* @param array $inputarray
		* @param string $matchkey -  Key to be searched
		* @param string $matchvalue - Value to be searched in the array
		* @returns array Array of column names which match the value
		* @author Peter Connolly
		*/
		private function _getCMSColumns($inputarray,$matchkey,$matchvalue)
		{
			$returnarray = array();
			foreach ($inputarray as $key=>$value) {
				if (array_key_exists($matchkey,$value)) {
					if (in_array($matchvalue,$value[$matchkey])) {
						$returnarray[] = $value['colname'];
					}
				}
			}
			return $returnarray;
		}
		
		/**
        * Generate a button which will activate the tablefields action
        * 
        * @param mixed $id
        * @author Peter Connolly
        */
        public function _generateEditButtonLink($id)
	    {
        $link = '<a href="' . $this->view->url(array('action' => 'edit', 'id' => intval($id))). '" class="button-edit" title="Edit"><img src="/images/icons/pencil.png" style="width:16px;" alt="Edit" Title="Edit" /></a>';

		    return $link;
	    }

/**
        * Generate a button which will activate the view action
        * 
        * @param mixed $id
        * @author Peter Connolly
        */
        public function _generateDetailsButtonLink($id)
	    {
	    	$link = '<a href="' . $this->view->url(array('action' => 'detail', 'id' => intval($id))). '" class="button-details" title="Edit"><img src="/images/icons/information.png" style="width:16px;" alt="Details" Title="Details" /></a>';
		    return $link;
	    }
	    		
        /**
         * Add a table row
         * 
	     * @author Peter Connolly
         */
        public function addAction()
        {

            $tablename = strtolower($this->getRequest()->getParam('table'));
            $tablenamepolite = ucwords(str_replace("_"," ",$tablename));
            if ($tablename != "") {
            	
				// Search for the json file
				// set file to read - Move relative from 'public' to find it.
				
				$userlevel = "superuser";
				// @todo Userlevel will be changed once logins and user level permissions are included.
				
				$file = sprintf('../application/modules/cms/models/%s.json',$userlevel);
				if (file_exists($file)) {
					
					$fh = fopen($file, 'r') or die('Could not open file!');
					$data = fread($fh, filesize($file)) or die('Could not read file!');
					// Process JSON file
					$json = Zend_Json::decode($data);
					fclose($fh);

				
					if (array_key_exists($tablename, $json['superuser'][0])) {
					
						$json = $json['superuser'][0][$tablename][0];
			
						// Start with a blank formelements array, and add the array items as we go
						$formElements = array();
		
						foreach ($json['columns'] as $key=>$value) {
					
							// Process the form fields for this table
							// We know there will be a colname and a type
							$colname = $value['colname'];
							$coltype = $value['type'];
							
							if (array_key_exists("listoptions",$value)) {			
								$listoptions = array(); 
								
								foreach ($value['listoptions'] as $listkey=>$listvalue) {
									$listoptions[$listvalue] = $listvalue;
								}
							}
							
							$coloptions = array();
							foreach ($value as $colkey=>$colvalue) {
								if (($colkey!='colname') and ($colkey!='type')) {
									$coloptions[$colkey] = $colvalue;
								}	
							}
						
							// Build this form element
							switch (strtolower($coltype)) {
								case "checkbox":
									$formElements[$colname] = new Form_Element_Checkbox($colname);
									break;
								case "datetime":
									 $formElements[$colname] = new Form_Element_Date($colname,array('jQueryParams' => array('dateFormat' => 'yy-mm-dd')));
									break;
								case "fkey":
									$formElements[$colname] = new Form_Element_Fkey($colname);
									$formElements[$colname]->setParams($value);
									break;
								case "hidden":
									$formElements[$colname] = new Form_Element_Hidden($colname);
									break;
								case "list":
									$formElements[$colname] = new Form_Element_Select($colname);
									$formElements[$colname]->setMultiOptions($listoptions);
									break;
								case "number":
									$formElements[$colname] = new Form_Element_Number($colname);
									break;
								case "string":
									 $formElements[$colname] = new Form_Element_Text($colname);
									break;
								
							}
							// general aspects of a form element
							
							// Override the field label
							if (array_key_exists('label',$coloptions)) {
								$formElements[$colname]->setLabel($coloptions['label']);
							}
							
							// Assign a default value
							if (array_key_exists('value',$coloptions)) {
								$formElements[$colname]->setValue($coloptions['value']);
							}
							
							// Tooltip help
							if (array_key_exists('help',$coloptions)) {
								$formElements[$colname]->setAttrib("title", $coloptions['help']);
							}
							
							// Display Width
							if (array_key_exists('width',$coloptions)) {
								$formElements[$colname]->setAttrib("size", $coloptions['width']);
							}
						}
					
						// All column elements have been built. Add the standard form elements
						$formElements['submit'] = new Zend_Form_Element_Submit('submit');
            			$formElements['submit'] ->setLabel(sprintf('Save New %s',$tablenamepolite)); // the button's value
				    								//->setIgnore(true); // very usefull -> it will be ignored before insertion
					    $form = new ZendX_JQuery_Form();
            			$form->setName($tablename);
	    				$form->addElements($formElements);
            			$form->addElement('hash', 'no_csrf_foo', array('salt' => 'uniquesay.so'));
	                	
	                
						if ($this->getRequest()->isPost()) { //is it a post request ?
                			$postData = $this->getRequest()->getPost(); // getting the $_POST data
                			if ($form->isValid($postData)) {
                				
                    			$formData = $form->getValues(); // data filtered
                    			// created and updated fields
                    			$formData += array('created' => date('Y-m-d H:i:s'), 'modified' => date('Y-m-d H:i:s'));
                   				
                   				unset($formData['no_csrf_foo']); // Remove the salt - we don't need it for the insert
                   				// remove any data with null values - we don't need them.
                   				$formData = array_filter($formData,array('self','_notnull'));
                   				
                   				
                   				$db = new Zend_Db_Table($tablename);
                   				$result = $db->insert($formData);
                   				
                   				$this->view->message = "Record successfully added ".$result;
                   				$form->reset();
                   				
								
                 
                			} else {
                				$form->populate($postData); // show errors and populate form with $postData
							}
	    	        	}
						$this->view->tablename = $tablenamepolite;
    	    	    	$this->view->form = $form; // assigning the form to view
					
					} else {
						
						$this->view->message = sprintf("Table definition [%s] does not exist in the JSON file",$tablename);
						
					}
				}
				else {
					
					$this->view->message = sprintf("File [%s] is missing",$file);
				}
	        }
		}
        
        /**
        * Callback function to remove empty values from a supplied array.
        * Used in the array_filter call
        * 
        * @param mixed $var
        * @author Peter Connolly
        */
        private function _notnull($var)
        {
        	if ($var!==null) {
        		return $var;
			}
		}
        /**
        * Given a table name and an associative array of data, this function builds a 
        * valid insert statement - fully escaped, ready for execution.
        * 
        * @param string $tablename
        * @param array $formData
        * @return String MySQL Insert Statement
        * @author Peter Connolly
        */
        private function _buildInsert($tablename, $formData) 
        {
        	$cols = "";
        	$vals = "";
        		
        		// Get list of values
        		foreach ($formData as $key=>$data) {
        			$cols .= sprintf("%s, ",$key);
        			$vals .= sprintf("'%s', ",$data);
				}
				
				// Remove last comma and space - not needed
				$cols = substr($cols,0,-2);
				$vals = substr($vals,0,-2);
				return mysql_real_escape_string(sprintf("INSERT INTO %s (%s) values (%s)",$tablename,$cols,$vals));
		}
        
    }