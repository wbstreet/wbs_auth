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

// create tables from sql dump file
if (is_readable(__DIR__.'/install-struct.sql')) {
    $r = $database->SqlImport(__DIR__.'/install-data.sql', TABLE_PREFIX, __FILE__ );
    if ($database->is_error()) {
        $admin->print_error($database->get_error());
    }
}

?>