<?php
    defined('BASEPATH') OR exit('No direct script access allowed');

    if(isset($_SESSION['login_details'])) {
        ?>
       <script type="text/javascript">
           location.href='/index.php/SiteWork/view_site_work';
       </script>
       <?php
    }
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
	<link href="/css/style.css" type="text/css" rel="stylesheet">

	<title>
		Welcome
	</title>
</head>

<body>

	<div class="row">
        <div class="col-lg-4">
        </div>
        
        <div class="col-lg-4" style="margin-top:10%">
            <div class="panel panel-default">
                <div class="panel-body">
                <h4 align="center">H M TRADING</h4>
                <p id="incorrect_credential"><font color="red">Username or password is incorrect!</font></p>
                <div class="form-group">
                    <label>Username </label>
                    <p id="email_warn"><font color="red">Please enter username!</font></p> 
                    <input type="text" class="form-control input-field" id="email" name="email">
                </div>
                    
                <div class="form-group">
	                <label>Password</label>
	                <p id="pass_warn"><font color="red">Please enter password!</font></p> 
					<input type="password" class="form-control input-field" id="password" name="password">
				</div>
				
				<div class="form-group" align="center">
					<button type="button" class="btn btn-block btn-default" id="loginBtn" onclick="login()">Submit</button>
                    <!-- <button type="button" class="btn btn-success" onclick="location.href='/ForgotPassword/'">Forgot password</button> -->
                </div>
                <div class="form-group">
                    <a href="#" style="color:#ff9933;">Forgot Password</a>
                    <p align="center" style="float:right;">Not registered? <a href="#" style="color:#ff9933;">Create an account</a></p>
                </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
		</div>
    </div>

    <script type="text/javascript">

    	$(function(){
    		$('#incorrect_credential').hide();
    		$('#email_warn').hide();
    		$('#pass_warn').hide();
    	})

    	function login()
        {
			var email = $("#email").val();
			email = email.trim();

			if (email === "" || email === null || email == 0)
            {    
                document.getElementById("email").focus();
                $('#incorrect_credential').hide();
	    		$('#email_warn').show();
	    		$('#pass_warn').hide();
                return false;
			}

			var password = $("#password").val();
			if (password === "" || password === null || (password.trim().length) == 0)
			{
                $("#password").focus();
                $('#incorrect_credential').hide();
	    		$('#email_warn').hide();
	    		$('#pass_warn').show();
                return false;
       		}

            var params = {
                            email_id:      email, 
                            password:   password
                        };
            $.ajax({
                url: '/index.php/Login/signIn',
				type: 'post',
				dataType: 'json',
				data: params,
				success: function (resp) {
					// console.log(resp);

                    if(resp!=null){
                        var login_array_length = Object.keys(resp).length;
                    }else{
                        var login_array_length = 0;
                    }

					if(login_array_length){
                        location.href="/index.php/Customer/";
                    }
                    else{
                        $('#incorrect_credential').show();
				    	$('#email_warn').hide();
				   		$('#pass_warn').hide();
                    }
                }
            });
        } 
            
            $(document).bind('keypress', function(e) {
            if (e.keyCode == 13) {
                $('#loginBtn').trigger('click');
            }
        });
    </script>
</body>

</html>