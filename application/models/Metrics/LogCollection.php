<?php
/**
 * @author alecksmart
 */
class Metrics_LogCollection
{
    /**
     * Number of records to get during the first run
     *
     * @var int
     */
    private $limitFirstRun  = 40;

    /**
     * Limit getting a number of records for all subsequent runs
     *
     * @var int
     */
    private $limitLiveFeed  = 1000;

    private $rowId          = 0;

    private $isFirstCall    = true;

    private $pollMetrics    = false;

    private $pollPageView   = false;

    private $pollSocial     = false;

    private $pollTags       = false;

    private $pollCreatives  = false;

    private $sql            = '';

    private $sqlParams      = array();

    private $onlyUser       = 0 ;

    /**
     * Whether to check for latest hits or previous hits
     * @var string up|down
     */
    private $direction      = 'up' ;

    /**
     * Set types to poll for
     *
     * @param array $types
     */
    public function setTypes(array $types)
    {
        if(isset($types['social']) && $types['social'] == 1)
        {
            $this->pollSocial = true;
        }
        if(isset($types['pageView']) && $types['pageView'] == 1)
        {
            $this->pollPageView = true;
        }
        if(isset($types['metrics']) && $types['metrics'] == 1)
        {
            $this->pollMetrics = true;
        }
        if(isset($types['tags']) && $types['tags'] == 1)
        {
            $this->pollTags = true;
        }
        if(isset($types['creatives']) && $types['creatives'] == 1)
        {
            $this->pollCreatives = true;
        }
    }

    /**
     * Set main settings
     *
     * @param array $criteria
     */
    public function setCriteria(array $criteria)
    {
        $this->onlyUser     = isset($criteria['onlyUser']) && intval($criteria['onlyUser']) > 0 ? intval($criteria['onlyUser']) : 0 ;
        $this->rowId        = isset($criteria['rowId']) ? intval($criteria['rowId']) : 0;
        $this->isFirstCall  = $this->rowId > 0 ? false : true ;
        if(isset($criteria['direction']) && in_array($criteria['direction'], array('up', 'down')))
        {
            $this->direction = $criteria['direction'];
        }
        //var_dump($this->isFirstCall);exit(0);
    }

    /**
     * Create sql and parameters
     *
     */
    private function _setSQL()
    {
        $conditions = array();

        // id
        if(!$this->isFirstCall)
        {
            if($this->direction == 'up')
            {
                $conditions[]  = " m.id > ?";
            }
            else
            {
                $conditions[]  = " m.id < ?";
            }
            $this->sqlParams[] = $this->rowId;
        }

        // metrics_type
        $types = array();
        if($this->pollMetrics)
        {
            $types[] = 1;
        }
        if($this->pollPageView)
        {
            $types[] = 2;
        }
        if($this->pollSocial)
        {
            $types[] = 3;
        }
        if($this->pollTags)
        {
            $types[] = 4;
            $types[] = 5;
        }
        if($this->pollCreatives)
        {
            $types[] = 6;
            $types[] = 7;
        }
        if(!empty($types))
        {
            $conditions[] = " m.metrics_type IN(" . implode(",", $types) . ") ";
        }

        // user_id
        if($this->onlyUser > 0)
        {
            $conditions[]       = " m.user_id = ? ";
            $this->sqlParams[]  = $this->onlyUser;
        }

        // add starbar id condition
        $conditions[] = " m.starbar_id  = s.id ";
        $where = implode("\nAND ", $conditions);

$this->sql=<<<EOT
SELECT
    m.*, s.label as starbar_name
FROM
    metrics_log m, starbar s
WHERE
    $where
ORDER BY
    id DESC
LIMIT
    ?
EOT;
        // add limit
        if($this->isFirstCall)
        {
            $this->sqlParams[]  = $this->limitFirstRun;
        }
        else
        {
            $this->sqlParams[]  = $this->limitLiveFeed;
        }
    }

    /**
     * Create sql and get data
     *
     * @return ArrayObject
     */
    public function run()
    {
        // Nothing to poll for?
        if(!$this->pollMetrics && !$this->pollSocial
            && !$this->pollPageView && !$this->pollTags && !$this->pollCreatives)
        {
            return new ArrayObject(array());
        }

        // Create sql and params array
        $this->_setSQL();
        //var_dump($this->sql);exit(0);
        
        // Call dynamically
        $results = call_user_func_array(array('Db_Pdo', 'fetchAll'), array_merge(array($this->sql), $this->sqlParams));

        return new ArrayObject($results);
    }
}
