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
        $values = $merged;
        //echo '<pre>';var_dump($values);exit(0);

        if($action == 'save')
        {
            $study->user_id = $user->id;
        }
        $study->study_type      = $values['radioProduct'];
        $study->name            = $values['txtStudyName'];
        $study->study_id        = $values['txtStudyId'];
        $study->size            = $values['txtSampleSize'];
        $study->size_minimum    = $values['txtMinThreshold'];
        $study->begin_date      = Data_FormatTools::displayDateToMysql($values['txtBegin']);
        $study->end_date        = Data_FormatTools::displayDateToMysql($values['txtEnd']);
        $study->click_track     = $values['radioIsSurvey'];
        $study->save();
    }
}

