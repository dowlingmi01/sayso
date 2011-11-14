<?php
/**
 * @author alecksmart
 */
class Metrics_FeedCollection
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
    private $limitLiveFeed  = 40;

    private $lastSearchId;

    private $lastPageViewId;

    private $lastSocialActivityId;

    private $isFirstCall    = true;

    private $pollMetrics    = false;

    private $pollPageView   = false;

    private $pollSocial     = false;

    private $sql            = '';

    private $sqlParams      = array();

    private $onlyUser       = 0 ;


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
    }

    private function _setSQL()
    {
        $sqlChunks = array();

        if($this->isFirstCall)
        {
            if($this->pollMetrics)
            {
                $addOnlyUserSQL = "";
                if($this->onlyUser > 0)
                {
                    $addOnlyUserSQL     = "AND ms.user_id = ?";
                    $this->sqlParams[]  = $this->onlyUser;
                }
                $sqlChunks[]=<<<EOT
(
    SELECT
        ms.id               AS lastId,
        ms.user_id          AS userId,
        u1.username         AS userName,
        ms.starbar_id       AS starbarId,
        s1.label            AS starbar,
        ms.created          AS dateTime,
        'Search'    AS metricsType,
        concat(lsa.label, ', query: ', ms.query)
                            AS `data`
    FROM
        metrics_search ms, `user` u1, starbar s1, lookup_search_engines lsa
    WHERE
        ms.user_id = u1.id
        $addOnlyUserSQL
        AND ms.starbar_id = s1.id
        AND ms.search_engine_id = lsa.id
)
EOT;
            }
            if($this->pollPageView)
            {
                $addOnlyUserSQL = "";
                if($this->onlyUser > 0)
                {
                    $addOnlyUserSQL     = "AND mpv.user_id = ?";
                    $this->sqlParams[]  = $this->onlyUser;
                }
                $sqlChunks[]=<<<EOT
(
    SELECT
        mpv.id              AS lastId,
        mpv.user_id         AS userId,
        u2.username         AS userName,
        mpv.starbar_id      AS starbarId,
        s2.label            AS starbar,
        mpv.created         AS dateTime,
        'Page View'         AS metricsType,
        mpv.url             AS `data`
    FROM
        metrics_page_view mpv, `user` u2, starbar s2
    WHERE
        mpv.user_id = u2.id
        $addOnlyUserSQL
        AND mpv.starbar_id = s2.id
)
EOT;
            }
            if($this->pollSocial)
            {
                $addOnlyUserSQL = "";
                if($this->onlyUser > 0)
                {
                    $addOnlyUserSQL     = "AND msa.user_id = ?";
                    $this->sqlParams[]  = $this->onlyUser;
                }
                $sqlChunks[]=<<<EOT
(
    SELECT
        msa.id              AS lastId,
        msa.user_id         AS userId,
        u3.username         AS userName,
        msa.starbar_id      AS starbarId,
        s3.label            AS starbar,
        msa.created         AS dateTime,
        'Social Activity'   AS metricsType,
        concat(sat.short_name, ', url: ', msa.url , ', content: ', msa.content)
                            AS `data`
    FROM
        metrics_social_activity msa, `user` u3, starbar s3, lookup_social_activity_type sat
    WHERE
        msa.user_id = u3.id
        $addOnlyUserSQL
        AND msa.starbar_id = s3.id
        AND msa.social_activity_type_id = sat.id
)
EOT;
            }

            $sql = implode(' UNION ', $sqlChunks);
            $sql .=<<<EOT

ORDER BY dateTime DESC
LIMIT ?
EOT;
            $this->sqlParams[]  = $this->limitFirstRun;
            $this->sql          = $sql;
        }
        else
        {
            if($this->pollMetrics)
            {
                $addOnlyUserSQL = "";
                if($this->onlyUser > 0)
                {
                    $addOnlyUserSQL     = "AND ms.user_id = ?";
                    $this->sqlParams[]  = $this->onlyUser;
                }
                $sqlChunks[] = <<<EOT
(
    SELECT
        ms.id               AS lastId,
        ms.user_id          AS userId,
        u1.username         AS userName,
        ms.starbar_id       AS starbarId,
        s1.label            AS starbar,
        ms.created          AS dateTime,
        'Search'    AS metricsType,
        concat(lsa.label, ', query: ', ms.query)
                            AS `data`
    FROM
        metrics_search ms, `user` u1, starbar s1, lookup_search_engines lsa
    WHERE
        ms.user_id = u1.id
        $addOnlyUserSQL
        AND ms.created > ?
        AND ms.starbar_id = s1.id
        AND ms.search_engine_id = lsa.id
        AND ms.id > ?
)
EOT;
                $this->sqlParams[] = $this->rowsAfter;
                $this->sqlParams[] = $this->lastSearchId;
            }
            if($this->pollPageView)
            {
                $addOnlyUserSQL = "";
                if($this->onlyUser > 0)
                {
                    $addOnlyUserSQL     = "AND mpv.user_id = ?";
                    $this->sqlParams[]  = $this->onlyUser;
                }
                $sqlChunks[]=<<<EOT
(
    SELECT
        mpv.id              AS lastId,
        mpv.user_id         AS userId,
        u2.username         AS userName,
        mpv.starbar_id      AS starbarId,
        s2.label            AS starbar,
        mpv.created         AS dateTime,
        'Page View'         AS metricsType,
        mpv.url             AS `data`
    FROM
        metrics_page_view mpv, `user` u2, starbar s2
    WHERE
        mpv.user_id = u2.id
        $addOnlyUserSQL
        AND mpv.created > ?
        AND mpv.starbar_id = s2.id
        AND mpv.id > ?
)
EOT;
                $this->sqlParams[] = $this->rowsAfter;
                $this->sqlParams[] = $this->lastPageViewId;
            }
            if($this->pollSocial)
            {
                $addOnlyUserSQL = "";
                if($this->onlyUser > 0)
                {
                    $addOnlyUserSQL     = "AND msa.user_id = ?";
                    $this->sqlParams[]  = $this->onlyUser;
                }
                $sqlChunks[]=<<<EOT
(
    SELECT
        msa.id              AS lastId,
        msa.user_id         AS userId,
        u3.username         AS userName,
        msa.starbar_id      AS starbarId,
        s3.label            AS starbar,
        msa.created         AS dateTime,
        'Social Activity'   AS metricsType,
        concat(sat.short_name, ', url: ', msa.url , ', content: ', msa.content)
                            AS `data`
    FROM
        metrics_social_activity msa, `user` u3, starbar s3, lookup_social_activity_type sat
    WHERE
        msa.user_id = u3.id
        $addOnlyUserSQL
        AND msa.created > ?
        AND msa.starbar_id = s3.id
        AND msa.social_activity_type_id = sat.id
        AND msa.id > ?
)
EOT;
                $this->sqlParams[] = $this->rowsAfter;
                $this->sqlParams[] = $this->lastSocialActivityId;
            }

            $sql = implode(' UNION ', $sqlChunks);
            $sql .=<<<EOT

ORDER BY dateTime DESC
LIMIT ?
EOT;
            $this->sqlParams[] = $this->limitLiveFeed;
            $this->sql = $sql;
        }
    }

    /**
     * Set data if polling was performed before in this session
     *
     * @param array $criteria
     */
    public function setLastIds(array $criteria)
    {
        $this->lastSearchId         = isset($criteria['lastSearchId']) ? intval($criteria['lastSearchId']) : 0;
        $this->lastPageViewId       = isset($criteria['lastPageViewId']) ? intval($criteria['lastPageViewId']) : 0;
        $this->lastSocialActivityId = isset($criteria['lastSocialActivityId']) ? intval($criteria['lastSocialActivityId']) : 0;
        $this->rowsAfter            = isset($criteria['rowsAfter']) ? $criteria['rowsAfter'] : '0000-00-00 00:00:00' ;
        $this->isFirstCall          = false;
    }

    /**
     * Set main settings
     *
     * @param array $criteria
     */
    public function setCriteria(array $criteria)
    {
        $this->onlyUser = isset($criteria['onlyUser']) && intval($criteria['onlyUser']) > 0 ? intval($criteria['onlyUser']) : 0 ;
    }

    /**
     * Crate sql and get data
     */
    public function run()
    {
        // Nothing to poll for?
        if(!$this->pollMetrics && !$this->pollSocial && !$this->pollPageView)
        {
            return new ArrayObject(array());
        }

        // Create sql and params array
        $this->_setSQL();

        // Prepare params array
        $sql = array($this->sql);
        // Call dynamically
        $results = call_user_func_array(array('Db_Pdo', 'fetchAll'), array_merge($sql, $this->sqlParams));
        return new ArrayObject($results);
    }
}
