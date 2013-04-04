CREATE TABLE metrics_video_view
     ( id         int(10)         NOT NULL AUTO_INCREMENT
     , user_id    int(10)         NOT NULL
     , starbar_id int(10)         NOT NULL
     , video_type enum('youtube') NOT NULL DEFAULT 'youtube'
     , video_id   varchar(255)    NOT NULL
     , video_url  varchar(2000)   NOT NULL
     , page_url   varchar(2000)   NULL
     , created    timestamp       NOT NULL DEFAULT '0000-00-00 00:00:00'
     , modified   timestamp       NOT NULL DEFAULT '0000-00-00 00:00:00'
     , PRIMARY KEY (id)
     , KEY metrics_video_view_user_id (user_id)
     , CONSTRAINT metrics_video_view_starbar_id FOREIGN KEY (starbar_id) REFERENCES starbar (id) ON DELETE RESTRICT ON UPDATE CASCADE
     , CONSTRAINT metrics_video_view_user_id FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE RESTRICT ON UPDATE CASCADE
     )
;
ALTER TABLE metrics_log CHANGE type type enum('search', 'page view', 'video view', 'social activity', 'campaign view', 'campaign click', 'creative view', 'creative click') NOT NULL DEFAULT 'page view'
;
DROP TRIGGER IF EXISTS metrics_video_view_to_metrics_log;
DELIMITER //
CREATE TRIGGER metrics_video_view_to_metrics_log AFTER INSERT ON metrics_video_view
 FOR EACH ROW BEGIN
    INSERT INTO metrics_log
         ( legacy_id, created, user_id, type, starbar_id, content )
    SELECT mpv.id, mpv.created, mpv.user_id, 'video view'
         , mpv.starbar_id, concat(mpv.video_type, ' id: ', mpv.video_id)
      FROM metrics_video_view mpv
     WHERE mpv.id = NEW.id;
  END;
//
DELIMITER ;
