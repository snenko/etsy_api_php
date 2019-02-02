<?php
class Session extends DBUser {
    /** @var User $User */
    private static $User = null, $hash = null;
    private const hashLegnth = 32;
    
    /**
     * @throws Exception
     * @throws NoSuchUserException
     * @throws WrongPasswordException
     */
    public static function load() {
        $args = func_get_args();
        if (count($args) === 0 || count($args) > 2) throw new Exception("Need 1 OR 2 arguments");
        elseif (count($args) === 1) {
            $hash = preg_replace('/[^\d\w]/', '', $args[0]);
            $result = self::query("SELECT `User`.id FROM `User`\n".
                "LEFT JOIN `Session` ON `Session`.userId = `User`.id\n".
                      "WHERE `Session`.hash = '$hash'");
            if ($result->num_rows > 0) {
                self::$User = User::byId($result->fetch_row()[0]);
                self::$hash = $hash;
                self::query("UPDATE `Session` SET lastAction = NOW() WHERE `hash` = '$hash'");
            }
            else {
                self::query("DELETE FROM `Session` WHERE `hash` = '$hash'");
            }
        }
        elseif (count($args) === 2) {
            self::$User = User::byAuth($args[0], $args[1]);
            self::query("DELETE FROM `Session` WHERE userId = " . Session::id());
            self::$hash = self::generateHash();
            $hash = self::$hash;
            $userId = self::$User->id;
            self::query("INSERT INTO `Session` (`hash`, userId) VALUES ('$hash', $userId)");
        }
    }
    
    public static function destroy() {
        $hash = self::$hash;
        self::query("DELETE FROM `Session` WHERE `hash` = '$hash'");
    }
    
    private static function generateHash() {
        $symbols = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $max = strlen($symbols) - 1;
        $hash = '';
        while (strlen($hash) < self::hashLegnth) $hash .= $symbols[rand(0, $max)];
        return $hash;
    }
    
    public static function hasAuth() {
        return is_object(self::$User);
    }
    
    public static function hash() {
        return self::$hash;
    }
    
    public static function role() {
        if (is_object(self::$User)) return self::$User->get('role');
        else return false;
    }
    
    public static function id() {
        if (is_object(self::$User)) return self::$User->get('id');
        else return false;
    }
    
    public static function login() {
        if (is_object(self::$User)) return self::$User->get('login');
        else return false;
    }
    
    public static function mustChangePassword() {
        if (is_object(self::$User)) return (bool)self::$User->get('mustChangePassword');
        else return false;
    }
    
    public static function getUser() {
        return self::$User;
    }
    
    /**
     * @throws Exception
     */
    public static function debugMode() {
        if (!DEBUG) throw new Exception("Not debug");
        self::$User = new User(['role' => 'SUPER']);
    }
}