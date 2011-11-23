<?php
/**
 * Create / Edit a Study
 * Tabbed form
 *
 * @author alecksmart
 * @todo refactor with using partials and better practices
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
            $hiddenStudyId =
                $this->createElement('hidden', 'hiddenStudyId')
                    ->setValue($this->study->id)
                    ->setDecorators(array('ViewHelper', 'Errors'));
            $this->addElement($hiddenStudyId);

            $this->setAction($this->action);
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


        /**
         * Survey tab
         */

        $freeLabel0 = new Form_Markup_Element_AnyHtml('freeLabel0');
            $freeLabel0->setValue('<p style="color:red;font-weight:bold;">SURVEYS ARE IN DEMO MODE, NO SAVING TO DB AVAILABLE YET</p>')
                ->removeDecorator('Label')
                ->addDecorators(array(
                    'ViewHelper',
                    array('HtmlTag', array('tag' => 'div', 'style'=>'margin:10px 4px 20px 4px;'))
        ));

        $radioSurveyCreate = new Zend_Form_Element_Radio('radioSurveyCreate');
            $radioSurveyCreate
                ->setMultiOptions(array(0=>'No Survey',1=>'Standard Survey', 2 => 'Custom Survey'))
                ->setRequired(false)
                ->setSeparator(' ')
                ->setValue(0)
                ->setLabel('Standard Ad Effectiveness Survey or Customized?');

        $txtPasteIframeUrl =
            $this->createElement('text', 'txtPasteIframeUrl')
                ->setLabel('Paste iFrame URL Here*:')
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    array('HtmlTag', array('tag' => 'div', 'style'=>'display:none'))
                ));

        $subforms[4]->addElements(array(
            $freeLabel0,
            $radioSurveyCreate,
            $txtPasteIframeUrl,
        ));
        $subforms[4]->addDisplayGroup(
            array(
                $freeLabel0,
                $radioSurveyCreate,
                $txtPasteIframeUrl,
            ),
            'group-survey-type', array('Legend' => 'Survey Type')
        );


        $freeLabel1 = new Form_Markup_Element_AnyHtml('freeLabel1');
            $freeLabel1->setValue('<p>When should the survey be delivered?</p>')
                ->removeDecorator('Label')
                ->addDecorators(array(
                    'ViewHelper',
                    array('HtmlTag', array('tag' => 'div', 'style'=>'margin:10px 4px 20px 4px;'))
        ));

        $txtDeliverSurvey =
            $this->createElement('text', 'txtDeliverSurvey')
                ->setLabel('Deliver survey to those that visit:');

        $selectSurveySite =
            $this->createElement('select', 'selectSurveySite')
                ->setMultiOptions(array(
                    '' =>'-- choose --',
                    'Facebook.com'=>'Facebook.com',
                    'CNN.com' => 'CNN.com',
                    'ESPN.com'=> 'ESPN.com'
                    )
                )
                ->setLabel('or')
                ->setDecorators($alignLeft);

        $selectSurveyTimeframe =
            $this->createElement('select', 'selectSurveyTimeframe')
                ->setMultiOptions(array(
                        '1' =>'1 Hour',
                        '2' =>'1 Day',
                        '3' =>'1 Week',
                        '4' =>'1 Month',
                    )
                )
                ->setLabel('within such time of seeing targeted ad(s):')
                ->setDecorators($alignLeft);

        $btnAddCriteria =
            $this->createElement('button', 'btnAddCriteria')
                ->setLabel('Add Criteria')
                ->setAttrib('class', 'add-fieldset-data styled-button');

        $subforms[4]->addElements(array(
            $freeLabel1,
            $txtDeliverSurvey,
            $selectSurveySite,
            $selectSurveyTimeframe,
            $btnAddCriteria,
        ));
        $subforms[4]->addDisplayGroup(
            array(
                $freeLabel1,
                $txtDeliverSurvey,
                $selectSurveySite,
                $selectSurveyTimeframe,
                $btnAddCriteria,
            ),
            'group-survey-delivery', array('Legend' => 'Create New Criteria', 'style' => 'display:none')
        );

        $htmlFromStudy = '';

        $freeLabel2 = new Form_Markup_Element_AnyHtml('freeLabel2');
            $freeLabel2->setValue('<table id="existing-criteria" cellspacing="0" cellpadding="0" align="center"><tbody>'
                    .'<tr><th>Type</th><th>Iframe</th><th>Site</th><th>Timeframe</th><th> </th></tr>'
                    . $htmlFromStudy
                    .'</tbody></table>')
                ->removeDecorator('Label')
                ->addDecorators(array(
                    'ViewHelper',
                    array('HtmlTag', array('tag' => 'div', 'class'=>'admin-table'))
        ));

        $subforms[4]->addElements(array(
            $freeLabel2,
        ));
        $subforms[4]->addDisplayGroup(
            array(
                $freeLabel2,
            ),
            'group-survey-criteria-added', array('Legend' => 'Existing Criteria', 'style' => '')
        );

        /**
         * Quotas
         */
        $collection = new Lookup_Collection_Gender();
        $collection->lookup();
        $multiOptions = array('' =>'-- choose --');
        foreach($collection as $entry)
        {
            $multiOptions[$entry->id] = $entry->short_name;
        }
        $selectQuotaGender =
            $this->createElement('select', 'selectQuotaGender')
                ->setMultiOptions($multiOptions)
                ->setLabel('M/F')
                ->setDecorators($alignLeft);

        $collection = new Lookup_Collection_AgeRange();
        $collection->lookup();
        $multiOptions = array('' =>'-- choose --');
        foreach($collection as $entry)
        {
            $multiOptions[$entry->id] = $entry->getTitle();
        }
        $selectQuotaAge =
            $this->createElement('select', 'selectQuotaAge')
                ->setMultiOptions($multiOptions)
                ->setLabel('Age')
                ->setDecorators($alignRight);

        $collection = new Lookup_Collection_EthnicBackground();
        $collection->lookup();
        $multiOptions = array('' =>'-- choose --');
        foreach($collection as $entry)
        {
            $multiOptions[$entry->id] = $entry->label;
        }
        $selectQuotaEthnicity =
            $this->createElement('select', 'selectQuotaEthnicity')
                ->setMultiOptions($multiOptions)
                ->setLabel('Ethnicity')
                ->setDecorators($alignLeft);

        $collection = new Lookup_Collection_QuotaPercentile();
        $collection->lookup();
        $multiOptions = array('' =>'-- choose --');
        foreach($collection as $entry)
        {
            $multiOptions[$entry->id] = $entry->getTitle();
        }
        $selectQuotaCellPerc =
            $this->createElement('select', 'selectQuotaCellPerc')
                ->setMultiOptions($multiOptions)
                ->setLabel('Cell %')
                ->setDecorators($alignRight);

        $btnAddQuota =
            $this->createElement('button', 'btnAddQuota')
                ->setLabel('Add This Quota')
                ->setAttrib('class', 'add-fieldset-data styled-button');

        $subforms[5]->addElements(array(
            $selectQuotaGender,
            $selectQuotaAge,
            $selectQuotaEthnicity,
            $selectQuotaCellPerc,
            $btnAddQuota,
        ));
        $subforms[5]->addDisplayGroup(
            array(
                $selectQuotaGender,
                $selectQuotaAge,
                $selectQuotaEthnicity,
                $selectQuotaCellPerc,
                $btnAddQuota,
            ),
            'group-survey-quotas', array('Legend' => 'Who will qualify for the study?')
        );

        // build quotas table from database data
        $htmlFromStudy = '';
        if($this->study instanceof Study)
        {
            $quotas = new Study_QuotaCollection();
            $quotas->loadForStudy($this->study->getId());
            if($quotas->count())
            {
                $cnt = 0; $uniqKey = substr(uniqid(md5(rand(0,1000))), 0, 8);
                foreach ($quotas as $quota)
                {
                    $value = '-'; $numVal = 0;
                    if(!is_null($quota->gender_id))
                    {
                        $p = new Lookup_Gender();
                        $p->loadData($quota->gender_id);
                        $value = $p->short_name;
                        $numVal = $p->id;
                    }
                    $tds = sprintf('<td class="align-center">%s</td>', $value);
                    $tds .= sprintf('<input type="hidden" name="quotas[%s][gender]" value="%s" class="hidden-quota-%s data-gender" />',
                        $uniqKey, $numVal, $uniqKey);


                    $value = '-';$numVal = 0;
                    if(!is_null($quota->age_range_id))
                    {
                        $p = new Lookup_AgeRange();
                        $p->loadData($quota->age_range_id);
                        $value = $p->getTitle();
                        $numVal = $p->id;
                    }
                    $tds .= sprintf('<td class="align-center">%s</td>', $value);
                    $tds .= sprintf('<input type="hidden" name="quotas[%s][age]" value="%s" class="hidden-quota-%s data-age" />',
                        $uniqKey, $numVal, $uniqKey);

                    $value = '-';$numVal = 0;
                    if(!is_null($quota->ethnicity_id))
                    {
                        $p = new Lookup_EthnicBackground();
                        $p->loadData($quota->ethnicity_id);
                        $value = $p->label;
                        $numVal = $p->id;
                    }
                    $tds .= sprintf('<td class="align-center">%s</td>', $value);
                    $tds .= sprintf('<input type="hidden" name="quotas[%s][eth]" value="%s" class="hidden-quota-%s data-eth" />',
                        $uniqKey, $numVal, $uniqKey);

                    $value = '-';$numVal = 0;
                    if(!is_null($quota->percentile_id))
                    {
                        $p = new Lookup_QuotaPercentile();
                        $p->loadData($quota->percentile_id);
                        $value = $p->getTitle();
                        $numVal = $p->id;
                    }
                    $tds .= sprintf('<td class="align-center data-cell-percentile">%s</td>', $value);
                    $tds .= sprintf('<input type="hidden" name="quotas[%s][cell]" value="%s" class="hidden-quota-%s data-cell" />',
                        $uniqKey, $numVal, $uniqKey);

                    $tds .= sprintf('<td style="width:20px"><a title="Delete" class="button-delete delete-quota" '
                                            . 'href="javascript:void(null)" rel="%s"></a></td>', $uniqKey);

                    $class = ++$cnt & 1 ? ' class="alt"' : '';
                    $htmlFromStudy .= '<tr'.$class.' id="row-quota-'.$uniqKey.'">'.$tds.'</tr>';
                }
            }
        }

        $freeLabel3 = new Form_Markup_Element_AnyHtml('freeLabel3');
            $freeLabel3->setValue('<table id="existing-quotas" cellspacing="0" cellpadding="0" align="center"><tbody>'
                    .'<tr><th>M/F</th><th>Age</th><th>Ethnicity</th><th>Cell %</th><th> </th></tr>'
                    . $htmlFromStudy
                    .'</tbody></table>')
                ->removeDecorator('Label')
                ->addDecorators(array(
                    'ViewHelper',
                    array('HtmlTag', array('tag' => 'div', 'class'=>'admin-table'))
        ));

        $subforms[5]->addElements(array(
            $freeLabel3,
        ));
        $subforms[5]->addDisplayGroup(
            array(
                $freeLabel3,
            ),
            'group-survey-criteria-added', array('Legend' => 'Existing Quotas', 'style' => '')
        );

        /**
         * Build Cells
         */

        // common

        $freeLabel4 = new Form_Markup_Element_AnyHtml('freeLabel4');
            $freeLabel4->setValue('<p>General information about the study cell</p>')
                ->removeDecorator('Label')
                ->addDecorators(array(
                    'ViewHelper',
                    array('HtmlTag', array('tag' => 'div', 'class'=>'form-label')),
        ));


        $txtCellDescription =
            $this->createElement('text', 'txtCellDescription')
                ->setLabel('Cell Description')
                ->addDecorators(array(

                ));

        $txtCellSize =
            $this->createElement('text', 'txtCellSize')
                ->setLabel('Cell Size')
                ->setDecorators($alignLeft);

        $radioCellType = new Zend_Form_Element_Radio('radioCellType');
            $radioCellType
                ->setMultiOptions(array(1=>'Control', 2 => 'Test'))
                ->setSeparator(' ')
                ->setValue(1)
                ->setLabel('Type of Cell')
                ->setDecorators($alignRight);

        $subforms[6]->addElements(array(
            $freeLabel4,
            $txtCellDescription,
            $txtCellSize,
            $radioCellType,
        ));
        $subforms[6]->addDisplayGroup(
            array(
                $txtCellDescription,
                $txtCellSize,
                $radioCellType,
            ),
            'group-survey-cell-info', array('Legend' => 'Cell Information', 'style' => '')
        );

        // Online Browsing

        $freeLabel5 = new Form_Markup_Element_AnyHtml('freeLabel5');
            $freeLabel5->setValue('<p>What behavioral qualifiers apply?</p>')
                ->removeDecorator('Label')
                ->addDecorators(array(
                    'ViewHelper',
                    array('HtmlTag', array('tag' => 'div', 'class'=>'form-label'))
        ));

        $selectOnlineBrowsing =
            $this->createElement('select', 'selectOnlineBrowsing')
                ->setMultiOptions(
                    array(
                        '' => 'Include/Exclude',
                        'Include' => 'Include',
                        'Exclude' => 'Exclude',
                    )
                )
                ->setLabel(' ')
                ->addDecorators(array(
                    $alignLeft,
                ));

        $txtWhoVisited =
            $this->createElement('text', 'txtWhoVisited')
                ->setLabel('those who visited')
                ->addDecorators(array(
                    $alignLeft,
                ));

        $collectionTimeframe = new Lookup_Collection_TimeFrame();
        $collectionTimeframe->lookup();
        $multiOptionsTimeFrame = array('' =>'-- choose --');
        foreach($collectionTimeframe as $entry)
        {
            $multiOptionsTimeFrame[$entry->id] = $entry->label;
        }
        $selectTimeframe =
            $this->createElement('select', 'selectTimeframe')
                ->setMultiOptions($multiOptionsTimeFrame)
                ->setLabel('in the last');

        $btnAddQualifierOnlineBrowsing =
            $this->createElement('button', 'btnAddQualifierOnlineBrowsing')
                ->setLabel('Add Qualifier')
                ->setAttrib('class', 'add-fieldset-data styled-button');

        $subforms[6]->addElements(array(
            $freeLabel5,
            $selectOnlineBrowsing,
            $txtWhoVisited,
            $selectTimeframe,
            $btnAddQualifierOnlineBrowsing,
        ));
        $subforms[6]->addDisplayGroup(
            array(
                $selectOnlineBrowsing,
                $txtWhoVisited,
                $selectTimeframe,
                $btnAddQualifierOnlineBrowsing,
            ),
            'group-survey-cell-qualifier', array('Legend' => 'Online Browsing', 'style' => '')
        );

        $htmlFromStudy = '';

        $freeLabel7 = new Form_Markup_Element_AnyHtml('freeLabel7');
            $freeLabel7->setValue('<table id="cell-qf-online" cellspacing="0" cellpadding="0" align="center"><tbody>'
                    .'<tr><th>Action</th><th>Url</th><th>Timeframe</th><th> </th></tr>'
                    . $htmlFromStudy
                    .'</tbody></table>')
                ->removeDecorator('Label')
                ->addDecorators(array(
                    'ViewHelper',
                    array('HtmlTag', array('tag' => 'div', 'class'=>'admin-table'))
        ));


        $subforms[6]->addElements(array(
            $freeLabel7,
        ));
        $subforms[6]->addDisplayGroup(
            array(
                $freeLabel7,
            ),
            'group-survey-cell-qualifier-data', array('Legend' => 'Qualifiers in Cell', 'style' => '')
        );

        // separator

        $freeLabel8 = new Form_Markup_Element_AnyHtml('freeLabel8');
            $freeLabel8->setValue(' ')
                ->removeDecorator('Label')
                ->addDecorators(array(
                    'ViewHelper',
                    array('HtmlTag', array('tag' => 'div', 'class'=>'clear'))
        ));

        $subforms[6]->addElements(array(
            $freeLabel8,
        ));

        // Search Actions

        $selectSearchActions =
            $this->createElement('select', 'selectSearchActions')
                ->setMultiOptions(
                    array(
                        '' => 'Include/Exclude',
                        'Include' => 'Include',
                        'Exclude' => 'Exclude',
                    )
                )
                ->setLabel(' ')
                ->addDecorators(array(
                    $alignLeft,
                ));

        $txtWhoSearchedFor =
            $this->createElement('text', 'txtWhoSearchedFor')
                ->setLabel('those who searched for')
                ->addDecorators(array(
                    $alignLeft,
                ));

        $selectTimeframeSearch =
            $this->createElement('select', 'selectTimeframeSearch')
                ->setMultiOptions($multiOptionsTimeFrame)
                ->setLabel('in the last');

        $collectionSearchEngines = new Lookup_Collection_SearchEngine();
        $collectionSearchEngines->lookup();
        $multiOptionsSearchEngines = array();
        foreach($collectionSearchEngines as $entry)
        {
            $multiOptionsSearchEngines[$entry->id] = $entry->label;
        }
        $cbSearchOnEngines = new Zend_Form_Element_MultiCheckbox('cbSearchOnEngines');
                $cbSearchOnEngines->setMultiOptions($multiOptionsSearchEngines)
                ->setLabel('on')
                ->setSeparator(' ')
                ->setAttrib('class', 'cb-search-on-engines');

        $btnAddQualifierSearchEngines =
            $this->createElement('button', 'btnAddQualifierSearchEngines')
                ->setLabel('Add Qualifier')
                ->setAttrib('class', 'add-fieldset-data styled-button');

        $subforms[6]->addElements(array(
            $selectSearchActions,
            $txtWhoSearchedFor,
            $cbSearchOnEngines,
            $selectTimeframeSearch,
            $btnAddQualifierSearchEngines,
        ));
        $subforms[6]->addDisplayGroup(
            array(
                $selectSearchActions,
                $txtWhoSearchedFor,
                $cbSearchOnEngines,
                $selectTimeframeSearch,
                $btnAddQualifierSearchEngines,
            ),
            'group-survey-cell-search-actions', array('Legend' => 'Search Actions', 'style' => '')
        );

        $htmlFromStudy = '';

        $freeLabel10 = new Form_Markup_Element_AnyHtml('freeLabel10');
            $freeLabel10->setValue('<table id="cell-qf-search" cellspacing="0" cellpadding="0" align="center"><tbody>'
                    .'<tr><th>Action</th><th>Query</th><th>Timeframe</th><th>Engines</th><th> </th></tr>'
                    . $htmlFromStudy
                    .'</tbody></table>')
                ->removeDecorator('Label')
                ->addDecorators(array(
                    'ViewHelper',
                    array('HtmlTag', array('tag' => 'div', 'class'=>'admin-table'))
        ));


        $subforms[6]->addElements(array(
            $freeLabel10,
        ));
        $subforms[6]->addDisplayGroup(
            array(
                $freeLabel10,
            ),
            'group-survey-cell-qualifier-data-2', array('Legend' => 'Qualifiers in Cell', 'style' => '')
        );


        // separator and button

        $freeLabel9 = new Form_Markup_Element_AnyHtml('freeLabel9');
            $freeLabel9->setValue(' ')
                ->removeDecorator('Label')
                ->addDecorators(array(
                    'ViewHelper',
                    array('HtmlTag', array('tag' => 'div', 'class'=>'clear'))
        ));

        $subforms[6]->addElements(array(
            $freeLabel9,
        ));


        $btnBuildCell =
            $this->createElement('button', 'btnBuildCell')
                ->setLabel('Build New Cell')
                ->setAttrib('class', 'add-fieldset-data styled-button');


        // existing cells

        // build quotas table from database data
        $htmlFromStudy = '';
        if($this->study instanceof Study)
        {

            $cells = new Study_CellCollection();
            $cells->loadForStudy($this->study->getId());
            if(!empty($cells))
            {
                $cnt =0;
                foreach ($cells as $cell)
                {
                    // cells
                    $cellKey = substr(uniqid(md5(rand(0,1000))), 0, 8);
                    $class = ++$cnt & 1 ? ' class="alt"' : '';

                    $tds = '';
                    $tds .= sprintf('<td class="align-center">%s</td>', $cell->description);
                    $tds .= sprintf('<td class="align-center">%s</td>', $cell->size);
                    $tds .= sprintf('<td class="align-center">%s</td>', ($cell->cell_type == 'control' ? 'Control' : 'Test'));
                    $tds .= sprintf('<td style="width:20px"><a title="Delete" class="button-delete delete-cell" '
                                            . 'href="javascript:void(null)" rel="%s"></a></td>', $cellKey);
                    // row
                    $htmlFromStudy .= '<tr'.$class.' id="cell-row-'.$cellKey.'">'.$tds.'</tr>';

                    // common meta
                    $htmlFromStudy .= sprintf('<input type="hidden" name="cell[%s][description]" class="cell-%s" value="%s" />',
                            $cellKey, $cellKey, $cell->description);
                    $htmlFromStudy .= sprintf('<input type="hidden" name="cell[%s][size]" class="cell-%s" value="%s" />',
                            $cellKey, $cellKey, $cell->size);
                    $htmlFromStudy .= sprintf('<input type="hidden" name="cell[%s][type]" class="cell-%s" value="%s" />',
                            $cellKey, $cellKey, ($cell->cell_type == 'control' ? 1 : 2));

                    // browser qualifiers
                    $qBrowsing = new Study_CellBrowsingQualifierCollection();
                    $qBrowsing->loadForCell($cell->getId());                    
                    foreach($qBrowsing as $qualifier)
                    {
                        $rowKey = substr(uniqid(md5(rand(0,1000))), 0, 8);
                        // common link
                        $htmlFromStudy .= sprintf('<input type="hidden" name="cell[%s][qualifiers][]" class="cell-%s" value="%s" />',
                            $cellKey, $cellKey, $rowKey);
                        // data
                        $htmlFromStudy .= sprintf('<input type="hidden" name="cell[%s][%s][qftype]" '.
                            'value="online-browsing" class="cell-%s cell-row-%s cell-data-ob-qftype" />',
                            $cellKey, $rowKey, $cellKey, $rowKey);
                        $htmlFromStudy .= sprintf('<input type="hidden" name="cell[%s][%s][action]" '.
                            'value="%s" class="cell-%s cell-row-%s cell-data-ob-action" />',
                            $cellKey, $rowKey, (!is_null($qualifier->exclude) ? 'Exclude' : 'Include'), $cellKey, $rowKey);
                        $htmlFromStudy .= sprintf('<input type="hidden" name="cell[%s][%s][url]" '.
                            'value="%s" class="cell-%s cell-row-%s cell-data-ob-url" />',
                            $cellKey, $rowKey, $qualifier->site, $cellKey, $rowKey);                        
                        $htmlFromStudy .= sprintf('<input type="hidden" name="cell[%s][%s][timeframe]" '.
                            'value="%s" class="cell-%s cell-row-%s cell-data-ob-timeframe" />',
                            $cellKey, $rowKey, $qualifier->timeframe_id, $cellKey, $rowKey);
                    }

                    // search qualifiers
                    $sBrowsing = new Study_CellSearchQualifierCollection();
                    $sBrowsing->loadForCell($cell->getId());
                    foreach($sBrowsing as $qualifier)
                    {
                        $rowKey = substr(uniqid(md5(rand(0,1000))), 0, 8);
                        // common link
                        $htmlFromStudy .= sprintf('<input type="hidden" name="cell[%s][qualifiers][]" class="cell-%s" value="%s" />',
                            $cellKey, $cellKey, $rowKey);
                        // data
                        $htmlFromStudy .= sprintf('<input type="hidden" name="cell[%s][%s][qftype]" '.
                            'value="search-action" class="cell-%s cell-row-%s cell-data-se-qftype" />',
                            $cellKey, $rowKey, $cellKey, $rowKey);
                        $htmlFromStudy .= sprintf('<input type="hidden" name="cell[%s][%s][action]" '.
                            'value="%s" class="cell-%s cell-row-%s cell-data-se-action" />',
                            $cellKey, $rowKey, (!is_null($qualifier->exclude) ? 'Exclude' : 'Include'), $cellKey, $rowKey);
                        $htmlFromStudy .= sprintf('<input type="hidden" name="cell[%s][%s][qs]" '.
                            'value="%s" class="cell-%s cell-row-%s cell-data-se-qs" />',
                            $cellKey, $rowKey, $qualifier->term, $cellKey, $rowKey);
                        $htmlFromStudy .= sprintf('<input type="hidden" name="cell[%s][%s][timeframe]" '.
                            'value="%s" class="cell-%s cell-row-%s cell-data-se-timeframe" />',
                            $cellKey, $rowKey, $qualifier->timeframe_id, $cellKey, $rowKey);
                        // engines
                        $engines = new Study_CellSearchQualifierMapCollection();
                        $engines->loadForQualifier($qualifier->getId());
                        foreach($engines as $engine)
                        {                            
                            $htmlFromStudy .= sprintf('<input type="hidden" name="cell[%s][%s][engines][]" '.
                                'value="%s" class="cell-%s cell-row-%s cell-data-se-engines" />',
                                $cellKey, $rowKey, $engine->search_engines_id, $cellKey, $rowKey);
                        }
                    }
                }
            }
        }

        $freeLabel6 = new Form_Markup_Element_AnyHtml('freeLabel6');
            $freeLabel6->setValue('<table id="existing-cells" cellspacing="0" cellpadding="0" align="center"><tbody>'
                    .'<tr><th>Description</th><th>Size</th><th>Type</th><th> </th></tr>'
                    . $htmlFromStudy
                    .'</tbody></table>')
                ->removeDecorator('Label')
                ->addDecorators(array(
                    'ViewHelper',
                    array('HtmlTag', array('tag' => 'div', 'class'=>'admin-table'))
        ));

        $subforms[6]->addElements(array(
            $btnBuildCell,
            $freeLabel6,
        ));
        $subforms[6]->addDisplayGroup(
            array(
                $freeLabel6,
            ),
            'group-survey-cell-table', array('Legend' => 'Existing Cells', 'style' => '')
        );
    }
}