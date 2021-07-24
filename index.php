<?php session_start() ?>

<!DOCTYPE html>
<html>
  <head>
    <title> NFL Pick'ems </title>
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/foundation-sites@6.6.3/dist/css/foundation.min.css" integrity="sha256-ogmFxjqiTMnZhxCqVmcqTvjfe1Y/ec4WaRj/aQPvn+I=" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/foundation-sites@6.6.3/dist/js/foundation.min.js" integrity="sha256-pRF3zifJRA9jXGv++b06qwtSqX1byFQOLjqa2PTEb2o=" crossorigin="anonymous"></script>
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

    </style>
  </head>
  <body>
      <div class="grid-container fluid full">
        <div class="grid-x grid-padding-x align-center">
          <div class="cell large-2 small-4 medium-3">
            <img src="img/NFL.png"></img>
          </div>
        </div>
        <div class="grid-x grid-padding-x align-center">
          <div class="cell">
            <h1 class="text-center title">Pick'em</h1>
          </div>
        </div>
          <div class="grid-x align-center button-group">
            <a class="button-text button small-5 large-3" href="picks.php">Pick/Edit Games</a>
            <a class="button-text button small-5 large-3" href="standings.php">Standings</a>
          </div>
      </div>
      <?php if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true){ ?>

      <?php } ?>
  </body>
</html>
