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

include(__DIR__.'/lib.class.auth.php');
$clsModAuth = new ModAuth($page_id, $section_id);

$settings = $database->query("SELECT * FROM {$clsModAuth->tbl_auth} WHERE `settings_id`=1");
if ($database->is_error()) { $clsModAuth->print_error($database->get_error()); $settings = null; }
else if ($settings->numRows() === 0) { $clsModAuth->print_error("Настройки не найдены!"); $settings = null; }
else $settings = $settings->fetchRow();

if(function_exists('wbs_core_include')) wbs_core_include(['functions.js', 'windows.js', 'windows.css']);
?>
<form>
    
    <table>
        <tr>
            <td>В какую группу будут входить зарегистрированные пользователи?</td>
            <td><input type="text" name="user_group" value="<?php echo $settings !== null ? $settings['user_group'] : ''; ?>"></td>
        </tr>
        <tr>
            <td colspan="2">Сообщение для авторизованных пользователей вместо формы:</td>
        </tr>
        <tr>
            <td colspan="2"><textarea style="width:100%;" name="message_authed"><?php echo $settings !== null ? $settings['message_authed'] : ''; ?></textarea></td>
        </tr>
    </table>

    <input type="button" value="Сохранить" onclick="sendform(this, 'save', {url: WB_URL+'/modules/wbs_auth/api.php'});">

</form>