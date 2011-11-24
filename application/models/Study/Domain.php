<?php


class Study_Domain extends Record
{
    protected $_tableName = 'study_domain';
    
    protected $_uniqueFields = array('domain' => '');
    
    public function exportData() {
        $fields = array(
            'user_id',
            'domain'
        );
        return array_intersect_key($this->getData(), array_flip($fields));
    }

    public function getByNameAndUserId($name, $userId)
    {        
        $this->loadDataByUniqueFields(array('domain' => $name, 'user_id' => $userId));
    }

}

