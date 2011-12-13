SELECT 
	s.id AS study_id, s.user_id AS study_user_id, s.name AS study_name, s.description AS study_description, s.size AS study_size, s.size_minimum AS study_size_minimum, s.begin_date AS study_begin_date, s.end_date AS study_end_date, s.click_track AS study_click_track, s.created AS study_created, s.modified AS study_modified,
	c.id AS study_cell_id, c.study_id AS study_cell_study_id, c.description AS study_cell_description, c.size AS study_cell_size, c.cell_type AS study_cell_cell_type, c.created AS study_cell_created, c.modified AS study_cell_modified,
	t.id AS study_tag_id, t.user_id AS study_tag_user_id, t.name AS study_tag_name, t.tag AS study_tag_tag, t.target_url AS study_tag_target_url, t.created AS study_tag_created, t.modified AS study_tag_modified,
	d.id AS study_domain_id, d.user_id AS study_domain_user_id, d.domain AS study_domain_domain, d.created AS study_domain_created, d.modified AS study_domain_modified,
	sc.id AS study_creative_id, sc.user_id AS study_creative_user_id, sc.mime_type_id AS study_creative_mime_type_id, sc.name AS study_creative_name, sc.url AS study_creative_url, sc.target_url AS study_creative_target_url, sc.created AS study_creative_created, sc.modified AS study_creative_modified
FROM
	study s
/* add study_search_engines_map and study_social_activity_type_map */	
LEFT JOIN
	study_cell c ON s.id = c.study_id
LEFT JOIN
	study_cell_tag_map tm ON c.id = tm.cell_id
LEFT JOIN
	study_tag t ON tm.tag_id = t.id
LEFT JOIN
	study_tag_domain_map tdm ON t.id = tdm.tag_id
LEFT JOIN
	study_domain d ON tdm.domain_id = d.id
LEFT JOIN
	study_creative_tag_map ctm ON t.id = ctm.tag_id
LEFT JOIN
	study_creative sc ON ctm.creative_id = sc.id
	