INSERT study_ad
     ( type, existing_ad_type, existing_ad_tag, existing_ad_domain, ad_target )
VALUES
     ( 'campaign', 'video', 'mxN47l2fB-M', '.', '' )
   , ( 'campaign', 'video', 'olr7V8JDUsE', '.', '' )
;
SET @study_ad_id = last_insert_id()
;
INSERT report_cell
     ( title, condition_type, category )
VALUES
     ( 'Viewed Halo4 tracker videos', 'or', 'study' )
;
SET @report_cell_id = last_insert_id()
;
INSERT report_cell_user_condition
     ( report_cell_id, condition_type, comparison_type, compare_study_ad_id )
VALUES
     ( @report_cell_id, 'study_ad', 'viewed', @study_ad_id )
   , ( @report_cell_id, 'study_ad', 'viewed', @study_ad_id + 1 )
;
