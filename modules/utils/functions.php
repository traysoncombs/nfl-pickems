<?php
require 'config/config.php';
function login() {
  global $mysql;
  $username = $_POST['username'];
  $password = $_POST['password'];
  if (isset($username, $password)) {
    $password_hash = hash('sha256', $password);
    $stmt = $mysql->prepare('SELECT user_id FROM users WHERE username=? AND password_hash=?');
    $stmt->bind_param('ss', $username, $password_hash);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows == 1) {
      $_SESSION['username'] = $username;
      $_SESSION['loggedIn'] = true;
      return true;
    }
    return false;
  }
  return false;
}

function prepared_statement($query, $param_types, $params){
  global $mysql;
  $stmt = $mysql->prepare($query);
  if (
  	$stmt &&
  	$stmt->bind_param($param_types, ...$params) &&
  	$stmt->execute()
  ) return $stmt->get_result();
  error_log('Failed to execute query: ', $stmt->error);
  if ($GLOBALS['debug']) var_dump($stmt->error);
  return null;
}

function array_manipulate($callback, $array) {
  $new = [];
  foreach($array as $k => $v) {
    $u = $callback($k, $v);
    $new[key($u)] = current($u);
  }
  return $new;
}

?>
