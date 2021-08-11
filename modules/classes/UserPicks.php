<?php
// Iterator that stores the weeks a user has picked.
require_once 'modules/utils/functions.php';
class UserPicks implements Iterator {
  private $position = 0;
  private $username;
  public $logged_in;
  public $unpicked_week = 0;
  public $weeks;
  private $mysql;

  public function __construct($mysql, $username, $current_week){
    $this->username = $username;
    $this->logged_in = ($username == ($_SESSION['username'] ?? null));
    $this->mysql = $mysql;
    $result = prepared_statement(
      'SELECT DISTINCT week FROM user_entries WHERE user_id = (SELECT user_id FROM users WHERE username = ?)',
      's',
      [$username]
    );
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    $this->weeks = array_column($rows, 'week');

    if($this->logged_in){
      $this->unpicked_week = end($this->weeks) < $current_week ? $current_week : null;
      array_push($this->weeks, $this->unpicked_week);
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
      return $this->current() == ($this->unpicked_week ?? null);
    }

}

?>
