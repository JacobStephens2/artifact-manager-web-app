<?php

class User extends DatabaseObject {

  static protected $table_name = 'players';
  static protected $db_columns = [
     'Age', 'FirstName', 'FullName', 'G', 'id', 'LastName', 'MenuPriority', 'Priority', 'user_id'
  ];

  public $id;
  
  public $user_id;
  public $Age;
  public $FirstName;
  public $FullName;
  public $G;
  public $LastName;
  public $MenuPriority;
  public $Priority;

  public static function list_users() {
    $sql = "SELECT ";
    $sql .= "id, ";
    $sql .= "FullName ";
    $sql .= "FROM players ";
    $sql .= "WHERE FullName IS NOT NULL ";
    $sql .= "ORDER BY FullName ASC ";
    $sql .= "LIMIT 1000";
    
    $result = self::$database->query($sql);
    if ($result->num_rows > 0) {
      while($record = $result->fetch_assoc()) {
        $array[] = $record;
      }
    } else {
      $array = array();
    }
    return $array;
  }

  public static function list_users_by_query($query, $user_id) {
    $sql = 
      "SELECT 
        id, 
        FullName,
        FirstName,
        LastName
      FROM players 
      WHERE FullName LIKE '%" . self::$database->escape_string($query) . "%'
      AND FullName IS NOT NULL
      AND user_id = '$user_id'
      ORDER BY LastName ASC,
      FirstName ASC
    ";
    $result = self::$database->query($sql);
    if ($result->num_rows > 0) {
      while($record = $result->fetch_assoc()) {
        $array[] = $record;
      }
    } else {
      $array = array();
    }
    return $array;
  }

}

?>