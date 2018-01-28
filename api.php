<?php

require_once(__DIR__.'/lib.class.auth.php');

$action = $_POST['action'];

require_once(WB_PATH."/framework/class.admin.php");
$admin = new admin('Start', '', false, false);
$clsModAuth = new ModAuth(null, null);

if ($admin->is_authenticated()) print_error("Вы уже вошли на сайт!");

if ($action == "login") { // совпадает с action, требуемым классом Login

    include(__DIR__."/Login_init.php");

    //$username = $clsFilter->f($thisApp->username_fieldname, [['1', 'Введите свой логин!']], 'append', null);
    //$password = $clsFilter->f($thisApp->password_fieldname, [['1', 'Введите свой пароль!']], 'append', null);
    //if ($clsFilter->is_error()) $clsFilter->print_error();

    $message = $thisApp->getMessage();
    if ($message) print_error($message);

    print_success("Успешно!", ['location'=>WB_URL]);

    
} else if ($action == "reg") {
    
    print_success("Успешно!");

} else if ($action == "forgot") {
    
} else { print_error("Неверный api name!"); }

?>