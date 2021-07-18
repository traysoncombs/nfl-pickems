<?php
require 'config.php';
define('UPDATE_INTERVAL', 60 * 5);
function update_events() {
    $last_updated = $mysql->query('
            SELECT
                time
            FROM
                last_updated;
        ')->fetch_row()[0];
    if ($last_updated != null && $last_updated + UPDATE_INTERVAL > time()) return;
    if (!$mysql->query("UPDATE last_updated SET time = ".time()." WHERE id = 0")) {
        error_log("Error updating time in last_update table: ".$mysql->error);
    }
    $events_to_update = $mysql->query('
        SELECT
            event_id
        FROM
            events
        WHERE
            start_date <= '.time().' AND
            completed != true
        ORDER BY
            start_date;
    ');
    if (!$events_to_update->num_rows) return;
    foreach ($events_to_update->fetch_all() as $row) {
        $data = file_get_contents("https://site.api.espn.com/apis/site/v2/sports/football/nfl/scoreboard/${row['event_id']}");
        $event = json_decode($data)['events'][0]
        $updated_data = create_updated_data($event);
        $query = "
            UPDATE
                events
            SET
        ";
        foreach ($updated_data as $key => $updated_value) { // Very fun way to create a query to only update changed values
            if ($row[$key] != $updated_value) {
               $query += $key + "=" + $updated_value + " ";
               if (array_key_last($updated_data) == $key) break;
               $query += "AND ";
            }
        }
        $query += "WHERE event_id = ${row['id']}"
        if (!$mysql->query($query)) error_log('Error updating event: '.$row['id'].' Error: '.$mysql->error)
    }
}

function create_updated_data($event) {
    $teams = $event['competitions'][0]['competitors']; // If there are any issues in the future with updating stuff this may be the cause as we are ignoring the id and going purely off order, which may look nice but behave badly
    $updated_data = array();
    $updated_data['completed'] = $event['competitions'][0]['status']['type']['completed'];
    $updated_data['team_one_score'] = $teams[0]['score'] ?: null;
    $updated_data['team_two_score'] = $teams[1]['score'] ?: null;
    $updated_data['winner'] = null;
    if ($updated_data['completed']) {
        if ((!$teams[0]['winner'] && !$teams[1]['winner']) || ($teams[0]['winner'] && $teams[1]['winner'])) {
            // checks for a tie, if both are either true or false then it must be a tie. this will be represented in the db by a completed game with null winner id
            return $updated_data;
        }
        $updated_data['winner'] = $teams[0]['winner'] ? $teams[0]['id'] : $teams[1]['id'];
    }
    return $updated_data;
}

?>