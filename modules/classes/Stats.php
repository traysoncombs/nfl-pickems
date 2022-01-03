<?php
require_once 'modules/utils/functions.php';

class Stats {
    public $current_week;
    public $stats = array();
    public function __construct($current_week, $mysql) {
        $this->current_week = $current_week;
        $this->mysql = $mysql;
        $this->wins_vs_losses();
        $result = $this->mysql->query("SELECT
                          username,
                          entry_id,
                          week,
                          confidence,
                          correct
                        FROM
                          stats
                        WHERE
                          completed = 1");
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        foreach($rows as $row) {
          if (!isset($this->stats[$row['username']]['weeks'][$row['week']])) {
            $this->stats[$row['username']]['weeks'][$row['week']] = array();
          }
          array_push($this->stats[$row['username']]['weeks'][$row['week']], array(
            'entry_id' => $row['entry_id'],
            'confidence' => $row['confidence'],
            'correct' => $row['correct']
          ));
        }
    }

    private function wins_vs_losses() {
        $result = $this->mysql->query("SELECT
                                    username,
                                    SUM(correct) as total_wins,
                                    SUM(IF(correct = 0, 1, 0)) as total_losses
                                FROM
                                    stats
                                WHERE
                                    completed = 1
                                GROUP BY
                                    username");
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        foreach($rows as $row) {
            $this->stats[$row['username']]['total_wins'] = $row['total_wins'];
            $this->stats[$row['username']]['total_losses'] = $row['total_losses'];

        }
    }

    function win_percentage($username) {
      return ($this->stats[$username]['total_wins'] / ($this->stats[$username]['total_wins'] + $this->stats[$username]['total_losses'])) * 100;
    }

    /*function average_point_score_week($username, $week){
      $week_data = $this->stats[$username]['weeks'][$week];
      $total_points = 0;
      $count = 0;
      foreach($week_data as $entry) {
        if ($entry['correct'] == "1") {
          $count += 1;
          $total_points += $entry['confidence'];
        }
      }
      return $total_points / $count;
    }

    function average_point_score($username) {
      $weeks = array_keys($this->stats[$username]['weeks']);
      $avg = 0;
      $total = count($weeks);
      foreach($weeks as $week) {
        $avg += $this->average_point_score_week($username, $week);
      }
      return $avg / $total;
    }*/

    function average_point_score($username) {
      $weeks = array_keys($this->stats[$username]['weeks']);
      $total = count($weeks);
      $avg = 0;
      foreach($weeks as $week) {
        foreach($this->stats[$username]['weeks'][$week] as $entry) {
          if ($entry['correct'] == "1") {
            $avg += $entry['confidence'];
          }
        }
      }
      return $avg / $total;
    }

}
?>
