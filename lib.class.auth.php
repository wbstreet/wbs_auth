<?php

$path_core = __DIR__.'/../wbs_core/include_all.php';
if (file_exists($path_core )) include($path_core );
else echo "<script>console.log('Модуль wbs_auth требует модуль wbs_core')</script>";

if (!class_exists('ModAuth')) {
class ModAuth extends Addon {

    function __construct($page_id, $section_id) {
        parent::__construct('auth', $page_id, $section_id);
    }
}
}
?>