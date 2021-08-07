<?php

class user_picks {
  private $username;
  private $logged_in;
  private $weeks_picked;
  private $unpicked_week;
  private $mysql;

  public function __construct($mysql, $username, $current_week){
    $this->username = $username;
    $this->logged_in = ($username == $_SESSION['username']);
    $this->mysql = $mysql;

    $stmt = $mysql->prepare('SELECT DISTINCT week FROM user_entries WHERE user_id = (SELECT user_id FROM users WHERE username = ?)');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    $this->weeks = array_column($rows, 'week');

    if($this->logged_in){
      $this->unpicked_week = end($this->weeks) < $current_week ? $current_week : null;
    }
  }

  

}

?>
