<?php
session_start();
require 'config/config.php';
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
}
?>

<!DOCTYPE html>
<html>
  <head>
    <title> NFL Pick'ems </title>
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/foundation-sites@6.6.3/dist/css/foundation.min.css" integrity="sha256-ogmFxjqiTMnZhxCqVmcqTvjfe1Y/ec4WaRj/aQPvn+I=" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/foundation-sites@6.6.3/dist/js/foundation.min.js" integrity="sha256-pRF3zifJRA9jXGv++b06qwtSqX1byFQOLjqa2PTEb2o=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="css/styles/custom.css"></link>
    <script>
      $(document).ready(function(){
        $(document).foundation();
        $('#name-select').change(function(){
          document.location.href = "?name=" + $("#name-select").val();
        });
      });
    </script>
  </head>
  <body>
    <div class="grid-container full">
      <div class="title-bar grid-x align-justify">
        <div class="cell small-5 medium-4 large-3">
          <nav>
            <ul style="margin-bottom:0px" class="breadcrumbs">
              <li style="font-size:1em;"><a href="/">Home</a></li>
              <li style="color:white; font-size:1em;">Picks</li>
            </ul>
          </nav>
        </div>
        <div class="cell small-7 medium-6 large-3 align-right grid-x">
          <div class="input-group" style="margin-bottom: 0px;">
            <span style="font-size: 0.9em;" class="input-group-label">Choose</span>
            <select id="name-select" class="input-group-field" style="margin-bottom:0px" required>
              <option class="" value="" disabled selected hidden>Choose name</option>
              <?php
                foreach ($users as $user) {
                  $selected = "";
                  if (isset($name))
                    $selected = $user == $name ? 'selected' : '';
                  echo "<option value='$user' $selected>$user</option>";
                }
              ?>
            </select>
          </div>
        </div>
      </div>
      <div style="margin-top:8px;" class="grid-x align-spaced align-left">
        <?php
          if (isset($weeks)) {
            foreach ($weeks as $week) {
              echo "
                <div class='cell grid-margin-x large-2 medium-3 small-5'>
                  <div class='card text-center'>
                    <div class='card-divider align-center'>
                      Week $week picks
                    </div>
                    <div class='card-section'>
                      <a href='/' class='button secondary'>
                        view/edit
                      </a>
                    </div>
                  </div>
                </div>
              ";
            }
          }
        ?>
      </div>
    </div>
  </body>
</html>
