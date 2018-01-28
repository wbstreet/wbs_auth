<?php
/**
 *
 * @category        framework
 * @package         backend login
 * @author          Ryan Djurovich, WebsiteBaker Project
 * @copyright       WebsiteBaker Org. e.V.
 * @link            http://websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.8.3
 * @requirements    PHP 5.3.6 and higher
 * @version         $Id: Login.php 67 2017-03-03 22:14:28Z manu $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb2.10/tags/WB-2.10.0/wb/framework/Login.php $
 * @lastmodified    $Date: 2017-03-03 23:14:28 +0100 (Fr, 03. Mär 2017) $
 *
 */
/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if(!defined('WB_PATH')) {
    require_once(dirname(__FILE__).'/globalExceptionHandler.php');
    throw new IllegalFileException();
}
/* -------------------------------------------------------- */
define('LOGIN_CLASS_LOADED', true);

// Load the other required class files if they are not already loaded
require_once(WB_PATH."/framework/class.admin.php");
// Get WB version
require_once(ADMIN_PATH.'/interface/version.php');

class Login extends admin {

    const PASS_CHARS = '[\,w!#$%&*+\-.:=?@\|]';
    const USER_CHARS = '[a-z0-9&\-.=@_]';

    protected $aConfig = array();
    protected $oDb     = null;
    protected $oTrans  = null;

    public function __construct($config_array) {
        // Get language vars
/*        global $MESSAGE, $database; */
        $this->oDb    = $GLOBALS['database'];
        $this->oTrans = $GLOBALS['MESSAGE'];
        parent::__construct();
    // Get configuration values
        while(list($key, $value) = each($config_array)) {
//            $this->{(strtolower($key))} = $value;
            $this->aConfig[strtolower($key)] = $value;
        }
        if (!isset($this->frontend)) { $this->frontend = false; }
        if (!isset($this->redirect_url)) { $this->redirect_url = ''; }

    // calculate redirect URL
    // begin new routine
        $sProtokol = ((!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off' ) ? 'http' : 'https') . '://';;
        $sInstallFolderRel = dirname(dirname($_SERVER['SCRIPT_NAME']));
        $sServerUrl = $sProtokol.$_SERVER['HTTP_HOST'].($_SERVER['SERVER_PORT'] == 80 ? '' : $_SERVER['SERVER_PORT'].':').'/'.$sInstallFolderRel;
    // end new routine
/*
        $aRedirecthUrl = null;
        $sServerUrl = $_SERVER['SERVER_NAME'];
        $aServerUrl = $this->mb_parse_url(WB_URL);
        $sServerScheme = isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : isset($aServerUrl['scheme']) ? $aServerUrl['scheme'] : ' http';
        $sServerPath = $_SERVER['SCRIPT_NAME'];
*/

        // If the url is blank, set it to the default url
        $this->url = @$this->get_post('url')?:@$this->get_post('redirect')?:$this->default_url;
/*
        if ( !$this->frontend ){ $this->redirect_url = ( @$this->url ? : '' );}
        if ( $this->frontend ){ $this->url = ( @$this->redirect_url ? : null );}
*/
        if (preg_match('/%0d|%0a|\s/i', $this->url)) {
            throw new Exception('Warning: possible intruder detected on login');
        }
/*
        $aUrl = $this->mb_parse_url( $this->url );
        if ($this->redirect_url!='') {
            $aRedirecthUrl = $this->mb_parse_url( $this->redirect_url );
            $this->redirect_url = isset($aRedirecthUrl['host']) &&($sServerUrl==$aRedirecthUrl['host']) ? $this->redirect_url:$sServerScheme.'://'.$sServerUrl;
            $this->url = $this->redirect_url;
        }
        $this->url = isset($aRedirecthUrl['host']) &&($sServerUrl==$aUrl['host']) ? $this->url:ADMIN_URL.'/start/index.php';
        if(strlen($this->url) < 2) {
            $aDefaultUrl = $this->mb_parse_url( $this->default_url );
            $this->default_url = isset($aDefaultUrl['host']) &&($sServerUrl==$aDefaultUrl['host']) ? $this->default_url:$sServerScheme.'://'.$sServerUrl;
            $this->url = $this->default_url;
        }
*/
    // get username & password and validate it
        $username_fieldname = (string)$this->get_post('username_fieldname');
        $username_fieldname = (preg_match('/^_?[a-z][\w]+$/i', $username_fieldname) ? $username_fieldname : 'username');
        $sUsername = strtolower(trim((string)$this->get_post($username_fieldname)));
        $this->username = (preg_match(
            '/^'.self::USER_CHARS.'{'.$this->min_username_len.','.$this->max_username_len.'}$/is',
            $sUsername
        ) ? $sUsername : '');
        $password_fieldname = (string)$this->get_post('password_fieldname');
        $password_fieldname = (preg_match('/^_?[a-z][\w]+$/i', $password_fieldname) ? $password_fieldname : 'password');

        if ($this->username) {
/** @TODO implement crypting */
            $this->password = md5(trim((string)$this->get_post($password_fieldname)));
            // Figure out if the "remember me" option has been checked
            $this->remember = (@$_POST['remember'] == 'true' ? true : false);
        // try to authenticate
            $bSuccess = false;
            if (!($bSuccess = $this->is_authenticated())) {
                if ($this->is_remembered()) {
                    $sql = 'SELECT * FROM `'.TABLE_PREFIX.'users` '
                         . 'WHERE `user_id`='.$this->get_safe_remember_key();
                    if (($oUsers = $this->oDb->query($sql))) {
                        if (($aUser = $oUsers->fetchRow(MYSQLI_ASSOC))) {
                            $this->username = $aUser['username'];
                            $this->password = $aUser['password'];
                            // Check if the user exists (authenticate them)
                            $bSuccess = $this->authenticate();
                        }
                    }
                } else {
                    // Check if the user exists (authenticate them)
                    $bSuccess = $this->authenticate();
                }
            }
            if ($bSuccess) {
                // Authentication successful
               if ($this->aConfig['frontend'] !== 'custom') $this->send_header($this->url);
            } else {
                $this->message = $this->_oTrans->MESSAGE_LOGIN_AUTHENTICATION_FAILED;
                $this->increase_attemps();
            }
        } else {
            $this->message = $this->_oTrans->MESSAGE_LOGIN_BOTH_BLANK;
            $this->display_login();
        }
    }

    public function __isset($name)
    {
        return isset($this->aConfig[$name]);
    }

    public function __set($name, $value)
    {
         return $this->aConfig[$name] = $value;
    }

   public function __get ($name){
        $retval = null;
        if ($this->__isset($name)) {
            $retval = $this->aConfig[$name];
        }
        return $retval;
    }

    // Authenticate the user (check if they exist in the database)
    public function authenticate()
    {
        // Get user information
        $loginname = ( preg_match('/^'.self::USER_CHARS.'+$/s',$this->username) ? $this->username : '0');
        $aSettings = array();
        $aSettings['SYSTEM_PERMISSIONS']   = array();
        $aSettings['MODULE_PERMISSIONS']   = array();
        $aSettings['TEMPLATE_PERMISSIONS'] = array();
        $bRetval = false;

        $sql = 'SELECT * FROM `'.TABLE_PREFIX.'users` '
             . 'WHERE `username`=\''.$this->oDb->escapeString($loginname).'\'';
        if (($oUser = $this->oDb->query($sql))) {
            if (($aUser = $oUser->fetchRow(MYSQLI_ASSOC))) {
                if (
                    $aUser['password'] == $this->password &&
                    $aUser['active'] == 1
                ) {
                // valide authentcation !!
                    $user_id                   = $aUser['user_id'];
                    $this->user_id             = $user_id;
                    $aSettings['USER_ID']      = $user_id;
                    $aSettings['GROUP_ID']     = $aUser['group_id'];
                    $aSettings['GROUPS_ID']    = $aUser['groups_id'];
                    $aSettings['USERNAME']     = $aUser['username'];
                    $aSettings['DISPLAY_NAME'] = $aUser['display_name'];
                    $aSettings['EMAIL']        = $aUser['email'];
                    $aSettings['HOME_FOLDER']  = $aUser['home_folder'];
                    // Run remember function if needed
                    if($this->remember == true) { $this->remember($this->user_id); }
                    // Set language
                    if($aUser['language'] != '') {
                        $aSettings['LANGUAGE'] = $aUser['language'];
                    }
                    // Set timezone
                    if($aUser['timezone'] != '-72000') {
                        $aSettings['TIMEZONE'] = $aUser['timezone'];
                    } else {
                        // Set a session var so apps can tell user is using default tz
                        $aSettings['USE_DEFAULT_TIMEZONE'] = true;
                    }
                    // Set date format
                    if($aUser['date_format'] != '') {
                        $aSettings['DATE_FORMAT'] = $aUser['date_format'];
                    } else {
                        // Set a session var so apps can tell user is using default date format
                        $aSettings['USE_DEFAULT_DATE_FORMAT'] = true;
                    }
                    // Set time format
                    if($aUser['time_format'] != '') {
                        $aSettings['TIME_FORMAT'] = $aUser['time_format'];
                    } else {
                        // Set a session var so apps can tell user is using default time format
                        $aSettings['USE_DEFAULT_TIME_FORMAT'] = true;
                    }
                    // Get group information
                    $aSettings['GROUP_NAME'] = array();
                    $bOnlyAdminGroup = $this->ami_group_member('1') && (sizeof($aGroupsIds) == 1);
                    $sql = 'SELECT * FROM `'.TABLE_PREFIX.'groups` '
                         . 'WHERE `group_id` IN ('.$aUser['groups_id'].',0) '
                         . 'ORDER BY `group_id`';

                    if (($oGroups = $this->oDb->query($sql))) {
                        while (($aGroup = $oGroups->fetchRow( MYSQLI_ASSOC ))) {
                            $aSettings['GROUP_NAME'][$aGroup['group_id']] = $aGroup['name'];
                        // collect system_permissions (additively)
                            $aSettings['SYSTEM_PERMISSIONS'] = array_merge(
                                $aSettings['SYSTEM_PERMISSIONS'],
                                explode(',', $aGroup['system_permissions'])
                            );
                        // collect module_permission (subtractive)
                            if (!sizeof($aSettings['MODULE_PERMISSIONS'])) {
                                $aSettings['MODULE_PERMISSIONS'] = explode(',', $aGroup['module_permissions']);
                            } else {
                                $aSettings['MODULE_PERMISSIONS'] = array_intersect(
                                    $aSettings['MODULE_PERMISSIONS'],
                                    preg_split('/\s*[,;\|\+]/', $aGroup['module_permissions'], -1, PREG_SPLIT_NO_EMPTY)
                                );
                            }
                        // collect template_permission (subtractive)
                            if (!sizeof($aSettings['TEMPLATE_PERMISSIONS'])) {
                                $aSettings['TEMPLATE_PERMISSIONS'] = explode(',', $aGroup['template_permissions']);
                            } else {
                                $aSettings['TEMPLATE_PERMISSIONS'] = array_intersect(
                                    $aSettings['TEMPLATE_PERMISSIONS'],
                                    preg_split('/\s*[,;\|\+]/', $aGroup['template_permissions'], -1, PREG_SPLIT_NO_EMPTY)
                                );
                            }
                        }
                    }
                    // Update the users table with current ip and timestamp
                    $sRemoteAddress = @$_SERVER['REMOTE_ADDR'] ?: 'unknown';
                    $sql = 'UPDATE `'.TABLE_PREFIX.'users` '
                         . 'SET `login_when`='.time().', '
                         .     '`login_ip`=\''.$sRemoteAddress.'\' '
                         . 'WHERE `user_id`=\''.$user_id.'\'';
                    $this->oDb->query($sql);
                    $bRetval = true;
                }
            }
        }
        // merge settings into $_SESSION and overwrite older one values
        $_SESSION = array_merge($_SESSION, $aSettings);
        // Return if the user exists or not
        return $bRetval;
    }

    // Increase the count for login attemps
    protected function increase_attemps()
    {
        $_SESSION['ATTEMPS'] = (isset($_SESSION['ATTEMPS']) ? $_SESSION['ATTEMPS']++ : 0);
        $this->display_login();
    }


    public function getMessage ( ) {
      return $this->message;
    }

    // Function to set a "remembering" cookie for the user - removed
   protected function remember($user_id)
    {
        return true;
    }

    // Function to check if a user has been remembered - removed
    protected function is_remembered()
    {
        return false;
    }

    // Display the login screen
    protected function display_login() {
        // Get language vars
        global $MESSAGE;
        global $MENU;
        global $TEXT;

        $Trans = $GLOBALS['oTrans'];
        $ThemeName = (defined('DEFAULT_THEME')?DEFAULT_THEME:'DefaultTheme');
        $Trans->enableAddon('templates\\'.$ThemeName);
        $aLang = $Trans->getLangArray();
        // If attemps more than allowed, warn the user
        if($this->get_session('ATTEMPS') > $this->max_attemps) {
            $this->warn();
        }
        // Show the login form
        if($this->frontend != true) {
//            require_once(WB_PATH.'/include/phplib/template.inc');
            $aWebsiteTitle['value'] = WEBSITE_TITLE;
            $sql = 'SELECT `value` FROM `'.TABLE_PREFIX.'settings` '
                 . 'WHERE `name`=\'website_title\'';
            if ($get_title = $this->oDb->query($sql)){
                $aWebsiteTitle= $get_title->fetchRow( MYSQLI_ASSOC );
            }
            // Setup template object, parse vars to it, then parse it
            $template = new Template(dirname($this->correct_theme_source($this->template_file)));
            $template->set_file('page', $this->template_file);
            $template->set_block('page', 'mainBlock', 'main');
            $template->set_var('DISPLAY_REMEMBER_ME', ($this->remember_me_option ? '' : 'display: none;'));

            $template->set_var(
                array(
                    'ACTION_URL' => $this->login_url,
                    'ATTEMPS' => $this->get_session('ATTEMPS'),
                    'USERNAME' => $this->username,
                    'USERNAME_FIELDNAME' => $this->username_fieldname,
                    'PASSWORD_FIELDNAME' => $this->password_fieldname,
                    'MESSAGE' => $this->message,
                    'INTERFACE_DIR_URL' =>  ADMIN_URL.'/interface',
                    'MAX_USERNAME_LEN' => $this->max_username_len,
                    'MAX_PASSWORD_LEN' => $this->max_password_len,
                    'ADMIN_URL' => ADMIN_URL,
                    'WB_URL' => WB_URL,
                    'URL' => $this->redirect_url,
                    'THEME_URL' => THEME_URL,
                    'VERSION' => VERSION,
                    'REVISION' => REVISION,
                    'LANGUAGE' => strtolower(LANGUAGE),
                    'FORGOTTEN_DETAILS_APP' => $this->forgotten_details_app,
                    'WEBSITE_TITLE'       => ($aWebsiteTitle['value']),
                    'TEXT_ADMINISTRATION' => $TEXT['ADMINISTRATION'],
//                    'TEXT_FORGOTTEN_DETAILS' => $Trans->TEXT_FORGOTTEN_DETAILS,
                    'TEXT_USERNAME' => $TEXT['USERNAME'],
                    'TEXT_PASSWORD' => $TEXT['PASSWORD'],
                    'TEXT_REMEMBER_ME' => $TEXT['REMEMBER_ME'],
                    'TEXT_LOGIN' => $TEXT['LOGIN'],
                    'TEXT_SAVE' => $TEXT['SAVE'],
                    'TEXT_RESET' => $TEXT['RESET'],
                    'TEXT_HOME' => $TEXT['HOME'],
                    'PAGES_DIRECTORY' => PAGES_DIRECTORY,
                    'SECTION_LOGIN' => $MENU['LOGIN'],
                    'LOGIN_DISPLAY_HIDDEN'   => !$this->is_authenticated() ? 'hidden' : '',
                    'LOGIN_DISPLAY_NONE'     => !$this->is_authenticated() ? 'none' : '',
                    'LOGIN_LINK'             => $_SERVER['SCRIPT_NAME'],
                    'LOGIN_ICON'             => 'login',
                    'START_ICON'             => 'blank',
                    'URL_HELP'               => 'http://wiki.websitebaker.org/',
                    )
            );
            $template->set_var($aLang);
            $template->set_var('CHARSET', (defined('DEFAULT_CHARSET') ? DEFAULT_CHARSET : 'utf-8'));
            $template->parse('main', 'mainBlock', false);
            $template->pparse('output', 'page');
        }
    }

    // sanities the REMEMBER_KEY cookie to avoid SQL injection
    protected function get_safe_remember_key()
    {
        $iMatches = 0;
        if (isset($_COOKIE['REMEMBER_KEY'])) {
            $sRetval = preg_replace('/^([0-9]{11})_([0-9a-f]{11})$/i', '\1\2', $_COOKIE['REMEMBER_KEY'], -1, $iMatches);
        }
        return ($iMatches ? $sRetval : '');
    }

    // Warn user that they have had to many login attemps
    protected function warn()
    {
//      header('Location: '.$this->warning_url);
        $this->send_header($this->warning_url);
        exit;
    }

}
