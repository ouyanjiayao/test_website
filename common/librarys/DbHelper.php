<?php
namespace common\librarys;

use Yii;

class DbHelper
{

    public $db;

    public static function db($db = "db")
    {
        $helper = new static();
        $helper->db = Yii::$app->{$db};
        return $helper;
    }

    public function executeQuery($sql, $params = array(), $single = false)
    {
        $command = $this->createCommand($sql, $params);
        if ($single) {
            return $command->queryOne();
        } else {
            return $command->queryAll();
        }
    }

    public function executeNonQuery($sql, $params = array())
    {
        $command = $this->createCommand($sql, $params);
        return $command->execute();
    }

    public function createCommand($sql = null, $params = array())
    {
        $command = $this->db->createCommand($sql);
        foreach ($params as $k => $v) {
            $command->bindValue($k, $v);
        }
        return $command;
    }
}

?>
