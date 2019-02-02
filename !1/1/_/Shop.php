<?php
class Shop extends DBEntity {
    protected const columns = ['id', 'created', 'name'];
    protected const arrayValues = ['id', 'name', 'created'];
    protected $id, $created, $name;
    
    public const regex = [
        'name' => '/^[-_\s\d\wа-яА-Я]+$/'
    ];
    
    /**
     * @param array $array
     * @return int
     * @throws Exception
     * @throws InputException
     */
    public static function add($array) {
        //todo check rights
        if (!self::isNameUnique($array['name'])) throw new NameNotUniqueException();
        if (isset($array['assignments'])) {
            $assignments = $array['assignments'];
            unset($array['assignments']);
        }
        $id = parent::add($array);
        if (isset($assignments)) {
            foreach ($array['assignments'] as $userId => $isAssigned) {
                if ($isAssigned) Assignment::add(['userId' => $userId, 'shopId' => $id]);
                else  Assignment::deleteByData(['userId' => $userId, 'shopId' => $id]);
            }
        }
        return $id;
    }
    
    /**
     * @param $array
     * @return bool
     * @throws Exception
     * @throws NameNotUniqueException
     */
    public function change($array) {
        //todo check rights
        if (isset($array['name']) && !self::isNameUnique($array['name'], $this->id))
            throw new NameNotUniqueException();
        if (isset($array['assignments'])) {
            foreach ($array['assignments'] as $userId => $isAssigned) {
                if ($isAssigned) Assignment::add(['userId' => $userId, 'shopId' => $this->id]);
                else  Assignment::deleteByData(['userId' => $userId, 'shopId' => $this->id]);
            }
            unset($array['assignments']);
        }
        return parent::change($array);
    }
    /**
     * @return bool
     * @throws Exception
     */
    public function delete() {
        //todo check rights
        return parent::delete();
    }
    
    /**
     * @return array
     * @throws Exception
     */
    public function asArray() {
        $return = parent::asArray();
        $return['assignments'] = array_map(function($v) {return (int)$v['userId'];}, Assignment::getByShop($this->id));
        return $return;
    }
    
    /**
     * @return array
     * @throws Exception
     */
    public static function getShortList() {
        $result = self::query("SELECT `id`, `name` FROM `Shop` ORDER BY id ASC");
        $return = [];
        while ($row = $result->fetch_row()) $return[$row[0]] = $row[1];
        return $return;
    }
    
    /**
     * @param string $name
     * @param int|null $id
     * @return bool
     * @throws Exception
     */
    public static function isNameUnique($name, $id = null) {
        if (self::_checkValue('name', $name)) {
            $result = self::query("SELECT id FROM `Shop` WHERE BINARY `name` = '$name' LIMIT 1");
            if ($result->num_rows === 0) return true;
            elseif ($id !== null && $id == $result->fetch_row()[0]) return true;
            else return false;
        }
        else throw new NotPassedCheckException('name', $name);
    }
    
    public static function _checkValue($name, $value) {
        if (array_search($name, self::columns) === false) return false;
        else {
            switch ($name) {
                case 'id':
                    return preg_match('/^\d+$/', $value) == 1;
                case 'created':
                    return preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $value) == 1;
                case 'name':
                    return preg_match(self::regex['name'].'u', $value) == 1;
                default:
                    return true;
            }
        }
    }
}

class NameNotUniqueException extends InputException {public $txt = "Неуникальное имя магазина", $input = 'name';}