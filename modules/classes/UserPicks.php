<?php
// Iterator that stores the weeks a user has picked.
require_once 'modules/utils/functions.php';
class UserPicks implements Iterator {
  private $position = 0;
  private $username;
  public $logged_in;
  public $unpicked_weeks = array();
  public $weeks;
  private $mysql;

  public function __construct($mysql, $username, $current_week){
    $this->username = $username;
    $this->logged_in = ($username == ($_SESSION['username'] ?? null));
    $this->mysql = $mysql;
    $result = prepared_statement(
      'SELECT DISTINCT week FROM user_entries WHERE user_id = (SELECT user_id FROM users WHERE username = ?) ORDER BY week ASC',
      's',
      [$username]
    );
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    $this->weeks = array_column($rows, 'week');
    if($this->logged_in){
      $end_of_week = end($this->weeks);
      for ($i = 1; $i <= $current_week+1; $i++){
        $unpicked_week = $end_of_week < $i ? $i : null;
        if ($unpicked_week) {
          array_push($this->unpicked_weeks, $unpicked_week);
          array_push($this->weeks, $unpicked_week);
        }
      }
    }
  }

  public function rewind() {
        $this->position = 0;
    }

    public function current() {
        return $this->weeks[$this->position];
    }

    public function key() {
        return $this->position;
    }

    public function next() {
        ++$this->position;
    }

    public function valid() {
        return isset($this->weeks[$this->position]);
    }

    public function is_unpicked(){
      return in_array($this->current(), $this->unpicked_weeks);
    }

}

?>
