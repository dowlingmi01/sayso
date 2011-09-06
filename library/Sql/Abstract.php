<?php

/**
 * Abstract SQL class for the Addictionary API
 * - overrides getting the path mapping for the SQL file which can 
 *   only be obtained by a file in this directory via __FILE__
 * - allows setting pagination for any query/builder based on this class
 * 
 * @author davidbjames
 *
 */
abstract class Sql_Abstract extends SqlAbstract {
    
    /**
     * Set page offset / page size defaults
     */
    public function __construct() {
        $this->page_offset = 0;
        $this->page_size = 100;
        parent::__construct();
    }
    
    /**
     * Set pagination on any query that supports it
     * 
     * @param integer $pageOffset
     * @param integer $pageSize
     */
    public function setPagination ($pageOffset, $pageSize) {
        $this->page_offset = $pageOffset;
        $this->page_size = $pageSize;
    }
    
    /**
     * Get the SQL file path which is relative to this class
     * - i.e. Sql_GetCommentsAggregate --> /path/to/this/dir/GetCommentsAggregate.sql
     */
    protected function _getSqlFilePath() {
        return dirname(__FILE__) . '/' . str_replace('Sql_', '', get_class($this)) . '.sql';
    }
}

