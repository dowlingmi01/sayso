CREATE TABLE study_cell_qualifier_search_engines_map (
    cell_qualifier_search_id int(10) NOT NULL,
    search_engines_id int(10) NOT NULL,
    UNIQUE KEY map_unique (cell_qualifier_search_id, search_engines_id),
    CONSTRAINT map_cell_qualifier_search FOREIGN KEY (cell_qualifier_search_id) REFERENCES study_cell_qualifier_search (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT map_search_engine FOREIGN KEY (search_engines_id) REFERENCES lookup_search_engines (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE study_cell_qualifier_search DROP FOREIGN KEY study_cell_qualifier_search_search_engine_id, DROP KEY study_cell_qualifier_search_search_engine_id, DROP search_engine_id;

ALTER TABLE study_cell_tag_map ADD UNIQUE KEY (cell_id, tag_id);