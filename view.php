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
include(__DIR__."/Login_init.php");

if (!$admin->is_authenticated()) {

?>

<style>
    .tab_bodies > form:nth-child(n+2) {
        display: none
    }
    .tab_bodies > form {
        border: 1px solid #bbbbbb;
        border-radius: 5px;
    }
    .tab_headers > .tab_header_active {
        background: #dddddd;
    }
    .tab_headers > span {
        cursor: pointer;
    }
</style>

<div class="tab_headers">
    <span data-name="login">Войти</span>
    <span data-name="reg">Зарегистрироваться</span>
</div>

<div class="tab_bodies">

    <form id="tab_login">
        <?php echo $thisApp->getMessage(); ?>

    	<!--<input type="hidden" name="url" value="/index.php" />-->
    	<input type="hidden" name="username_fieldname" value="<? echo $thisApp->username_fieldname ?>" />
    	<input type="hidden" name="password_fieldname" value="<? echo $thisApp->password_fieldname ?>" />

        <table class="adaptive_table">
            <tr>
                <td>Логин:</td>
                <td> <input type="text" value="" name="<? echo $thisApp->username_fieldname ?>" maxlength="<? echo $thisApp->max_username_len ?>"> </td>
            </tr><tr>
                <td>Пароль:</td>
                <td> <input type="password" value="" name="<? echo $thisApp->password_fieldname ?>" maxlength="<? echo $thisApp->max_password_len ?>"> </td>
            </tr><tr>
                <td colspan="2"> <input type="button" value="Войти" onclick="sendform(this, 'login', {url:WB_URL+'/modules/wbs_auth/api.php', func_after_success: function() {/*window.location='';*/}})"> </td>
            </tr>
        </table>
      	<input type="checkbox" name="remember" value="true" /> Запомнить меня
    </form>

    <form id="tab_reg">
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
                <td colspan="2"> <input type="button" value="Зарегистрироваться" onclick="sendform(sendform(this, 'reg', {url:WB_URL+'/modules/wbs_auth/api.php'}))"> </td>
            </tr>
        </table>
    </form>

    <form id="tab_forgot">
        <table class="adaptive_table">
        </table>
    </form>
</div>

<script>
    "use strict";
    
    class Tabs2 {
        constructor(headers, bodies) {
            this.headers = headers;

            this.cur_header = this.headers.children.length > 0 ? this.headers.children[0] : null;
            if (this.cur_header) this.cur_header.classList.add('tab_header_active')

            this.ev_header_click = this.ev_header_click.bind(this);
            
            for (let header of this.headers.children) {
                header.addEventListener("click", this.ev_header_click);
            }
        }
        
        ev_header_click(e) {
            let body, header = e.target;

            if (this.cur_header) {
                body = document.getElementById("tab_"+this.cur_header.dataset.name)
                body.style.display = "none";
                this.cur_header.classList.remove('tab_header_active')
            }

            this.cur_header = header;

            body = document.getElementById("tab_"+this.cur_header.dataset.name)
            body.style.display = "block";
            this.cur_header.classList.add('tab_header_active')
            
        }
        
    }

   let tab = new Tabs2(document.querySelector('.tab_headers'), document.querySelector('.tab_bodies'));

    //$('.tab_headers').children().click(function());
</script>

<?php } else { ?>

Вы уже вошли на сайт. Форма входа и регистрации недоступна.

<?php } ?>