CREATE TABLE `tbl_sessions` (
  `session_id` varchar(255) collate utf8_bin NOT NULL,
  `session_expire` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `session_data` blob NOT NULL,
  PRIMARY KEY  (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Revision: 1';