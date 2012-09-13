CREATE VIEW v_report_cell_user_map
AS
SELECT u.id AS user_id
     , rc.id AS report_cell_id
  FROM user u, report_cell rc
 WHERE rc.comma_delimited_list_of_users
      LIKE concat('%,', CONVERT(u.id, CHAR), ',%')
;
