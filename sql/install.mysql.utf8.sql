DROP TABLE IF EXISTS `#__gcomments_comments`;
CREATE TABLE `#__gcomments_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `context` varchar(255) DEFAULT '',
  `bind_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_name` varchar(255) DEFAULT NULL,
  `user_email` varchar(255) DEFAULT NULL,
  `user_ip` varchar(255) DEFAULT NULL,
  `creation_date` datetime DEFAULT current_timestamp(),
  `deleted` tinyint(1) DEFAULT 0,
  `text` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;