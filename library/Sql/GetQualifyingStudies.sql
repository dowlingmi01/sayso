SELECT 
    s.id AS study_id, s.name AS study_name, s.description AS study_description, s.size AS study_size, s.size_minimum AS study_size_minimum, s.begin_date AS study_begin_date, s.end_date AS study_end_date, s.click_track AS study_click_track,
    quota.id AS quota_id, quota.study_id AS quota_study_id, 
    percentile.quota AS lookup_quota_percentile_quota,
    gender.short_name AS lookup_gender_short_name,
    age_range.age_from AS lookup_age_range_age_from, age_range.age_to AS lookup_age_range_age_to 
FROM
    study s 
LEFT JOIN 
    study_quota quota ON s.id = quota.study_id
LEFT JOIN
    lookup_quota_percentile percentile ON quota.percentile_id = percentile.id
LEFT JOIN
    lookup_gender gender ON quota.gender_id = gender.id
LEFT JOIN
    lookup_age_range age_range ON quota.age_range_id = age_range.id
    
WHERE
    (
		(now() BETWEEN s.begin_date AND s.end_date)
		OR
		(!s.end_date AND now() >= s.begin_date)
		OR
		(!s.begin_date AND now() <= s.end_date)
		OR
    	(!s.begin_date AND !s.end_date)
    )
    AND
        quota.gender_id IN (@gender_id)
    AND
    (
        age_range.age_from <= @age AND age_range.age_to >= @age
    )
ORDER BY s.created ASC