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

    private $lastTagId;

    private $lastTagViewId;

    private $lastCreativeId;

    private $lastCreativeViewId;

    private $isFirstCall    = true;

    private $pollMetrics    = false;

    private $pollPageView   = false;

    private $pollSocial     = false;

    private $pollTags       = false;

    private $pollCreatives  = false;

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
     * @todo
     * Better refactoring: add functions for chunks to process with passing by reference
     */
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
        'Search'            AS metricsType,
        0                   AS selectType,
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
        0                   AS selectType,
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
        0                   AS selectType,
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

            if($this->pollTags)
            {
                // metrics_tag_view

                $addOnlyUserSQL = "";
                if($this->onlyUser > 0)
                {
                    $addOnlyUserSQL     = "AND mtv.user_id = ?";
                    $this->sqlParams[]  = $this->onlyUser;
                }
                $sqlChunks[]=<<<EOT
(
    SELECT
        mtv.id              AS lastId,
        mtv.user_id         AS userId,
        u4.username         AS userName,
        mtv.starbar_id      AS starbarId,
        s4.label            AS starbar,
        mtv.created         AS dateTime,
        'Tag'               AS metricsType,
        1                   AS selectType,
        concat('Cell: ', c4.cell_type , ', tag: ', t4.name, ', url: ', t4.target_url)
                            AS `data`
    FROM
        metrics_tag_view mtv, `user` u4, starbar s4,
                study_tag as t4, study_cell c4
    WHERE
        mtv.user_id = u4.id
        $addOnlyUserSQL
        AND mtv.starbar_id = s4.id
        AND mtv.tag_id = t4.id
        AND mtv.cell_id = c4.id
)
EOT;
                // metrics_tag_click_thru

                $addOnlyUserSQL = "";
                if($this->onlyUser > 0)
                {
                    $addOnlyUserSQL     = "AND mtv5.user_id = ?";
                    $this->sqlParams[]  = $this->onlyUser;
                }
                $sqlChunks[]=<<<EOT
(
    SELECT
        mct.id              AS lastId,
        mtv5.user_id        AS userId,
        u5.username         AS userName,
        mtv5.starbar_id     AS starbarId,
        s5.label            AS starbar,
        mtv5.created        AS dateTime,
        'Tag'               AS metricsType,
        2                   AS selectType,
        concat('Click through for cell: ', c5.cell_type , ', tag: ', t5.name, ', url: ', t5.target_url)
                            AS `data`
    FROM
        metrics_tag_click_thru mct, metrics_tag_view mtv5, `user` u5, starbar s5,
                study_tag as t5, study_cell c5
    WHERE
        mct.metrics_tag_view_id = mtv5.id
        AND mtv5.user_id = u5.id
        $addOnlyUserSQL
        AND mtv5.starbar_id = s5.id
        AND mtv5.tag_id = t5.id
        AND mtv5.cell_id = c5.id
)
EOT;

            }
            if($this->pollCreatives)
            {
                // metrics_creative_view

                $addOnlyUserSQL = "";
                if($this->onlyUser > 0)
                {
                    $addOnlyUserSQL     = "AND mcv.user_id = ?";
                    $this->sqlParams[]  = $this->onlyUser;
                }
                $sqlChunks[]=<<<EOT
(
    SELECT
        mcv.id              AS lastId,
        mcv.user_id         AS userId,
        u6.username         AS userName,
        mcv.starbar_id      AS starbarId,
        s6.label            AS starbar,
        mcv.created         AS dateTime,
        'Creative'          AS metricsType,
        1                   AS selectType,
        concat('Cell: ', c6.cell_type , ', creative: ', t6.name, 
                CASE WHEN t6.url IS NOT NULL THEN CONCAT(', url: ', t6.url) ELSE '' END ) AS `data`
    FROM
        metrics_creative_view mcv, `user` u6, starbar s6,
                study_creative as t6, study_cell c6
    WHERE
        mcv.user_id = u6.id
        $addOnlyUserSQL
        AND mcv.starbar_id = s6.id
        AND mcv.creative_id = t6.id
        AND mcv.cell_id = c6.id
)
EOT;
                // metrics_creative_click_thru

                $addOnlyUserSQL = "";
                if($this->onlyUser > 0)
                {
                    $addOnlyUserSQL     = "AND mcv7.user_id = ?";
                    $this->sqlParams[]  = $this->onlyUser;
                }
                $sqlChunks[]=<<<EOT
(
    SELECT
        mzt.id              AS lastId,
        mcv7.user_id        AS userId,
        u7.username         AS userName,
        mcv7.starbar_id     AS starbarId,
        s7.label            AS starbar,
        mcv7.created        AS dateTime,
        'Creative'          AS metricsType,
        2                   AS selectType,
        concat('Click through for cell: ', c7.cell_type , ', creative: ', t7.name, 
                CASE WHEN t7.url IS NOT NULL THEN CONCAT(', url: ', t7.url) ELSE '' END ) AS `data`
    FROM
        metrics_creative_click_thru mzt, metrics_creative_view mcv7, `user` u7, starbar s7,
                study_creative as t7, study_cell c7
    WHERE
        mzt.metrics_creative_view_id = mcv7.id
        AND mcv7.user_id = u7.id
        $addOnlyUserSQL
        AND mcv7.starbar_id = s7.id
        AND mcv7.creative_id = t7.id
        AND mcv7.cell_id = c7.id
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

            //var_dump($this->sql);exit(0);

        }

        /**
         * Consequent call
         */

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
        'Search'            AS metricsType,
        0                   AS selectType,
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
        0                   AS selectType,
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
        0                   AS selectType,
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

            if($this->pollTags)
            {
                // metrics_tag_view

                $addOnlyUserSQL = "";
                if($this->onlyUser > 0)
                {
                    $addOnlyUserSQL     = "AND mtv.user_id = ?";
                    $this->sqlParams[]  = $this->onlyUser;
                }
                $sqlChunks[]=<<<EOT
(
    SELECT
        mtv.id              AS lastId,
        mtv.user_id         AS userId,
        u4.username         AS userName,
        mtv.starbar_id      AS starbarId,
        s4.label            AS starbar,
        mtv.created         AS dateTime,
        'Tag'               AS metricsType,
        1                   AS selectType,
        concat('Cell: ', c4.cell_type , ', tag: ', t4.name, ', url: ', t4.target_url)
                            AS `data`
    FROM
        metrics_tag_view mtv, `user` u4, starbar s4,
                study_tag as t4, study_cell c4
    WHERE
        mtv.user_id = u4.id
        $addOnlyUserSQL
        AND mtv.created > ?
        AND mtv.starbar_id = s4.id
        AND mtv.tag_id = t4.id
        AND mtv.cell_id = c4.id
        AND mtv.id > ?
)
EOT;
                $this->sqlParams[] = $this->rowsAfter;
                $this->sqlParams[] = $this->lastTagId;

                // metrics_tag_click_thru

                $addOnlyUserSQL = "";
                if($this->onlyUser > 0)
                {
                    $addOnlyUserSQL     = "AND mtv5.user_id = ?";
                    $this->sqlParams[]  = $this->onlyUser;
                }
                $sqlChunks[]=<<<EOT
(
    SELECT
        mct5.id             AS lastId,
        mtv5.user_id        AS userId,
        u5.username         AS userName,
        mtv5.starbar_id     AS starbarId,
        s5.label            AS starbar,
        mtv5.created        AS dateTime,
        'Tag'               AS metricsType,
        2                   AS selectType,
        concat('Click through for cell: ', c5.cell_type , ', tag: ', t5.name, ', url: ', t5.target_url)
                            AS `data`
    FROM
        metrics_tag_click_thru mct5, metrics_tag_view mtv5, `user` u5, starbar s5,
                study_tag as t5, study_cell c5
    WHERE
        mct5.metrics_tag_view_id = mtv5.id
        AND mct5.created > ?
        AND mtv5.user_id = u5.id
        $addOnlyUserSQL
        AND mtv5.starbar_id = s5.id
        AND mtv5.tag_id = t5.id
        AND mtv5.cell_id = c5.id
        AND mct5.id > ?
)
EOT;
                $this->sqlParams[] = $this->rowsAfter;
                $this->sqlParams[] = $this->lastTagViewId;
            }

            if($this->pollCreatives)
            {
                // metrics_creative_view

                $addOnlyUserSQL = "";
                if($this->onlyUser > 0)
                {
                    $addOnlyUserSQL     = "AND mcv.user_id = ?";
                    $this->sqlParams[]  = $this->onlyUser;
                }
                $sqlChunks[]=<<<EOT
(
    SELECT
        mcv.id              AS lastId,
        mcv.user_id         AS userId,
        u6.username         AS userName,
        mcv.starbar_id      AS starbarId,
        s6.label            AS starbar,
        mcv.created         AS dateTime,
        'Creative'          AS metricsType,
        1                   AS selectType,
        concat('Cell: ', c6.cell_type , ', creative: ', t6.name,
                CASE WHEN t6.url IS NOT NULL THEN CONCAT(', url: ', t6.url) ELSE '' END ) AS `data`
    FROM
        metrics_creative_view mcv, `user` u6, starbar s6,
                study_creative as t6, study_cell c6
    WHERE
        mcv.user_id = u6.id
        AND mcv.created > ?
        $addOnlyUserSQL
        AND mcv.starbar_id = s6.id
        AND mcv.creative_id = t6.id
        AND mcv.cell_id = c6.id
        AND mcv.id > ?
)
EOT;
                $this->sqlParams[] = $this->rowsAfter;
                $this->sqlParams[] = $this->lastCreativeId;

                // metrics_creative_click_thru

                $addOnlyUserSQL = "";
                if($this->onlyUser > 0)
                {
                    $addOnlyUserSQL     = "AND mcv7.user_id = ?";
                    $this->sqlParams[]  = $this->onlyUser;
                }
                $sqlChunks[]=<<<EOT
(
    SELECT
        mzt.id              AS lastId,
        mcv7.user_id        AS userId,
        u7.username         AS userName,
        mcv7.starbar_id     AS starbarId,
        s7.label            AS starbar,
        mcv7.created        AS dateTime,
        'Creative'          AS metricsType,
        2                   AS selectType,
        concat('Click through for cell: ', c7.cell_type , ', creative: ', t7.name,
                CASE WHEN t7.url IS NOT NULL THEN CONCAT(', url: ', t7.url) ELSE '' END ) AS `data`
                            
    FROM
        metrics_creative_click_thru mzt, metrics_creative_view mcv7, `user` u7, starbar s7,
                study_creative as t7, study_cell c7
    WHERE
        mzt.metrics_creative_view_id = mcv7.id
        AND mzt.created > ?
        AND mcv7.user_id = u7.id
        $addOnlyUserSQL
        AND mcv7.starbar_id = s7.id
        AND mcv7.creative_id = t7.id
        AND mcv7.cell_id = c7.id
        AND mzt.id > ?
)
EOT;
                $this->sqlParams[] = $this->rowsAfter;
                $this->sqlParams[] = $this->lastCreativeViewId;
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

        $this->lastTagId        = isset($criteria['lastTagId']) ? intval($criteria['lastTagId']) : 0;
        $this->lastTagViewId    = isset($criteria['lastTagViewId']) ? intval($criteria['lastTagViewId']) : 0;

        $this->lastCreativeId           = isset($criteria['lastCreativeId']) ? intval($criteria['lastCreativeId']) : 0;
        $this->lastCreativeViewId       = isset($criteria['lastCreativeViewId']) ? intval($criteria['lastCreativeViewId']) : 0;


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
        if(!$this->pollMetrics && !$this->pollSocial && !$this->pollPageView && !$this->pollTags && !$this->pollCreatives)
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
