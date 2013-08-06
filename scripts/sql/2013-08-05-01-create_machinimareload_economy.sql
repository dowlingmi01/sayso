INSERT economy ( id, title ) VALUES
( 7, 'Machinima Reload' )
;

INSERT game_asset (id, economy_id, type, name, bdid) VALUES
(78, 7, 'currency', 'XP', NULL),
(79, 7, 'currency', 'Coins', NULL),
(80, 7, 'currency', 'Check-In Points', NULL),
(81, 7, 'currency', 'Creation Points', NULL),
(82, 7, 'currency', 'Influence Points', NULL),
(83, 7, 'currency', 'Mission Points', NULL),
(84, 7, 'currency', 'Opinion Points', NULL),
(85, 7, 'currency', 'Personal Points', NULL),
(86, 7, 'currency', 'Poll Points - Premium', NULL),
(87, 7, 'currency', 'Poll Points - Standard', NULL),
(88, 7, 'currency', 'Purchase Points', NULL),
(89, 7, 'currency', 'Social Points', NULL),
(90, 7, 'currency', 'Survey Points - Premium', NULL),
(91, 7, 'currency', 'Survey Points - Profile', NULL),
(92, 7, 'currency', 'Survey Points - Standard', NULL),
(93, 7, 'currency', 'Tenure Points', NULL),
(94, 7, 'currency', 'Token Points', NULL),
(95, 7, 'currency', 'Trailer Quiz Points', NULL),
(96, 7, 'level', 'User Level', NULL);

INSERT game_currency (game_asset_id, game_currency_type_id) VALUES
(78, 1),
(79, 2),
(80, 3),
(81, 4),
(82, 5),
(83, 6),
(84, 8),
(85, 9),
(86, 10),
(87, 11),
(88, 12),
(89, 13),
(90, 14),
(91, 15),
(92, 16),
(93, 17),
(94, 18),
(95, 19);

INSERT game_transaction_type (id, economy_id, short_name, class, name) VALUES
(178, 7, 'ADHOC_EXPERIENCEPOINTS', '', ''),
(179, 7, 'ADHOC_REDEEMABLEPOINTS', '', ''),
(180, 7, 'BAR_TENURE_TWO_WEEKS', '', ''),
(181, 7, 'BAR_TENURE_WEEK', '', ''),
(182, 7, 'BEATBAR_LIKE', '', ''),
(183, 7, 'FACEBOOK_ASSOCIATE', '', ''),
(184, 7, 'FB_LIKE_BRAND', '', ''),
(185, 7, 'FB_POLL_PREMIUM_SHARE', '', ''),
(186, 7, 'FB_POLL_STANDARD_SHARE', '', ''),
(187, 7, 'FB_SHARE_STARBAR', '', ''),
(188, 7, 'FB_SURVEY_PREMIUM_SHARE', '', ''),
(189, 7, 'FB_SURVEY_PROFILE_SHARE', '', ''),
(190, 7, 'FB_SURVEY_STANDARD_SHARE', '', ''),
(191, 7, 'FB_TRAILER_STANDARD_SHARE', '', ''),
(192, 7, 'LEVEL_UP_BONUS', '', ''),
(193, 7, 'POLL_PREMIUM', '', ''),
(194, 7, 'POLL_STANDARD', '', ''),
(195, 7, 'STARBAR_CHECKIN', '', ''),
(196, 7, 'STARBAR_OPT_IN', '', ''),
(197, 7, 'SURVEY_PREMIUM', '', ''),
(198, 7, 'SURVEY_PREMIUM_DISQUALIFIED', '', ''),
(199, 7, 'SURVEY_PROFILE', '', ''),
(200, 7, 'SURVEY_STANDARD', '', ''),
(201, 7, 'SURVEY_STANDARD_DISQUALIFIED', '', ''),
(202, 7, 'TEST_REWARD_NOTES', '', ''),
(203, 7, 'TRAILER_PREMIUM', '', ''),
(204, 7, 'TRAILER_STANDARD', '', ''),
(205, 7, 'TW_POLL_PREMIUM_SHARE', '', ''),
(206, 7, 'TW_POLL_STANDARD_SHARE', '', ''),
(207, 7, 'TW_SHARE_BRAND', '', ''),
(208, 7, 'TW_SHARE_STARBAR', '', ''),
(209, 7, 'TW_SURVEY_PREMIUM_SHARE', '', ''),
(210, 7, 'TW_SURVEY_PROFILE_SHARE', '', ''),
(211, 7, 'TW_SURVEY_STANDARD_SHARE', '', ''),
(212, 7, 'TW_TRAILER_STANDARD_SHARE', '', ''),
(213, 7, 'TWITTER_ASSOCIATE', '', ''),
(214, 7, 'LEVEL_UP', '', ''),
(215, 7, 'PURCHASE', 'Purchase', ''),
(216, 7, 'INITIAL_STOCK', 'AddStock', ''),
(217, 7, 'ADJUST_STOCK', 'AdjustStock', '');

INSERT game_transaction_type_line (game_transaction_type_id, game_asset_id, amount) VALUES
(178, 78, 1),
(179, 79, 7),
(180, 78, 300),
(180, 79, 23),
(180, 93, 1),
(181, 78, 150),
(181, 79, 11),
(181, 93, 1),
(182, 78, 200),
(182, 79, 15),
(182, 89, 1),
(183, 78, 750),
(183, 79, 56),
(183, 89, 1),
(184, 78, 400),
(184, 79, 30),
(184, 89, 1),
(185, 78, 100),
(185, 79, 8),
(185, 89, 1),
(186, 78, 100),
(186, 79, 8),
(186, 89, 1),
(187, 78, 250),
(187, 79, 19),
(187, 89, 1),
(188, 78, 400),
(188, 79, 30),
(188, 89, 1),
(189, 78, 200),
(189, 79, 15),
(189, 89, 1),
(190, 78, 100),
(190, 79, 8),
(190, 89, 1),
(191, 78, 100),
(191, 79, 8),
(191, 89, 1),
(192, 78, 100),
(192, 79, 8),
(193, 78, 500),
(193, 79, 38),
(193, 86, 1),
(194, 78, 250),
(194, 79, 19),
(194, 87, 1),
(195, 78, 150),
(195, 79, 11),
(195, 80, 1),
(196, 78, 1000),
(196, 79, 75),
(197, 78, 5000),
(197, 79, 375),
(197, 90, 1),
(198, 78, 1000),
(198, 79, 75),
(198, 90, 1),
(199, 78, 2000),
(199, 79, 150),
(199, 85, 1),
(199, 91, 1),
(200, 78, 500),
(200, 79, 38),
(200, 92, 1),
(201, 78, 250),
(201, 79, 19),
(201, 92, 1),
(202, 78, 10000),
(202, 79, 5000),
(203, 78, 250),
(203, 79, 19),
(203, 95, 1),
(204, 78, 250),
(204, 79, 19),
(204, 95, 1),
(205, 78, 100),
(205, 79, 8),
(205, 89, 1),
(206, 78, 100),
(206, 79, 8),
(206, 89, 1),
(207, 78, 400),
(207, 79, 30),
(207, 89, 1),
(208, 78, 250),
(208, 79, 19),
(208, 89, 1),
(209, 78, 400),
(209, 79, 30),
(209, 89, 1),
(210, 78, 200),
(210, 79, 15),
(210, 89, 1),
(211, 78, 100),
(211, 79, 8),
(211, 89, 1),
(212, 78, 100),
(212, 79, 8),
(212, 89, 1),
(213, 78, 750),
(213, 79, 56),
(213, 89, 1),
(214, 96, 1);

INSERT game_level(game_asset_id, ordinal, threshold, name, img_url, img_url_small, description) VALUES
(96, 1, 1000, 'Gray Recruit I', 'http://media.saysollc.com/media/machinimareload/Level1_B.png', 'http://media.saysollc.com/media/machinimareload/Level1_S.png', ''),
(96, 2, 4000, 'Gray Recruit II', 'http://media.saysollc.com/media/machinimareload/Level2_B.png', 'http://media.saysollc.com/media/machinimareload/Level2_S.png', ''),
(96, 3, 10000, 'Gray Recruit III', 'http://media.saysollc.com/media/machinimareload/Level3_B.png', 'http://media.saysollc.com/media/machinimareload/Level3_S.png', ''),
(96, 4, 20000, 'Gray Recruit IV', 'http://media.saysollc.com/media/machinimareload/Level4_B.png', 'http://media.saysollc.com/media/machinimareload/Level4_S.png', ''),
(96, 5, 30000, 'White Recruit I', 'http://media.saysollc.com/media/machinimareload/Level5_B.png', 'http://media.saysollc.com/media/machinimareload/Level5_S.png', ''),
(96, 6, 40000, 'White Recruit II', 'http://media.saysollc.com/media/machinimareload/Level6_B.png', 'http://media.saysollc.com/media/machinimareload/Level6_S.png', ''),
(96, 7, 50000, 'White Recruit III', 'http://media.saysollc.com/media/machinimareload/Level7_B.png', 'http://media.saysollc.com/media/machinimareload/Level7_S.png', ''),
(96, 8, 65000, 'White Recruit IV', 'http://media.saysollc.com/media/machinimareload/Level8_B.png', 'http://media.saysollc.com/media/machinimareload/Level8_S.png', ''),
(96, 9, 80000, 'Red Pro I', 'http://media.saysollc.com/media/machinimareload/Level9_B.png', 'http://media.saysollc.com/media/machinimareload/Level9_S.png', ''),
(96, 10, 100000, 'Red Pro II', 'http://media.saysollc.com/media/machinimareload/Level10_B.png', 'http://media.saysollc.com/media/machinimareload/Level10_S.png', ''),
(96, 11, 125000, 'Red Pro III', 'http://media.saysollc.com/media/machinimareload/Level11_B.png', 'http://media.saysollc.com/media/machinimareload/Level11_S.png', ''),
(96, 12, 150000, 'Red Pro IV', 'http://media.saysollc.com/media/machinimareload/Level12_B.png', 'http://media.saysollc.com/media/machinimareload/Level12_S.png', ''),
(96, 13, 175000, 'Black Pro I', 'http://media.saysollc.com/media/machinimareload/Level13_B.png', 'http://media.saysollc.com/media/machinimareload/Level13_S.png', ''),
(96, 14, 200000, 'Black Pro II', 'http://media.saysollc.com/media/machinimareload/Level14_B.png', 'http://media.saysollc.com/media/machinimareload/Level14_S.png', ''),
(96, 15, 250000, 'Black Pro III', 'http://media.saysollc.com/media/machinimareload/Level15_B.png', 'http://media.saysollc.com/media/machinimareload/Level15_S.png', ''),
(96, 16, 300000, 'Black IV', 'http://media.saysollc.com/media/machinimareload/Level16_B.png', 'http://media.saysollc.com/media/machinimareload/Level16_S.png', ''),
(96, 17, 350000, 'Bronze Master I', 'http://media.saysollc.com/media/machinimareload/Level17_B.png', 'http://media.saysollc.com/media/machinimareload/Level17_S.png', ''),
(96, 18, 400000, 'Bronze Master II', 'http://media.saysollc.com/media/machinimareload/Level18_B.png', 'http://media.saysollc.com/media/machinimareload/Level18_S.png', ''),
(96, 19, 450000, 'Bronze Master III', 'http://media.saysollc.com/media/machinimareload/Level19_B.png', 'http://media.saysollc.com/media/machinimareload/Level19_S.png', ''),
(96, 20, 500000, 'Bronze Master IV', 'http://media.saysollc.com/media/machinimareload/Level20_B.png', 'http://media.saysollc.com/media/machinimareload/Level20_S.png', ''),
(96, 21, 575000, 'Silver Master I', 'http://media.saysollc.com/media/machinimareload/Level21_B.png', 'http://media.saysollc.com/media/machinimareload/Level21_S.png', ''),
(96, 22, 650000, 'Silver Master II', 'http://media.saysollc.com/media/machinimareload/Level22_B.png', 'http://media.saysollc.com/media/machinimareload/Level22_S.png', ''),
(96, 23, 725000, 'Silver Master III', 'http://media.saysollc.com/media/machinimareload/Level23_B.png', 'http://media.saysollc.com/media/machinimareload/Level23_S.png', ''),
(96, 24, 925000, 'Silver Master IV', 'http://media.saysollc.com/media/machinimareload/Level24_B.png', 'http://media.saysollc.com/media/machinimareload/Level24_S.png', ''),
(96, 25, 1025000, 'Gold Legend I', 'http://media.saysollc.com/media/machinimareload/Level25_B.png', 'http://media.saysollc.com/media/machinimareload/Level25_S.png', ''),
(96, 26, 1150000, 'Gold Legend II', 'http://media.saysollc.com/media/machinimareload/Level26_B.png', 'http://media.saysollc.com/media/machinimareload/Level26_S.png', ''),
(96, 27, 1275000, 'Gold Legend III', 'http://media.saysollc.com/media/machinimareload/Level27_B.png', 'http://media.saysollc.com/media/machinimareload/Level27_S.png', ''),
(96, 28, 1400000, 'Gold Legend IV', 'http://media.saysollc.com/media/machinimareload/Level28_B.png', 'http://media.saysollc.com/media/machinimareload/Level28_S.png', ''),
(96, 29, 1550000, 'Platinum Legend I', 'http://media.saysollc.com/media/machinimareload/Level29_B.png', 'http://media.saysollc.com/media/machinimareload/Level29_S.png', ''),
(96, 30, 1700000, 'Platinum Legend II', 'http://media.saysollc.com/media/machinimareload/Level30_B.png', 'http://media.saysollc.com/media/machinimareload/Level30_S.png', '');

UPDATE starbar
   SET economy_id = 7
 WHERE id = 7
;
