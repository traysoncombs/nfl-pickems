<?php
require_once 'config/config.php';
require_once 'modules/utils/functions.php';
require_once 'vendor/autoload.php';

spl_autoload_register(function ($class_name) {
  if (file_exists('modules/classes/' . $class_name . '.php')){
    include 'modules/classes/' . $class_name . '.php';
    return true;
  }
  return false;
});

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

Route::add('/weekly_picks', function(){
  global $latte, $mysql, $current_week;
  if (isset($_SESSION['username']) && !isset($_GET['name'])) $_GET['name'] = $_SESSION['username'];
  $result = $mysql->query("SELECT username FROM users");
  $users = array_column($result->fetch_all(MYSQLI_ASSOC), "username");
  if (isset($_GET['name'])) {
    $picks = new UserPicks($mysql, $_GET['name'], $current_week);
  }
  $params = [
    'users' => $users,
    'weeks' => $picks ?? null
  ];
  $latte->render("templates/weekly_picks.latte", $params);
});

Route::add('/standings', function(){
  global $latte, $mysql;
  $leaderboard = new Leaderboard($mysql);
  $params = [
    'leaderboard' => $leaderboard
  ];
  $latte->render('templates/standings.latte', $params);
});

Route::add("/picks", function(){
  global $mysql, $latte;
  if (!isset($_GET['username'], $_GET['week'])){
    header("location:javascript://history.go(-1)");
    exit;
  }
  $picks = new Picks($mysql, $_GET['week'], $_GET['username']);
  $latte->render('templates/picks.latte', ['picks' => $picks]);
});

Route::run('/');
?>
