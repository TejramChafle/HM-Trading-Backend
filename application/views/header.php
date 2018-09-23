<?php

    if(!isset($_SESSION['login_details'])) {
        ?>
       <script type="text/javascript">
           location.href='/';
       </script>
       <?php
    }

    $user = $_SESSION['login_details'];

    // echo '<pre>';
    // print_r($user);
    // echo '</pre>';

    $url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];

    $customer      = "";


    if ((strpos($url, '/Customer') !== false) || (strripos($url, '/Customer') !== false)) {
        $customer = "open";
    }


?>

<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <link href="/css/style.css" type="text/css" rel="stylesheet">
    <!-- <link rel="stylesheet" href="http://www.w3schools.com/lib/w3.css"> -->
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">
    <script src="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>

</head>

<body>


<!-- navbar-fixed-top -->
<nav class="navbar navbar-inverse">
  <div class="container">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="#">H M Trading</a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul class="nav navbar-nav">
        <!-- <li class='<? echo $open; ?>' ><a href="/index.php/WorkDone">Work</a></li> -->
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Site Work <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="/index.php/Customer/add_customer">Add Customer</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="/index.php/Customer/">View Customers</a></li>
          </ul>
        </li>
      </ul>
      <!-- <form class="navbar-form navbar-left">
        <div class="form-group">
          <input type="text" class="form-control" placeholder="Search">
        </div>
        <button type="submit" class="btn btn-default">Submit</button>
      </form> -->

      <ul class="nav navbar-nav navbar-right">
        <li><a href="#"><span class="glyphicon glyphicon-user"></span> <?php echo $user['name']; ?></a></li>
        <li><a href="/index.php/Login/signOut"><span class="glyphicon glyphicon-log-out"></span> Logout</a></li>
      </ul>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>

     <!--<footer>
      <p>Website is designed and developed by <a href="http://wizbee.com/" target="_blank" >WizBee Business Solutions</a></p>
    </footer>-->
<script>
$(document).ready(function() {
    $('.news a').click(function(){
         $('.selected').removeClass('selected')
         $(this).addClass("selected");
    });
});
</script>
</body>
</html>
