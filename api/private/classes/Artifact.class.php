<?php

class Artifact extends DatabaseObject {

  static protected $table_name = 'games';
  static protected $db_columns = [
    'Access', 'Acq', 'Age', 'age_max', 'Av', 'BGG_Rat', 'Candidate', 'FavCt', 'FullTitle', 'id', 'KeptCol', 
    'KeptDig', 'KeptPhys', 'MnP', 'MnT', 'MxP', 'MxT', 'OrigPlat', 'SS', 'System', 'Title', 
    'type', 'UsedRecUserCt', 'user_id', 'Wt', 'Yr'
  ];

  public $id;

  public $Access;
  public $Acq;
  public $Age;
  public $age_max;
  public $Av;
  public $BGG_Rat;
  public $Candidate;
  public $FavCt;
  public $FullTitle;
  public $KeptCol;
  public $KeptDig;
  public $KeptPhys;
  public $MnP;
  public $MnT;
  public $MxP;
  public $MxT;
  public $OrigPlat;
  public $SS;
  public $System;
  public $Title;
  public $type;
  public $UsedRecUserCt;
  public $user_id;
  public $Wt;
  public $Yr;

  public static function list_artifacts($page = 1, $per_page = 50) {
    $page = max(1, (int) $page);
    $per_page = max(1, min(200, (int) $per_page));
    $offset = ($page - 1) * $per_page;

    $stmt = self::$database->prepare(
      "SELECT games.id, games.Title
       FROM games
       ORDER BY games.Title ASC
       LIMIT ? OFFSET ?"
    );
    $stmt->bind_param("ii", $per_page, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $array = array();
    while($record = $result->fetch_assoc()) {
      $array[] = $record;
    }
    $stmt->close();
    return $array;
  }

  public static function list_artifacts_by_query($query, $user_id, $page = 1, $per_page = 50) {
    $page = max(1, (int) $page);
    $per_page = max(1, min(200, (int) $per_page));
    $offset = ($page - 1) * $per_page;

    $stmt = self::$database->prepare(
      "SELECT games.id, games.Title
       FROM games
       WHERE games.Title LIKE ?
       AND user_id = ?
       ORDER BY games.Title ASC
       LIMIT ? OFFSET ?"
    );
    $like_query = '%' . $query . '%';
    $stmt->bind_param("siii", $like_query, $user_id, $per_page, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $array = array();
    while($record = $result->fetch_assoc()) {
      $array[] = $record;
    }
    $stmt->close();
    return $array;
  }

}

?>