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
    private $limitFirstRun  = 100;

    /**
     * Limit getting a number of records for all subsequent runs
     * Set it to a quite big number of rows,
     * this is needed mostly to prever some attacks
     *
     * @var int
     */
    private $limitLiveFeed  = 1000;

    private $lastSearchId;

    private $lastPageViewId;

    private $lastSocialActivityId;

    private $isFirstCall = true;

    public function getSQL()
    {
        if($this->isFirstCall)
        {
return <<<EOT
(
    SELECT
        ms.id               AS lastId,
        ms.user_id          AS userId,
        u1.username         AS userName,
        ms.starbar_id       AS starbarId,
        s1.label            AS starbar,
        ms.created          AS dateTime,
        'Metrics Search'    AS metricsType,
        concat(lsa.label, ', query: ', ms.query)
                            AS `data`
    FROM
        metrics_search ms, `user` u1, starbar s1, lookup_search_engines lsa
    WHERE
        ms.user_id = u1.id
        AND ms.starbar_id = s1.id
        AND ms.search_engine_id = lsa.id
)
UNION
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
        AND mpv.starbar_id = s2.id

)
UNION
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
        AND msa.starbar_id = s3.id
        AND msa.social_activity_type_id = sat.id
)
ORDER BY dateTime ASC
LIMIT ?
EOT;
        }
        else
        {
            return <<<EOT
(
    SELECT
        ms.id               AS lastId,
        ms.user_id          AS userId,
        u1.username         AS userName,
        ms.starbar_id       AS starbarId,
        s1.label            AS starbar,
        ms.created          AS dateTime,
        'Metrics Search'    AS metricsType,
        concat(lsa.label, ', query: ', ms.query)
                            AS `data`
    FROM
        metrics_search ms, `user` u1, starbar s1, lookup_search_engines lsa
    WHERE
        ms.user_id = u1.id
        AND ms.starbar_id = s1.id
        AND ms.search_engine_id = lsa.id
        AND ms.id > ?
)
UNION
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
        AND mpv.starbar_id = s2.id
        AND mpv.id > ?

)
UNION
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
        AND msa.starbar_id = s3.id
        AND msa.social_activity_type_id = sat.id
        AND msa.id > ?
)
ORDER BY dateTime ASC
LIMIT ?
EOT;
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
        $this->isFirstCall          = false;
    }

    /**
     * Get data from sql
     */
    public function run()
    {
        $results = array();
        if($this->isFirstCall)
        {
            $results = Db_Pdo::fetchAll($this->getSQL(), $this->limitFirstRun);
        }
        else
        {
            $results = Db_Pdo::fetchAll(
                $this->getSQL(), 
                $this->lastSearchId,
                $this->lastPageViewId,
                $this->lastSocialActivityId,
                $this->limitLiveFeed
            );
        }

        return new ArrayObject($results);
    }
}
