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
        AND ms.id > @lastSearchId
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
        AND mpv.id > @lastPageViewId

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
        AND msa.id > @lastSocialActivityId
)
ORDER BY dateTime DESC
LIMIT @limitLiveFeed