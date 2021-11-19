<?php
require_once 'modules/utils/functions.php';

class Stats {
    public $current_week;
    public $stats = array();
    public function __construct($current_week, $mysql) {
        $this->current_week = $current_week;
        $this->mysql = $mysql;
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
        $rows = $result->fetch_all(MYSQL_ASSOC);
        foreach($rows as $row) {
            $this->stats[$row['username']]['total_wins'] = $row['total_wins'];
            $this->stats[$row['username']]['total_losses'] = $row['total_losses'];
        }
    }
}
?>
