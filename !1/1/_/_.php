<?php
require '_conf.php';
spl_autoload_register(function ($class_name) {include "$class_name.php";});
$timezone = 'Etc/GMT';
if (TIMEZONE_OFFSET !== 0) $timezone .= (TIMEZONE_OFFSET > 0 ? '+' : '-') . TIMEZONE_OFFSET;
date_default_timezone_set($timezone);