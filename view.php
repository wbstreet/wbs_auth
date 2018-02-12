<?php
/**
 *
 * @category        module
 * @package         wbs_auth
 * @author          Konstantin Polyakov
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.10.0
 * @requirements    PHP 5.2.2 and higher
 *
 */

if(!defined('WB_PATH')) {
        require_once(dirname(dirname(__FILE__)).'/framework/globalExceptionHandler.php');
        throw new IllegalFileException();
}

include(WB_PATH."/modules/wbs_auth/lib.class.auth.php");
require_once(WB_PATH.'/include/captcha/captcha.php');
if(function_exists('wbs_core_include')) wbs_core_include(['functions.js', 'windows.js', 'windows.css', 'effects.css']);

$clsModAuth = new ModAuth($section_id, $page_id);

$settings = null;
$r = select_row($clsModAuth->tbl_auth, '*', '`settings_id`=1');
if (gettype($r) === 'string') $clsModAuth->print_error($r);
else if ($r === null) $clsModAuth->print_error('Настройки модуля Auth не найдены!');
else { $settings = $r->fetchRow();}


if (!$admin->is_authenticated()) {

    include(__DIR__."/Login_init.php");

    $clsModAuth->render('view.html', [
        'message'=>$thisApp->getMessage(),
        'app'=>$thisApp,
    ]);

} else {

    echo $settings ? $settings['message_authed'] : '';

}