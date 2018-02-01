<?php

require_once(__DIR__.'/lib.class.auth.php');

$action = $_POST['action'];

require_once(WB_PATH."/framework/class.admin.php");
$admin = new admin('Start', '', false, false);
$clsModAuth = new ModAuth(null, null);

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    if ($action === 'confirm_email') {
        
        $code = preg_replace("/[^A-F0-9]+/", '', $_GET['code']);
    
        echo "<!doctype html><html>
        <head>
            <meta charset='utf-8'>
            <title>Подтверждение регистрации</title>
        </head>
        <body>";
    
        $sql = "SELECT * FROM `".TABLE_PREFIX."users` WHERE `confirm_reg`='$code'";
        $result = $database->query($sql);
        if ($database->is_error()) echo $database->get_error();
        else if ($result->numRows() == 0) echo "Пользователь не найден: возможно, email был подтверждён ранее.";
        else {
                $user = $result->fetchRow();
                if ($database->is_error() && $r === false) echo $database->get_error();
                else $database->query("UPDATE `".TABLE_PREFIX."users` SET `confirm_reg`='1' WHERE `confirm_reg`='$code'");
        
                echo "Email успешно подтверждён. Теперь Вы можете использовать Ваши логин и пароль для входа в личный кабинет.";
        }
        echo "<br><br><a href='".WB_URL."'>На главную</a>";
                
        echo "</body>
        </html>";
        
    } else if ($action == "logout") {

        $clsModAuth->logout();
        print_success('Успешно!', ['location'=>WB_URL]);

    } else if ($action == "confirm_repair") {
        
        $code = preg_replace("/[^A-F0-9]+/", '', $_GET['code']);
    
        echo "<!doctype html><html>
        <head>
            <meta charset='utf-8'>
            <title>Подтверждение регистрации</title>
        </head>
        <body>";
    
        $sql = "SELECT * FROM `".TABLE_PREFIX."users` WHERE `confirm_repair`='$code'";
        $result = $database->query($sql);
        if ($database->is_error()) echo $database->get_error();
        else if ($result->numRows() == 0) echo "Код подтверждения не найден.";
        else {
            $user = $result->fetchRow();
            $r = $database->query("UPDATE `".TABLE_PREFIX."users` SET `confirm_repair`='', `new_password`='', `password`='{$user['new_password']}' WHERE `confirm_repair`='$code'");
            if ($database->is_error() && $r === false) echo $database->get_error();
            else echo "Пароль успешно изменён. Теперь Вы можете использовать Ваши логин и новый пароль для входа в личный кабинет.";
        }
        echo "<br><br><a href='".WB_URL."'>На главную</a>";
                
        echo "</body>
        </html>";
    }
    die();
}

if ($action == "login") { // совпадает с action, требуемым классом Login

    if ($admin->is_authenticated()) print_error("Вы уже вошли на сайт!");

    include(__DIR__."/Login_init.php");

    //$username = $clsFilter->f($thisApp->username_fieldname, [['1', 'Введите свой логин!']], 'append', null);
    //$password = $clsFilter->f($thisApp->password_fieldname, [['1', 'Введите свой пароль!']], 'append', null);
    //if ($clsFilter->is_error()) $clsFilter->print_error();

    $message = $thisApp->getMessage();
    if ($message) print_error($message);

    print_success("Успешно!", ['location'=>WB_URL]);

    
} else if ($action == "reg") {

    if ($admin->is_authenticated()) print_error("Вы уже вошли на сайт!");

    if ($admin->is_authenticated()) print_error('Вы уже зарегистрированы!');

    $clsFilter->f('accept', [['variants', 'Для регистрации необходимо согласиться с соглашением!', ['true']]]);

    $groups_id = "1";
    $active = "1";
    $username_fieldname = $admin->get_post_escaped('username_fieldname');
    $username = strtolower($clsFilter->f($username_fieldname, [['1', 'Укажите login!']], 'append'));
    $password = $clsFilter->f('password', [['1', 'Укажите пароль!']], 'append');
    $password2 = $clsFilter->f('password2', [['1', 'Повторите пароль!']], 'append');
    $display_name = $username;
    $email = $clsFilter->f('email', [['1', $MESSAGE['SIGNUP']['NO_EMAIL']]], 'append');;
    $home_folder = "/home/".$username;
    $default_language = DEFAULT_LANGUAGE;
    $name = $clsFilter->f('name', [['1', 'Не указано имя!']], 'append');;
    $surname = $clsFilter->f('surname', [['1', 'Не указана фамилия!']], 'append');
    $additional_info = $_POST['is_send_password'] == 'true' ? "Пароль: $password</span><br>" : '';

    $clsFilter->f('captcha', [['1', "Введите Защитный код!"], ['variants', "Введите Защитный код!", [$_SESSION['captcha']]]], 'append', '');
        
    if ($clsFilter->is_error()) $clsFilter->print_error();

    if (!preg_match('/^[a-z]{1}[a-z0-9_-]{2,}$/i', $username)) { $clsFilter->add_error($MESSAGE['USERS_NAME_INVALID_CHARS'].' / '. $MESSAGE['USERS_USERNAME_TOO_SHORT']);
    } else if (strlen($password) < 2) { $clsFilter->add_error($MESSAGE['USERS']['PASSWORD_TOO_SHORT'], 'password');
    } else if ($password != $password2) $clsFilter->add_error($MESSAGE['USERS']['PASSWORD_MISMATCH'], 'password2');

    if ($admin->validate_email($email) == false) $clsFilter->add_error($MESSAGE['USERS']['INVALID_EMAIL']);

    $message = $clsModAuth->create_user($groups_id, $active, $username, $password, $display_name, $email, $home_folder, $default_language, $name, $surname, 0, $additional_info);
    if ($message !== true) print_error($message);

    print_success("<br>Вы успешно зарегистрированы!<br>Перейдите по ссылке в письме, отправленном на Ваш email <br>", ['timeout'=>0]);

} else if ($action == "repair") {

    if ($admin->is_authenticated()) print_error("Вы уже вошли на сайт!");

    $email = $clsFilter->f('email', [['1', 'Укажите e-mail!']], 'append');
    $password = $clsFilter->f('password', [['1', 'Укажите пароль!']], 'append');
    $password2 = $clsFilter->f('password2', [['1', 'Повторите пароль!'], ['variants', 'Пароли должны совпадать!', [$password]]], 'append');
    $clsFilter->f('captcha', [['1', "Введите Защитный код!"], ['variants', "Введите Защитный код!", [$_SESSION['captcha']]]], 'append', '');
    if ($clsFilter->is_error()) $clsFilter->print_error();

    $r = $clsModAuth->repair_password($email, $password);
    if ($r !== true) print_error($r);

    print_success('На Ваш Email выслано письмо с подтверждением.');

} else if ($action == "save") {

    $fields = [
        'user_group' => $clsFilter->f('user_group', [['integer', 'Укажите группу пользователей!']], 'append')
    ];
    if ($clsFilter->is_error()) $clsFilter->print_error();
    
    $r = update_row($clsModAuth->tbl_auth, $fields, '`settings_id`=1');
    if ($r !== true) print_error($r);

    print_success($MESSAGE['SETTINGS_SAVED']."!");

} else { print_error("Wrong api name!"); }

?>