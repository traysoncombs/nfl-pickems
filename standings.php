<?php


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
              <li style="color:white; font-size:1em;">Standings</li>
            </ul>
          </nav>
        </div>
      </div>
      <div class="table-scroll">
        <table>
        </table>
      </div>
    </div>
  </body>
</html>
