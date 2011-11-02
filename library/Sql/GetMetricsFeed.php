<?php
/**
 * Get metrics stats
 *
 * @author davidbjames
 *
 */
class Sql_GetMetricsFeed extends Sql_Abstract
{
    /**
     * Is it the very start of polling?
     *
     * @var bool
     */
    private $isFirstRun     = true;
    
    /**
     * Number of records to get during the first run
     * 
     * @var int
     */
    private $limitFirstRun  = 100;

    /**
     * Limit getting a number of records for all subsequent runs
     * Set it to a quite big number of rows,
     * this is needed mostly to prever some attacks
     *
     * @var int
     */
    private $limitLiveFeed  = 1000;

    public function init()
    {
        $this->_collection = new Metrics_FeedCollection();
    }

    public function setLastIds(array $criteria)
    {
        $this->isFirstRun               = false;
        $this->lastSearchId             = isset($criteria['lastSearchId']) ? intval($criteria['lastSearchId']) : 0;
        $this->lastPageViewId           = isset($criteria['lastPageViewId']) ? intval($criteria['lastPageViewId']) : 0;
        $this->lastSocialActivityId     = isset($criteria['lastSocialActivityId']) ? intval($criteria['lastSocialActivityId']) : 0;
    }


    public function build(&$data, $builder = null)
    {
        $feed = new Metrics_Feed();
        $feed->build($data);
        return $feed;
    }

    protected function _getSqlFilePath()
    {
        if($this->isFirstRun)
        {
            return dirname(__FILE__) . '/' . str_replace('Sql_', '', get_class($this)) . 'FirstRun.sql';
        }
        return dirname(__FILE__) . '/' . str_replace('Sql_', '', get_class($this)) . 'Live.sql';
    }

}
