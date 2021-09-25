<?php
require_once "modules/utils/functions.php";

class PickValidityCheck {
  private $picks;
  private $picks_week;
  private $current_week;
  private $supplied_username;
  public function __construct($current_week, $data) {
    $this->current_week = $current_week;
    $this->picks_week = $data['week'];
    $this->supplied_username = $data['username'];
    $this->picks = $data['entries'];
  }

  public function full_check(){
    return $this->user_check() && $this->check_picks_confidence() && $this->verify_events();
  }

  public function user_check() {
    if ($_SESSION['loggedIn'] && isset($_SESSION['username']) && ($_SESSION['username'] == $this->supplied_username)) {
      return true;
    }
    return false;
  }

  public function check_picks_confidence(){
    $confidences = array_column($this->picks, 'confidence');
    if (count(array_unique($confidences)) < count($confidences)){ // makes sure there anre't any duplicate confidences.
      return false;
    }
    foreach($confidences as $confidence){
      if ($confidence > 16 || $confidence < 1) {
        return false;
      }
    }
    return true;
  }

  public function verify_events(){
    $events_query = prepared_statement(
      'SELECT
        event_id,
        team_one_id,
        team_two_id,
        IF(start_date > (SELECT UNIX_TIMESTAMP()), False, True) as locked
      FROM
        events
      WHERE
        week = ?
    ', 'i', [$this->picks_week]);
    $events = array_manipulate(function($k, $v){  // Modifies array to ensure all games are keyed based off their entry_id.
      return array($v['event_id'] => $v);
    }, $events_query->fetch_all(MYSQLI_ASSOC));
    foreach ($this->picks as $pick) {
      try {
        $event = $events[$pick['event_id']];
        if (!array_search($pick['winner_id'], $event) || $event['locked']) return false;
      } catch (Exception $e) {
        error_log("Error submitting picks, event not in databse: {$e}");
        return false;
      }
    }
    return true;
  }
}


?>
