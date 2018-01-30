DROP TABLE IF EXISTS `{TABLE_PREFIX}mod_wbs_auth`;
CREATE TABLE `{TABLE_PREFIX}mod_wbs_auth` (
  `settings_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_group` int(11) NOT NULL,
  `html_form` text NOT NULL DEFAULT '',
  `use_smart_login` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`settings_id`)
){TABLE_ENGINE=MyISAM};