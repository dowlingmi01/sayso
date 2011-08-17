INSERT INTO user_role (id, short_name, description, ordinal, parent_id) VALUES 
    (1, 'guests', 'Guest role (not actually a user)', 10, null), 
    (2, 'users', 'Basic user role', 20, 1), 
    (3, 'moderators', 'User role with added privilege of moderation within a site', 30, 2), 
    (4, 'site_admins', 'Moderator role with added privilege of administering users/moderators within a site', 40, 3), 
    (5, 'group_admins', 'Administrator role with added privilege of administering site groups', 50, 4), 
    (6, 'root_admins', 'Administrator with "root" privileges across all sites', 60, 5);

INSERT INTO lookup_gender (id, short_name) VALUES 
    (1, 'male'),
    (2, 'female'),
    (3, 'unspecified');

INSERT INTO lookup_ethnicity (id, short_name, label) VALUES
    (1, 'white', 'White'),
    (2, 'african_american', 'African American'),
    (3, 'asian', 'Asian'),
    (4, 'latino', 'Latino'),
    (5, 'native_american', 'Native American'),
    (6, 'hawaiin-pacific-islander', 'Hawaiin Pacific Islander')
    /*,(99, 'unspecified', 'Unspecified')*/;

INSERT INTO lookup_income_range (id, income_from, income_to, ordinal) VALUES 
    (1, 0, 20000, 10),
    (2, 20000, 40000, 20),
    (3, 40000, 60000, 30),
    (4, 60000, 80000, 40),
    (5, 80000, 100000, 50),
    (6, 100000, null, 60);   

INSERT INTO lookup_age_range (id, age_from, age_to, ordinal) VALUES 
    (1, 13, 17, 10),
    (2, 18, 24, 20),
    (3, 25, 34, 30),
    (4, 35, 44, 40),
    (5, 45, 54, 50),
    (6, 55, 64, 60),
    (7, 65, null, 70),
    (8, 18, null, 80),
    (9, 18, 49, 90);

INSERT INTO lookup_quota_percentile (id, quota, quarter, ordinal) VALUES
    (1, 10, false, 10),
    (2, 20, false, 20),
    (3, 25, true, 30),
    (4, 30, false, 40),
    (5, 40, false, 50),
    (6, 50, true, 60),
    (7, 60, false, 70),
    (8, 70, false, 80),
    (9, 75, true, 90),
    (10, 80, false, 100),
    (11, 90, false, 110),
    (12, 100, true, 120);
    
INSERT INTO lookup_survey_type (id, short_name) VALUES 
    (1, 'technology'),
    (2, 'food'),
    (3, 'religion'),
    (4, 'news'),
    (5, 'celebrities'),
    (6, 'politics'),
    (7, 'sports'),
    (8, 'household'),
    (9, 'television');

INSERT INTO lookup_poll_frequency (id, short_name, label, description, extra, default_frequency) VALUES 
    (1, 'often', 'Bring ''em on!', 'Often - earn the most Pay.So!', 'Earn a lotta Pay.So! :D', false),
    (2, 'occasionally', 'Occasionally', 'Occasionally - earn a little Pay.So', 'Earn a little Pay.So. :|', true),
    (3, 'never', 'Never', 'Never - no Pay.So :(', 'Earn no Pay.So. :(', false);

INSERT INTO lookup_email_frequency (id, short_name, label, description, extra, default_frequency) VALUES 
    (1, 'often', 'Bring ''em on!', 'Often - earn the most Pay.So!', 'Earn a lotta Pay.So! :D', false),
    (2, 'occasionally', 'Occasionally', 'Occasionally - earn a little Pay.So', 'Earn a little Pay.So. :|', true),
    (3, 'never', 'Never', 'Never - no Pay.So :(', 'Earn no Pay.So. :(', false);

INSERT INTO lookup_search_engines (id, short_name) VALUES 
    (1, 'bing'),
    (2, 'google'),
    (3, 'yahoo!');
    
INSERT INTO lookup_social_activity_type (id, short_name) VALUES 
    (1, 'facebook_like'),
    (2, 'tweet');
    
INSERT INTO lookup_timeframe (id, short_name, label, seconds) VALUES
    (1, 'one_hour', '1 Hour', '3600'),
    (2, 'one_day', '1 Day', '86400'),
    (3, 'one_week', '1 Week', '604800'),
    (4, 'one_month', '1 Month', '2592000');
