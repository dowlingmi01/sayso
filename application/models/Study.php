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
        $values = $merged;

        //echo '<pre>';var_dump($_POST);exit(0);
        //echo '<pre>';var_dump($values);exit(0);

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
        // Surveys
        if(!empty($values['criteria']))
        {
            
        }
    }
}

