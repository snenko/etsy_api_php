<?php
class Assignment extends DBEntity {
    private const columns = ['id', 'userId', 'shopId', 'type', 'argument'];
    
    /**
     * @param array $array
     * @return int
     * @throws Exception
     */
    public static function add($array) {
        //todo check rights
        $userId = (int)$array['userId'];
        $shopId = (int)$array['shopId'];
        $result = self::query(
            "SELECT id FROM `User` WHERE id = $userId UNION ALL SELECT id FROM `Shop` WHERE id = $shopId");
        if ($result->num_rows < 2) throw new Exception("User($userId) OR shop($shopId) doesn't exist");
        $result = self::query("SELECT id FROM `Assignment` WHERE userId = $userId AND shopId = $shopId");
        if ($result->num_rows > 0) return true;
        else return parent::add($array);
    }
    
    /**
     * @throws Exception
     */
    public function delete() {
        //todo check rights
        parent::delete();
    }
    
    /**
     * @param $id
     * @return array
     * @throws Exception
     */
    public static function getByUser($id) {
        $id = (int)$id;
        return self::query("SELECT * FROM Assignment WHERE userId = $id")->fetch_all(MYSQLI_ASSOC);
    }
    /**
     * @param $id
     * @return array
     * @throws Exception
     */
    public static function getByShop($id) {
        $id = (int)$id;
        return self::query("SELECT * FROM Assignment WHERE shopId = $id")->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * @param $shopId
     * @return mixed
     * @throws Exception
     * @throws NotPassedCheckException
     */
    public static function getRulesByShop($shopId) {
        if (!self::_checkValue('shopId', $shopId)) throw new NotPassedCheckException('shopId', $shopId);
        $result = self::query(
            "SELECT `Assignment`.id as id, `User`.login, Assignment.type as type, Assignment.argument as argument\n".
            "FROM Assignment\n".
            "LEFT JOIN `User` ON userId = `User`.id\n".
            "WHERE Assignment.shopId = $shopId AND `User`.role = 'MANAGER'");
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * @param string $name
     * @param mixed $value
     * @return bool
     */
    public static function _checkValue($name, $value) {
        if (array_search($name, self::columns) === false) return false;
        else {
            switch ($name) {
                case 'id':
                case 'shopId':
                case 'userId':
                    return preg_match('/^\d+$/', $value) == 1;
                case 'type':
                    return array_search($value, ['ORDER', 'TIME', 'COUNT', null]) !== false;
                case 'argument':
                    return preg_match('/^[-\d:\/]*$/', $value) == 1;
            }
        }
    }
}