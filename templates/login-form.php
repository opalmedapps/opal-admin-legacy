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
          <div class="bannerMessage alert-success">{{bannerMessage}}</div>