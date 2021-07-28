<?php session_start() ?>

<!DOCTYPE html>
<html>
  <head>
    <title>NFL Pick'ems</title>
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/foundation-sites@6.6.3/dist/css/foundation.min.css" integrity="sha256-ogmFxjqiTMnZhxCqVmcqTvjfe1Y/ec4WaRj/aQPvn+I=" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/foundation-sites@6.6.3/dist/js/foundation.min.js" integrity="sha256-pRF3zifJRA9jXGv++b06qwtSqX1byFQOLjqa2PTEb2o=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="css/styles/custom.css"></link>
    <script>
      $(document).ready(function(){
        $(document).foundation();
      });
    </script>
  </head>
  <body>
    <div class="mobile-modal reveal" id="login-modal" data-reveal>
      <div class="mobile-modal-inner">
        <form>
          <div class="grid-container">
            <div class="grid-x grid-padding-x">
              <div class="cell">
                <label>Username
                  <input type="text" placeholder="Username">
                </label>
              </div>
              <div class="cell">
                <label>Password
                  <input type="password" placeholder="password">
                </label>
              </div>
              <div class="cell grid-x align-center">
                <button type="submit" class="button expanded">Submit</button>
              </div>
              <div class="cell grid-x align-center">
                <a href="register.php">Don't have an account yet?</a>
              </div>
            </div>
          </div>
        </form>
      </div>
      <button class="close-button" data-close aria-label="Close modal" type="button">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
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
        <a style="margin-right:2px" class="button-text button-border button small-5 large-3" href="picks.php">Pick/Edit Games</a>
        <a style="margin-right:2px" class="button-text button-border button small-5 large-3" href="standings.php">Standings</a>
      </div>
      <?php if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true){ ?>
        <div class="grid-x align-center">
          <button class="button-text button-border button small-5 large-3 secondary" data-open="login-modal">Login/Register</button>
        </div>
      <?php } ?>
    </div>
  </body>
</html>
