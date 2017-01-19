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
          <div class="form-box animated" ng-class="{'pulse': !formLoaded}">
            <p class="login-title">
              <span>Log in to start your session</span>
            </p>  
            <div id="block-system-main" class="login-register-block-main">
              <div class="login-content clearfix">
                <form ng-submit="submitLogin(credentials)" method="post">
                  <div class="row clearfix" style="margin-top:20px; margin-bottom:20px;">
                    <div class="col-md-12">
                      <label>Username</label>
                      <div class="input-group">
                        <span class="input-group-addon">U</span>
                        <input type="text" class="form-control" required="required" ng-model="credentials.username">    
                      </div>
                    </div>
                  </div>
                  <div class="row" style="margin-bottom:20px;">    
                    <div class="col-md-12">
                      <label>Password</label>
                      <div class="input-group">
                        <span class="input-group-addon">P</span>
                        <input type="password" class="form-control" required="required" ng-model="credentials.password">    
                      </div>
                    </div>
                  </div>  
                  <div class="table-buttons">
                    <div class="btn-group btn-group-justified" role="group">
                      <div class="btn-group" role="group">
                        <input class="btn btn-primary" ng-class="{'disabled': !loginFormComplete()}" type="submit" value="Log in">
                      </div>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>  
    <div class="bannerMessage alert-success">{{bannerMessage}}</div>                       
  </div>

          
                      
                    
