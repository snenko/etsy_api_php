<?php
require '_/_.php';
ob_start();
$return = ['result' => ''];
try {
    if (isset($_POST['hash'])) {
        Session::load($_POST['hash']);
        if (!Session::hasAuth()) $return['result'] = 'AUTH_EXPIRED';
    }
    if (!$return['result']) switch ($_POST['action']) {
        case 'getGlobals':
            $return['result'] = [
                'User' => ['regex' => User::regex],
                'Shop' => ['regex' => Shop::regex],
                'Customer' => ['regex' => Customer::regex],
                'debug' => DEBUG,
            ];
            break;
        case 'checkLogin':
            try {
                Session::load($_POST['login'], $_POST['password']);
                if (Session::hasAuth()) {
                    $return['result'] = [
                        'hash' => Session::hash(),
                        'role' => Session::role(),
                        'mustChangePassword' => Session::mustChangePassword()
                    ];
                }
                else $return['result'] = false;
            }
            catch (NoSuchUserException $e) {$return['result'] = 'NO_SUCH_USER';}
            catch (WrongPasswordException $e) {$return['result'] = 'WRONG_PASSWORD';}
            break;
        case 'changeOwnPassword':
            if (Session::hasAuth())
                $return['result'] = Session::getUser()
                    ->change(['password' => $_POST['password'], 'mustChangePassword' => false]);
            else throw new Exception("No auth");
            break;
        case 'destroySession':
            Session::destroy();
            break;
        case 'checkAuth':
            $return['result'] =
                (Session::hasAuth() && $_POST['role'] == Session::role() && $_POST['login'] == Session::login());
            break;
        case 'addEntity':
            try {
                $class = ($_POST['type']);
                /** @var DBEntity $class */
                $return['result'] = [
                    'id' => $class::add($_POST['data']),
                    'values' => [
                        'created' => DBUser::query("SELECT NOW()")->fetch_row()[0]
                    ]
                ];
            }
            catch (InputException $E) {
                $return['result'] = $E->arr();
            }
            break;
        case 'getEntity':
            $Obj = ($_POST['type'])::byId($_POST['id']);
            /** @var DBEntity $Obj */
            if ($Obj) $return['result'] = $Obj->asArray();
            else throw new Exception("Object {$_POST['type']} with id = {$_POST['id']} not found");
            break;
        case 'editEntity':
            $Obj = ($_POST['type'])::byId($_POST['id']);
            /** @var DBEntity $Obj */
            if ($Obj) {
                try {
                    $return['result'] = $Obj->change($_POST['data']);
                } catch (InputException $E) {
                    $return['result'] = $E->arr();
                }
            }
            else throw new Exception("Object {$_POST['type']} with id = {$_POST['id']} not found");
            break;
        case 'deleteEntity':
            $Obj = ($_POST['type'])::byId($_POST['id']);
            /** @var DBEntity $Obj */
            if ($Obj) $return['result'] = $Obj->delete();
            else throw new Exception("Object {$_POST['type']} with id = {$_POST['id']} not found");
            break;
        case 'getActiveCustomers':
            $return['result'] = Customer::getActive();
            break;
        case 'getList':
            $return['result'] = ($_POST['type'])::getArrayList();
            break;
        case 'getShortList':
            $return['result'] = ($_POST['type'])::getShortList();
            break;
        case 'getAssignmentsRules':
            $return['result'] = Assignment::getRulesByShop($_POST['shopId']);
            break;
        case 'setAssignmentsRules':
            foreach ($_POST['data'] as $id => $array) {
                $Assignment = Assignment::byId($id);
                $Assignment->change($array);
            }
            break;
        default:
            throw new Exception("Wrong action '" . $_POST['action']."'");
    }
    if (!isset($return['result'])) $return['result'] = true;
}
catch (Exception $e) {
    $return['error'] = 'Exception: '.$e->getMessage().' in '.$e->getFile().'('.$e->getLine().')'
        ."\nTrace:\n"
        .$e->getTraceAsString()
        .' '.$e->getFile().'('.$e->getLine().')';
    print_r($_POST);
}
catch (Error $e) {
    $return['error'] = 'Error: '.$e->getMessage().'in '.$e->getFile().'('.$e->getLine().')'
        ."\nTrace:\n"
        .$e->getTraceAsString()
        .' '.$e->getFile().'('.$e->getLine().')';
    print_r($_POST);
}
finally {
    $output = ob_get_contents();
    if ($output) $return['output'] = $output;
    ob_end_clean();
    echo json_encode($return);
}
