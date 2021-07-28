<?php
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
    <style>
      body, html {
        height: 100%;
      }

      @font-face {
          font-family: "traffic";
          src: url('css/font/traffic.ttf') format('truetype');
      }

      body {
        background: url('img/stadium.jpg') no-repeat bottom fixed;
        -webkit-background-size: cover;
        -moz-background-size: cover;
        -o-background-size: cover;
        background-size: cover;
        z-index: 0;
        width: 100% !important;
        height: 100% !important;
      }

      .title {
        font-size: 15vh;
        font-family: "traffic";
        letter-spacing: 0.05em;
        color: white;
        text-shadow: 0 0 3px #FF0000, 0 0 5px #0000FF;
      }

      @media only screen and (max-width:600px){
        .button-text {
          font-size: 1.1em !important;
        }
      }

      @media only screen and (min-width:601px){
        .button-text {
          font-size: 1.6em !important;
        }
      }
      
      select:invalid {
        color: gray; 
      }
    </style>
    <script>
      $(document).ready(function(){
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
      <div class="grid-x align-center">
        <?php
          if (isset($weeks)) {
            foreach ($weeks as $week) {
              echo "
                <div class='card'>
                  <div class='card-divider'>
                    week $week picks
                  </div>
                  <div class='card-section'>
                    view/edit
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
