DROP TABLE IF EXISTS `{TABLE_PREFIX}mod_wbs_auth`;
CREATE TABLE `{TABLE_PREFIX}mod_wbs_auth` (
  `settings_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_group` int(11) NOT NULL,
  `message_authed` text NOT NULL DEFAULT '',
  `html_form` text NOT NULL DEFAULT '',
  `css_form` text NOT NULL DEFAULT '',
  `use_smart_login` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`settings_id`)
){TABLE_ENGINE=MyISAM};

ALTER TABLE `{TABLE_PREFIX}users`
  ADD `name` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `{TABLE_PREFIX}users`
  ADD `surname` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `{TABLE_PREFIX}users`
  ADD `confirm_reg` varchar(255) NOT NULL DEFAULT '1';
ALTER TABLE `{TABLE_PREFIX}users`
  ADD `confirm_repair` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `{TABLE_PREFIX}users`
  ADD `new_password` varchar(255) NOT NULL DEFAULT '';