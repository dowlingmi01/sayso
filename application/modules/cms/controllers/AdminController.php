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

            $tablename = $this->getRequest()->getParam('table');
            if ($tablename != "") {
            	
printf("we have a table edit request = for [%s]",$tablename);
exit;
			}
			else {
				printf("Table name is null");
			}
           
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
					
							// Process the form fields
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
								$formElements[$colname]->setAttrib("Title", $coloptions['help']);
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
                   				print_r($formData);
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
        
        /**
        * Generate a button which will activate the tablefields action
        * 
        * @param mixed $id
        * @author Peter Connolly
        */
        public function generateEditButtonLink($id)
	    {
        //$link = '<a href="' . $this->view->url(array('action' => 'tablefields', 'id' => intval($id))). '" class="button-edit" title="Edit">Fields</a>';
        $link = '<a href="' . $this->view->url(array('controller'=>'table', 'action' => 'index', 'cms_table_list_id' => intval($id))). '" class="button-edit" title="Edit">Fields</a>';
// Need to be http://local.sayso.com/cms/table/index/cms_table_list_id/63
		    return $link;
	    }
        
    }