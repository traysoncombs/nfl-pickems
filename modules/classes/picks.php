<?php
require '../utils/functions.php';
class Picks {
  private $week;
  private $username;
  private $picks;
  private $events = [];
  public function __construct($mysql, $week, $username){
    $this->username = $username;
    $this->week = $week;
    $events_result = prepared_statement(
      'SELECT
      	event_id,
        team_one_id,
        team_two_id,
        start_date,
        T1.short_display_name as team_one_name,
        T1.wins as team_one_wins,
        T1.losses as team_one_id_losses,
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
      WHERE week = ?',
      'i',
      [$week]
    );
    $this->events = array_manipulate(function($k, $v){  // Modifies array to ensure all games are keyed based off their entry_id.
      $entry_id = $v['entry_id'];
      unset($v['entry_id']);
      return array($entry_id => $v);
    }, $events_result->fetch_all(MYSQLI_ASSOC));

    $entries_result = prepared_statement(
      'SELECT
        entry_id,
        event_id,
        confidence,
        winner_id,
      FROM
        user_entries
      WHERE
        week = ? AND
        user_id = (SELECT user_id FROM users WHERE username = ?)
      ORDER BY
        confidence',
      'is',
      [$week, $username]
    );
    if($entries_result && $entries_result->num_rows >= 1){  // Executed if user has already selected picks
      $this->picks = $entries_result->fetch_all(MYSQLI_ASSOC);
    }
  }
}

?>
