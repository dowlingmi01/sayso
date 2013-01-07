INSERT metrics_event_type VALUES
(5, 'purchase_virtual'),
(6, 'ad_view'),
(7, 'ad_click'),
(8, 'game_loc_visit'),
(9, 'add_cash');

INSERT metrics_property VALUES
(8, 'event_source', 'lookup'),
(9, 'partner_name', 'lookup'),
(10, 'partner_uid', 'string'),
(11, 'game_name', 'lookup'),
(12, 'game_source', 'lookup'),
(13, 'purchase_type', 'lookup'),
(14, 'purchase_amt', 'double'),
(15, 'purchase_currency', 'lookup'),
(16, 'purchase_subtype', 'lookup'),
(17, 'stimuli_type', 'lookup'),
(18, 'game_loc_name', 'lookup'),
(19, 'add_cash_stage', 'lookup');
