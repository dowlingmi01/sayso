INSERT metrics_event_type VALUES
(2, 'brandboost_launch_screen'),
(3, 'brandboost_interstitial_screen'),
(4, 'brandboost_end_screen');

INSERT metrics_property VALUES
(1, 'brandboost_session_id', 'int'),
(2, 'brandboost_campaign_id', 'lookup'),
(3, 'brandboost_partner_name', 'lookup'),
(4, 'brandboost_game_name', 'lookup'),
(5, 'brandboost_item_id', 'lookup'),
(6, 'brandboost_sponsor_name', 'lookup'),
(7, 'brandboost_uid', 'string');
