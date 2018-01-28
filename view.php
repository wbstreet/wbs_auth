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

?>

<div class="tab_headers">
    <span>Войти</span>
    <span>Зарегистрироваться</span>
</div>

<div class="tab_bodies">
    <form>
        <table class="adaptive_table">
            <tr>
                <td>Логин:</td>
                <td> <input type="text" name="username" value=""> </td>
            </tr><tr>
                <td>Пароль:</td>
                <td> <input type="password" name="password" value=""> </td>
            </tr><tr>
                <td colspan="2"> <input type="button" value="Войти" onclick="sendform()"> </td>
            </tr>
        </table>
    </form>

    <form>
        <table class="adaptive_table">
           <tr>
               <td>Логин:</td>
               <td> <input type="text" name="username" value=""> </td>
            </tr><tr>
                <td>E-mail:</td>
                <td> <input type="text" name="email" value=""> </td>
            </tr><tr>
                <td>Пароль:</td>
                <td> <input type="password" name="password" value=""> </td>
            </tr><tr>
                <td>Повторите пароль:</td>
                <td> <input type="password" name="password2" value=""> </td>
            </tr><tr>
                <td colspan="2"> <input type="button" value="Зарегистрироваться" onclick="sendform()"> </td>
            </tr>
        </table>
    </form>
</div>

<style>
    .tab_bodies:nth-child(n+1) {
        display: none
    }
    .tab_bodies:first-child {
        display: block
    }
</style>

<script>
    $('.tab_headers')
</script>