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

include(WB_PATH."/modules/wbs_core/include_all.php");
require_once(WB_PATH.'/include/captcha/captcha.php');
if(function_exists('wbs_core_include')) wbs_core_include(['functions.js', 'windows.js', 'windows.css', 'effects.css']);

if (!$admin->is_authenticated()) {

    include(__DIR__."/Login_init.php");

    $loader = new Twig_Loader_Array(array(
        'view' => file_get_contents(__DIR__.'/view.html'),
    ));
    $twig = new Twig_Environment($loader);
    
    echo $twig->render('view', [
        'message'=>$thisApp->getMessage(),
        'app'=>$thisApp,
    ]);

} else {

    echo "Вы уже вошли на сайт. Форма входа и регистрации недоступна.";

}