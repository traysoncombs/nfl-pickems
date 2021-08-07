<?php
require 'config/config.php';
require 'modules/classes/leaderboard.php';
require 'modules/utils/functions.php';
require_once 'vendor/autoload.php';

session_start();

$latte = new Latte\Engine;
$latte->setTempDirectory('tmp');

use Steampixel\Route;

$current_week = (floor(((time() - 1631163600) / (60 * 60 * 24 * 7))) + 1) >= 1 ?: 1;  // Subtracts the current time from the start of week 1, and divides it by the length of a week to find the current week.

Route::add('/', function(){
  global $latte, $current_week;
  $latte->render('templates/index.latte', ['loggedIn' => $_SESSION['loggedIn'] ?? false]);
});

Route::add('/', function(){
  global $latte, $mysql;
  $latte->render('templates/index.latte', ['login_successful' => login()]);
}, 'POST');

Route::add('/picks', function(){
  global $latte, $mysql, $current_week;
  if (isset($_SESSION['username']) && !isset($_GET['name'])) $_GET['name'] = $_SESSION['username'];
  $result = $mysql->query("SELECT username FROM users");
  $users = array_column($result->fetch_all(MYSQLI_ASSOC), "username");
  if (isset($_GET['name'])) {
    $name = $_GET['name'];
    $stmt = $mysql->prepare('SELECT DISTINCT week FROM user_entries WHERE user_id = (SELECT user_id FROM users WHERE username = ?)');
    $stmt->bind_param('s', $name);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    $weeks = array_column($rows, 'week');
    if ((($_SESSION['username'] ?? null) == $_GET['name']) && (end($weeks) < $current_week)){
      array_push($weeks, $current_week);
      $unpicked = $current_week;
    }
  }
  $params = [
    'users' => $users,
    'name' => $name ?? null,
    'weeks' => $weeks ?? null,
    'unpicked' => $unpicked ?? null
  ];
  $latte->render("templates/picks.latte", $params);
});

Route::add('/standings', function(){
  global $latte, $mysql;
  $leaderboard = new Leaderboard($mysql);
  $params = [
    'leaderboard' => $leaderboard
  ];
  $latte->render('templates/standings.latte', $params);
});

Route::add("/view", function(){

});

Route::add("/select", function(){

});

Route::run('/');
?>
