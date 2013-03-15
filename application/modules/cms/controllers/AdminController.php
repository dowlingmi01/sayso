<?php
	/**
	* controllers/CmsController.php
	* @author Peter Connolly, March 2012
	*/

	require_once APPLICATION_PATH . '/modules/admin/controllers/CommonController.php';
	require_once 'surveygizmo.php';

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

		/**
		* Define the CSS and Javascript files that will be included automatically when this
		* controller is called.
		*/
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
			$this->view->headScript()->appendFile('/modules/common.js');
			$this->view->headScript()->appendFile('/js/cms/jquery.Menu.js');
			$this->view->headScript()->appendFile('/js/cms/jquery.ui.tabs.js');

			$this->view->headScript()->appendFile('/js/cms/init.js');

			Breadcrumb::startBreadcrumbTrail();

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
			parent::init();

			if (!$this->_request->isXmlHttpRequest())
			{
				$this->setLayoutBasics();

				$scripts = $this->view->headScript();
				$scripts->appendFile('/js/pubsub.js');
				$scripts->appendFile('/js/jquery.lightbox_me.js');
				$scripts->appendFile('/js/mustache.js');
				$scripts->appendFile('/js/templates.js');
				$scripts->appendFile('/js/bind.js');
				$scripts->appendFile('/modules/admin/index/index.js');
				$this->view->headLink()->appendStylesheet('/modules/admin/index/index.css', 'screen');
			}


		}

		/**
		* Display a list of all tables in the database records
		*
		* @author Peter Connolly
		*/
		public function indexAction()
		{
			$auth = Zend_Auth::getInstance();
			if ($auth->hasIdentity()) {
				$this->view->identity = $auth->getIdentity();
			}

		}

		/**
		* Given an input date, returns a string saying how long ago it was
		*
		* @param string $d
		*/
		private function _ago($d)
		{
			date_default_timezone_set("America/Denver");
		    $c = getdate();
     		$p = array('year', 'mon', 'mday', 'hours', 'minutes', 'seconds');
     		$display = array('year', 'month', 'day', 'hour', 'minute', 'second');
     		$factor = array(0, 12, 30, 24, 60, 60);
     		$d = $this->_datetoarr($d);
     		for ($w = 0; $w < 6; $w++) {
          		if ($w > 0) {
               		$c[$p[$w]] += $c[$p[$w-1]] * $factor[$w];
               		$d[$p[$w]] += $d[$p[$w-1]] * $factor[$w];
          		}
          		if ($c[$p[$w]] - $d[$p[$w]] > 1) {
               		return ($c[$p[$w]] - $d[$p[$w]]).' '.$display[$w].'s ago';
          		}
     		}
     		return $d;


		}

		/**
		* Converts a text date object to an array
		* @example
		* _datetoarr("2012-06-25 11:08:12")
		* returns an array ("2012","06","25","11","08","12")
		*
		* @param mixed $d
		*/
		private function _datetoarr($d) {
     		preg_match("/([0-9]{4})(\\-)([0-9]{2})(\\-)([0-9]{2}) ([0-9]{2})(\\:)([0-9]{2})(\\:)([0-9]{2})/", $d, $matches);
		    return array(
		          'seconds' => $matches[10],
		          'minutes' => $matches[8],
		          'hours' => $matches[6],
		          'mday' => $matches[5],
		          'mon' => $matches[3],
		          'year' => $matches[1],
		     );
		}

		/**
		* Returns a string describing a date interval object
		* Used for determining in friendly terms how long ago soemthing happened.
		*
		* @param dateinterval $d
		* @return String
		*/
		private function _timeactive($d)
		{
			$active = array();
			if ($d->y!=0) $active[] = $d->y. ' years';
			if ($d->m!=0) $active[] = $d->m. ' months';
			if ($d->d!=0) $active[] = $d->d. ' days';
			if ($d->h!=0) $active[] = $d->h. ' hours';
			if ($d->i!=0) $active[] = $d->m. ' minutes';
			if ($d->s!=0) $active[] = $d->s. ' seconds';
			return implode(', ',$active);
		}

		private function _mediaanalysis($user_id,$starbar_id,$social_media="facebook_like")
		{
			$strWhere = sprintf("m.user_id='%s' and m.starbar_id='%s' and s.short_name='%s' ",$user_id,$starbar_id,$social_media);

			$select = Zend_Registry::get('db')->select()
						->from(array('m'=>'metrics_social_activity'),array("url","content","created"))
						->join(array('s'=>'lookup_social_activity_type'),'s.id=m.social_activity_type_id')
						->where($strWhere)
						->order('m.id desc')
						->limit(10,0);
			$stmt = $select->query();

			$mediaresults = $stmt->fetchAll();

			return $mediaresults;
		}

		private function _usedstarbars($usersstarbars)
		{
			$validstarbars = array();
			foreach ($usersstarbars as $starbar) {

				$strWhere = sprintf("user_id = %s and starbar_id = %s",$starbar['user_id'],$starbar['starbar_id']);
				$select = Zend_Registry::get('db')
							->select()
							->from("metrics_log",array("created","starbar_id"))
							->where($strWhere);

				$stmt = $select->query();
				$lastseen = $stmt->fetchAll();
				if ($lastseen) {
					// We've seen this user on this starbar
					$validstarbars[] = $starbar;
				}
			}
			return $validstarbars;
		}

		private function _surveyanalysis($user_id,$type="survey",$status="completed")
		{
			$strWhere = sprintf("m.user_id='%s' and m.status='%s' and type='%s' ",$user_id,$status,$type);


			$select = Zend_Registry::get('db')->select()
						->from(array('s'=>'survey'),"title")
						->join(array('m'=>'survey_user_map'),'s.id=m.survey_id')
						->where($strWhere);
			$stmt = $select->query();
			$analysisresults = $stmt->fetchAll();

			return $analysisresults;
		}

		/**
		* View a subobject grid
		*
		* @author Peter Connolly
		*/
		private function _subobject($fktablename,$fkfield,$fkval,$gridid,$realtable="",$lookuporder="id desc")
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
					if (empty($columnlist)) {
						$this->view->message = sprintf("E04: No fields defined for subgrid in %s.json",$fktablename);
					} else {
						// Hide the ID column in this table
						if (array_key_exists('id',$columnlist)) {
							// Note that the id here is the id in the subobject table, not the one from the main table
							$columnlist['hiddenid'] = 'id';
							unset($columnlist['id']);
						}

						$strWhere = sprintf("%s = %s",$fkfield,$fkval);

						$select = Zend_Registry::get('db')->select()->from($realfktablename,$columnlist)->where($strWhere)->order($lookuporder);

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

								if ($coltype=="datetime") {
								    $grid2->updateColumn($value, array('format'=> array('date',array('date_format'=>'dd-MM-yyyy'))));
								}

								if ($coltype=="hidden") {
								    $grid2->updateColumn($value,array('hidden' => true));
								}

								// Set column widths
								$colwidth =  $saysojson->getColAttr($value,'width');
								if ($colwidth==Null) {
									$grid2->updateColumn($value,array('style'=>'width:150px'));
								} else {
									$width = sprintf("width:%spx",$colwidth);
									$grid2->updateColumn($value,array('style'=>$width));
								}

								// Set column title
								if ($saysojson->getColAttr($value,'label')!=null) {
									$grid2->updateColumn($value,array('title'=>$saysojson->getColAttr($value,'label')));
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

								if ($coltype=='checkbox') {
									// For Checkboxes, we can replace the value '1' with a tick
									$fieldname = sprintf("{{%s}}",$value);
									$grid2->updateColumn($value,array(
											'callback' => array(
											'function'=>array($this,'_generateTickMark'),
													'params'=>array($fieldname,
																	$value
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

						if ($saysojson->checkTablePermission("allowduplicate")) {

							$extraColumnDetails = new Bvb_Grid_Extra_Column();
							$extraColumnDetails
								->position('left')
								->name('duplicate')
								->title(' ')
								->callback(
									array(
										'function'  => array($this, '_generateDuplicateButtonLink'),
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

					}
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

						$this->view->breadcrumb = Breadcrumb::add("Edit ".$tablename,$_SERVER['REQUEST_URI']);

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
										$formElements[$colname]->setAttrib("size", $coloptions['width']/8);
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


					$this->view->breadcrumb = Breadcrumb::getTrail();

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
										$formElements[$colname]->setAttrib("size", $coloptions['width']/8);
									}
								}

								if (array_key_exists('title',$currentData)) {
									// Set the breadcrumb
									$this->view->breadcrumb = Breadcrumb::add($currentData['title'],$_SERVER['REQUEST_URI']);
								} else {
									// Set the breadcrumb
									$this->view->breadcrumb = Breadcrumb::add("Detail ".$tablename,$_SERVER['REQUEST_URI']);
								}

								$form = new ZendX_JQuery_Form();
								$form->setName($realtable);
								$form->addElements($formElements);

								$this->view->tablename = $tablenamepolite;

								// Process all subobects, if there are any
								$cnt = 1;

								if (array_key_exists("subobjects",$saysojson->getJson())) {
									foreach ($saysojson->getJson('subobjects') as $key=>$value) {

										$this->_subobject($value['table'], $value['fk'],$formElements["id"]->getValue(),$cnt,$realtable,(isset($value['lookuporder']) ? $value['lookuporder'] : null));
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

						$this->view->breadcrumb = Breadcrumb::add("Delete ".$tablename,$_SERVER['REQUEST_URI']);

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

										$coloptions['meta']['tablename'] = $realtable; // not $tablename;
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
										try {

											$result = $model->delete();

											$this->msg->addMessage('Record successfully deleted');
											$this->rd->gotoSimple('view','admin','cms',array('table' => $tablename));

											} catch (Zend_Db_Exception $e) {
												$previous = $e->getPrevious();

												if ($previous->getCode()==23000) {
													$this->msg->addMessage('Record cannot be deleted.<br /> Responses to this '.$tablename.' exist<br /> and must be removed first');
													$this->rd->gotoSimple('delete','admin','cms',array('table' => $tablename,'id'=>$id));
												} else {
													$this->msg->addMessage('Record cannot be deleted.<br />Reason:'.$previous->getMessage());
													$this->rd->gotoSimple('delete','admin','cms',array('table' => $tablename,'id'=>$id));
												}

											}

										} else {
											// Delete cancelled
											$this->msg->addMessage('Delete cancelled');
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

		public function duplicateAction()
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

						$this->view->breadcrumb = Breadcrumb::add("Duplicate ".$tablename,$_SERVER['REQUEST_URI']);

						if ($saysojson->checkTablePermission('allowduplicate')) {
							$columnlist = $saysojson->getCMSColumns("displaywhen","duplicate");

							if ($columnlist) {
								$realtable = strtolower($saysojson->getTableAttr('tablename'));
								$select = Zend_Registry::get('db')->select()->from($realtable,$columnlist)->where("id = ?",$id);

								$stmt = $select->query();
								$currentData = $stmt->fetchAll();
								if (count($currentData) == 1) {
									$currentData = $currentData[0];

									// currentData contains a list of fields and values from the table,
									// which we can put in as initial values

									// Start with a blank formelements array, and add the array items
									// as we go
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
											$formElements[$colname]->buildElement("duplicate",$coloptions);

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

									$formElements['submityes'] = new Zend_Form_Element_Submit('dup');
									$formElements['submityes'] ->setLabel(sprintf('Confirm Duplicate')); // the button's value

									$formElements['submitno'] = new Zend_Form_Element_Submit('del');
									$formElements['submitno'] ->setLabel(sprintf('Cancel')); // the button's value

									$form = new ZendX_JQuery_Form();
									$form->setName($realtable);
									$form->addElements($formElements);

									$form->addElement('hash', 'no_csrf_foo', array('salt' => 'uniquesay.so'));

								if ($this->getRequest()->isPost()) { //is it a post request ?
									$postData = $this->getRequest()->getPost(); // getting the $_POST data

									if ($form->isValid($postData)) {
										if ($postData['dup']=="Confirm Duplicate") {
											$formData = $form->getValues(); // data filtered
											$tablefrommodel = $saysojson->getModel();
											$model = new $tablefrommodel();

											unset($formData['no_csrf_foo']); // Remove the salt - we don't need it for the duplicate

											$model->setData($formData);
											$result = $model->save();

											$this->msg->addMessage('Record successfully duplicated');
											$this->rd->gotoSimple('view','admin','cms',array('table' => 'trailer'));

											} else {
												// Delete cancelled
												$this->msg->addMessage('Duplicate cancelled');
												$this->rd->gotoSimple('view','admin','cms',array('table' => 'trailer'));
											}
										} else {
											$form->populate($postData); // show errors and populate form with $postData
										}
									}

									$this->view->tablename = $tablenamepolite;

									$this->view->BackLink = sprintf('<span class="backlink"><a href="/cms/admin/view/table/%s/"><img src="/images/icons/arrow_left.png" style="width:16px;" alt="Back" Title="Back" /> Back</a></span>',$tablename);

									$this->view->form = $form; // assigning the form to view
								} else {
									$this->view->message = sprintf("E05: Invalid ID [%s] for %s",$id,$tablename);
								}
							} else {
								$this->view->message = sprintf("E08: No fields set to allow Duplicate in the model %s",$tablename);
							}
						} else {
							$this->view->message = sprintf("E06: Duplicate not allowed for %s",$tablename);
						}
					} else {

						$this->view->message = sprintf("E07: Table definition [%s] does not exist in the JSON file",$tablename);

					}
				}
			}
		}

		public function userAction()
		{
			$this->view->breadcrumb = Breadcrumb::add("Edit User",$_SERVER['REQUEST_URI']);
			if ($this->getRequest()->isPost()) { //is it a post request ?
				$this->_helper->layout->disableLayout();
			}

			$formElements = array();

            $formElements['emailaddress'] = new Form_Element_Text('emailaddress');
            $formElements['emailaddress']->setLabel("Users Email Address");

			$form = new ZendX_JQuery_Form();
			$form->setName("Reward");
			$form->setAttrib('id','div_form');
			$form->addElements($formElements);
			$form->addElement('hash', 'no_csrf_foo', array('salt' => 'uniquesay.so'));
			$form->addElement('submit', 'submit', array(
        		'label' => 'Get User Info',
        		'onclick' => "$('#main').load('" . "/cms/admin/user" . "', $('#div_form').serializeArray() ); return false;"
    		));
			if ($this->getRequest()->isPost()) { //is it a post request ?

				$postData = $this->getRequest()->getPost(); // getting the $_POST data

				$select = Zend_Registry::get('db')
							->select()
							->from("starbar");

				$stmt = $select->query();
				$starbarreference = $stmt->fetchAll();
				// Starbar reference contains an array of starbars that this user has installed (or started to install)
				/**
				* @example Array
(
    [0] => Array
        (
            [id] => 1
            [short_name] => hellomusic
            [label] => Hello Music
            [description] =>
            [user_pseudonym] => Rocker
            [domain] => hellomusic.com
            [auth_key] => 309e34632c2ca9cd5edaf2388f5fa3db
            [flags] => adjuster_ads
            [created] => 2011-11-09 12:08:54
            [modified] => 2011-11-09 12:08:54
            [launched] => 0000-00-00 00:00:00
            [economy_id] => 1
        )

    [1] => Array
        (
            [id] => 2
            [short_name] => snakkle
            [label] => Snakkle
            [description] =>
            [user_pseudonym] => Papparazzi
            [domain] => snakkle.com
            [auth_key] => 309e34632c2ca9cd5edaf2388f5fa3db
            [flags] =>
            [created] => 0000-00-00 00:00:00
            [modified] => 0000-00-00 00:00:00
            [launched] => 0000-00-00 00:00:00
            [economy_id] => 2
        )

				*/


				if ($postData['emailaddress']!=null) {
					// We have an email address. Get all starbars associated with this user
					$user_email = new User_Email();
					$userdata = $user_email->getUserID($postData['emailaddress']);

					if ($userdata) {
						// Find out which starbars have been actually been used by this user
						$tabs = $this->_usedstarbars($userdata);
						// Pass the starbar details back to the view
						$this->view->userdata = $userdata;// was usersstarbars
						$sessionvar = new Zend_Session_Namespace('sessionvar');
						$sessionvar->userunderobservation = $postData['emailaddress'];
						$this->view->tabs = $tabs;// was usersstarbars
					} else {
						$this->view->error = sprintf("No data found for user %s",$postData['emailaddress']);
					}

				} else {
					$this->view->error = "Please enter an email address";
				}
			}
			$this->view->form = $form; // assigning the form to view
		}

		public function goodAction()
		{
			if ($this->getRequest()->isPost()) { //is it a post request ?
				$this->_helper->layout->disableLayout();
			}

			$formElements = array();
			$formElements['starbarid'] = new Form_Element_Select('starbarid');
			$formElements['starbarid']->setLabel('Starbar')
					->setDescription('Please select the Starbar that this item belongs to')
					->setRequired(true)
					->setMultiOptions(array(
					    '2' => 'Snakkle',
					    '3' => 'Movie',
					    '4' => 'Machinima'

					));

            $formElements['goodid'] = new Form_Element_Text('goodid');
            $formElements['goodid']->setLabel("Good ID (e.g 4204004)");

			$form = new ZendX_JQuery_Form();
			$form->setName("Good Info");
			$form->setAttrib('id','div_form');
			$form->addElements($formElements);
			$form->addElement('hash', 'no_csrf_foo', array('salt' => 'uniquesay.so'));
			$form->addElement('submit', 'submit', array(
        		'label' => 'Get Good Info',
        		'onclick' => "$('#main').load('" . "/cms/admin/good" . "', $('#div_form').serializeArray() ); return false;"
    		));
			if ($this->getRequest()->isPost()) { //is it a post request ?

				$postData = $this->getRequest()->getPost(); // getting the $_POST data

				if ($postData['goodid']!=null) {

					$request = $this->getRequest();
					$goodId = $postData['goodid'];

					$request->getParam('named_good_id');
					$newInventory = $request->getParam('new_inventory');

					$this->getRequest()->setParam('user_id',1);
					$this->getRequest()->setParam('starbar_id',$request->getParam('starbarid'));
 					$gameStarbar = Game_Starbar::getInstance();
					$goodsData = $gameStarbar->getGoodsFromStore();
					$goods = new ItemCollection();

					foreach ($goodsData as $goodData) {
					    $good = new Gaming_BigDoor_Good();
					    $good->setPrimaryCurrencyId($gameStarbar->getPurchaseCurrencyId());
					    $good->build($goodData);
					    $goods[(int) $good->getId()] = $good;
					}

		   			$info->id = $goodId;
		   			$info->starbar = $request->getParam('starbarid');
					$info->title = $goods[$goodId]['title'];
					$info->cost = $goods[$goodId]['cost'];
					$info->sold = $goods[$goodId]['inventory_sold'];
					$info->total = $goods[$goodId]['inventory_total'];


					$this->view->info = $info; // Assign the output variables to view

					$this->view->form = $form; // assigning the form to view, in case the user wants another search

				} else {
					$this->view->error = "Please enter a Good ID";
				}

			}
			$this->view->form = $form; // assigning the form to view

		}


		// Called by AJAX from /cms/admin/user
		public function sboverviewAction()
		{
			$this->_helper->layout->disableLayout();
			$user_id = $this->getRequest()->getParam('user');

			// Find out what day this user arrived
			$strWhere = sprintf("id = %s",$user_id);
			$select = Zend_Registry::get('db')
						->select()
						->from("user",array("created","originating_starbar_id"))
						->where($strWhere);

			$stmt = $select->query();
			$datecreated = $stmt->fetchAll();
			// We now know the date this user was created, and their originating starbar (which may be null)

			if ($datecreated[0]['originating_starbar_id']!=null){

				// We have an originating starbar. Find out what it is called.
				$strWhere = sprintf("id = %s",$datecreated[0]['originating_starbar_id']);

				$select = Zend_Registry::get('db')
							->select()
							->from("starbar","label")
							->where($strWhere);

				$stmt = $select->query();
				$originalstarbar = $stmt->fetchAll();
				$firstseenstarbar = $originalstarbar[0]['label'];
			} else {
				$firstseenstarbar = null;
			}

			// Find out what day this user was last seen
			$strWhere = sprintf("user_id = %s",$user_id);
			$select = Zend_Registry::get('db')
						->select()
						->from("metrics_log",array("created","starbar_id"))
						->where($strWhere)
						->order('id desc');

			$stmt = $select->query();
			$datelastseen = $stmt->fetchAll();

			$info = new stdClass(); // set up the class we are going to send to the view

			if ($datelastseen) {
				// User has been seen at least once since installing
				$info->neverseen = false;
				// What was the last starbar we saw this user on?
				$strWhere = sprintf("id = %s",$datelastseen[0]['starbar_id']);

				$select = Zend_Registry::get('db')
							->select()
							->from("starbar","label")
							->where($strWhere);

				$stmt = $select->query();
				$lateststarbar = $stmt->fetchAll();


				$info->firstseentext = $this->_ago($datecreated[0]['created']);
				$info->firstseen = $datecreated[0]['created'];
				$info->firstseenstarbar = $firstseenstarbar;
				$info->lastseentext = $this->_ago($datelastseen[0]['created']);
				$info->lastseen = $datelastseen[0]['created'];
				$info->lastseenstarbar = $lateststarbar[0]['label'];
				$diff = date_diff(date_create($info->lastseen),date_create($info->firstseen));

				$info->activedays = $this->_timeactive($diff);

			} else {
				// This user has not been seen since install
				$info->neverseen = true;
				$info->firstseentext = $this->_ago($datecreated[0]['created']);
				$info->firstseen = $datecreated[0]['created'];
				$info->firstseenstarbar = $firstseenstarbar;
			}

			$this->view->info = $info;

		}

		// Called by AJAX from /cms/admin/user
		public function sbinfoAction()
		{
			$this->_helper->layout->disableLayout();

			$user_id = $this->getRequest()->getParam('user');
			$starbar_id = $this->getRequest()->getParam('starbar');


			// Get the post variables
			$postData =$this->_getAllParams();

			$this->getRequest()->setParam('user_id',$user_id);
			$this->getRequest()->setParam('starbar_id',$starbar_id);

			$starbar = new Starbar();
			$starbar->loadData($starbar_id);  // Create a starbar object for the bar we are investigating for this user

			$email = new User_Email();

			$email->loadData($user_id);

			$gameStarbar = Game_Starbar::getInstance();

			$economy = $gameStarbar->getEconomy();


			try {

				$gameStarbar->loadGamerProfile();

				$info = new stdClass(); // Variable created in order to transport data into the view
				$client = $economy->getClient();


				$gamer = $gameStarbar->getGamer();
				$gamer->removeProfileCache();

				$client->getEndUser($gamer->getGamingId());

				$data = $client->getData(); // Gives us an object which contains the currency balances. Other methods are unreliable, as they seem to get caught in a 7-day cache somewhere in the system.


				$gamer->loadProfile($client, $gameStarbar); // In case loadGamerProfile doesn't work
				$gamingid = $gamer->getGamingId();


				$redeemablecurrency = $economy->getCurrencyIdByType('redeemable');
				$experiencecurrency = $economy->getCurrencyIdByType('experience');

				foreach ($data->currency_balances as $currency) {

				    if ($currency->currency_id==$experiencecurrency) {
				    	$info->experiencebalance = $currency->current_balance;
					}

					if ($currency->currency_id==$redeemablecurrency) {
						$info->redeemablebalance = $currency->current_balance;
					}
				}

   				$currencyRed = $economy->getCurrencyTitleFromType("redeemable");
   				$currencyExp = $economy->getCurrencyTitleFromType("experience");

   				$info->currency = ucwords(strtolower(str_replace('_',' ',$currencyRed)));

   				$info->XP = ucwords(strtolower(str_replace('_',' ',$currencyExp)));

   				$info->starbar_id = $starbar_id;
   				$info->level = $gamer->getHighestLevel();


   				// Get list of goods purchased by this user
   				$info->goods = array();
   				$ctr=0;
				$goods = $gamer->getGoods();
				foreach ($goods as $good) {
					$info->goods[$ctr]['description'] = $good['description'];
					$info->goods[$ctr]['url_preview'] = $good['url_preview'];
					$ctr++;
				}

   				$info->leveltitle = $economy->end_user_title;
   				$info->levelurl = $info->level->urls[1]->url;
				$info->affiliatewarning = null;

			} catch (Api_Exception $e) {
				// We know that code 251 is 'Gaming system error: NOT FOUND'. Notify any other errors
				if ($e->getCode() != 251) {
					printf( "<h2>Unknown exception [%s] caught: [%s]</h2>",$e->getCode(),$e->getMessage());
					printf("<h3>Stack Trace</h3><pre>%s</pre>",print_r($e->getTraceAsString(),true));
				} else {
					$info->affiliatewarning = "Affiliate no longer exists. Game information is not available";
				}
			}

			// reward form to apply bonuses
			$formElements = array();

            $formElements['redeemablecurrency'] = new Form_Element_Text('redeemablecurrency');
            $formElements['redeemablecurrency']->setLabel("Experience Points");
            $formElements['redeemableaward'] = new Form_Element_Text('redeemableaward');
            $formElements['redeemableaward']->setLabel("Redeemable Points");
            $formElements['emailaddress'] = new Form_Element_Hidden('emailaddress');
           // $formElements['emailaddress']->setValue($_SESSION['userunderobservation']);
           $sessionvar = new Zend_Session_Namespace('sessionvar');
           $formElements['emailaddress']->setValue($sessionvar->userunderobservation);

            $formElements['bar']= new Form_Element_Hidden('starbar_id');
            $formElements['bar']->setValue($starbar_id);
 			$formElements['user']= new Form_Element_Hidden('user_id');
            $formElements['user']->setValue($user_id);

			$rewardform = new ZendX_JQuery_Form();
			$rewardform->setName("Reward");
			$rewardform->setAttrib('id','sb_bonus_'.$starbar_id);
			$rewardform->addElement('hash', 'no_csrf_foo', array('salt' => 'uniquesay.so'));
			$rewardform->addElement('hidden', 'plaintext', array(
    					'description' => 'Positive numbers - add points. Negative numbers - remove points',
    					'ignore' => true,
    					'decorators' => array(
        					array('Description', array('escape'=>false, 'tag'=>'')),
    					),
			));
			$rewardform->addElements($formElements);
			$rewardform->addElement('submit', 'submit', array(
        		'label' => 'Adjust Points',
        		'onclick' => "$('#sb_bonusrewards_".$starbar_id."').load('" . "/cms/admin/notifyreward" . "', $('#sb_bonus_".$starbar_id."').serializeArray() ); return false;"
    		));

    		// We don't handle the post here - that's handled in the notifyrewardAction

			$this->view->rewardform = $rewardform; // assigning the form to view

			// Social Media Results
			$info->likes = array();
			$ctr=0;
			foreach ($this->_mediaanalysis($user_id,$starbar_id) as $like) {
				$info->likes[$ctr]['url'] = $like['url'];
				$info->likes[$ctr]['content'] = $like['content'];
				$info->likes[$ctr]['created'] = $like['created'];
				$ctr++;
			}

			$info->tweets = array();
			$ctr=0;
			foreach ($this->_mediaanalysis($user_id,$starbar_id,'tweet') as $twit) {
				$info->tweets[$ctr]['url'] = $twit['url'];
				$info->tweets[$ctr]['content'] = $twit['content'];
				$info->tweets[$ctr]['created'] = $twit['created'];
				$ctr++;
			}

			// Surveys and Polls completed

			$info->surveyscompleted = array();
			foreach ($this->_surveyanalysis($user_id) as $survey) {
				$info->surveyscompleted[] = $survey['title'];
			}

			$info->pollscompleted = array();
			foreach ($this->_surveyanalysis($user_id,"poll") as $survey) {
				$info->pollscompleted[] = $survey['title'];
			}


			$info->quizzescompleted = array();
			foreach ($this->_surveyanalysis($user_id,"quiz") as $survey) {
				$info->quizzescompleted[] = $survey['title'];
			}

   			$info->trailerscompleted = array();
   			foreach ($this->_surveyanalysis($user_id,"trailer") as $survey) {
				$info->trailerscompleted[] = $survey['title'];
			}

   			$this->view->info = $info;
			$registry = Registry::getInstance();
			$registry->offsetUnset('starbar');

		}

		// Called by AJAX from /cms/admin/user
		public function snakklexferAction()
		{
			// Get a list of all tmp_user_transfer users where starbar is snakkle

			$transferusers = new User_TransferCollection();
			$transferusers->loadForStarbarBlock('Snakkle',307,308);
			//$transferusers->loadForStarbar('Snakkle');
			$cnt = 0;
			$this->getRequest()->setParam('starbar_id','2');// We know that Starbar ID 2 is Snakkle
			$starbar = new Starbar();
			$starbar->loadData('2');  // Create a starbar object for the bar we are investigating for this user

			printf("<table><tr><th>&nbsp;</th><th>ID</th><th>Email</th><th>Level</th><th>Experience Points</th><th>Redeemable Points</th></tr>");

			foreach($transferusers as $user) {
				$cnt++;
			//	printf("gaming id is [%s], email is [%s]",$user->gaming_id,$user->email);
				$user_id = $user->user_id;
				$user_email = $user->email;
				$gaming_id = $user->gaming_id;

			//****************************************************
				$this->getRequest()->setParam('user_id',$user_id);
				$email = new User_Email();

				$email->loadData($user_id);
				$gameStarbar = Game_Starbar::getInstance();
				$economy = $gameStarbar->getEconomy();
				$gameStarbar->loadGamerProfile();

				$client = $economy->getClient();


				$gamer = $gameStarbar->getGamer();
				$gamer->loadProfile($client, $gameStarbar); // In case loadGamerProfile doesn't work
//$user->gaming_id = $gamer->gaming_id;
				$client->getEndUser($user->gaming_id);

				$data = $client->getData(); // Gives us an object which contains the currency balances. Other methods are unreliable, as they seem to get caught in a 7-day cache somewhere in the system.

				$gamingid = $gamer->getGamingId();
				$level = $gamer->getHighestLevel();
							// Check that $gamingid is the same as $gaming_id - otherwise we have a problem.

				$redeemablecurrency = $economy->getCurrencyIdByType('redeemable');
				$experiencecurrency = $economy->getCurrencyIdByType('experience');

				$user->experiencepoints = 0;
				$user->redeemablepoints = 0;
				$user->level = 'Not known';

				//if ($data->currency_balances) {
					foreach ($data->currency_balances as $currency) {

						if ($currency->currency_id==$experiencecurrency) {
							$user->experiencepoints = $currency->current_balance;
							$experiencepoints = $user->experiencepoints;
						}

						if ($currency->currency_id==$redeemablecurrency) {
							$user->redeemablepoints = $currency->current_balance;
							$redeemablepoints = $user->redeemablepoints;
						}
					}
				//}
				$user->level =  $data->level_summaries[0]->end_user_title;//gameStarbar->getHighestLevel();
							//$level = $user->level->title;

							//var_dump($user->level);
				printf("<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>",$cnt,$user->user_id,$user->email,$user->level,$user->experiencepoints,$user->redeemablepoints);


				unset($client);
				unset($data);
				unset($gamer);
				unset($economy);

				$user->save();
			}
			printf("</table>");
			printf("completed. %s records updated.",$cnt);

		}

		public function notifyrewardAction()
		{
			$this->_helper->layout->disableLayout();

			if (!$this->getRequest()->isPost()) { //is it a post request ?
			 	printf("You should not be here. Execution terminated");
			} else {
				// It's a post request. We can take it from here.
				$formData = $_POST;


			  	$starbar = new Starbar();
				$starbar->loadData($_POST['starbar_id']);
				$request = $this->getRequest();

				$request->setParam('user_id',$_POST['user_id']);
				$gameStarbar = Game_Starbar::getInstance();

				$economy = $gameStarbar->getEconomy();

				$email = new User_Email();
				$emaildata = $email->getUserID($_POST['emailaddress']);

				/* Get the correct gaming_id for this starbar */
				for ($ctr=0;$ctr<count($emaildata);$ctr++) {
					if ($emaildata[$ctr]['starbar_id'] == $_POST['starbar_id']) {
						$gaming_id = $emaildata[$ctr]['gaming_id'];
					}
				}

				$client = $economy->getClient();
				$client->addCustomParameter('verbosity', '9');
				$gamer = $gameStarbar->getGamer();
$results = array();
				$gamerCurrencies = $gamer->getCurrencies();

				if (isset($formData['redeemablecurrency'])) {

					$actionID = $economy->getActionId('ADHOC_EXPERIENCEPOINTS');
					$client->setParameterPost('amount',$formData['redeemablecurrency']);
					$result = $client->namedTransactionGroup($actionID)->postExecute($gaming_id);
					if( !$client->hasError() )
						Game_Transaction::run( $_POST['user_id'], $_POST['starbar_id'], 'ADHOC_EXPERIENCEPOINTS'
						                     , array('custom_amount'=>$formData['redeemablecurrency']) );
$results[] = $result;
				}

				if (isset($formData['redeemableaward'])) {
					$actionID = $economy->getActionId('ADHOC_REDEEMABLEPOINTS');
					$client->setParameterPost('amount',$formData['redeemableaward']);
					$result = $client->namedTransactionGroup($actionID)->postExecute($gaming_id);
					if( !$client->hasError() )
						Game_Transaction::run( $_POST['user_id'], $_POST['starbar_id'], 'ADHOC_REDEEMABLEPOINTS'
						                     , array('custom_amount'=>$formData['redeemableaward']) );
					$results[] = $result;
				}

				$client->getEndUser($gamer->getGamingId());

				$data = $client->getData();
				$redeemablecurrency = $economy->getCurrencyIdByType('redeemable');
				$experiencecurrency = $economy->getCurrencyIdByType('experience');

				$info = new stdClass(); // Variable created in order to transport data into the view

				foreach ($data->currency_balances as $currency) {

				    if ($currency->currency_id==$experiencecurrency) {
				    	$info->experiencebalance = (int)$currency->current_balance;
					}

					if ($currency->currency_id==$redeemablecurrency) {
						$info->redeemablebalance = (int)$currency->current_balance;
					}
				}

	printf("<h2>Points adjustment</h2><p>User now has <strong>%s</strong> experience points, and <strong>%s</strong> redeemable points.</p>",$info->experiencebalance,$info->redeemablebalance);
	//printf("<pre>%s</pre>",print_r($results,true));

  			}
		}

		/**
		* Reward User with point
		*/
		public function rewardAction()
		{
			//PTC Wed $this->_helper->layout->disableLayout();
printf("In reward action");
			$formElements = array();

			$formElements['starbar'] = new Form_Element_Starbar('starbar_id');
            $formElements['emailaddress'] = new Form_Element_Text('emailaddress');
            $formElements['emailaddress']->setLabel("Users Email Address");
            $formElements['redeemableaward'] = new Form_Element_Text('redeemableaward');
            $formElements['redeemableaward']->setLabel("Award Points");
            $formElements['redeemablecurrency'] = new Form_Element_Text('redeemablecurrency');
            $formElements['redeemablecurrency']->setLabel("Award Currency");
			$formElements['submit'] = new Zend_Form_Element_Submit('submit');
            $formElements['submit'] ->setLabel('Give Rewaccccrd') // the button's value
				    ->setIgnore(true); // very usefull -> it will be ignored before insertion


						$form = new ZendX_JQuery_Form();
						$form->setName("Reward");
						$form->addElements($formElements);
						$form->addElement('hash', 'no_csrf_foo', array('salt' => 'uniquesay.so'));

						if ($this->getRequest()->isPost()) { //is it a post request ?

							$postData = $this->getRequest()->getPost(); // getting the $_POST data
printf("It's a post!");
printf("<h1>Point 1</h1><pre>%s</pre>",print_r($postData,true));
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
								$email = new User_Email();

								$emaildata = $email->getUserID($formData['emailaddress']);
								//  In getInstance, we pass the key and secret (obtainable from publisher.bigdoor.com)

								$client = Gaming_BigDoor_HttpClient::getInstance('43bfbce697bd4be99c9bf276f9c6b086', '35eb12f3e87144a0822cf1d18d93d867');

								// Set the enduser by passing the gaming_id from the user_gaming table
								$client->getEndUser($emaildata[0]['gaming_id']);

$data = $client->getData();
								do_dump($data);

								if (isset($formData['redeemablecurrency'])) {
									// Call transaction 5256048 - defined at http://publisher.bigdoor.com/economy/ntgs
									$client->setParameterPost('amount',$formData['redeemablecurrency']);
									$client->namedTransactionGroup(5256048)->postExecute($emaildata[0]['gaming_id']);
								}

								if (isset($formData['redeemableaward'])) {
									// Call transaction 5256049 - defined at http://publisher.bigdoor.com/economy/ntgs
									$client->setParameterPost('amount',$formData['redeemableaward']);
									$client->namedTransactionGroup(5256049)->postExecute($emaildata[0]['gaming_id']);
								}
$data = $client->getData();

								$currencyBalance = (int) $data['currency_balances'][0]['current_balance'];

								$this->view->message = "Reward successfully applied. User now has ".$currencyBalance;


									$form->reset();


							} else {
								$form->populate($postData); // show errors and populate form with $postData
							}
						}
						$this->view->form = $form; // assigning the form to view
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

					// Set the breadcrumb
					Breadcrumb::resetBreadcrumbTrail(); // Back to the start
					$this->view->breadcrumb = Breadcrumb::add("View all ".$tablename."s",$_SERVER['REQUEST_URI']);

					// Create the grid
					// Find the columns we want to see on the grid
					$columnlist = $saysojson->getCMSColumns("displaywhen","grid");
					$where = $saysojson->getTableAttr('where');
					$sortorder = $saysojson->getTableAttr('lookuporder');
					$realtable = strtolower($saysojson->getTableAttr('tablename'));
					if ($where==null) {
						$select = Zend_Registry::get('db')->select()->from($realtable,$columnlist);
					} else {
						$select = Zend_Registry::get('db')->select()->from($realtable,$columnlist)->where($where);
					}
					if ($sortorder!=null) {
						$select->order($sortorder);
					} else {
						$select->order("id desc");
					}
					$grid   = new Cms_Matrix();
					$grid->setJqgParams(array('altRows' => true));// rows will alternate color
					$grid->setSource(new Bvb_Grid_Source_Zend_Select($select));

					$jquerywidth = array(); // Will store details of filter column header widths

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

						// Set search filter widths
						$filtercolwidth =  $saysojson->getColAttr($value,'filterwidth');
						if ($filtercolwidth!=Null) {
							$jquerywidth['#filter_'.$value.'list'] = sprintf("'width','%spx'",$filtercolwidth);
						}

						// Set column title
						if ($saysojson->getColAttr($value,'label')!=null) {
							$grid->updateColumn($value,array('title'=>$saysojson->getColAttr($value,'label')));
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

					// Retrieve the table comment, if any
					$this->view->tablecomment = $saysojson->getTableAttr('comment');

					$this->view->jquerywidth = $jquerywidth;
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
		* If the input value is a '1', generate a tick mark
		*
		* @param mixed $id
		* @param integer $value
		* @author Peter Connolly
		*/
		public function _generateTickMark($id,$value)
		{
			if ($id=="1") {
				return "<img src='/images/icons/tick.png' alt='Checked' />";
			}
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
		* Generate a button which will activate the view action
		*
		* @param mixed $id
		* @author Peter Connolly
		*/
		public function _generateDuplicateButtonLink($id,$tablename=null,$tablenamepolite=null)
		{
			$currentURL = $this->view->url();

			if ($tablename!=null) {
				$reverse = strrev($currentURL);
				// If the last character is a /, remove it
				if ($reverse[0]=="/") {
					$currentURL = substr($currentURL,0,strlen($currentURL)-1);
				}

				$link = '<a href="' .$this->view->url(array('action' => 'duplicate', 'table'=> $tablename, 'id' => intval($id))). '" class="button-details" title="Duplicate"><img src="/images/icons/duplicate.png" style="width:16px;" alt="Duplicate" Title="Duplicate" /></a>';

			} else {

				$link = '<a href="' .$this->view->url(array('action' => 'duplicate', 'id' => intval($id))). '" class="button-duplicate" title="Duplicate"><img src="/images/icons/duplicate.png" style="width:16px;" alt="Duplicate" Title="Duplicate" /></a>';
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

					$this->view->breadcrumb = Breadcrumb::add("Add ".$tablename,$_SERVER['REQUEST_URI']);

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
								$formElements[$colname]->setAttrib("size", (int)$coloptions['width']/8);
							}
						}

						// All column elements have been built. Add the standard form elements
						$formElements['submit'] = new Zend_Form_Element_Submit('submit');
						$formElements['submit'] ->setLabel(sprintf('Save New %s',$tablenamepolite)); // the button's value

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
									if ((strpos($key,"_notrequired")===true)
										| (strpos($key,"nocol")===true))
									  {
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

								$this->msg->addMessage('Record successfully added');

								if (($parenttable!=null) && ($parentid!=null))
								{
									if ($tablename=="quiz_question") {
										$this->rd->gotoSimple('detail','admin','cms',array('table' => 'quiz','id'=>$parentid));
									} else {
										$this->rd->gotoSimple('detail','admin','cms',array('table' => $parenttable,'id'=>$parentid));
									}
								} else {
									$this->rd->gotoSimple('detail','admin','cms',array('table' => $tablename,'id'=>$model->id));
									//$form->reset();
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

