<?php
require_once 'modules/utils/functions.php';

class Leaderboard {
  private $weeks = [];
  private $scores = []; // Array Structure as follows: Array([week] => Array([username] => score, [username] => score))
  private $week_winners = [];
  private $usernames = [];
  private $current_week;
  private $mysql;
  public function __construct($mysql, $current_week){
    $this->mysql = $mysql;
    $this->current_week = $current_week;
    $result = $mysql->query("SELECT
                              P.week, U.username, SUM(P.score) as total
                            FROM
                              point_additives P
                            INNER JOIN users U ON
                              P.user_id = U.user_id
                            GROUP BY
                              P.week, P.user_id
                            ORDER BY
                              P.week;
                          ");
    if ($result){
      $rows = $result->fetch_all(MYSQLI_ASSOC);
      foreach ($rows as $row) {
        $week = $row['week'];
        $score = $row['total'];
        $username = $row['username'];
        if (!in_array($week, $this->weeks)) array_push($this->weeks, $week);
        if (!in_array($username, $this->usernames)) array_push($this->usernames, $username);
        $this->scores[$week][$username] = $score;
      }
    }

    foreach($this->weeks as $week){
      if ($week == $this->current_week) continue;
      if (count(array_keys($this->scores[$week]), max($this->scores[$week]))) { // This entire block is for tie checking, this part specifically checks if their are two of the max scores for the week
        if (array_count_values($this->scores[$week])[max($this->scores[$week])] > 1){
          $names = array_keys($this->scores[$week], max($this->scores[$week]));
          $broken_tie = $this->break_tie($names, $week);
          $this->week_winners[$week] = $broken_tie[0];
          continue;
        }
      }
      $largest = max(array_values($this->scores[$week]));
      $this->week_winners[$week] = array_search($largest, $this->scores[$week]);
    }
    $this->order_usernames(); //Makes usernames appear in correct order on standings page.
  }

  public function order_usernames(){
    $tmp_usernames = [];
    foreach ($this->usernames as $user){
      $tmp_usernames[$user] = $this->count_wins($user);
    }
    arsort($tmp_usernames);
    $this->usernames = array_keys($tmp_usernames);
  }

  public function get_usernames(){
    return $this->usernames;
  }

  public function get_weeks(){
    return $this->weeks;
  }

  public function get_score($username, $week) {
    return $this->scores[$week][$username];
  }

  public function get_winner($week) {
    return $this->week_winners[$week] ?? null;
  }

  public function sum_score($username){
    $sum = 0;
    foreach ($this->weeks as $week) {
      $sum += $this->scores[$week][$username];
    }
    return $sum;
  }

  public function count_wins($username){
    return array_count_values($this->week_winners)[$username] ?? 0;
  }

  public function get_money($username){
    $won = $this->count_wins($username);
    $lost = ($this->current_week - 1) - $won;
    return ($won*6) - ($lost*3);
  }

  private function break_tie($usernames, $week){ // Player with lowest confidence correct pick for the week wins.
    $week = intval($week);
    $confs = []; // List of lowest confidence winners by users
    $stmt = $this->mysql->prepare('SELECT MIN(U.confidence) as conf FROM user_entries AS U INNER JOIN events AS E ON E.event_id=U.event_id WHERE E.winner=U.winner_id AND U.user_id=(SELECT user_id FROM users WHERE username=?) AND U.week=?');
    foreach ($usernames as $username){
      $stmt->bind_param('si', $username, $week);
      $stmt->execute();
      $result = $stmt->bind_result($conf);
      $stmt->fetch();
      $confs[$username] = $conf ?? 100; // If user has no wins for some reason obviously they do win so this is a hacky way of doing this.
    }
    return array_keys($confs, min($confs)); // Returns an array, may cause future issues
  }
}


?>
