<?php
echo date('Z');
for ($x = 1; $x < 10; $x++) {
    echo $x;
}
exit;
require '_/_.php';
if (!DEBUG) exit;
Session::debugMode();
if (isset($_GET['action'])) switch ($_GET['action']) {
    case 'createSuperAdminUser':
        DBUser::query("DELETE FROM `User` WHERE id = 1");
        $result = User::add([
            'id' => 1,
            'role' => 'SUPER',
            'login' => 'superadmin',
            'password' => 'qqqqqq',
            'mustChangePassword' => false
        ]);
        print_r($result);
        break;
}
?>
<a href="?action=createSuperAdminUser">Create super admin (pwd qqqqqq)</a>