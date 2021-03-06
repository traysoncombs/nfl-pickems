<?php
require_once 'config/config.php';
require_once 'modules/utils/functions.php';
require_once 'vendor/autoload.php';
require_once 'modules/classes/Picks.php';
require_once 'modules/classes/Leaderboard.php';
require_once 'modules/classes/UserPicks.php';
require_once 'modules/db/update.php';
require_once 'modules/classes/PickValidityCheck.php';
require_once 'modules/classes/Stats.php';

/*spl_autoload_register(function ($class_name) {
  if (file_exists('modules/classes/' . $class_name . '.php')){
    include 'modules/classes/' . $class_name . '.php';
    return true;
  }
  return false;
});*/
date_default_timezone_set("America/Chicago");
session_start();

$latte = new Latte\Engine;
$latte->setTempDirectory('tmp');

use Steampixel\Route;
update_events();

$current_week = (floor(((time() - 1630976401) / (60 * 60 * 24 * 7))) + 1);  // changed start of week to tuesday for leaderboard updates. Internally everything else is still from Thursday.
Route::add('/', function(){
  global $latte, $current_week;
  $latte->render('templates/index.latte', ['loggedIn' => $_SESSION['loggedIn'] ?? false]);
});

Route::add('/', function(){
  global $latte, $mysql;
  $latte->render('templates/index.latte', ['show_dialog' => true, 'msg' => login() ? 'Login Success!' : 'Login Error']);
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
    'weeks' => $picks ?? null,
    'current_week' => $current_week
  ];
  $latte->render("templates/weekly_picks.latte", $params);
});

Route::add('/standings', function(){
  global $latte, $mysql, $current_week;
  $leaderboard = new Leaderboard($mysql, $current_week);
  $params = [
    'leaderboard' => $leaderboard,
    'stats' => new Stats($current_week, $mysql)
  ];
  $latte->render('templates/standings.latte', $params);
});

Route::add("/picks", function(){
  global $mysql, $latte, $current_week;
  if (!isset($_GET['username'], $_GET['week'])){
    header("location:javascript://history.go(-1)");
    exit;
  }
  $picks = new Picks($mysql, $_GET['week'], $_GET['username']);
  $latte->render('templates/picks.latte', ['picks' => $picks, 'current_week' => $current_week]);
});

Route::add("/picks", function(){ // need server side check to prevent locked entrties from being edited.
  global $mysql, $current_week;
  if(!isset($_POST['entries']) || !isset($_SESSION['loggedIn'])){
    http_response_code(400);
    return false;
  }
  $advanced_anticheat = new PickValidityCheck($current_week, $_POST);
  if (!$advanced_anticheat->full_check()) {
    http_response_code(400);
    return false;
  }
  $entries = $_POST['entries'];
  $stmt = $mysql->prepare("
    INSERT INTO
      user_entries (confidence, event_id, user_id, week, winner_id)
    VALUES
      (?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
      confidence = VALUES(confidence), winner_id = VALUES(winner_id)
  ");
  foreach ($entries as $entry){
    if (
      $stmt &&
      $stmt->bind_param('iiiii', $entry['confidence'], $entry['event_id'], $_SESSION['user_id'], $_POST['week'], $entry['winner_id']) &&
      $stmt->execute()
    ) {
      continue;
    } else {
      error_log("error updating user picks: {$mysql->error}");
      if ($_GLOBALS['debug'] ?? false) var_dump($mysql);
      http_response_code(400);
      return false;
    }
  }
  http_response_code(200);
  return true;
}, 'POST');

Route::add('/register', function(){
  global $latte;
  $latte->render('templates/register.latte');
});

Route::add('/register', function(){
    global $mysql, $latte;
    if (isset($_POST['username'], $_POST['password'])){
      $password_hash = hash('sha256', $_POST['password']);
      $res = prepared_statement(
        'INSERT INTO
          users (username, password_hash)
        VALUES (?, ?)',
        'ss',
        [$_POST['username'], $password_hash]
      );
      if ($res != null) {
        $latte->render('templates/index.latte', ['show_dialog' => true, 'msg' => 'Registration Error', 'color' => 'alert']);
      } else {
        $login_success = login();
        $latte->render('templates/index.latte', ['show_dialog' => true, 'msg' => $login_success ? 'Registration Successful!' : 'Registration Error', 'color' => $login_success ? 'success' : 'alert']);
      }
      unset($_POST['username'], $_POST['password']);
    }
}, 'POST');

Route::run('/');
?>
