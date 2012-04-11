<?php
    /**
     * controllers/SurveyController.php
     * @author Peter Connolly, March 2012
     */

    require_once APPLICATION_PATH . '/modules/admin/controllers/CommonController.php';

    class Cms_SurveysController extends Admin_CommonController
    {
	public function preDispatch() {
		// i.e. for everything based on Generic Starbar, use these includes
		$this->view->headLink()->appendStylesheet('/css/starbar-generic.css');
		$this->view->headLink()->appendStylesheet('/css/cms.css');
		$this->view->headScript()->appendFile('/js/starbar/jquery-1.7.1.min.js');
		$this->view->headScript()->appendFile('/js/starbar/jquery-ui-1.8.16.custom.min.js');
		$this->view->headScript()->appendFile('/js/starbar/jquery.jscrollpane.min.js');
		$this->view->headScript()->appendFile('/js/starbar/jquery.cookie.js');
		$this->view->headScript()->appendFile('/js/starbar/jquery.jeip.js');
		$this->view->headScript()->appendFile('/js/starbar/jquery.cycle.all.js');
		$this->view->headScript()->appendFile('/js/starbar/jquery.easyTooltip.js');
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
            $this->Surveys = new Survey(); // DbTable
        }

        /**
         * Action to Add a Survey to the database
         */
        public function addAction()
        {
            /*
             * Set up the form to be built
             */
            $this->view->headLink()->appendStylesheet('/modules/admin/survey/module.css', 'screen');
            $this->view->headScript()->appendFile('/modules/admin/survey/survey.js');
         //   $this->view->headScript()->appendFile('/modules/admin/survey/add.js');
	   // $this->view->headScript()->appendFile('/modules/jquery-ui-timepicker-addon.js');
            $this->view->indexLink = '<a href="' . $this->view->url(array('action' => 'index')) . '">Back to All Surveys</a>';


            $form = $this->getForm(); // getting the post form
	//temp    $form->getElement('submit')->setLabel('Add Survey');
            if ($this->getRequest()->isPost()) { //is it a post request ?
                $postData = $this->getRequest()->getPost(); // getting the $_POST data
                if ($form->isValid($postData)) {
                    $formData = $form->getValues(); // data filtered
                    // created and updated fields
                    $formData += array('created' => date('Y-m-d H:i:s'), 'updated' => date('Y-m-d H:i:s'));
                    $this->Surveys->insert($formData); // database insertion

                  // $this->Surveys->save();
                }
                else $form->populate($postData); // show errors and populate form with $postData
            }

            $this->view->form = $form; // assigning the form to view
        }

        /**
         * Display a Survey form
         * @return \Zend_Form
         */
        public function getForm()
        {
	    $formElements = array();

            $formElements['starbarlist'] = new Form_Element_Starbar('starbar_id');

            $formElements['title'] = new Form_Element_Text('title');
$formElements['title']->setAttrib("size","60");
		$formElements['title']	->setDescription('Title for this Survey')
					->setRequired(true); // required field

	    $formElements['external_id'] = new Form_Element_Text('external_id');

	    $formElements['external_key'] = new Form_Element_Text('external_key');

            $formElements['premium'] = new Form_Element_Select('premium');
		$formElements['premium']->setLabel('Survey Type')
					->setDescription('Is this a Standard or Premium Survey')
					->setRequired(false)
					->setMultiOptions(array(
					    '' => 'Standard',
					    '1' => 'Premium'

					));

            $formElements['number_of_questions'] = new Form_Element_Number('number_of_questions');

	    $formElements['number_of_answers'] = new Form_Element_Number('number_of_answers');

	    $formElements['display_number_of_questions'] = new Form_Element_Number('display_number_of_questions');

	    $formElements['ordinal'] = new Form_Element_Number('ordinal');

	    $formElements['start_after'] = new Form_Element_Number('start_after');

	    $formElements['start_at'] = new Form_Element_Date('start_at');

	    $formElements['end_at'] = new Form_Element_Date('end_at');

            $formElements['submit'] = new Zend_Form_Element_Submit('submit');
            $formElements['submit'] ->setLabel('Save New Survey') // the button's value
				    ->setIgnore(true); // very usefull -> it will be ignored before insertion

          //  $form = new Zend_Form();
	    $form = new ZendX_JQuery_Form();
            $form->setName('survey');
	    $form->addElements($formElements);
            $form->addElement('hash', 'no_csrf_foo', array('salt' => 'uniquesay.so'));
                // ->setAction('') // you can set your action. We will let blank, to send the request to the same action

            return $form; // return the form
        }

        /**
         * Display all Survey records
	 * @author Peter Connolly
         */
        public function indexAction()
        {

            $surveyCollection = new SurveyCollection();
            $surveyCollection->loadAllSurveys('surveys');


            // Pass the collection to the view component
            $this->view->surveyCollection = $surveyCollection;
            $this->view->numberOfSurveys = sizeof($surveyCollection);
         //   $this->view->addLink = '<a href="' . $this->view->url(array('action' => 'add')) . '">Create A New Survey</a>';

	    $this->view->addLink = '<a href="' . $this->view->url(array('action' => 'add')) . '"><img src="http://local.sayso.com/images/icons/add.png" style="width:16px;" alt="Create A New Survey" Title="Create A New Survey" /> Create A New Survey</a>';

        }

	/**
	 * Display a single record on the screen
	 * @author Peter Connolly
	 *
	 */
	public function showAction()
        {
            $id = $this->getRequest()->getParam('id');
            if ($id > 0) {
                $post = $this->Surveys->loadData($id);
                $this->view->survey = $post;
            }
            else $this->view->message = 'The post ID does not exist';
        }

        /**
         * Edit a survey
	 * @author Peter Connolly
	 *
         */
        public function editAction()
        {
            $form = $this->getForm();

	    $form->getElement('submit')->setLabel('Save Changes');
            $id = $this->getRequest()->getParam('id');
            if ($id > 0) {
                if ($this->getRequest()->isPost()) { // update form submit
                    $surveyData = $this->getRequest()->getPost();
                    if ($form->isValid($surveyData)) {
                        $formData = $form->getValues();
                        $formData += array('updated' => date('Y-m-d H:i:s'));
                        $this->Surveys->update($formData, "id = $id"); // update
                        $this->_redirect('/cms/surveys/index');
                    }
                    else $form->populate($surveyData);
                }
                else {
                    $survey = $this->Surveys->loadData($id);//->current();
                    $form->populate($survey->toArray()); // populate method parameter has to be an array

                    // add the id hidden field in the form
                    $hidden = new Zend_Form_Element_Hidden('id');
                    $hidden->setValue($id);

                    $form->addElement($hidden);
                }
            }
            else $this->view->message = 'The survey ID does not exist';

            $this->view->form = $form;
        }

        /**
         * Delete a Survey record.
         * Redirects to surveys/index when complete
	 * @todo Display confirmation screen first, then delete
         */
        public function delAction()
        {
//            $id = $this->getRequest()->getParam('id');
//            if ($id > 0) {
//                // option 1
//                /*$post = $this->Posts->find($id)->current();
//                $post->delete();*/
//
//                // option 2
//                $this->Surveys->delete("id = $id");
//
//                $this->_redirect('/cms/surveys/index');
//            }
	    if ($this->getRequest()->isPost()) {
		$del = $this->getRequest()->getPost('del');
		if ($del == 'Yes') {
		    $id = $this->getRequest()->getPost('id');
		    $survey = new Survey();
		    $survey->deleteSurvey($id);

		}
		$this->_helper->redirector('index');
	    } else {
		$id = $this->_getParam('id',0);
		$survey = new Survey();
		$this->view->survey = $survey->getSurvey($id);
		// @todo getSurvey does not exist
	    }
        }


    }