<?php

$path_core = __DIR__.'/../wbs_core/include_all.php';
if (file_exists($path_core )) include($path_core );
else echo "<script>console.log('Модуль wbs_auth требует модуль wbs_core')</script>";

if (!class_exists('ModAuth')) {
class ModAuth extends Addon {

    function __construct($page_id, $section_id) {
        parent::__construct('auth', $page_id, $section_id);
        $this->tbl_auth = "`".TABLE_PREFIX."mod_wbs_auth`";
    }
    
    function uninstall() {
        global $clsEmail;
        delete_row(
            $clsEmail->tbl_templates_of_letter,
            glue_fields(['letter_template_name'=>'success_registration', 'letter_template_name'=>'repair_password'], 'OR')
        );
    }

    function create_confirm_email_code($username, $name) {
    	return strtoupper(md5($username.$name).time());
    }
    
    function send_confirm_email($confirm_email_code, $email, $username, $name, $surname, $additional_info='') {
    	global $clsEmail;
        return $clsEmail->send_template($email, 'success_registration', [
            'username'=>$username,
            'name'=>$name,
            'surname'=>$surname,
            'email'=>$email,
            'additional_info'=>$additional_info,
            'wb_url'=>idn_decode(WB_URL)[0]."/modules/wbs_auth/api.php?action=confirm_email&code=".$confirm_email_code
        ]);
    }
    
    function create_user($groups_id, $active, $username, $password, $display_name, $email, $home_folder, $default_language, $name, $surname, $who_invite=0, $additional_info='') {
    	global $MESSAGE, $database, $admin;
    
        if (strpos($home_folder, '/') !== 0) $home_folder = '/'.$home_folder;
    
    	// choose group_id from groups_id - workaround for still remaining calls to group_id (to be cleaned-up)
    	$gid_tmp = explode(',', $groups_id);
    	if(in_array('1', $gid_tmp)) $group_id = '1'; // if user is in administrator-group, get this group
    	else $group_id = $gid_tmp[0]; // else just get the first one
    	unset($gid_tmp);
    
    	// Check if username already exists
    	$results = $database->query("SELECT user_id FROM ".TABLE_PREFIX."users WHERE username = '$username'");
    	if($results->numRows() > 0) {
    		return $MESSAGE['USERS']['USERNAME_TAKEN'];
    	}
    
    	// Check if the email already exists
    	$results = $database->query("SELECT user_id FROM ".TABLE_PREFIX."users WHERE email = '".$admin->add_slashes($email)."'");
    	if($results->numRows() > 0) {
    		if(isset($MESSAGE['USERS']['EMAIL_TAKEN'])) {
    			return $MESSAGE['USERS']['EMAIL_TAKEN'];
    		} else {
    			return $MESSAGE['USERS']['INVALID_EMAIL'];
    		}
    	}
    
    	// MD5 supplied password
    	$md5_password = md5($password);
    	$confirm_email_code = $this->create_confirm_email_code($username, $name);
    	// Inser the user into the database
    	$query = "INSERT INTO ".TABLE_PREFIX."users (group_id,groups_id,active,username,password,display_name,home_folder,email,timezone, language,name,surname,confirm_reg) VALUES ('$group_id', '$groups_id', '$active', '$username','$md5_password','$display_name','$home_folder','$email','-72000', '$default_language', '$name', '$surname', '$confirm_email_code')";
    	$database->query($query);
    	if($database->is_error()) {
    		return $database->get_error();
    	}

        if ($home_folder) make_dir(WB_PATH.'/'.MEDIA_DIRECTORY.$home_folder);
    
        list($result, $letter_id) = $this->send_confirm_email($confirm_email_code, $email, $username, $name, $surname, $additional_info);
        if ($result !== true) return $result;
    
    	return true;
    }
    
    function logout() {
    	global $database;

    	// delete remember key of current user from database
    	if (isset($_SESSION['USER_ID']) && isset($database)) {
    		$table = TABLE_PREFIX . 'users';
    		$sql = "UPDATE `$table` SET `remember_key` = '' WHERE `user_id` = '" . (int) $_SESSION['USER_ID'] . "'";
    		$database->query($sql);
    	}
    	
    	// delete remember key cookie if set
    	if (isset($_COOKIE['REMEMBER_KEY'])) {
    		setcookie('REMEMBER_KEY', '', time() - 3600, '/');
    	}
    	
    	// delete most critical session variables manually
    	$_SESSION['USER_ID'] = null;
    	$_SESSION['GROUP_ID'] = null;
    	$_SESSION['GROUPS_ID'] = null;
    	$_SESSION['USERNAME'] = null;
    	$_SESSION['PAGE_PERMISSIONS'] = null;
    	$_SESSION['SYSTEM_PERMISSIONS'] = null;
    	
    	// overwrite session array
    	$_SESSION = array();
    	
    	// delete session cookie if set
    	if (isset($_COOKIE[session_name()])) {
    	    setcookie(session_name(), '', time() - 42000, '/');
    	}
    	
    	// delete the session itself
    	session_destroy();
    }
    
    function repair_password($email, $new_password) {
        global $database, $MESSAGE, $clsEmail;
        // Check if the email exists in the database
        $query = "SELECT user_id,username,display_name,email,last_reset,password FROM ".TABLE_PREFIX."users WHERE email = '".$database->escapeString($email)."'";
        $results = $database->query($query);
        if($results->numRows() == 0) return $MESSAGE['FORGOT_PASS']['EMAIL_NOT_FOUND'];
        $results_array = $results->fetchRow();
        
        // Check if the password has been reset in the last 2 hours
        $last_reset = $results_array['last_reset'];
        $time_diff = time()-$last_reset; // Time since last reset in seconds
        $time_diff = $time_diff/60/60; // Time since last reset in hours
        //if($time_diff < 2) return $MESSAGE['FORGOT_PASS']['ALREADY_RESET'];

        $confirm_code = $this->create_confirm_email_code($results_array['user_id'], $name);
    
        $database->query("UPDATE ".TABLE_PREFIX."users SET new_password = '".md5($new_password)."', last_reset = '".time()."', `confirm_repair`='{$confirm_code}' WHERE user_id = '".$results_array['user_id']."'");
        if($database->is_error()) return $database->get_error();
    
        list($r, $letter_id) = $clsEmail->send_template($email, 'repair_password', [
            'name'=>$name,
            'surname'=>$surname,
            'email'=>$email,
            'url'=>idn_decode(WB_URL)[0]."/modules/wbs_auth/api.php?action=confirm_repair&code=".$confirm_code
        ]);
        if ($r !== true) {
            $database->query("UPDATE ".TABLE_PREFIX."users SET new_password='', confirm_repair='' WHERE user_id = '".$results_array['user_id']."'");
                return 'Пароль не изменён. Не удалось отправить письмо: '.$r;
        }
        return true;
    }
    
}
}
?>