<?php

class Study extends Record
{
    protected $_tableName = 'study';

    /**
     * @var Study_CellCollection
     */
    protected $_cells;

    public function init () {
        $this->_cells = new Study_CellCollection();
        parent::init();
    }

    public function addCell (Study_Cell $cell) {
        $this->_cells->addItem($cell);
    }

    /**
     * @var Study_CellCollection
     */
    public function getCells () {
        return $this->_cells;
    }

    public function exportData() {
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

    public function exportProperties($parentObject = null) {
        $props = array(
            '_cells' => $this->_cells
        );
        return array_merge(parent::exportProperties($parentObject), $props);
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
        $values = $merged;

        //echo '<pre>';var_dump($_POST);exit(0);
        //echo '<pre>';var_dump($values);exit(0);
        /*echo '<pre>';
        foreach ($values['cell'] as $cell)
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

        // Drop existing cells...


        /**
         * @todo add check for containing both types of cells
         */

        // Save new cells
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
            //exit(0);
        }


    }
}

