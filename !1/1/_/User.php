<?php
class User extends DBEntity {
    private const salt = "TPixAs4Z";
    private const roles = ['SUPER', 'ADMIN', 'MANAGER'];
    public const regex = [
        'login' => '/^[-_\w\dа-яА-Я]{4,32}$/',
        'password' => '/^[-_\w\d]{6,}$/',
        'percent' => '/^\d[\d\.]*$/'
    ];
    protected const columns = ['id', 'role', 'created', 'login', 'passwordHash', 'salt',
        'mustChangePassword', 'percent'];
    public $id, $role, $created, $login, $mustChangePassword, $percent;
    
    /**
     * @param $password
     * @param $salt
     * @return mixed
     */
    private static function hashPassword($password, $salt) {
        return explode('$', crypt($password, '$6$'.$salt.self::salt))[3];
    }
    
    /**
     * @return string
     * @throws Exception
     */
    private static function generateSalt() {
        $symbols = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $max = strlen($symbols) - 1;
        $return = '';
        while (strlen($return) < 8) $return .= $symbols[random_int(0, $max)];
        return $return;
    }
    
    /**
     * @param $login
     * @param $password
     * @return User
     * @throws Exception
     * @throws NoSuchUserException
     * @throws WrongPasswordException
     */
    public static function byAuth($login, $password) {
        if (!self::_checkValue('login', $login)) throw new NotPassedCheckException('login', $login);
        elseif (!self::_checkValue('password', $password)) throw new NotPassedCheckException('password', $password);
        else {
            $result = self::query("SELECT * FROM `User` WHERE BINARY login = '$login'");
            if ($result->num_rows == 0) throw new NoSuchUserException();
            else {
                $array = $result->fetch_assoc();
                if (hash_equals($array['passwordHash'], self::hashPassword($password, $array['salt'])))
                    return new User($array);
                else throw new WrongPasswordException();
            }
        }
    }
    
    /**
     * @param $array
     * @return bool|void
     * @throws Exception
     * @throws LoginNotUniqueException
     * @throws NotPassedCheckException
     */
    public function change($array) {
        //todo check rights
        if (isset($array['login']) && !self::isNameUnique($array['login'], $this->id))
            throw new LoginNotUniqueException();
        if (isset($array['password'])) {
            if (!self::_checkValue('password', $array['password']))
                throw new NotPassedCheckException('password', $array['password']);
            $array['salt'] = self::generateSalt();
            $array['passwordHash'] = self::hashPassword($array['password'], $array['salt']);
            unset($array['password']);
        }
        if (isset($array['assignments'])) {
            foreach ($array['assignments'] as $shopId => $isAssigned) {
                if ($isAssigned) Assignment::add(['userId' => $this->id, 'shopId' => $shopId]);
                else  Assignment::deleteByData(['userId' => $this->id, 'shopId' => $shopId]);
            }
            unset($array['assignments']);
        }
        if (isset($array['customers'])) {
            foreach ($array['customers'] as $customerId => $isAssigned) {
                $Customer = Customer::byId($customerId);
                $Customer->change(['userId' => (int)$isAssigned ? (int)$_POST['id'] : NULL]);
            }
            unset($array['customers']);
        }
        if (count($array) > 0) parent::change($array);
    }
    
    /**
     * @param array $array
     * @return int
     * @throws Exception
     */
    public static function add($array) {
        //todo check rights
        if (!self::isNameUnique($array['login'])) throw new LoginNotUniqueException();
        if (isset($array['password'])) {
            $password = $array['password'];
            if (!self::_checkValue('password', $password))
                throw new Exception("password = '$password' did not pass check");
            unset($array['password']);
        }
        else $password = self::generateSalt();
        $array['salt'] = self::generateSalt();
        $array['passwordHash'] = self::hashPassword($password, $array['salt']);
        if (isset($array['assignments'])) {
            $assignments = $array['assignments'];
            unset($array['assignments']);
        }
        if (isset($array['mustChangePassword']))
            $array['mustChangePassword'] = $array['mustChangePassword'] == 'true' ? 1 : 0;
        $id = parent::add($array);
        if (isset($assignments)) {
            foreach ($assignments as $shopId => $isAssigned) {
                if ($isAssigned) Assignment::add(['userId' => $id, 'shopId' => $shopId]);
                else  Assignment::deleteByData(['userId' => $id, 'shopId' => $shopId]);
            }
        }
        return $id;
    }
    
    /**
     * @return array
     * @throws Exception
     */
    public static function getArrayList() {
        $result = self::query("
                          SELECT id, role, created, login, mustChangePassword, percent
                          FROM `User`
                          WHERE role != 'SUPER'
                          ORDER BY id ASC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    /**
     * @return array
     * @throws Exception
     */
    public static function getShortList() {
        $result = self::query("SELECT id, login FROM `User` WHERE role != 'SUPER' ORDER BY id ASC");
        $return = [];
        while ($row = $result->fetch_row()) $return[$row[0]] = $row[1];
        return $return;
    }
    /**
     * @return array
     * @throws Exception
     */
    public function asArray() {
        $return = parent::asArray();
        unset($return['passwordHash']);
        unset($return['salt']);
        $return['assignments'] = array_map(function($v) {return (int)$v['shopId'];}, Assignment::getByUser($this->id));
        $return['customers'] = array_map(function($v) {return (int)$v['shopId'];}, Customer::getByUser($this->id));
        return $return;
    }
    
    /**
     * @param $login
     * @param null|int $id
     * @return bool
     * @throws Exception
     * @throws NotPassedCheckException
     */
    public static function isNameUnique($login, $id = null) {
        if (self::_checkValue('login', $login)) {
            $result = self::query("SELECT id FROM `User` WHERE BINARY `login` = '$login' LIMIT 1");
            if ($result->num_rows === 0) return true;
            elseif ($id !== null && $id == $result->fetch_row()[0]) return true;
            else return false;
        }
        else throw new NotPassedCheckException('name', $login);
    }
    
    /**
     * @throws Exception
     */
    public function delete() {
        //todo check rights
        Assignment::deleteByData(['userId' => $this->id]);
        parent::delete();
    }
    
    /**
     * @param string $name
     * @param mixed $value
     * @return bool
     */
    public static function _checkValue($name, $value) {
        if ($name === 'password') return preg_match(self::regex['password'], $value) == 1;
        elseif (array_search($name, self::columns) === false) return false;
        else {
            switch ($name) {
                case 'id':
                    return preg_match('/^\d+$/', $value) == 1;
                case 'role':
                    return array_search($value, self::roles) !== false;
                case 'passwordHash':
                    return strlen($value) == 86;
                case 'salt':
                    return strlen($value) == 8;
                case 'created':
                    return preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $value) == 1;
                case 'login':
                    return preg_match(self::regex['login'], $value) == 1;
                case 'mustChangePassword':
                    return is_bool($value) || $value == 1 || $value == 0;
                case 'percent':
                    return preg_match(self::regex['percent'], $value) == 1 AND $value >= 0;
            }
        }
    }
}

class NoSuchUserException extends InputException {public $txt = "Нет такого пользователя", $input = 'name';}
class WrongPasswordException extends InputException {public $txt = "Пароль неверен", $input = 'password';}
class LoginNotUniqueException extends  InputException {public $txt = "Логин пользователя неуникален", $input = 'name';}