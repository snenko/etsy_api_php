<?php
class Customer extends DBEntity {
    protected const columns = ['id', 'created', 'name', 'userId', 'isActive'];
    protected const arrayValues = ['id', 'name', 'created'];
    protected $id, $created, $name;
    
    public const regex = [
        'name' => '/^[-_\s\d\wа-яА-Я\.]+$/'
    ];
    
    public static function _checkValue($name, $value) {
        if (array_search($name, self::columns) === false) return false;
        else {
            switch ($name) {
                case 'id':
                    return preg_match('/^\d+$/', $value) == 1;
                case 'userId':
                    return $value == null || preg_match('/^\d+$/', $value) == 1;
                case 'created':
                    return preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $value) == 1;
                case 'name':
                    return preg_match(self::regex['name'].'u', $value) == 1;
                case 'isActive':
                    return $value == 0 || $value == 1;
            }
        }
    }
    
    public static function getArrayList() {
        $result = self::query(
            "SELECT Customer.*, User.login as login
               FROM `Customer` LEFT JOIN `User` ON Customer.userId = `User`.id
               WHERE isActive = 1 ORDER BY id ASC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * @return array
     * @throws Exception
     */
    public static function getShortList() {
        $result = self::query("SELECT id, `name`, userId FROM `Customer` WHERE 1 ORDER BY id ASC");
        $return = [];
        while ($row = $result->fetch_row()) $return[$row[0]] = $row[1];
        return $return;
    }
    
    /**
     * @param $id
     * @return array
     * @throws Exception
     */
    public static function getByUser($id) {
        $id = (int)$id;
        return self::query("SELECT * FROM Customer WHERE userId = $id")->fetch_all(MYSQLI_ASSOC);
    }
    
    public static function getActive() {
        return self::query("SELECT id, name, userId FROM Customer WHERE isActive = 1 ORDER BY id ASC")
            ->fetch_all(MYSQLI_ASSOC);
    }
}