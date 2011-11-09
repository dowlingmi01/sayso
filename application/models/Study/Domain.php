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
}

