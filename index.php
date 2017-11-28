<?php
ini_set ('display_errors', 'On');
error_reporting(E_ALL);

include "displayHTML.php";
use display\displayHTML as displayHTML;
$obj = new displayHTML;

define('DATABASE','tej2');
define('USERNAME','tej2');
define('PASSWORD','sCUGMmHv');
define('CONNECTION','sql1.njit.edu');


class Manage {
    public static function autoload($class) {
        include $class . '.php';
    }
}
spl_autoload_register(array('Manage', 'autoload'));
$obj=new displayHTML;
$obj=new main();
class dbConn{
    protected static $db;
    private function __construct() {
        try {
            self::$db = new PDO( 'mysql:host=' . CONNECTION .';dbname=' . DATABASE, USERNAME, PASSWORD );
            self::$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        } catch (PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
        }
    }
    public static function getConnection() {
        if (!self::$db) {
            new dbConn();
        }
        return self::$db;
    }
}
abstract class collection {
    protected $html;
    static public function create() {
        $model = new static::$modelName;
        return $model;
    }
    static public function findAll() {
        $db = dbConn::getConnection();
        $tableName = get_called_class();
        $sql = 'SELECT * FROM ' . $tableName;
        $statement = $db->prepare($sql);
        $statement->execute();
        $class = static::$modelName;
        $statement->setFetchMode(PDO::FETCH_CLASS, $class);
        $recordsSet =  $statement->fetchAll();
        return $recordsSet;
    }
    static public function findOne($id) {
        $db = dbConn::getConnection();
        $tableName = get_called_class();
        $sql = 'SELECT * FROM ' . $tableName . ' WHERE id =' . $id;
        $statement = $db->prepare($sql);
        $statement->execute();
        $class = static::$modelName;
        $statement->setFetchMode(PDO::FETCH_CLASS, $class);
        $recordsSet =  $statement->fetchAll();
        return $recordsSet[0];
    }
}
class accounts extends collection {
    protected static $modelName = 'account';
}
class todos extends collection {
    protected static $modelName = 'todo';
}
abstract class model {
    protected $tableName;
    public function save(){
        if ($this->id != '') {
            $sql = $this->update();
        } else {
           $sql = $this->insert();
        }
        $db = dbConn::getConnection();
        $statement = $db->prepare($sql);
        $array = get_object_vars($this);
        foreach (array_flip($array) as $key=>$value){
            $statement->bindParam(":$value", $this->$value);
        }
        $statement->execute();
        $id = $db->lastInsertId();
        return $id;
    }
    private function insert() {      
        $modelName=get_called_class();
        $tableName = $modelName::getTablename();
        $array = get_object_vars($this);
        $columnString = implode(',', array_flip($array));
        $valueString = ':'.implode(',:', array_flip($array));
        $sql =  'INSERT INTO '.$tableName.' ('.$columnString.') VALUES ('.$valueString.')';
        return $sql;
    }
    private function update() {  
        $modelName=get_called_class();
        $tableName = $modelName::getTablename();
        $array = get_object_vars($this);
        $comma = " ";
        $sql = 'UPDATE '.$tableName.' SET ';
        foreach ($array as $key=>$value){
            if( ! empty($value)) {
                $sql .= $comma . $key . ' = "'. $value .'"';
                $comma = ", ";
                }
            }
            $sql .= ' WHERE id='.$this->id;
        return $sql;
    }
    public function delete() {
        $db = dbConn::getConnection();
        $modelName=get_called_class();
        $tableName = $modelName::getTablename();
        $sql = 'DELETE FROM '.$tableName.' WHERE id ='.$this->id;
        $statement = $db->prepare($sql);
        $statement->execute();
    }
}
class account extends model {
    public $id;
    public $email;
    public $fname;
    public $lname;
    public $phone;
    public $birthday;
    public $gender;
    public $password;
    public static function getTablename(){
        $tableName='accounts';
        return $tableName;
    }
}
class todo extends model {
    public $id;
    public $owneremail;
    public $ownerid;
    public $createddate;
    public $duedate;
    public $message;
    public $isdone;
    public static function getTablename(){
        $tableName='todos';
        return $tableName;
    }
} 

class main
{
	public function __construct() {
	$form = '<form method ="post" enctype="multipart/form-data">';
	$form .= '<center><b>Table</b> <i>Accounts</i>';
	$form .= '<br>Select all records';
	$records = accounts::findAll();
        $tableGen = displayHTML::displayTable($records);
	$form .= $tableGen;
	
	$form .= '<p>Select one record';
	$id = 4;
	$records = accounts::findOne($id);
	$tableGen = displayHTML::displayTableAlternate($records);
	$form .= '<i><br>Retrieved record '.$id.'</i>';
	$form .= $tableGen;

	$form .= '<p>Insert one record';
	$record = new account();
	$record->email="tej2@njit.edu";
	$record->fname="Tiffany";
	$record->lname="Johnson";
	$record->phone="983-989-9889";
	$record->birthday="1994-09-08";
	$record->gender="female";
	$record->password="9876578";
	$lastInsertedId=$record->save();
	$records = accounts::findAll();
	$tableGen = displayHTML::displayTable($records);
	$form .= '<i><br>Inserted  '.$lastInsertedId.'</i>';
	$form .= $tableGen;

        $form .= '<p>Update one record';
        $records = accounts::findOne($lastInsertedId);
        $record = new account();
        $record->id=$records->id;
        $record->password="09877";
        $record->save();
        $form .= '<i><br>Updated password of id '.$records->id.'</i>';
        $records = accounts::findAll();
        $tableGen = displayHTML::displayTable($records);
        $form .= $tableGen;

        $form .= '<p>Delete one record';
        $records = accounts::findOne($lastInsertedId);
        $record= new account();
        $record->id=$records->id;
        $record->delete();
	$form .= '<i><br>Record '.$records->id.' deleted</i>';
	$records = accounts::findAll();
        $tableGen = displayHTML::displayTable($records);
	$form .= $tableGen;

	$form .= '<p><b>Table</b> <i>Todos</i>';
	$form .= '<br>Select all records';
	$records = todos::findAll();
	$tableGen = displayHTML::displayTable($records);
	$form .= $tableGen;

	$form .= '<p>Select one record';
	$id = 3;
	$records = todos::findOne($id);
	$tableGen = displayHTML::displayTableAlternate($records);
	$form .= '<i><br>Retrieved record '.$id.'</i>';
	$form .= $tableGen;

	$form .= '<p>Insert one record';
	$record = new todo();
        $record->owneremail="tej2@njit.edu";
        $record->ownerid=24;
        $record->createddate="2018-01-20";
        $record->duedate="2018-02-20";
        $record->message="create mobile application";
        $record->isdone=0;
        $lastInsertedId=$record->save();
	$records = todos::findAll();
	$tableGen = displayHTML::displayTable($records);
	$form .= '<i><br>Inserted '.$lastInsertedId.'</i>';
	$form .= $tableGen;

        $form .= '<p>Update one record';
        $records = todos::findOne($lastInsertedId);
        $record = new todo();
        $record->id=$records->id;
	$record->createddate="2018-02-16";
        $record->save();
        $form .= '<i><br>Updated created date of id '.$records->id.'</i>';
        $records = todos::findAll();
        $tableGen = displayHTML::displayTable($records);
        $form .= $tableGen;

        $form .= '<p>Delete one record';
        $records = todos::findOne($lastInsertedId);
        $record = new todo();
        $record->id=$records->id;
        $record->delete();
	$form .= '<i><br>Record '.$records->id.' deleted</i>';
        $records = todos::findAll();
        $tableGen = displayHTML::displayTable($records);
        $form .= $tableGen;

        $form .= '</center></form> ';
	print($form);
	}
}
?>
