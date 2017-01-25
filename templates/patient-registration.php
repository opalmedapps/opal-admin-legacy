<?php session_start();

	if (!isset($_SESSION[SESSION_KEY_LOGIN])) {
		echo "<script>
			window.location.href = 'php/user/logout.php';
		      </script> ";
	}

?> 
<div id="main">
    <div id="top">
      <div class="clearfix">
        <div class="row">
          <div class="col-md-2" style="position:fixed;">
            <div ng-controller="sidePanelMenuController">
              <div class="row side-logo" ng-click="goToHome()">
                <div class="col-md-12">
                  <img style="margin-top: -20px;" class="animated rotateIn" src="images/opal_logo_transparent_purple.png" height="130" width="130">
                  <h1 class="animated fadeIn">opal <strong>ADMIN</strong> </h1>
                </div>
              </div>   
            </div>  
            <div ng-show="validSSN.status=='valid'" class="row">
              <div class="side-menu-title">
                <div class="horz-line" style="height: 10px; text-align: center">
                  <span style="background-color:#e6edfa; padding: 0 10px;">
                    <h2>Summary</h2>
                  </span>  
                </div>  
              </div>
            </div>
            <div ng-show="validSSN.status=='valid'" class="row">
              <div class="progress progress-striped active">
                <div class="progress-bar" ng-class="{'progress-bar-success': stepProgress == 100}" role="progressbar" aria-valuenow="{{stepProgress}}" aria-valuemin="0" aria-valuemax="100" style="width: {{stepProgress}}%">
                </div>
              </div>
            </div>
            <div ng-show="validSSN.status=='valid'" class="panel-container">
              <div class="panel-info">
                <div class="panel-content" style="padding-top: 0">
                  <table class="summary-box" style="width: 100%">
                    <col width="7%">
                    <col width="93%"> 
                    <tr>
                      <td>
                        <span ng-hide="validEmail.status == 'valid'" class="glyphicon glyphicon-minus"></span>
                        <span ng-show="validEmail.status == 'valid'" style="color:#5cb85c" class="glyphicon glyphicon-ok"></span>
                      </td>
                      <td>
                        <div class="horz-line" style="line-height: 0px;">
                          <span class="title" style="background-color: #fff;">
                            <strong>Email</strong>
                          </span>
                        </div> 
                      </td>
                    </tr>

                    <tr>
                      <td>
                        <span ng-hide="validPassword.status == 'valid' && validConfirmPassword.status == 'valid'" class="glyphicon glyphicon-minus"></span>
                        <span ng-show="validPassword.status == 'valid' && validConfirmPassword.status == 'valid'" style="color:#5cb85c" class="glyphicon glyphicon-ok"></span>
                      </td>
                      <td>
                        <div class="horz-line" style="line-height: 0px;">
                          <span class="title" style="background-color: #fff;">
                            <strong>Password</strong>
                          </span>
                        </div> 
                      </td>
                    </tr>

                    <tr>
                      <td>
                        <span ng-hide="newPatient.language" class="glyphicon glyphicon-minus"></span>
                        <span ng-show="newPatient.language" style="color:#5cb85c" class="glyphicon glyphicon-ok"></span>
                      </td>
                      <td>
                        <div class="horz-line" style="line-height: 0px;">
                          <span class="title" style="background-color: #fff;">
                            <strong>Language</strong>
                          </span>
                        </div> 
                      </td>
                    </tr>

                    <tr>
                      <td>
                        <span ng-hide="validCellNum.status=='valid'" class="glyphicon glyphicon-minus"></span>
                        <span ng-show="validCellNum.status=='valid'" style="color:#5cb85c" class="glyphicon glyphicon-ok"></span>
                      </td>
                      <td>
                        <div class="horz-line" style="line-height: 0px;">
                          <span class="title" style="background-color: #fff;">
                            <strong>Cellphone Number</strong>
                          </span>
                        </div> 
                      </td>
                    </tr>

                    <tr>
                      <td>
                        <span ng-hide="validAnswer1.status == 'valid'" class="glyphicon glyphicon-minus"></span>
                        <span ng-show="validAnswer1.status == 'valid'" style="color:#5cb85c" class="glyphicon glyphicon-ok"></span>
                      </td>
                      <td>
                        <div class="horz-line" style="line-height: 0px;">
                          <span class="title" style="background-color: #fff;">
                            <strong>Security Question 1</strong>
                          </span>
                        </div> 
                      </td>
                    </tr>

                    <tr>
                      <td>
                        <span ng-hide="validAnswer2.status == 'valid'" class="glyphicon glyphicon-minus"></span>
                        <span ng-show="validAnswer2.status == 'valid'" style="color:#5cb85c" class="glyphicon glyphicon-ok"></span>
                      </td>
                      <td>
                        <div class="horz-line" style="line-height: 0px;">
                          <span class="title" style="background-color: #fff;">
                            <strong>Security Question 2</strong>
                          </span>
                        </div> 
                      </td>
                    </tr>

                    <tr>
                      <td>
                        <span ng-hide="validAnswer3.status == 'valid'" class="glyphicon glyphicon-minus"></span>
                        <span ng-show="validAnswer3.status == 'valid'" style="color:#5cb85c" class="glyphicon glyphicon-ok"></span>
                      </td>
                      <td>
                        <div class="horz-line" style="line-height: 0px;">
                          <span class="title" style="background-color: #fff;">
                            <strong>Security Question 3</strong>
                          </span>
                        </div> 
                      </td>
                    </tr>
                  </table>
                  <div class="table-buttons" style="text-align: center">
                    <form ng-submit="registerPatient()" method="post">
                      <button class="btn btn-primary" ng-class="{'disabled': !checkRegistrationForm()}" type="submit">Register</button>
                    </form>
                  </div>
                </div>
              </div>
            </div>



            <div class="panel-container animated" ng-class="{pulse: hoverA}" ng-mouseenter="hoverA=true" ng-mouseleave="hoverA=false" style="cursor:pointer;" ng-click="goBack()">
              <div class="side-panel-info">
                <div class="panel-content" style="text-align:center">
                  <span style="font-size: 23px; padding-right:10px;" class="glyphicon glyphicon-arrow-left" aria-hidden="true"></span>
                  <span style="font-size: 30px; font-weight:700">Go Back</span>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-10 animated fadeIn" style="margin-left:17%">
            <div class="row main-title">
              <div class="col-md-6 title-content">
                <span class="glyphicon glyphicon-list-alt"></span>
                <h1><strong>Registration</strong></h1>
              </div>
              <div class="col-md-6 title-breadcrumbs"> 
                <span>
                  <span><a href="#/">Home</a></span> 
                  <span class="glyphicon glyphicon-menu-right teflon"></span> 
                  <span><a href="#/patients">Patients</a></span>
                  <span class="glyphicon glyphicon-menu-right teflon"></span>
                  <span><strong>Registration</strong></span>
                </span>
              </div>
            </div>    
            <div class="row">
              <div class="col-md-10 side-menu-title">
                <div style="height: 10px; border-bottom: 1px solid #6f5499;">
                  <span style="background-color:#e6edfa; padding: 0 10px;">
                    <span class="glyphicon glyphicon-menu-down"></span>
                    <h2>Registration SSN</h2>
                  </span>  
                </div>  
              </div>
            </div>
            <div class="row">
              <div class="col-md-10">
                <p style="margin-bottom: 10px;">
                  Please enter a <strong>12-character</strong> Medicare card number to start the registration process.
                </p>
              </div>
            </div> 
            <div class="row" style="margin-bottom: 20px;">
              <div class="col-md-8 form-horizontal">
                <form ng-submit="validateSSN(validSSN.SSN)">
                  <div class="form-group has-feedback" ng-class="{'has-success': validSSN.status=='valid', 'has-error': validSSN.status=='invalid', 'has-warning': validSSN.status=='warning'}">
                    <div class="col-md-5">
                      <input type="text" class="form-control" required="required" ng-change="flushNewPatient()" ng-model="validSSN.SSN" placeholder="Enter Medicare Card Number" maxlength="20">
                      <span class="glyphicon form-control-feedback" ng-class="{'glyphicon-ok':validSSN.status=='valid', 'glyphicon-remove':validSSN.status=='invalid', 'glyphicon-warning-sign':validSSN.status=='warning'}" aria-hidden="true"></span>
                    </div>
                    <div class="col-md-1" style="padding-left:0px;">
                    	<div>
                    		<button class="btn btn-primary" type="submit">
                    			<span class="glyphicon glyphicon-search"></span> Search
                    		</button>
                    	</div>
                    </div>
                    <em class="control-label col-md-4" ng-show="validSSN.status" ng-class="{'has-error': validSSN.status == 'invalid', 'has-success': validSSN.status == 'valid'}" style="text-align:left; padding-left:10px;">
                      {{validSSN.message}}
                    </em>
                  </div>
                </form>
              </div>
            </div>
            <div ng-show="validSSN.status=='valid'">
              <div style="margin-bottom:20px; color:#3c763d;">
                <h1><strong>--- Patient: <em>{{newPatient.data.lastname}}, {{newPatient.data.firstname}}</em></strong></h1>
              </div>
              <div class="row">
                <div class="col-md-10 side-menu-title">
                  <div style="height: 10px; border-bottom: 1px solid #6f5499;">
                    <span style="background-color:#e6edfa; padding: 0 10px;">
                      <span class="glyphicon glyphicon-menu-down"></span>
                      <h2>Email & Password</h2>
                    </span>  
                  </div>  
                </div>
              </div>   
              <div class="row">
                <div class="col-md-10">
                  <p style="margin-bottom: 10px;">
                    <span style="color:#d9534f"><strong>Required fields:</strong></span>
                    Please add a valid email and password.
                  </p>
                </div>
              </div>  
              <div class="row"> 
                <div class="col-md-8">
                  <div class="panel" ng-class="(validEmail.status == 'valid' && validPassword.status == 'valid' && validConfirmPassword.status == 'valid') ? 'panel-success': 'panel-danger'">
                    <div class="panel-heading"><strong>Email & Password</strong>
                      <span ng-hide="validEmail.status == 'valid' && validPassword.status == 'valid' && validConfirmPassword.status == 'valid'" style="float:right"><em>Incomplete</em></span>
                      <span ng-show="validEmail.status == 'valid' && validPassword.status == 'valid' && validConfirmPassword.status == 'valid'" style="float:right"><em>Complete</em></span>
                    </div>
                    <div class="panel-body form-horizontal">
                      <div class="form-group has-feedback" ng-class="{'has-success': validEmail.status == 'valid', 'has-error': validEmail.status == 'invalid', 'has-warning': validEmail.status == 'warning'}">
                        <label class="control-label col-md-2">
                          Email
                        </label>
                        <div class="col-md-7">
                          <input type="text" class="form-control" required="required" ng-blur="validateEmail(newPatient.email)" ng-change="emailUpdate()" ng-model="newPatient.email" placeholder="example@someone.com">
                          <span ng-show="validEmail.status" class="glyphicon form-control-feedback" ng-class="{'glyphicon-ok': validEmail.status == 'valid', 'glyphicon-remove': validEmail.status == 'invalid', 'glyphicon-warning-sign': validEmail.status == 'warning'}" aria-hidden="true"></span>
                        </div>
                        <em class="control-label col-md-3" ng-show="validEmail.status == 'invalid' || validEmail.status == 'warning'" ng-class="{'has-success': validEmail.status == 'valid', 'has-error': validEmail.status == 'invalid', 'has-warning': validEmail.status == 'warning'}" style="text-align:left;">
                          {{validEmail.message}}
                        </em>
                      </div>
                      <div class="form-group has-feedback" ng-class="{'has-success': validPassword.status == 'valid', 'has-error': validPassword.status == 'invalid', 'has-warning': validPassword.status == 'warning'}">
                        <label class="control-label col-md-2">
                          Password <br><em><small>(min: 6 characters)</small></em>
                        </label>
                        <div class="col-md-7">
                          <input type="password" class="form-control" required="required" ng-blur="validatePassword(newPatient.password)" ng-change="passwordUpdate()" ng-model="newPatient.password">
                          <span ng-show="validPassword.status" class="glyphicon form-control-feedback" ng-class="{'glyphicon-ok': validPassword.status == 'valid', 'glyphicon-remove': validPassword.status == 'invalid', 'glyphicon-warning-sign': validPassword.status == 'warning'}" aria-hidden="true"></span>
                        </div>
                        <em class="control-label col-md-3" ng-show="validPassword.status == 'invalid' || validPassword == 'warning'" ng-class="{'has-success': validPassword.status == 'valid', 'has-error': validPassword.status == 'invalid', 'has-warning': validPassword.status == 'warning'}" style="text-align:left">
                          {{validPassword.message}}
                        </em>
                      </div>    
                      <div class="form-group has-feedback" ng-class="{'has-success': validConfirmPassword.status == 'valid', 'has-error': validConfirmPassword.status == 'invalid', 'has-warning': validConfirmPassword.status == 'warning'}">
                        <label class="control-label col-md-2">
                          Re-type Password
                        </label>
                        <div class="col-md-7">
                          <input type="password" class="form-control" required="required" ng-change="validateConfirmPassword(newPatient.confirmPassword)" ng-model="newPatient.confirmPassword" ng-disabled="newPatient.password.length < 6 || !newPatient.password">
                          <span ng-show="validConfirmPassword.status" class="glyphicon form-control-feedback" ng-class="{'glyphicon-ok': validConfirmPassword.status == 'valid', 'glyphicon-remove': validConfirmPassword.status == 'invalid', 'glyphicon-warning-sign': validConfirmPassword.status == 'warning'}" aria-hidden="true"></span>
                        </div>
                        <em class="control-label col-md-3" ng-show="validConfirmPassword.status == 'invalid' || validConfirmPassword.status == 'warning'" ng-class="{'has-success': validConfirmPassword.status == 'valid', 'has-error': validConfirmPassword.status == 'invalid', 'has-warning': validConfirmPassword.status == 'warning'}" style="text-align:left">
                          {{validConfirmPassword.message}}
                        </em>
                      </div>
                    </div>
                  </div>
                </div>          
              </div>

              <div class="row">
                <div class="col-md-10 side-menu-title">
                  <div style="height: 10px; border-bottom: 1px solid #6f5499;">
                    <span style="background-color:#e6edfa; padding: 0 10px;">
                      <span class="glyphicon glyphicon-menu-down"></span>
                      <h2>Language</h2>
                    </span>  
                  </div>  
                </div>
              </div>   
              <div class="row">
                <div class="col-md-10">
                  <p style="margin-bottom: 10px;">
                    <span style="color:#d9534f"><strong>Required field:</strong></span>
                    Please choose the primary language of choice for Opal.
                  </p>
                </div>
              </div>  
              <div class="row" style="margin-bottom: 15px;">
                <div class="col-md-4">
                  <div class="row">
                    <div class="col-md-5">
                      <select ng-model="newPatient.language" ng-change="languageUpdate()" ng-options="language.id as language.name for language in languages" class="form-control">
                        <option ng-hide="newPatient.language" value="">Select Language</option>
                      </select>
                    </div>
                  </div>
                </div>  
              </div>

              <div class="row">
                <div class="col-md-10 side-menu-title">
                  <div style="height: 10px; border-bottom: 1px solid #6f5499;">
                    <span style="background-color:#e6edfa; padding: 0 10px;">
                      <span class="glyphicon glyphicon-menu-down"></span>
                      <h2>Cellphone Number</h2>
                    </span>  
                  </div>  
                </div>
              </div>   
              <div class="row">
                <div class="col-md-10">
                  <p style="margin-bottom: 10px;">
                    <span><strong>Optional field:</strong></span>
                    Please assign a cellphone number in a <strong>10-digit</strong> format (no hyphens). 
                  </p>
                </div>
              </div>   
              <div class="row">
                <div class="col-md-8 form-horizontal">
                  <div class="form-group has-feedback" ng-class="{'has-success': validCellNum.status=='valid', 'has-error': validCellNum.status=='invalid'}">
                    <div class="col-md-5">
                      <input type="text" class="form-control" ng-model="newPatient.cellNum" ng-change="validateCellNum(newPatient.cellNum)" placeholder="1234567890" maxlength="10">
                      <span ng-show="newPatient.cellNum" class="glyphicon form-control-feedback" ng-class="{'glyphicon-ok':validCellNum.status=='valid', 'glyphicon-remove':validCellNum.status=='invalid'}" aria-hidden="true"></span>
                    </div>
                    <em class="control-label col-md-3" ng-show="validCellNum.status == 'invalid'" ng-class="{'has-error': validCellNum.status == 'invalid'}" style="text-align:left">
                      {{validCellNum.message}}
                    </em>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-10 side-menu-title">
                  <div style="height: 10px; border-bottom: 1px solid #6f5499;">
                    <span style="background-color:#e6edfa; padding: 0 10px;">
                      <span class="glyphicon glyphicon-menu-down"></span>
                      <h2>Security Questions</h2>
                    </span>  
                  </div>  
                </div>
              </div>   
              <div class="row">
                <div class="col-md-10">
                  <p style="margin-bottom: 10px;">
                    <span style="color:#d9534f"><strong>Required fields:</strong></span>
                    Please choose three distinct security questions and answer them. 
                  </p>
                </div>
              </div>  
              <div class="row"> 
                <div class="col-md-7">
                  <div class="panel" ng-class="validAnswer1.status == 'valid' ? 'panel-success': 'panel-danger'">
                    <div class="panel-heading"><strong>Security Question 1</strong>
                      <span ng-hide="validAnswer1.status == 'valid'" style="float:right"><em>Incomplete</em></span>
                      <span ng-show="validAnswer1.status == 'valid'" style="float:right"><em>Complete</em></span>
                    </div>
                    <div class="panel-body form-horizontal">
                      <div class="form-group has-feedback" ng-class="{'has-success': newPatient.securityQuestion1.serial}">
                        <label class="control-label col-md-2">
                          Question 1
                        </label>
                        <div class="col-md-7">
                          <select ng-model="newPatient.securityQuestion1.serial" ng-change="securityQuestion1Update()" ng-options="securityQuestion.serial as securityQuestion.question for securityQuestion in securityQuestions | filter:filterFromQ2 | filter:filterFromQ3" class="form-control" >
                            <option ng-hide="newPatient.securityQuestion1.serial" value="">Select Question 1</option>
                          </select>
                        </div>
                      </div>    
                      <div class="form-group has-feedback" ng-class="{'has-success': validAnswer1.status == 'valid', 'has-error': validAnswer1.status == 'invalid'}">
                        <label class="control-label col-md-2">
                          Answer
                        </label>
                        <div class="col-md-7">
                          <input type="text" class="form-control" required="required" ng-model="newPatient.securityQuestion1.answer" ng-disabled="!newPatient.securityQuestion1.serial" ng-change="validateAnswer1(newPatient.securityQuestion1.answer)" placeholder="Your answer here">
                          <span ng-show="newPatient.securityQuestion1.answer" class="glyphicon form-control-feedback" ng-class="{'glyphicon-ok': validAnswer1.status == 'valid', 'glyphicon-remove': validAnswer1.status == 'invalid'}" aria-hidden="true"></span>
                        </div>
                        <em class="col-md-3 control-label" ng-show="validAnswer1.status == 'invalid'" ng-class="{'has-error': validAnswer1.status == 'invalid'}" style="text-align:left;">
                          {{validAnswer1.message}}
                        </em>
                      </div>
                    </div>
                  </div>
                </div>          
                <div class="col-md-7">
                  <div class="panel" ng-class="validAnswer2.status == 'valid' ? 'panel-success': 'panel-danger'">
                    <div class="panel-heading"><strong>Security Question 2</strong>
                      <span ng-hide="validAnswer2.status == 'valid'" style="float:right"><em>Incomplete</em></span>
                      <span ng-show="validAnswer2.status == 'valid'" style="float:right"><em>Complete</em></span>
                    </div>
                    <div class="panel-body form-horizontal">
                      <div class="form-group has-feedback" ng-class="{'has-success': newPatient.securityQuestion2.serial}">
                        <label class="control-label col-md-2">
                          Question 2
                        </label>
                        <div class="col-md-7">
                          <select ng-model="newPatient.securityQuestion2.serial" ng-change="securityQuestion2Update()" ng-options="securityQuestion.serial as securityQuestion.question for securityQuestion in securityQuestions | filter:filterFromQ1 | filter:filterFromQ3" class="form-control" >
                            <option ng-hide="newPatient.securityQuestion2.serial" value="">Select Question 2</option>
                          </select>
                        </div>
                      </div>    
                      <div class="form-group has-feedback" ng-class="{'has-success': validAnswer2.status == 'valid', 'has-error': validAnswer2.status == 'invalid'}">
                        <label class="control-label col-md-2">
                          Answer
                        </label>
                        <div class="col-md-7">
                          <input type="text" class="form-control" required="required" ng-model="newPatient.securityQuestion2.answer" ng-disabled="!newPatient.securityQuestion2.serial" ng-change="validateAnswer2(newPatient.securityQuestion2.answer)" placeholder="Your answer here">
                          <span ng-show="newPatient.securityQuestion2.answer" class="glyphicon form-control-feedback" ng-class="{'glyphicon-ok': validAnswer2.status == 'valid', 'glyphicon-remove': validAnswer2.status == 'invalid'}" aria-hidden="true"></span>
                        </div>
                        <em class="col-md-3 control-label" ng-show="validAnswer2.status == 'invalid'" ng-class="{'has-error': validAnswer2.status == 'invalid'}" style="text-align:left;">
                          {{validAnswer2.message}}
                        </em>
                      </div>
                    </div>
                  </div>
                </div> 
                <div class="col-md-7">
                  <div class="panel" ng-class="validAnswer3.status == 'valid' ? 'panel-success': 'panel-danger'">
                    <div class="panel-heading"><strong>Security Question 3</strong>
                      <span ng-hide="validAnswer3.status == 'valid'" style="float:right"><em>Incomplete</em></span>
                      <span ng-show="validAnswer3.status == 'valid'" style="float:right"><em>Complete</em></span>
                    </div>
                    <div class="panel-body form-horizontal">
                      <div class="form-group has-feedback" ng-class="{'has-success': newPatient.securityQuestion3.serial}">
                        <label class="control-label col-md-2">
                          Question 3
                        </label>
                        <div class="col-md-7">
                          <select ng-model="newPatient.securityQuestion3.serial" ng-change="securityQuestion3Update()" ng-options="securityQuestion.serial as securityQuestion.question for securityQuestion in securityQuestions | filter:filterFromQ1 | filter:filterFromQ2" class="form-control" >
                            <option ng-hide="newPatient.securityQuestion3.serial" value="">Select Question 3</option>
                          </select>
                        </div>
                      </div>    
                      <div class="form-group has-feedback" ng-class="{'has-success': validAnswer3.status == 'valid', 'has-error': validAnswer3.status == 'invalid'}">
                        <label class="control-label col-md-2">
                          Answer
                        </label>
                        <div class="col-md-7">
                          <input type="text" class="form-control" required="required" ng-model="newPatient.securityQuestion3.answer" ng-disabled="!newPatient.securityQuestion3.serial" ng-change="validateAnswer3(newPatient.securityQuestion3.answer)" placeholder="Your answer here">
                          <span ng-show="newPatient.securityQuestion3.answer" class="glyphicon form-control-feedback" ng-class="{'glyphicon-ok': validAnswer3.status == 'valid', 'glyphicon-remove': validAnswer3.status == 'invalid'}" aria-hidden="true"></span>
                        </div>
                        <em class="col-md-3 control-label" ng-show="validAnswer3.status == 'invalid'" ng-class="{'has-error': validAnswer3.status == 'invalid'}" style="text-align:left;">
                          {{validAnswer3.message}}
                        </em>
                      </div>
                    </div>
                  </div>
                </div>          
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
    <div class="bannerMessage alert-success">{{bannerMessage}}</div>
  </div>
                 