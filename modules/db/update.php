<?php
require 'config/config.php';
define('UPDATE_INTERVAL', 60 * 5);
function update_events() {
    global $mysql;
    $last_updated = $mysql->query('
            SELECT
                time
            FROM
                last_updated;
        ')->fetch_row()[0];
    if ($last_updated != null && $last_updated + UPDATE_INTERVAL > time()) return;
    if (!$mysql->query("UPDATE last_updated SET time = ".time()." WHERE id = 0")) error_log("Error updating time in last_update table: ".$mysql->error);
    $events_to_update = $mysql->query('
        SELECT
            event_id,
            start_date,
            completed,
            team_one_score,
            team_two_score,
            winner
        FROM
            events
        WHERE
            start_date <= '.time().' AND
            completed != true
        ORDER BY
            start_date;
    ');
    if (!$events_to_update->num_rows) return;
    $rows = $events_to_update->fetch_all(MYSQLI_ASSOC);
    $ids = array_column($rows, 'event_id');
    $start_date = date("Ymd", $rows[0]['start_date']);
    $end_date = date("Ymd", end($rows)['start_date']+(60*60*24)); # end date in api is exclusive so we need to add 1 day to get all events needed.
    $events = json_decode(file_get_contents("https://site.api.espn.com/apis/site/v2/sports/football/nfl/scoreboard?dates=$start_date-$end_date&limit=900"), true)['events'];
    $updated_events = array_filter($events, function($val) use ($ids) {
        return in_array($val['id'], $ids);
    });
    foreach ($updated_events as $event) {
        $row = $rows[array_search($event['id'], $ids)]; // ids array is in same order as row, and so the indices are the same, thus we can get a row item by id.
        $updated_data = create_updated_data($event);
        foreach ($updated_data as $key => $updated_value) {
            // Very fun way to create a query to only update changed values
            if ($row[$key] != $updated_value) {
                $result = $mysql->query("
                    UPDATE
                        events
                    SET
                        $key = $updated_value
                    WHERE
                        event_id = {$row['event_id']}
                    ");
                if (!$result) error_log("Error updating db: ".$mysql->error);
            }
        }
    }
}

function create_updated_data($event) {
    $teams = $event['competitions'][0]['competitors']; // If there are any issues in the future with updating stuff this may be the cause as we are ignoring the id and going purely off order, which may look nice but behave badly
    $updated_data = array();
    $updated_data['completed'] = $event['competitions'][0]['status']['type']['completed'];
    $updated_data['team_one_score'] = $teams[0]['score'];
    $updated_data['team_two_score'] = $teams[1]['score'];
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
