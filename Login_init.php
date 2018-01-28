<?php

if(!defined('WB_PATH')) {
        require_once(dirname(dirname(__FILE__)).'/framework/globalExceptionHandler.php');
        throw new IllegalFileException();
}

require_once(__DIR__."/Login2.php");

if(defined('SMART_LOGIN') AND SMART_LOGIN == 'enabled') {
	$username_fieldname = 'username_'.generate_image_name(7, 'up');
	$password_fieldname = 'password_'.generate_image_name(7, 'up');
} else {
	$username_fieldname = 'username';
	$password_fieldname = 'password';
}

$thisApp = new Login([  'MAX_ATTEMPS' => "3",
						'WARNING_URL' => str_replace(WB_PATH,WB_URL,$admin->correct_theme_source('warning.html')),
						'USERNAME_FIELDNAME' => $username_fieldname,
						'PASSWORD_FIELDNAME' => $password_fieldname,
						'REMEMBER_ME_OPTION' => SMART_LOGIN,
						'MIN_USERNAME_LEN' => "2",
						'MIN_PASSWORD_LEN' => "2",
						'MAX_USERNAME_LEN' => "30",
						'MAX_PASSWORD_LEN' => "30",
						'LOGIN_URL' => ADMIN_URL."/login/index.php",
						'DEFAULT_URL' => ADMIN_URL."/start/index.php",
						'TEMPLATE_DIR' => null,
						'TEMPLATE_FILE' => "login.htt",
						'FRONTEND' => 'custom',
						'FORGOTTEN_DETAILS_APP' => ADMIN_URL."/login/forgot/index.php",
						'USERS_TABLE' => TABLE_PREFIX."users",
						'GROUPS_TABLE' => TABLE_PREFIX."groups",
				]);

?>