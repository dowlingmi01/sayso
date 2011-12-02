<?php

class Study extends Record
{

    const STATUS_IN_DESIGN  = 0;
    const STATUS_LAUNCHED   = 10;
    const STATUS_LIVE       = 20;
    const STATUS_COMPLETE   = 30;

    protected $_tableName   = 'study';

    private static $statusArray    = array();

    /**
     * @var Study_CellCollection
     */
    protected $_cells;

    public function init ()
    {
        $this->_cells = new Study_CellCollection();
        parent::init();
    }

    public function addCell (Study_Cell $cell)
    {
        $this->_cells->addItem($cell);
    }

    /**
     * Get status properties hash
     *
     * @return array
     */
    public static function getStatusArray()
    {
        if(empty(self::$statusArray))
        {
            self::$statusArray = array
            (
                self::STATUS_IN_DESIGN    => array('label' => 'In-Design',  'icon-class' => 'button-study-status-indesign'),
                self::STATUS_LAUNCHED     => array('label' => 'Launched',   'icon-class' => 'button-study-status-launched'),
                self::STATUS_LIVE         => array('label' => 'Live',       'icon-class' => 'button-study-status-live'),
                self::STATUS_COMPLETE     => array('label' => 'Complete',   'icon-class' => 'button-study-status-complete'),
            );
        }
        return self::$statusArray;
    }

    /**
     * @throws Exception
     */
    public function checkLaunchValidity()
    {
        // main properties

        if(!$this->getId())
        {
            throw new Exception('Study is not loaded!');
        }
        if(!in_array($this->study_type, array(1,2,3)))
        {
            throw new Exception('Study product is not selected or wrong!');
        }
        if(!$this->user_id)
        {
            throw new Exception('Study owner user is not defined!');
        }        
        if(false === $this->size > 0)
        {
            throw new Exception('Study size must be > 0!');
        }
        if(false === $this->size_minimum > 0)
        {
            throw new Exception('Study min.threshhold must be > 0!');
        }
        if(!$this->begin_date || $this->begin_date == '0000-00-00 00:00:00')
        {
            throw new Exception('Study begin date is wrong!');
        }
        if(!$this->end_date || $this->end_date == '0000-00-00 00:00:00')
        {
            throw new Exception('Study end date is wrong!');
        }
        if(!$this->begin_date >= $this->end_date)
        {
            throw new Exception('Study begin date and/or study end date is wrong!');
        }

        //Cells - Must have both Control and Test

        $cells = new Study_CellCollection();
        $cells->loadForStudy($this->getId());
        if($cells->count() == 0)
        {
            throw new Exception('Cannot find sells in study, please create cells first!');
        }
        $hasControl = $hasTest = false;
        foreach ($cells as $cell)
        {
            if($cell->cell_type == 'control')
            {
                $hasControl = true;
            }
            if($cell->cell_type == 'test')
            {
                $hasTest = true;
            }
        }
        if($hasControl || $hasTest)
        {
            throw new Exception('Cannot find sells of both types in study, please create cells of both `Control` and `Test` types first!');
        }

        //ADjuster Campaign - at least one tag must be present

        if($this->study_type == 2)
        {
            $tags = new Study_TagCollection();
            $tags->loadForStudy($this->getId());
            if(false === $tags->count() > 0)
            {
                throw new Exception('ADjuster Campaign requires at least one tag to be created, please create some tags first!');
            }
        }

        //ADjuster Creative - at least one creative must be present

        if($this->study_type == 3)
        {
            $creatives = new Study_CreativeCollection();
            $creatives->loadForStudy($this->getId());
            if(false === $creatives->count() > 0)
            {
                throw new Exception('ADjuster Creative requires at least one creative to be created, please design some creatives first!');
            }
        }
    }

    /**
     * @param string $begin_date
     * @param string $end_date
     * @return bool
     */
    public static function hasStatusLive($begin_date, $end_date)
    {
        $now = new DateTime();
        return $now->format('Y-m-d H:i:s') > $begin_date && $now->format('Y-m-d H:i:s') < $end_date;
    }

    /**
     * @param string $end_date
     * @return bool
     */
    public static function hasStatusComplete($end_date)
    {
        $now = new DateTime();
        return $now->format('Y-m-d H:i:s') > $end_date;
    }
    
    /**
     * @var Study_CellCollection
     */
    public function getCells ()
    {
        return $this->_cells;
    }

    public function exportData()
    {
        $fields = array(
            'user_id',
            'name',
            'description',
            'size',
            'size_minimum',
            'begin_date',
            'end_date',
            'click_track'
        );
        return array_intersect_key($this->getData(), array_flip($fields));
    }

    public function exportProperties($parentObject = null)
    {
        $props = array(
            '_cells' => $this->_cells
        );
        return array_merge(parent::exportProperties($parentObject), $props);
    }

	/**
     * Get properties (used for serialization)
     *
     * @see Object::serialize
     * @return array
     */
    protected function _getProperties()
    {
        return array_merge(array(
        	'_cells' => $this->_cells
        ), parent::_getProperties());
    }

    /**
     * Restore properties from array (used with serialization)
     *
     * @see Object::unserialize
     * @param array $properties
     */
    protected function _restoreProperties (array $properties)
    {
        $this->_cells = $properties['_cells'];
        parent::_restoreProperties($properties);
    }

    /**
     * @param Study $study
     * @param AdminUser $user
     * @param array $values
     */
    public static function saveStudyFromValues(Study $study, AdminUser $user, array $values, $action = 'save')
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
        $study->study_type      = $values['radioProduct'];

        // Basic Tab
        $study->name            = $values['txtStudyName'];
        $study->study_id        = $values['txtStudyId'];
        $study->size            = $values['txtSampleSize'];
        $study->size_minimum    = $values['txtMinThreshold'];
        $study->begin_date      = Data_FormatTools::displayDateToMysql($values['txtBegin']);
        $study->end_date        = Data_FormatTools::displayDateToMysql($values['txtEnd']);

        // Metrics tab
        $study->click_track     = $values['radioOnline'];

        // Save before saving associations
        $study->save();

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
        // Search Engines
        if(isset($values['cbSearchEngines']) && !empty($values['cbSearchEngines']))
        {
            foreach ($values['cbSearchEngines'] as $engineId)
            {
                $map                        = new Study_SearchEnginesMap();
                $map->study_id              = $study->getId();
                $map->search_engines_id     = $engineId;
                $map->save();
            }
        }
        // Social Metrics
        if(isset($values['cbSocialMetrics']) && !empty($values['cbSocialMetrics']))
        {
            foreach ($values['cbSocialMetrics'] as $socialId)
            {
                $map                            = new Study_SocialActivityTypeMap();
                $map->study_id                  = $study->getId();
                $map->social_activity_type_id   = $socialId;
                $map->save();
            }
        }

        // Surveys NOT SUPPORTED YET!
        if(!empty($values['criteria']))
        {

        }

        // Quotas
        $quotaFilter = new Zend_Filter_Int();

        if(!empty($values['quotas']))
        {
            foreach ($values['quotas'] as $quotaData)
            {
                $quota = new Study_Quota();
                $quota->study_id = $study->getId();
                if(intval($quotaData['cell']))
                {
                    $quota->percentile_id = $quotaFilter->filter(intval($quotaData['cell']));
                }
                if(intval($quotaData['gender']))
                {
                    $quota->gender_id = $quotaFilter->filter(intval($quotaData['gender']));
                }
                if(intval($quotaData['age']))
                {
                    $quota->age_range_id = $quotaFilter->filter(intval($quotaData['age']));
                }
                if(intval($quotaData['eth']))
                {
                    $quota->ethnicity_id = $quotaFilter->filter(intval($quotaData['eth']));
                }
                $quota->save();
            }
        }

        // Cells

        // Validity check temporarily disabled - DO NOT DELETE THE COMMENTED BLOCK BELOW!
        /*
        if(empty($values['cell']))
        {
            throw new Exception('Cell data not available!');
        }

        // Check validity
        foreach ($values['cell'] as $cell)
        {
            if(!isset($cell['qualifiers']) || !is_array($cell['qualifiers']) || empty($cell['qualifiers']))
            {
                throw new Exception('Cell information passed is invalid!');
            }
        }
         */

        /**
         * @todo add check for containing both types of cells
         */

        // Save new cells
        if(!empty($values['cell']))
        {
            foreach ($values['cell'] as $cellData)
            {
                /**
                 * @todo verify data with Zend_Filter*
                 */
                $cell               = new Study_Cell();
                $cell->study_id     = $study->getId();
                $cell->description  = $cellData['description'];
                $cell->size         = $cellData['size'];
                $cell->cell_type    = $cellData['type'] == 1 ? 'control' : 'test';
                $cell->save();

                if(!empty($cellData['qualifiers']))
                {
                    foreach ($cellData['qualifiers'] as $qualifier)
                    {
                        $qa = &$cellData[$qualifier];
                        //echo '<pre>'; var_dump($qa);echo '</pre>';

                        switch($qa['qftype'])
                        {
                            case 'online-browsing':

                                $browseQualifier            = new Study_CellBrowsingQualifier();
                                $browseQualifier->cell_id   = $cell->getId();
                                if ($qa['action'] === 'Exclude')
                                {
                                    $browseQualifier->exclude = 1;
                                }
                                $browseQualifier->site          = $qa['url'];
                                $browseQualifier->timeframe_id  = $qa['timeframe'];
                                $browseQualifier->save();

                                break;
                            case 'search-action':

                                $searchQualifier            = new Study_CellSearchQualifier();
                                $searchQualifier->cell_id   = $cell->getId();
                                if ($qa['action'] === 'Exclude')
                                {
                                    $searchQualifier->exclude = 1;
                                }
                                $searchQualifier->term          = $qa['qs'];
                                $searchQualifier->timeframe_id  = $qa['timeframe'];
                                $searchQualifier->save();

                                foreach ($qa['engines'] as $engine)
                                {
                                    //echo '<pre>'; var_dump($engine);echo '</pre>';
                                    $map = new Study_CellSearchQualifierMap();
                                    $map->cell_qualifier_search_id  = $searchQualifier->getId();
                                    $map->search_engines_id         = $engine;
                                    $map->save();
                                }

                                break;
                        }
                    }
                }
            }
        }

        // Adjuster Campaign

        // Validity check temporarily disabled - DO NOT DELETE THE COMMENTED BLOCK BELOW!
        /*
        if($study->study_type == 2 && empty($values['tag']))
        {
            throw new Exception('At leat one tag must be created forAdjuster Campaign!');
        }
         */

        if(!empty($values['tag']))
        {
            foreach ($values['tag'] as $tagData)
            {
                $tag = new Study_Tag();
                $tag->name          = $tagData['label'];
                $tag->tag           = $tagData['jq'];
                $tag->target_url    = $tagData['target'];
                $tag->study_id      = $study->getId();
                /**
                 * @todo why is this here?
                 */
                $tag->user_id       = $user->id;
                $tag->save();

                if(!empty($tagData['domain']))
                {
                    foreach($tagData['domain'] as $domainData)
                    {
                        $domain = new Study_Domain();

                        // when numeric, check if exists and re-associate
                        if(is_numeric($domainData))
                        {
                            $domain->loadData($domainData);
                            if(false === $domain->getId() > 0)
                            {
                                $domain->domain     = $domainData;
                                $domain->user_id    = $user->id;
                                $domain->save();
                            }
                        }
                        // when string, check it, create new if not existing
                        elseif(is_string($domainData))
                        {
                            $domain->getByNameAndUserId($domainData, $user->id);
                            if(false === $domain->getId() > 0)
                            {
                                $domain->domain     = $domainData;
                                $domain->user_id    = $user->id;
                                $domain->save();
                            }
                        }
                        // associate
                        $dm                 = new Study_TagDomainMap();
                        $dm->tag_id         = $tag->getId();
                        $dm->domain_id      = $domain->getId();
                        $dm->save();
                    }
                }
            }
        }

        // Adjuster Creative

        // Validity check temporarily disabled - DO NOT DELETE THE COMMENTED BLOCK BELOW!
        /**

        if($study->study_type == 3 && empty($values['creative']))
        {
            throw new Exception('At leat one creative must be created forAdjuster Creative!');
        }

        // Check validity
        foreach ($values['creative'] as $creative)
        {
            if(!isset($creative['avails']) || !is_array($creative['avails']) || empty($creative['avails']))
            {
                throw new Exception('Avails information passed is invalid!');
            }
        }
         */

        // Save new cells
        if(!empty($values['creative']))
        {

            foreach ($values['creative'] as $creativeData)
            {
                /**
                 * @todo verify data with Zend_Filter*
                 */

                // create ..khm .. a creative
                $creative               = new Study_Creative();
                $creative->user_id      = $user->getId();
                $creative->mime_type_id = $creativeData['mimetype'];
                $creative->name         = $creativeData['name'];
                $creative->url          = $creativeData['url'];
                $creative->target_url   = $creativeData['segment'];

                $creative->save();

                // associate to study
                $creativeAssoc              = new Study_CreativeMap();
                $creativeAssoc->study_id    = $study->getId();
                $creativeAssoc->creative_id = $creative->getId();

                $creativeAssoc->save();

                // add avails
                if(!empty($creativeData['avails']))
                {
                    foreach ($creativeData['avails'] as $availIndex)
                    {
                        $availData = &$creativeData[$availIndex];

                        $avail                          = new Study_Avail();
                        $avail->creative_id             = $creative->getId();
                        $avail->label                   = $availData['label'];
                        $avail->selector                = $availData['jq'];
                        $avail->save();

                        if(!empty($availData['domain']))
                        {
                            foreach ($availData['domain'] as $domainData)
                            {
                                $domain = new Study_Domain();

                                // when numeric, check if exists and re-associate
                                if(is_numeric($domainData))
                                {
                                    $domain->loadData($domainData);
                                    if(false === $domain->getId() > 0)
                                    {
                                        $domain->domain     = $domainData;
                                        $domain->user_id    = $user->id;
                                        $domain->save();
                                    }
                                }
                                // when string, check it, create new if not existing
                                elseif(is_string($domainData))
                                {
                                    $domain->getByNameAndUserId($domainData, $user->id);
                                    if(false === $domain->getId() > 0)
                                    {
                                        $domain->domain     = $domainData;
                                        $domain->user_id    = $user->id;
                                        $domain->save();
                                    }
                                }

                                // associate
                                $dm                 = new Study_AvailDomainMap();
                                $dm->study_avail_id = $avail->getId();
                                $dm->domain_id      = $domain->getId();
                                $dm->save();
                            }
                        }
                    }
                }
            }
        }
    }
}

