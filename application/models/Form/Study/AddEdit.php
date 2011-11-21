<?php
/**
 * Create / Edit a Study
 * Tabbed form
 *
 * @author alecksmart
 */

final class Form_Study_AddEdit extends ZendX_JQuery_Form
{

    /**
     * @var Study
     */
    private $study;
    /**
     *
     * @var string
     */
    private $action;

    public function init()
    {
        $this->setAttrib('id', 'mainForm')
            ->setAttrib('style', 'display:none;');


        $submitBtn = $this->createElement('submit', 'submitBtn')->setLabel('Save Study');
        $this->addElement($submitBtn);

        $this->setDecorators(array
            (
                array('decorator' => array('SubformElements' => 'FormElements')),
                array('HtmlTag', array('tag' => 'div', 'id' => 'tabContainer', 'class' => 'mainForm')),
                array('TabContainer', array('id' => 'tabContainer', 'style' => 'width: auto;')),
                'FormElements',
                'Form'
            )
        );
    }

    public function setStudy(Study $study)
    {
        $this->study = $study;
    }

    public function setActionURL($action)
    {
        $this->action = $action;
    }

    public function buildDeferred()
    {
        $tabs = array
        (
            0 => array('name' => 'Basics'),
            1 => array('name' => 'ADjuster Campaign'),
            2 => array('name' => 'ADjuster Creative'),
            3 => array('name' => 'Behavioral Metrics'),
            4 => array('name' => 'Survey'),
            5 => array('name' => 'Quotas'),
            6 => array('name' => 'Cells'),
        );

        $subforms       = array();
        foreach ($tabs as $pageno => $subform)
        {
            $subforms[$pageno]    = new ZendX_JQuery_Form();
        }

        /**
         * Set the decorators on the subforms to use TabPane View Helper
         */

        foreach ($subforms as $pageno => $subform)
        {
            $subform->setAttrib('id', 'subForm');
            $subform->setDecorators(array
                (
                    'FormElements',
                    array('HtmlTag', array('tag' => 'div', 'class' => 'subForm')),
                    array('TabPane', array('jQueryParams' => array('containerId' => 'mainForm',
                        'title' => $tabs[$pageno]['name']))),
                    'Form'
                )
            );
            $this->addSubform($subform, 'subform_' . $pageno);
        }

        $leftCol    = array('HtmlTag', array('tag' => 'div', 'class' => 'f-deco-left-col'));
        $rightCol   = array('HtmlTag', array('tag' => 'div', 'class' => 'f-deco-right-col'));
        $alignLeft  = array('ViewHelper','Description','Errors', array('Label'), $leftCol);
        $alignRight = array('ViewHelper','Description','Errors', array('Label'), $rightCol);

        $commonTxtFilters = array('StripTags', 'StringTrim');
        $commonIntFilters = array('Int');


        /**
         * Basics
         */

        if($this->study instanceof Study)
        {
            //var_dump($this->study->id);exit(0);
            $hiddenStudyId =
                $this->createElement('hidden', 'hiddenStudyId')
                    ->setValue($this->study->id)
                    ->setDecorators(array('ViewHelper', 'Errors'));
            $this->addElement($hiddenStudyId);

            $this->setAction($this->action);
            //var_dump($this->action);exit(0);
        }

        $radioProductValue = 1;
        $radioProduct = new Zend_Form_Element_Radio('radioProduct');
            $radioProduct
                ->setMultiOptions(array(1=>'ADjuster Behavioral™',2=>'ADjuster Campaign™',3=>'ADjuster Creative™'))
                ->setLabel('Please select a desired study type*:')
                ->setRequired(true)
                ->addValidator(new Zend_Validate_Int())
                ->addFilters($commonIntFilters)
                ->setSeparator(' ')
                ->setValue($radioProductValue);

        $subforms[0]->addElements(array($radioProduct));
        $subforms[0]->addDisplayGroup(
            array($radioProduct),
            'group-product', array('Legend' => 'Select Product')
        );

        $txtStudyName =
            $this->createElement('text', 'txtStudyName')
                ->setLabel('Study Name*:')
                ->setRequired(true)
                ->addValidator(new Zend_Validate_NotEmpty())
                ->addFilters($commonTxtFilters)
                ->setDecorators($alignLeft);

        $txtStudyId =
            $this->createElement('text', 'txtStudyId')
                ->setLabel('Study Id:')
                ->setDecorators($alignRight);

        $txtSampleSize =
            $this->createElement('text', 'txtSampleSize')
                ->setLabel('Sample Size (Number)*:')
                ->setRequired(true)
                ->addValidator(new Zend_Validate_Int())
                ->addFilters($commonIntFilters)
                ->setDecorators($alignLeft);

        $txtMinThreshold =
            $this->createElement('text', 'txtMinThreshold')
                ->setLabel('Min. Threshold*:')
                ->setRequired(true)
                ->addValidator(new Zend_Validate_Int())
                ->addFilters($commonIntFilters)
                ->setDecorators($alignRight);

        $txtBegin =
            $this->createElement('text', 'txtBegin')
                ->setLabel('Begin*:')
                ->setRequired(true)
                ->addValidator(new Zend_Validate_NotEmpty())
                ->addFilters($commonTxtFilters)
                ->setDecorators($alignLeft);

        $txtEnd =
            $this->createElement('text', 'txtEnd')
                ->setLabel('End*:')
                ->setRequired(true)
                ->addValidator(new Zend_Validate_NotEmpty())
                ->addFilters($commonTxtFilters)
                ->setDecorators($alignRight);

        /*$radioIsSurveyValue = 1;
        $radioIsSurvey = new Zend_Form_Element_Radio('radioIsSurvey');
            $radioIsSurvey
                ->setMultiOptions(array(1=>'Yes',2=>'No'))
                ->setLabel('Is this going to be a survey?')
                ->setRequired(true)
                ->addValidator(new Zend_Validate_Int())
                ->addFilters($commonIntFilters)
                ->setDecorators($alignLeft)
                ->setSeparator(' ')
                ->setValue($radioIsSurveyValue);*/

        $subforms[0]->addElements(array($txtStudyName, $txtStudyId, $txtSampleSize, $txtMinThreshold, $txtBegin, $txtEnd));
        $subforms[0]->addDisplayGroup(
            array($txtStudyName, $txtStudyId, $txtSampleSize, $txtMinThreshold, $txtBegin, $txtEnd),
            'group-basic', array('Legend' => 'Basic Options')
        );

        /**
         * Social
         */

        $radioOnlineValue = 1;
        $radioOnline = new Zend_Form_Element_Radio('radioOnline');
            $radioOnline
                ->setMultiOptions(array(1=>'Yes',0=>'No'))
                ->setRequired(true)
                ->addValidator(new Zend_Validate_Int())
                ->addFilters($commonIntFilters)
                //->setDecorators($alignLeft)
                //->removeDecorator('Label')
                ->setSeparator(' ')
                ->setLabel('Record Full Click-Track?')
                ->setValue($radioOnlineValue);

        $subforms[3]->addElements(array($radioOnline));
        $subforms[3]->addDisplayGroup(
            array($radioOnline),
            'group-online', array('Legend' => 'Online')
        );


        $elements = array();
        $lookupSearchEngines = new Lookup_Collection_SearchEngine();
        $lookupSearchEngines->lookup();

        foreach ($lookupSearchEngines as $engine)
        {
            $elements[$engine->id] = $engine->label;
        }

        $cbSearchEngines = new Zend_Form_Element_MultiCheckbox('cbSearchEngines', array('multiOptions' => $elements));
            $cbSearchEngines->setLabel('Record Search Behavior?')
                ->setSeparator(' ');

            if($this->study instanceof Study)
            {
                $collection = new Study_SearchEnginesMapCollection();
                $collection->loadForStudy($this->study->id);
                $values = array();
                if($collection->count())
                {
                    foreach ($collection as $entry)
                    {
                        $values[] = $entry->search_engines_id;
                    }
                }
                $cbSearchEngines->setValue($values);
            }

        $subforms[3]->addElements(array($cbSearchEngines));
        $subforms[3]->addDisplayGroup(
            array($cbSearchEngines),
            'group-engines', array('Legend' => 'Search')
        );

        $elements = array();
        $socialMetrics = new Lookup_Collection_SocialActivityType();
        $socialMetrics->lookup();

        foreach ($socialMetrics as $engine)
        {
            $elements[$engine->id] = $engine->label;
        }

        $cbSocialMetrics = new Zend_Form_Element_MultiCheckbox('cbSocialMetrics', array('multiOptions' => $elements));
            $cbSocialMetrics->setLabel('Record Social Behavior?')
                ->setSeparator(' ');

            if($this->study instanceof Study)
            {
                $collection = new Study_SocialActivityTypeMapCollection();
                $collection->loadForStudy($this->study->id);
                $values = array();
                if($collection->count())
                {
                    foreach ($collection as $entry)
                    {
                        $values[] = $entry->social_activity_type_id;
                    }
                }                
                $cbSocialMetrics->setValue($values);
            }
            
        $subforms[3]->addElements(array($cbSocialMetrics));
        $subforms[3]->addDisplayGroup(
            array($cbSocialMetrics),
            'group-social', array('Legend' => 'Social')
        );

    }
}