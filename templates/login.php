<?php session_start();

	$username 	    = $_SESSION[SESSION_KEY_NAME];
	$loginAttempt 	= $_SESSION[SESSION_KEY_LOGIN];
	$registerAttempt= $_SESSION[SESSION_KEY_REGISTER];
	$userid		      = $_SESSION[SESSION_KEY_USERID];

  if (isset($_SESSION[SESSION_KEY_LOGIN])) {
    echo "<script>
      window.location.href = 'main.php#/';
          </script> ";
  }
 ?>

  <div id="main">
    <div class="container login-register">
      <div class="row">
        <div class="col-sm-8 col-md-6 col-sm-offset-2 col-md-offset-3"> 
          <div class="login-logo">
            <img class="animated rotateIn" src="images/opal_logo_transparent_purple.png" height="200" width="200">
            <h1><b>opal</b> ADMIN</h1>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-sm-8 col-md-6 col-sm-offset-2 col-md-offset-3">
          <div ng-include="'templates/login-form.php'"></div>
        </div>
      </div>
    </div>  
    <div class="bannerMessage alert-success">{{bannerMessage}}</div>                       
  </div>

          
                      
                    
