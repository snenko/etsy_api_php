<?php
abstract class DBEntity extends DBUser implements EntityInterface {
    protected $id = null;
    
    /**
     * @param array $array
     * @return int
     * @throws Exception
     */
    public static function add($array) {
        $class = get_called_class();
        foreach ($array as $name => $value)
            /** @var DBEntity $class */
            if (!$class::_checkValue($name, $value))
                throw new Exception("$name = '$value' did not pass check");
        $names = implode(', ', array_map(function($value) {return "`$value`";}, array_keys($array)));
        $values = implode(', ',
            array_map(
                function($value) {
                    if (is_int($value)) return $value;
                    else if (is_bool($value)) return (int)$value;
                    else return "'$value'";
                },
                $array
            )
        );
        self::query("INSERT INTO `$class` ($names) VALUES($values)");
        return self::lastInsertId();
    }
    /**
     * @param $id
     * @return bool|DBEntity
     * @throws Exception
     */
    public static function byId($id) {
        $class = get_called_class();
        $id = (int)$id;
        $result = self::query("SELECT * FROM `$class` WHERE id = $id");
        return $result->num_rows ? new $class($result->fetch_assoc()) : false;
    }
    /**
     * @return array
     * @throws Exception
     */
    public static function getArrayList() {
        $class = get_called_class();
        $result = self::query("SELECT * FROM `$class` WHERE 1 ORDER BY id ASC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    public function asArray() {
        $class = get_called_class();
        $return = [];
        /** @var DBEntity $class */
        $names = defined("$class::arrayValues") ? $class::arrayValues : $class::columns;
        foreach ($names as $name) $return[$name] = $this->$name;
        return $return;
    }
    
    /**
     * @param $array
     * @return bool
     * @throws Exception
     */
    public function change($array) {
        $class = get_called_class();
        $strings = [];
        foreach ($array as $name => $value) {
            /** @var DBEntity $class */
            if (!$class::_checkValue($name, $value)) throw new Exception("$name = '$value' did not pass check");
            $string = "`$name` = ";
            if (is_bool($value)) $string .= $value ? 1 : 0;
            elseif (is_int($value) || is_float($value)) $string .= $value;
            elseif (is_null($value)) $string .= 'NULL';
            else $string .= "'$value'";
            $strings[] = $string;
        }
        $strings = implode(', ', $strings);
        $class = get_called_class();
        return self::query("UPDATE `$class` SET $strings WHERE id = " . $this->id);
    }
    
    /**
     * @param $id
     * @param $array
     * @throws Exception
     */
    public static function changeById($id, $array) {
        $class = get_called_class();
        /** @var $Object DBEntity */
        $Object = new $class($id);
        $Object->change($array);
    }
    /**
     * @return bool
     * @throws Exception
     */
    public function delete() {
        $class = get_called_class();
        return self::query("DELETE FROM `$class` WHERE id = " . $this->id);
    }
    /**
     * @param $id
     * @throws Exception
     */
    public static function deleteById($id) {
        $class = get_called_class();
        /** @var $Object DBEntity */
        $Object = new $class($id);
        $Object->delete();
    }
    /**
     * @param $array
     * @return bool|mysqli_result
     * @throws Exception
     */
    public static function deleteByData($array) {
        $class = get_called_class();
        $WHERE = [];
        foreach ($array as $name => $value) $WHERE[] = "`$name` = '$value'";
        $WHERE = implode(' AND ', $WHERE);
        return self::query("DELETE FROM `$class` WHERE $WHERE");
    }
    /**
     * DBEntity constructor.
     * @param mixed $arg
     * @throws Exception
     */
    public function __construct($arg) {
        $class = get_called_class();
        /** @var DBEntity $class */
        if (is_array($arg)) {
            foreach ($arg as $name => $value) {
                if (!$class::_checkValue($name, $value)) throw new Exception("$name = '$value' did not pass check");
                $this->$name = $value;
            }
            return $this;
        }
        else {
            if (!$class::_checkValue('id', $arg)) throw new Exception("id = $arg did not pass check");
            $class = get_called_class();
            $result = self::query("SELECT * FROM `$class` WHERE id = $arg");
            if ($result->num_rows) return new $class($result->fetch_row());
            else throw new Exception("Entity '$class' with id = $arg doesn't exists");
        }
    }
    /**
     * @param int $id
     * @return bool
     * @throws Exception
     */
    public static function isExists($id) {
        $class = get_called_class();
        /** @var DBEntity $class */
        if (!$class::_checkValue('id', $id)) throw new Exception("id = '$id' did not pass check");
        return self::query("SELECT id FROM `$class` WHERE id = $id")->num_rows === 1;
    }
    /**
     * @param string $name
     * @return mixed
     */
    public function get($name) {
        return $this->$name;
    }
}

interface EntityInterface {
    public static function add($array);
    public static function isExists($id);
    public function delete();
    public static function deleteById($id);
    public function change($array);
    public static function changeById($id, $array);
    public static function _checkValue($name, $value);
    public function get($name);
}

abstract class InputException extends Exception{
    public $txt,$input;
    public function arr() {
        return ['inputError' =>
            [
                'txt' => $this->txt,
                'input' => $this->input
            ]
        ];
    }
}

class NotPassedCheckException extends InputException {
    public function __construct($name, $value, string $message = "", int $code = 0, Throwable $previous = null)
    {
        $this->txt = "$name = '$value' not passed check";
        $this->input = $name;
        $this->message = $this->txt;
        parent::__construct($message, $code, $previous);
    }
}