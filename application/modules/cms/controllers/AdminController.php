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

		private $_gridCollection = array();
		private $_gridAssociatedData = array();

		private $_newElements = array();

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
			$this->view->headScript()->appendFile('/js/cms/jquery.Menu.js');
			$this->view->headScript()->appendFile('/js/cms/jquery.ui.tabs.js');
			$this->view->headScript()->appendFile('/js/cms/init.js');
//printf("<h1>All original parameters</h1><pre>%s</pre>",print_r($this->_getAllParams(),true));
			$crumb = new Breadcrumb($this->_getAllParams());
			$this->view->breadcrumb = $crumb->getBreadcrumb();

			$newParams = $crumb->getParameters();

			// Zap any existing parameters
			foreach ($this->_getAllParams() as $key=>$value) {
			//	$this->_setParam($key,null);
			}

			foreach ($newParams as $key=>$value) {
			//	$this->_setParam($key,$value);
			}

			// Changing the action (by _setParam) doesn't do anything unless we forward...
			if (($this->_getParam('action')!= $this->getRequest()->getActionName()) && ($this->_getParam('action') != null)) {
			//	$this->_forward($this->_getParam('action'),'admin','cms');
			}
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


		}

		/**
		* View a subobject grid
		*
		* @author Peter Connolly
		*/
		private function _subobject($fktablename,$fkfield,$fkval,$gridid,$realtable="")
		{
			$griddata = array();

			$tablenamepolite = ucwords(str_replace("_"," ",$fktablename));

			if ($fktablename != null) {

				// Search for the json file
				// set file to read - Move relative from 'public' to find it.

				$userlevel = "superuser";
				// @todo Userlevel will be changed once logins and user level permissions are included.

				$file = sprintf('../application/modules/cms/models/%s/%s.json',$userlevel,$fktablename);

				$saysojson = new Json($file);

				if ($saysojson->validJson($fktablename)) {

					$realfktablename = strtolower($saysojson->getTableAttr('tablename'));
					// Create the grid
					// Find the columns we want to see on the grid
					$columnlist = $saysojson->getCMSColumnsAssoc("displaywhen","subgrid");

					// Hide the ID column in this table
					if (array_key_exists('id',$columnlist)) {
						// Note that the id here is the id in the subobject table, not the one from the main table
						$columnlist['hiddenid'] = 'id';
						unset($columnlist['id']);
					}

					$strWhere = sprintf("%s = %s",$fkfield,$fkval);

					$select = Zend_Registry::get('db')->select()->from($realfktablename,$columnlist)->where($strWhere)->order("id desc");

					$grid2   = new Cms_Matrix();
					$grid2->setNoFilters(true); // We don't need to see filters on subobjects
					$grid2->setJqgParams(array('altRows' => true));// rows will alternate color
					$grid2->setSource(new Bvb_Grid_Source_Zend_Select($select));

					// Process columns
					foreach ($columnlist as $key=>$value) {

						// Hide the 'hiddenid' column - it shouldn't be seen by the user.
						if ($key=="hiddenid") {
							$grid2->updateColumn($key,array('hidden' => true));
						} else {
							// we want to hide any hidden columns
							$coltype = $saysojson->getColAttr($value,'type');
							if ($coltype=="hidden") {
							    $grid2->updateColumn($value,array('hidden' => true));
							}

							// Set column widths
							$colwidth =  $saysojson->getColAttr($value,'width');
							if ($colwidth==Null) {
								$grid2->updateColumn($value,array('style'=>'width:100px'));
							} else {
								$width = sprintf("width:%spx",$colwidth);
								$grid2->updateColumn($value,array('style'=>$width));
							}

							// Cross reference any foreign keys
							if ($coltype=="fkey") {
								// For fkeys, we know we will have a lookuptable, lookupfield and lookuplabel

								$lookuptable = $saysojson->getColAttr($value,'lookuptable');
								$lookupfield = $saysojson->getColAttr($value,'lookupfield');
								$lookuplabel = $saysojson->getColAttr($value,'lookuplabel');

								$fieldname = sprintf("{{%s}}",$value);


								$grid2->updateColumn($value,array(
										'callback' => array(
										'function'=>array($this,'_getDataField'),
												'params'=>array($fieldname,
																$lookuptable,
																$lookupfield,
																$lookuplabel
																)
										)));
							}
						}
					}

					if ($saysojson->checkTablePermission("allowedit")) {

						$extraColumnEdit = new Bvb_Grid_Extra_Column();
						$extraColumnEdit
							->position('left')
							->name('editit')
							->title(' ')
							->callback(
								array(
									'function'  => array($this, '_generateEditButtonLink'),
									'params'	=> array('{{hiddenid}}',$fktablename,$tablenamepolite)
								)
							);
						$grid2->addExtraColumns($extraColumnEdit);

					}

					if ($saysojson->checkTablePermission("allowdetails")) {

						$extraColumnDetails = new Bvb_Grid_Extra_Column();
						$extraColumnDetails
							->position('left')
							->name('details')
							->title(' ')
							->callback(
								array(
									'function'  => array($this, '_generateDetailsButtonLink'),
									'params'	=> array('{{hiddenid}}',$fktablename,$tablenamepolite)
								)
							);

						$grid2->addExtraColumns($extraColumnDetails);
					}

					if ($saysojson->checkTablePermission("allowdelete")) {

						$extraColumnDelete = new Bvb_Grid_Extra_Column();
						$extraColumnDelete
							->position('right')
							->name('delete')
							->title(' ')
							->callback(
								array(
									'function'  => array($this, '_generateDeleteButtonLink'),
									'params'	=> array('{{hiddenid}}',$fktablename)
								)
							);
						$grid2->addExtraColumns($extraColumnDelete);

					}

					$form = new Bvb_Grid_Form($class='Zend_Form', $options=array());

					$grid2->setGridId($gridid);

					$grid2->setForm($form);

					if ($saysojson->checkTablePermission("allowadd")) {
						$fullURL = $this->_getFullURL();
						$tablename = $this->getRequest()->getParam('table');
						$tablename = $realtable ;
						$id = $this->getRequest()->getParam('id');
						if (($tablename != null) && ($id!=null)) {
							$redirect = "pt/".$tablename."/pi/".$id;
						}

						$griddata['newrecord'] = sprintf('<span class="newlink"><a href="/cms/admin/add/table/%s/%s"><img src="/images/icons/add.png" style="width:16px;" alt="Add" Title="Add" /> Add New %s</a></span>',$fktablename,$redirect,$tablenamepolite);
					}

					if ($saysojson->getTableAttr('label')!=null) {
						$griddata['title'] = $saysojson->getTableAttr('label');
					} else {
						$griddata['title'] = $tablenamepolite;

					}
					$this->_gridAssociatedData[] = $griddata;
					$DeployedGrid = $grid2->deploy();
					$this->_gridCollection[] = $DeployedGrid;

				} else {

					$this->view->message = sprintf("E03: File [%s] is missing",$file);
				}
			} else {

				$this->view->message = sprintf("E03: Table name is missing");
			}
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

				$tablename = $this->getRequest()->getParam('table');
				$tablenamepolite = ucwords(str_replace("_"," ",strtolower($this->getRequest()->getParam('table'))));
				if ($tablename != null) {

					// Search for the json file
					// set file to read - Move relative from 'public' to find it.

					$userlevel = "superuser";
					// @todo Userlevel will be changed once logins and user level permissions are included.

					$file = sprintf('../application/modules/cms/models/%s/%s.json',$userlevel,strtolower($tablename));

					$saysojson = new Json($file);

					if ($saysojson->validJson($tablename)) {

						$realtablename = strtolower($saysojson->getTableAttr('tablename'));

						if ($saysojson->checkTablePermission("allowedit")) {
							// We need to get the details for the ID from this table
							// Find the columns we want to see on the grid
							$columnlist = $saysojson->getCMSColumns("displaywhen","edit");

							$select = Zend_Registry::get('db')->select()->from($realtablename,$columnlist)->where("id = ?",$id);


							$stmt = $select->query();
							$currentData = $stmt->fetchAll();
							if (count($currentData) == 1) {
								$currentData = $currentData[0];

								// currentData contains a list of fields and values from the JSON, which we can put in as initial values

								// Start with a blank formelements array, and add the array items as we go
								$formElements = array();

								foreach ($saysojson->getCMSColumns("displaywhen","edit",true) as $key=>$value) {

									// Process the form fields for this table
									// We know there will be a colname and a type

									$colname = $value['colname'];
									$coltype = $value['type'];

									$coloptions = array();
									foreach ($value as $colkey=>$colvalue) {
										if (($colkey!='colname') and ($colkey!='type')) {
											$coloptions[$colkey] = $colvalue;
										}
									}

									$coloptions['meta']['tablename'] = $realtablename;
									$coloptions['meta']['colname'] = $colname;

									// Build this form element
									$elementmodel = "Form_Element_".ucfirst(strtolower($coltype));
									$formElements[$colname] = new $elementmodel($colname);
									$formElements[$colname]->buildElement("edit",$coloptions);

									// general aspects of a form element

									// Override the field label
									if (array_key_exists('label',$coloptions)) {
										$formElements[$colname]->setLabel($coloptions['label']);
									}

									// Assign a default value if set in the JSON file
									if (array_key_exists('value',$coloptions)) {
										$formElements[$colname]->setValue($coloptions['value']);
									}

									// Display Width
									if (array_key_exists('width',$coloptions)) {
										$formElements[$colname]->setAttrib("size", $coloptions['width']);
									}

									if (array_key_exists($colname,$currentData)) {
										// Assign a value if found in the select statement results
										if (array_key_exists($colname,$currentData) && (array_key_exists($colname,$formElements))) {
												$formElements[$colname]->setValue($currentData[$colname]);
										}
									}

									if (array_key_exists('attributes',$coloptions)) {
										if ($coloptions['attributes'][0]=="writeonly") {
											//  It's a write only field
											$this->_newElements[$colname] = new Form_Element_Hidden($colname,array());
											$this->_newElements[$colname]->setValue($formElements[$colname]->getValue());
											$formElements[$colname]->setName($colname."_readonly");
											$formElements[$colname."_notrequired"] = $formElements[$colname];

										}
									}


								}

								// All column elements have been built. Add the standard form elements
								$formElements['submit'] = new Zend_Form_Element_Submit('submit');
								$formElements['submit'] ->setLabel(sprintf('Save Changes')); // the button's value
														//->setIgnore(true); // very usefull -> it will be ignored before insertion
								$form = new ZendX_JQuery_Form();
								$form->setName($realtablename);

								$form->addElements($formElements);
								$form->addElements($this->_newElements);


								$form->addElement('hash', 'no_csrf_foo', array('salt' => 'uniquesay.so'));

								// Find the record and populate the initial form values


								if ($this->getRequest()->isPost()) { //is it a post request ?
									$postData = $this->getRequest()->getPost(); // getting the $_POST data


									if ($form->isValid($postData)) {

										$formData = $form->getValues(); // data filtered
										// Update the 'modified' field (don't update the 'Created' field)
										$formData += array('modified' => date('Y-m-d H:i:s'));

										// Remove any fields not required in the save
										foreach ($formData as $key=>$value) {
											if (strpos($key,"_notrequired")!==false) {
												unset($formData[$key]);
											}
										}

										unset($formData['no_csrf_foo']); // Remove the salt - we don't need it for an update
										$tablefrommodel = $saysojson->getModel();
										$model = new $tablefrommodel();
										$model->setData($formData);
										$result = $model->save();

										$this->msg->addMessage('Record successfully updated');
										// Redirect to the View screen
										$this->rd->gotoSimple('detail','admin','cms',array('table' => $tablename,'id'=>$id));

									} else {
										$form->populate($postData); // show errors and populate form with $postData
									}
								}

								$this->view->tablename = $tablenamepolite;
								$this->view->BackLink = sprintf('<span class="backlink"><a href="/cms/admin/view/table/%s/"><img src="/images/icons/arrow_left.png" style="width:16px;" alt="Back" Title="Back" /> Back</a></span>',$tablename);
								$this->view->form = $form; // assigning the form to view
							} else {
								$this->view->message = sprintf("E00: Invalid ID [%s] for %s",$id,$tablename);
							}
						}
						else {
							$this->view->message = sprintf("E00: Editing not allowed on the %s table",$tablename);
						}
					}
					else {

						$this->view->message = sprintf("E00: File [%s] is missing",$file);
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
			$longid = explode("?",$this->getRequest()->getParam('id'));
			$id = $longid[0];
			if ($id===null) {
				printf("<p>No ID found. Cannot display.</p>");
			} else {

				if ($this->getRequest()->getParam('sid')!=null) {
					$longid = explode("?",$this->getRequest()->getParam('sid'));
					$id = $longid[0];
				}

				$longtable = explode("?",$this->getRequest()->getParam('table'));
				$tablename = strtolower($longtable[0]);
				$tablenamepolite = ucwords(str_replace("_"," ",$tablename));
				if ($tablename !== null) {

					// Search for the json file
					// set file to read - Move relative from 'public' to find it.

					$userlevel = "superuser";
					// @todo Userlevel will be changed once logins and user level permissions are included.

					$file = sprintf('../application/modules/cms/models/%s/%s.json',$userlevel,$tablename);

					$saysojson = new Json($file);

					if ($saysojson->validJson($tablename)) {
						if ($saysojson->checkTablePermission('allowdetails')) {
							$columnlist = $saysojson->getCMSColumns("displaywhen","detail");

							$realtable = strtolower($saysojson->getTableAttr('tablename'));



							$select = Zend_Registry::get('db')->select()->from($realtable,$columnlist)->where("id = ?",$id);

							$stmt = $select->query();
							$currentData = $stmt->fetchAll();
							if (count($currentData) == 1) {
								$currentData = $currentData[0];

								// currentData contains a list of fields and values from the table, which we can put in as initial values

								// Start with a blank formelements array, and add the array items as we go
								$formElements = array();

								foreach ($saysojson->getJson('columns') as $key=>$value) {
									// Process the form fields for this table
									// We know there will be a colname and a type
									$colname = $value['colname'];
									$coltype = $value['type'];

									$coloptions = array();
									foreach ($value as $colkey=>$colvalue) {
										if (($colkey!='colname') and ($colkey!='type')) {
											$coloptions[$colkey] = $colvalue;
										}
									}

									$coloptions['meta']['tablename'] = $realtable;
									$coloptions['meta']['colname'] = $colname;

									// Build this form element
									$elementmodel = "Form_Element_".ucfirst(strtolower($coltype));
									$formElements[$colname] = new $elementmodel($colname);
									$formElements[$colname]->buildElement("detail",$coloptions);

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

									// Display Width
									if (array_key_exists('width',$coloptions)) {
										$formElements[$colname]->setAttrib("size", $coloptions['width']);
									}
								}

								$form = new ZendX_JQuery_Form();
								$form->setName($realtable);
								$form->addElements($formElements);

								$this->view->tablename = $tablenamepolite;

								// Process all subobects, if there are any
								$cnt = 1;

								if (array_key_exists("subobjects",$saysojson->getJson())) {
									foreach ($saysojson->getJson('subobjects') as $key=>$value) {

										$this->_subobject($value['table'],$value['fk'],$formElements["id"]->getValue(),$cnt,$realtable);
										$cnt++;
									}

								}
								$this->view->grid_array = $this->_gridCollection;
								$this->view->grid_data = $this->_gridAssociatedData;

								$this->view->form = $form; // assigning the form to view
							} else {
								$this->view->message = sprintf("E01: Invalid ID [%s] for %s",$id,$tablename);
							}
						} else {
							$this->view->message = sprintf("E01: Detail View not allowed for %s",$tablename);
						}
					} else {

						$this->view->message = sprintf("E01: Table definition [%s] does not exist in the JSON file",$tablename);

					}
				}
			}
		}

		/**
		* Delete a record
		*
		* @author Peter Connolly
		*/
		public function deleteAction()
		{
			$longid = explode("?",$this->getRequest()->getParam('id'));
			$id = $longid[0];
			if ($id===null) {
				printf("<p>No ID found. Cannot display.</p>");
			} else {

				$longtable = explode("?",$this->getRequest()->getParam('table'));
				$tablename = strtolower($longtable[0]);
				$tablenamepolite = ucwords(str_replace("_"," ",$tablename));
				if ($tablename !== null) {

					// Search for the json file
					// set file to read - Move relative from 'public' to find it.

					$userlevel = "superuser";
					// @todo Userlevel will be changed once logins and user level permissions are included.

					$file = sprintf('../application/modules/cms/models/%s/%s.json',$userlevel,$tablename);

					$saysojson = new Json($file);

					if ($saysojson->validJson($tablename)) {
						if ($saysojson->checkTablePermission('allowdelete')) {
							$columnlist = $saysojson->getCMSColumns("displaywhen","delete");

							$realtable = strtolower($saysojson->getTableAttr('tablename'));
							$select = Zend_Registry::get('db')->select()->from($realtable,$columnlist)->where("id = ?",$id);

							$stmt = $select->query();
							$currentData = $stmt->fetchAll();
							if (count($currentData) == 1) {
								$currentData = $currentData[0];

								// currentData contains a list of fields and values from the table, which we can put in as initial values

								// Start with a blank formelements array, and add the array items as we go
								$formElements = array();

								foreach ($saysojson->getJson('columns') as $key=>$value) {

									if (in_array($value['colname'],$columnlist)) {

										// Process the form fields for this table
										// We know there will be a colname and a type
										$colname = $value['colname'];
										$coltype = $value['type'];

										$coloptions = array();
										foreach ($value as $colkey=>$colvalue) {
											if (($colkey!='colname') and ($colkey!='type')) {
												$coloptions[$colkey] = $colvalue;
											}
										}

										$coloptions['meta']['tablename'] = $tablename;
										$coloptions['meta']['colname'] = $colname;


										$elementmodel = "Form_Element_".ucfirst(strtolower($coltype));
										$formElements[$colname] = new $elementmodel($colname);
										$formElements[$colname]->buildElement("delete",$coloptions);

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

										// Display Width
										if (array_key_exists('width',$coloptions)) {
											$formElements[$colname]->setAttrib("size", $coloptions['width']);
										}
									}
								}

								// All column elements have been built. Add the standard form elements

								$formElements['submityes'] = new Zend_Form_Element_Submit('del');
								$formElements['submityes'] ->setLabel(sprintf('Confirm Delete')); // the button's value
																//->setIgnore(true); // very usefull -> it will be ignored before insertion
								$formElements['submitno'] = new Zend_Form_Element_Submit('del');
								$formElements['submitno'] ->setLabel(sprintf('Cancel')); // the button's value
															//->setIgnore(true); // very usefull -> it will be ignored before insertion
								$form = new ZendX_JQuery_Form();
								$form->setName($realtable);
								$form->addElements($formElements);

								$form->addElement('hash', 'no_csrf_foo', array('salt' => 'uniquesay.so'));

							if ($this->getRequest()->isPost()) { //is it a post request ?
								$postData = $this->getRequest()->getPost(); // getting the $_POST data

								if ($form->isValid($postData)) {
									if ($postData['del']=="Confirm Delete") {
										$formData = $form->getValues(); // data filtered
										$tablefrommodel = $saysojson->getModel();
										$model = new $tablefrommodel();
										$model->setData($formData);
										$result = $model->delete();

										$this->msg->addMessage('Record successfully deleted');
										$this->rd->gotoSimple('view','admin','cms',array('table' => $tablename));

										} else {
											// Delete cancelled
											$this->view->message = "Delete cancelled";
											$this->rd->gotoSimple('view','admin','cms',array('table' => $tablename));
										}
									} else {
										$form->populate($postData); // show errors and populate form with $postData
									}
								}

								$this->view->tablename = $tablenamepolite;

								$this->view->BackLink = sprintf('<span class="backlink"><a href="/cms/admin/view/table/%s/"><img src="/images/icons/arrow_left.png" style="width:16px;" alt="Back" Title="Back" /> Back</a></span>',$tablename);

								$this->view->form = $form; // assigning the form to view
							} else {
								$this->view->message = sprintf("E01: Invalid ID [%s] for %s",$id,$tablename);
							}
						} else {
							$this->view->message = sprintf("E01: Delete not allowed for %s",$tablename);
						}
					} else {

						$this->view->message = sprintf("E01: Table definition [%s] does not exist in the JSON file",$tablename);

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
			$longtable = explode("?",$this->getRequest()->getParam('table'));
			// We need to strip any parameters off the table name - they're added at times by the debugger if we're using it. There are no sideeffects if there are no parameters.
			$tablename = strtolower($longtable[0]);
			$tablenamepolite = ucwords(str_replace("_"," ",$tablename));
			if ($tablename != null) {

				// Search for the json file
				// set file to read - Move relative from 'public' to find it.

				$userlevel = "superuser";
				// @todo Userlevel will be changed once logins and user level permissions are included.

				$file = sprintf('../application/modules/cms/models/%s/%s.json',$userlevel,$tablename);

				$saysojson = new Json($file);

				if ($saysojson->validJson($tablename)) {

					// Create the grid
					// Find the columns we want to see on the grid
					$columnlist = $saysojson->getCMSColumns("displaywhen","grid");
					$where = $saysojson->getTableAttr('where');
					$realtable = strtolower($saysojson->getTableAttr('tablename'));
					if ($where==null) {
						$select = Zend_Registry::get('db')->select()->from($realtable,$columnlist)->order("id desc");
					} else {
						$select = Zend_Registry::get('db')->select()->from($realtable,$columnlist)->where($where)->order("id desc");
					}
					$grid   = new Cms_Matrix();
					$grid->setJqgParams(array('altRows' => true));// rows will alternate color
					$grid->setSource(new Bvb_Grid_Source_Zend_Select($select));

					// Adjust the widths of any columns on the grid
					foreach ($columnlist as $key=>$value) {

						// we want to hide any hidden columns
						$coltype = $saysojson->getColAttr($value,'type');
						if ($coltype=="hidden") {
						    $grid->updateColumn($value,array('hide' => true));
						}

						// Set column widths
						$colwidth =  $saysojson->getColAttr($value,'width');
						if ($colwidth==Null) {
							$grid->updateColumn($value,array('style'=>'width:100px'));
						} else {
							$width = sprintf("width:%spx",$colwidth);
							$grid->updateColumn($value,array('style'=>$width));
						}

						// Cross reference any foreign keys
						if ($coltype=="fkey") {
							// For fkeys, we know we will have a lookuptable, lookupfield and lookuplabel

							$lookuptable = $saysojson->getColAttr($value,'lookuptable');
							$lookupfield = $saysojson->getColAttr($value,'lookupfield');
							$lookuplabel = $saysojson->getColAttr($value,'lookuplabel');

							$fieldname = sprintf("{{%s}}",$value);

							$grid->updateColumn($value,array(
									'callback' => array(
									'function'=>array($this,'_getDataField'),
											'params'=>array($fieldname,
															$lookuptable,
															$lookupfield,
															$lookuplabel
															)
									)));

						}
					}

					if ($saysojson->checkTablePermission("allowedit")) {
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
					}

					if ($saysojson->checkTablePermission("allowdetails")) {
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
					}

					if ($saysojson->checkTablePermission("allowdelete")) {
						$extraColumnDelete = new Bvb_Grid_Extra_Column();
						$extraColumnDelete
							->position('right')
							->name('delete')
							->title(' ')
							->callback(
								array(
									'function'  => array($this, '_generateDeleteButtonLink'),
									'params'	=> array('{{id}}')
								)
							);
						$grid->addExtraColumns($extraColumnDelete);
					}

					$form = new Bvb_Grid_Form($class='Zend_Form', $options=array());

					$grid->setAjax("list");
					$grid->setForm($form);

					$this->view->tablename = $tablenamepolite;
					if ($saysojson->checkTablePermission("allowadd")) {

						$this->view->newRecordLink = sprintf('<span class="newlink"><a href="/cms/admin/add/table/%s/"><img src="/images/icons/add.png" style="width:16px;" alt="Add" Title="Add" /> Add New %s</a></span>',$tablename,$tablenamepolite);

					}

					$this->view->grid = $grid->deploy();

				} else {

					$this->view->message = sprintf("E03: File [%s] is missing",$file);
				}
			} else {

				$this->view->message = sprintf("E03: Table name is missing");
			}
		}

		/**
		* Generate a button which will activate the tablefields action
		*
		* @param mixed $id
		* @author Peter Connolly
		*/
		public function _generateEditButtonLink($id,$tablename=null,$tablenamepolite=null)
		{

			$currentURL = $this->view->url();

			if ($tablename!=null) {
				$reverse = strrev($currentURL);
				// If the last character is a /, remove it
				if ($reverse[0]=="/") {
					$currentURL = substr($currentURL,0,strlen($currentURL)-1);
				}

				$link = '<a href="' .$this->view->url(array('action' => 'edit', 'table'=>$tablename,'id' => intval($id))). '" class="button-details" title="Edit"><img src="/images/icons/pencil.png" style="width:16px;" alt="Edit" Title="Edit" /></a>';

			} else {

				$link = '<a href="' .$this->view->url(array('action' => 'edit', 'id' => intval($id))). '" class="button-details" title="Edit"><img src="/images/icons/pencil.png" style="width:16px;" alt="Edit" Title="Edit" /></a>';
			}
			return $link;
		}

		/**
		* Generate a button which will delete the selected record
		*
		* @param mixed $id
		* @author Peter Connolly
		*/
		public function _generateDeleteButtonLink($id,$tablename=null)
		{

			$currentURL = $this->view->url();

			if ($tablename!=null) {
				$reverse = strrev($currentURL);
				// If the last character is a /, remove it
				if ($reverse[0]=="/") {
					$currentURL = substr($currentURL,0,strlen($currentURL)-1);
				}

				$link = '<a href="' .$this->view->url(array('action' => 'delete', 'table' => $tablename, 'id' => intval($id))). '" class="button-details" title="Delete"><img src="/images/icons/delete.png" style="width:16px;" alt="Delete" Title="Delete" /></a>';

			} else {

				$link = '<a href="' .$this->view->url(array('action' => 'delete', 'id' => intval($id))). '" class="button-details" title="Delete"><img src="/images/icons/delete.png" style="width:16px;" alt="Delete" Title="Delete" /></a>';
			}
			return $link;
		}

		/**
		* Generate a button which will activate the view action
		*
		* @param mixed $id
		* @author Peter Connolly
		*/
		public function _generateDetailsButtonLink($id,$tablename=null,$tablenamepolite=null)
		{
			$currentURL = $this->view->url();

			if ($tablename!=null) {
				$reverse = strrev($currentURL);
				// If the last character is a /, remove it
				if ($reverse[0]=="/") {
					$currentURL = substr($currentURL,0,strlen($currentURL)-1);
				}

				//$newURL = $currentURL."/detail/table/".$tablename."/id/".intval($id);

				//$link = '<a href="'.$newURL. '" class="button-details" title="Details"><img src="/images/icons/information.png" style="width:16px;" alt="Details" Title="Details" /></a>';
				// The above is used during breadcrumb generation
				$link = '<a href="' .$this->view->url(array('action' => 'detail', 'table'=> $tablename, 'id' => intval($id))). '" class="button-details" title="Edit"><img src="/images/icons/information.png" style="width:16px;" alt="Details" Title="Details" /></a>';

			} else {

				$link = '<a href="' .$this->view->url(array('action' => 'detail', 'id' => intval($id))). '" class="button-details" title="Edit"><img src="/images/icons/information.png" style="width:16px;" alt="Details" Title="Details" /></a>';
			}
			return $link;
		}

		/**
		* Add a table row
		*
		* @author Peter Connolly
		*/
		public function addAction()
		{

			$tablename = $this->getRequest()->getParam('table');
			$tablenamepolite = ucwords(str_replace("_"," ",$tablename));
			$parenttable = $this->getRequest()->getParam('pt');
			$parentid = $this->getRequest()->getParam('pi');
			if ($tablename != "") {

				// Search for the json file
				// set file to read - Move relative from 'public' to find it.

				$userlevel = "superuser";
				// @todo Userlevel will be changed once logins and user level permissions are included.
				$file = sprintf('../application/modules/cms/models/%s/%s.json',$userlevel,$tablename);

				$saysojson = new Json($file);

				if ($saysojson->validJson($tablename)) {

					$realtable = strtolower($saysojson->getTableAttr('tablename'));

					if ($saysojson->checkTablePermission("allowadd")) {
						// Start with a blank formelements array, and add the array items as we go

						$formElements = array();


						foreach ($saysojson->getCMSColumns("displaywhen","add",true) as $key=>$value) {

							// Process the form fields for this table

							// We know there will be a colname and a type
							$colname = $value['colname'];
							$coltype = $value['type'];

							$coloptions = array();
							foreach ($value as $colkey=>$colvalue) {
								if (($colkey!='colname') and ($colkey!='type')) {
									$coloptions[$colkey] = $colvalue;
								}
							}

							$coloptions['meta']['tablename'] = $realtable;
							$coloptions['meta']['colname'] = $colname;


							// Build this form element
							// Looks for the Form Element class matching the row.
							// Will be found in /library/Form/Element/...

							$elementmodel = "Form_Element_".ucfirst(strtolower($coltype));
							$formElements[$colname] = new $elementmodel($colname);
							$formElements[$colname]->buildElement("add",$coloptions);


							// general aspects of a form element

							// Override the field label
							if (array_key_exists('label',$coloptions)) {
								$formElements[$colname]->setLabel($coloptions['label']);
							}

							// Assign a default value
							if (array_key_exists('value',$coloptions)) {
								$formElements[$colname]->setValue($coloptions['value']);
							}

							if ($colname==$parenttable."_id") {
								// We have a parent table, and this field is the foreign key for it
								$formElements[$colname]->setValue($parentid);
								$formElements[$colname]->setReadOnly();
								// Because it's readonly, we need to create a hidden field to match
								$this->_newElements[$colname] = new Form_Element_Hidden($colname,array());
								$this->_newElements[$colname]->setValue($parentid);
								$formElements[$colname]->setName($colname."_readonly");
								$formElements[$colname."_notrequired"] = $formElements[$colname];
								unset($formElements[$colname]);
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
						$form->setName($realtable);
						$form->addElements($formElements);
						$form->addElements($this->_newElements);
						$form->addElement('hash', 'no_csrf_foo', array('salt' => 'uniquesay.so'));

						if ($this->getRequest()->isPost()) { //is it a post request ?

							$postData = $this->getRequest()->getPost(); // getting the $_POST data

							if ($form->isValid($postData)) {

								$formData = $form->getValues(); // data filtered
								// created and updated fields
								$formData += array('created' => date('Y-m-d H:i:s'), 'modified' => date('Y-m-d H:i:s'));

								// Remove any fields not required in the save
								foreach ($formData as $key=>$value) {
									if (strpos($key,"_notrequired")!==false) {
										unset($formData[$key]);
									}
								}

								unset($formData['no_csrf_foo']); // Remove the salt - we don't need it for the insert
								// remove any data with null values - we don't need them.

								$formData = array_filter($formData,array('self','_notnull'));

								$modelname = $saysojson->getModel();
								$model = new $modelname;
								$model->setData($formData);
								$result = $model->save();

								$this->view->message = "Record successfully added ".$result;

								if (($parenttable!=null) && ($parentid!=null))
								{
									$this->rd->gotoSimple('detail','admin','cms',array('table' => $parenttable,'id'=>$parentid));
								} else {
									$form->reset();
								}

							} else {
								$form->populate($postData); // show errors and populate form with $postData
							}
						}
						$this->view->tablename = $tablenamepolite;
						$this->view->form = $form; // assigning the form to view

				} else {
					$this->view->message = sprintf("E02: Add not allowed for this table");
				}
				}
				else {

					$this->view->message = sprintf("E02: File [%s] is missing",$file);
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
		* Return the value of a foreign key field
		*
		* @param mixed $findvalue
		* @param mixed $lookuptable
		* @param mixed $lookupfield
		* @param mixed $lookuplabel
		*
		* @author Peter Connolly
		*/
		public function _getDataField($findvalue,$lookuptable,$lookupfield,$lookuplabel)
		{
			if ($findvalue==null) {
				return null;
			} else {
				$sql = sprintf("SELECT %s FROM %s WHERE %s=%s",$lookuplabel,$lookuptable,$lookupfield,$findvalue);

				$results = Db_Pdo::fetchAll($sql);

				if ($results)
				{
					// We only want the result of the datafield
					return $results[0][$lookuplabel];
				} else {
					return null;
				}
			}
		}

		private function _getFullURL()
		{
			$currentURL = $this->view->url();

			$reverse = strrev($currentURL);
			// If the last character is a /, remove it
			if ($reverse[0]=="/") {
				$currentURL = substr($currentURL,0,strlen($currentURL)-1);
			}

			return $currentURL;
		}

	}

