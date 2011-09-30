SET foreign_key_checks = 0;

DELETE FROM survey_user_map WHERE 1;
DELETE FROM survey WHERE 1;
ALTER TABLE survey AUTO_INCREMENT=1;
ALTER TABLE survey_user_map AUTO_INCREMENT=1;

INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_questions) VALUES (30, 1, 'survey', 'SurveyGizmo', 1, 'Hellomusic.com', now(), '651152', 'Survey-V12', 1, 55);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_questions) VALUES (40, 1, 'survey', 'SurveyGizmo', 1, 'What''s the deal?', now(), '655509', 'Gear-Deals-survey-2', null, 5);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_questions) VALUES (50, 1, 'survey', 'SurveyGizmo', 1, 'Listening', now(), '655517', 'Listening', null, 5);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_questions) VALUES (60, 1, 'survey', 'SurveyGizmo', 1, 'Where did you get that?', now(), '655526', 'Where-did-you-get-that', null, 5);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_questions) VALUES (70, 1, 'survey', 'SurveyGizmo', 1, 'Take 1', now(), '655529', 'Take-1', null, 5);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_questions) VALUES (80, 1, 'survey', 'SurveyGizmo', 1, 'That''s What Friends Are For', now(), '655531', 'That-s-What-Friends-Are-For', null, 5);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_questions) VALUES (90, 1, 'survey', 'SurveyGizmo', 1, 'Services', now(), '656924', 'Services', null, 5);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_questions) VALUES (100, 1, 'survey', 'SurveyGizmo', 1, 'On the Road Again', now(), '656935', 'On-the-Road-Again', null, 5);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_questions) VALUES (110, 1, 'survey', 'SurveyGizmo', 1, 'Vintage Gear', now(), '656939', 'Vintage-Gear', null, 5);

INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_answers) VALUES (30, 1, 'poll', 'SurveyGizmo', 1, 'What''s your favorite type of guitar pickup?', now(), '655490', '6CX8ILU9ED46AUR9T77SWA4LYC7SLD', null, 4);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_answers) VALUES (40, 1, 'poll', 'SurveyGizmo', 1, 'What drives you most to play your instrument?', now(), '655495', 'D16FBY7AGJWEH9D0KCD4679SAZCHLR', null, 4);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_answers) VALUES (50, 1, 'poll', 'SurveyGizmo', 1, 'Songwriters: What''s your writing style?', now(), '655497', '1TOEMQHSZ0T261M7EUC4EF2L68IIZM', null, 4);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_answers) VALUES (60, 1, 'poll', 'SurveyGizmo', 1, 'Do you prefer to write songs:', now(), '656955', '98FUNHX6TV7W7PUS05ML8Q6NGEXETG', null, 2);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_answers) VALUES (70, 1, 'poll', 'SurveyGizmo', 1, 'What mic setup do you use for recording guitars?', now(), '656956', 'WN7ZEZL333HX79TS99NXHQ3FOJGE81', null, 5);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_answers) VALUES (80, 1, 'poll', 'SurveyGizmo', 1, 'Would you ever take an online class for mixing, tracking, mastering etc?', now(), '656962', '2AG59VNFTT8HGWKTKV486MMF40L3Y3', null, 3);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_answers) VALUES (90, 1, 'poll', 'SurveyGizmo', 1, 'What level of touring have you reached?', now(), '656964', 'V7GUMWTHQQH8B75ZIAZYA8O1JZ7IQ6', null, 5);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_answers) VALUES (100, 1, 'poll', 'SurveyGizmo', 1, 'How do you get the word out about your gigs offline?', now(), '657103', 'N8H3PPR29Q9A47Z8KE1H7FD1PZRYM2', null, 3);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_answers) VALUES (110, 1, 'poll', 'SurveyGizmo', 1, 'How do you get the word out about your gigs online?', now(), '657105', 'BRW0KXPB3NEGXUDIG41OBGNL8K0QEI', null, 5);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_answers) VALUES (120, 1, 'poll', 'SurveyGizmo', 1, 'Where do you rehearse?', now(), '656969', 'DM2PKFOZ806SRVCR7KM0ZQJB5N4TF5', null, 4);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_answers) VALUES (130, 1, 'poll', 'SurveyGizmo', 1, 'China Cymbals: Great or Grating?', now(), '656970', 'RFFFF2SM05B7L59Z7ABUWHY4BAZV1F', null, 2);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_answers) VALUES (140, 1, 'poll', 'SurveyGizmo', 1, 'Do you bring a lighting rig to your gigs?', now(), '656971', 'RD484EZECWVD2P7EJIMP702KZAJIBR', null, 2);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_answers) VALUES (150, 1, 'poll', 'SurveyGizmo', 1, 'Is a great guitar or great amp more important for good tone?', now(), '656973', '5TC4YF9M6L2JSY9MK4OKSYHAYEF1TK', null, 2);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_answers) VALUES (160, 1, 'poll', 'SurveyGizmo', 1, 'How do you release your music offline?', now(), '657114', 'GL157C4C1B9OD0DRCF478XIZ243MK4', null, 3);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_answers) VALUES (170, 1, 'poll', 'SurveyGizmo', 1, 'How do you release your music online?', now(), '657116', '6ZZAZ8SL05C6YVIJMR6SHKR1ZHQCU8', null, 3);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_answers) VALUES (180, 1, 'poll', 'SurveyGizmo', 1, 'What kind of strings do you prefer to use on your acoustic guitar?', now(), '656975', '6238NNZ7G9JUL2Y2MGR9CT9Q8A6UL1', null, 4);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_answers) VALUES (190, 1, 'poll', 'SurveyGizmo', 1, 'Acoustic guitar players: Do you use an acoustic guitar amp live or plug into a DI for the PA?', now(), '656976', '3338TC6KNTTJK3FYBK1R5Y9SU6CD5Z', null, 2);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_answers) VALUES (200, 1, 'poll', 'SurveyGizmo', 1, 'Do you know how to register the songs you write with the US Copy-write Office?', now(), '656980', 'AYB0X7Z8J0EAC4LDK9XJ2X36RZ715G', null, 2);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_answers) VALUES (210, 1, 'poll', 'SurveyGizmo', 1, 'Bass players: What kind of strings do you like to use?', now(), '656981', 'ZF201H7XDJP0BGMP0WTXG4L4EEEGDX', null, 3);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_answers) VALUES (220, 1, 'poll', 'SurveyGizmo', 1, 'Bass players: How often do you change your strings?', now(), '656982', 'XIWBGV0Y0B0BK7A2MI1BR4PPX51JD1', null, 4);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_answers) VALUES (230, 1, 'poll', 'SurveyGizmo', 1, 'Do you prefer to read music magazines in print or online?', now(), '656983', 'LAQIHAPW54609YPX15V8R2ZBRE17W7', null, 2);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_answers) VALUES (240, 1, 'poll', 'SurveyGizmo', 1, 'Are you looking for a record deal?', now(), '656985', 'FK22UNRV2ZBBSYVIV9V5O2G1LMJ4IA', null, 2);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_answers) VALUES (250, 1, 'poll', 'SurveyGizmo', 1, 'Where do you most often purchase new music?', now(), '656987', 'BGAUVW75A144NUVPL6H2FMZNIDSEM2', null, 4);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_answers) VALUES (260, 1, 'poll', 'SurveyGizmo', 1, 'Would you consider hiring a service that helped you develop a marketing plan for your release?', now(), '656988', 'UFSCG9YOFD7OUMPVPD10D1T62TIE84', null, 2);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_answers) VALUES (270, 1, 'poll', 'SurveyGizmo', 1, 'Guitarists: What power tubes sound the best overdriven?', now(), '656990', 'JDBWENY61RIRUWSWJ5GI0760XTL6F2', null, 6);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_answers) VALUES (280, 1, 'poll', 'SurveyGizmo', 1, 'In a perfect world, how often would you like to gig?', now(), '656991', 'NJFGAU51YZ8EW2NXW054JJHT0UN0X3', null, 5);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_answers) VALUES (290, 1, 'poll', 'SurveyGizmo', 1, 'Bass players - do you prefer active or passive pickups?', now(), '657079', '3SDQ1XMCPYLND52JGSV9HQHSGKI42P', null, 2);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_answers) VALUES (300, 1, 'poll', 'SurveyGizmo', 1, 'Bass players - fretted or fretless?', now(), '657082', 'P6MOV8HLDFUJCYAWR0S3P78ONVL68W', null, 2);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_answers) VALUES (310, 1, 'poll', 'SurveyGizmo', 1, 'Drummers, what drum heads do you prefer?', now(), '657084', 'R077KZ4BNMO678P2Q1OPC1N1WS2HSY', null, 3);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_answers) VALUES (320, 1, 'poll', 'SurveyGizmo', 1, 'Drummers, what sticks do you prefer?', now(), '657086', 'F99FTZ7WGVRG0AG5HAWDYQ9BBII2F0', null, 6);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_answers) VALUES (330, 1, 'poll', 'SurveyGizmo', 1, 'Keyboardists - Weighted or non-weighted keys?', now(), '657090', 'KP2OSZD56NGLXETMLPLCFC2SVH6DSE', null, 2);

SET foreign_key_checks = 1;
