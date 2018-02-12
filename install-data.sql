INSERT INTO `{TABLE_PREFIX}mod_wbs_core_templates_of_letter` (`letter_template_name`, `letter_template_body`, `letter_template_subject`, `letter_template_description`) VALUES
('success_registration', '    <p style=\'text-align:left;\'>         <h1>{{SURNAME}} {{NAME}},</h1>         Поздравляем Вас с успешной регистрацией на всероссийском портале Инфо-РФ.РФ!<br><br>         Пожалуйста, перейдите по ссылке <a href=\'{{URL}}\'>{{URL}}</a> для подтверждения email-адреса.     </p><br>      <p>     Регистрационные данные:<br>          Логин: {{USERNAME}}<br>     Email: {{EMAIL}}<br><br>     </p> {{ADDITIONAL_INFO}}         <p>После подтверждения email-адреса Вы сможете в красках рассказать о своей организации всему русскоязычному населению.</p>          <p>С уважением, команда Инфо-РФ.РФ.</p>', 'Регистрация на портале Инфо-РФ.РФ', 'Письмо модуля wbs_auth'),
('repair_password', '<p>Здравствуйте, {{SURNAME}} {{NAME}}!</p><p>Для подтверждения смены пароля перейдите по ссылке: <a href="{{URL}}">{{URL}}</a></p><p>Если Вы не запрашивали смену пароля, просто проигнрируйте это письмо.</p>', 'Восстановление пароля', 'Письмо модуля wbs_auth');

INSERT INTO `{TABLE_PREFIX}mod_wbs_auth` (`user_group`, `message_authed`, `css_form`, `html_form`, `use_smart_login`) VALUES
(1, 'Вы уже вошли на сайт. Форма входа и регистрации недоступна', '', '', 1);