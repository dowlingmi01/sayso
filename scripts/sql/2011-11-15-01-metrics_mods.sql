/**
 * Add field to capture the full URL of the click through destination
 * 
 */
ALTER TABLE metrics_tag_click_thru ADD url varchar(255) AFTER metrics_tag_view_id;
ALTER TABLE metrics_creative_click_thru ADD url varchar(255) AFTER metrics_creative_view_id;