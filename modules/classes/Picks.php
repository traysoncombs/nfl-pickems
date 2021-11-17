<?php
require_once 'modules/utils/functions.php';
class Picks implements Iterator{
  public $week;
  private $username;
  private $picks;
  private $events = [];
  public $logged_in;
  private $position = 0;
  private $event_keys;
  public $editing = False;
  public function __construct($mysql, $week, $username){
    $this->username = $username;
    $this->week = intval($week);
    $this->logged_in = ($username == ($_SESSION['username'] ?? false));
    $events_result = prepared_statement(
      'SELECT
      	event_id,
        team_one_id,
        team_two_id,
        start_date,
        T1.short_display_name as team_one_name,
        T1.wins as team_one_wins,
        T1.losses as team_one_losses,
        T1.color as team_one_color,
        T2.color as team_two_color,
        T2.wins as team_two_wins,
        T2.losses as team_two_losses,
        T2.short_display_name as team_two_name,
        IF(start_date > (SELECT UNIX_TIMESTAMP()), False, True) as locked
      FROM
         	events
      INNER JOIN teams as T1 ON
          T1.team_id=events.team_one_id
      INNER JOIN teams as T2 ON
          T2.team_id=events.team_two_id
      WHERE week = ?
      ORDER BY 
        start_date DESC',
      'i',
      [$this->week]
    );
    $this->events = array_manipulate(function($k, $v){  // Modifies array to ensure all games are keyed based off their entry_id.
      return array($v['event_id'] => $v);
    }, $events_result->fetch_all(MYSQLI_ASSOC));
    $this->event_keys = array_keys($this->events);
    $entries_result = prepared_statement(
      'SELECT
        entry_id as entry,
        event_id as event,
        confidence,
        winner_id,
        (SELECT E.completed FROM events E WHERE E.event_id = event) as completed,
        (SELECT EXISTS(SELECT * FROM point_additives P WHERE P.entry_id = entry)) as correct
      FROM
        user_entries
      WHERE
        week = ? AND
        user_id = (SELECT user_id FROM users WHERE username = ?)
      ORDER BY
        confidence DESC',
      'is',
      [$this->week, $username]
    );
    if($entries_result && $entries_result->num_rows >= 1){  // Executed if user has already selected picks
      $this->picks = $entries_result->fetch_all(MYSQLI_ASSOC);
      $conf_counter = 1;
      var_dump($this->events);
      var_dump(array_column($this->picks, 'event'));
      $event_ids = array_column($this->picks, 'event');
      foreach (array_reverse($this->events, 1) as $key => $event) {
        var_dump($key);
        if (!in_array($key, $event_ids)) { // check if event id exists in the picks
          $tmp_pick = array();
          $tmp_pick['entry'] = -1;
          $tmp_pick['event'] = $key;
          $tmp_pick['confidence'] = $conf_counter;
          $tmp_pick['winner_id'] = -1;
          $tmp_pick['completed'] = 1;
          $tmp_pick['correct'] = 0;
          $this->picks[(count($this->events) - 1) - ($conf_counter - 1)] = $tmp_pick;
          $conf_counter++;
          var_dump($tmp_pick);
          var_dump($this->picks);
        }
      }
      $this->editing = true;
    }
  }

  public function rewind() {
    $this->position = 0;
  }

  public function current() {
    if ($this->picks) {
      return array_merge($this->picks[$this->position], $this->events[$this->picks[$this->position]['event']]);
    } else {
      return $this->events[$this->event_keys[$this->position]];
    }
  }

  public function key() {
    return $this->position;
  }

  public function next() {
    ++$this->position;
  }

  public function valid() {
    if ($this->picks) {
      return isset($this->picks[$this->position]);
    } else {
      return isset($this->event_keys[$this->position]);
    }
  }

  public function count_events() {
    return count($this->events);
  }

  public function contains_locked(){
    foreach($this->events as $event){
      if ($event['locked']) return true;
    }
    return false;
  }
}

?>
