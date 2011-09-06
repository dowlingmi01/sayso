<?php


class Study_CellAssignment extends Record
{
    protected $_tableName = 'study_cell_assignment';
    
    protected $_uniqueFields = array('user_id' => 0, 'study_cell_id' => 0);
    
    public static function getActiveCellIdByUser ($userId) {
        $results = Db_Pdo::fetch('SELECT * FROM study_cell_assignment WHERE user_id = ? AND active', $userId);
        if ($results) {
            return $results['study_cell_id'];
        } else {
            return 0;
        }
    }
}

