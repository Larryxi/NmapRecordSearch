<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>index</title>

    <!-- Bootstrap -->
    <link href="dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="http://cdn.bootcss.com/html5shiv/3.7.2/html5shiv.min.js"></script>
        <script src="http://cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <style type="text/css">
        .starter-template {
            padding: 40px 15px;
            /*text-align: center;*/
        }
    </style>
    </head>
    <body>

        <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
            <div class="container">
                <div class="navbar-header">
                    <a href="#" class="navbar-brand">Nmap Result Search</a>
                </div>

                <ul class="nav navbar-nav navbar-right">
                    <li class="active"><a href="index.php?action=about">About</a></li>
                    <li><a href="index.php?action=record">Record</a></li>
                    <li><a href="index.php?action=search">Search</a></li>
                </ul>
            </div>
        </nav>

        <div class="container">

            <div class="starter-template" role="main">
                <?php
                    if (!file_exists('install/install.lock')) {
                        include('install/index.php');
                    } else {
                        $action = isset($_GET['action']) ? $_GET['action'] : 'about';
                        if ($action == 'search') {
                            include('search.php');
                        } elseif ($action == 'record') {
                            include('record.php');
                        } else {
                            include('about.php');
                        }
                    }
                ?>
            </div>
        </div>
        <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
        <script src="dist/jquery/1.11.1/jquery.min.js"></script>
        <!-- Include all compiled plugins (below), or include individual files as needed -->
        <script src="dist/js/bootstrap.min.js"></script> 
    </body>
</html>