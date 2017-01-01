<?php
    if (file_exists('install.lock')) {
        die('Forbidden!');
    }

    $message = 'Welcome to install';

    if (isset($_POST['submit'])) {
        $link = mysql_connect($_POST['server'], $_POST['username'], $_POST['password']);
        if (!$link) {
            die('Connection Error: ' . mysql_error());
        }

        mysql_select_db($_POST['db_name'], $link) or die('Select db Error: ' . mysql_error());

        $config_text = file_get_contents('config.php');
        $config_file = fopen('config.php', 'w');
        $config_array = array($_POST['server'], $_POST['username'], $_POST['password'], $_POST['db_name']);
        for ($i=0; $i <count($config_array) ; $i++) { 
            $config_text = preg_replace('/#larrycompress#/', $config_array[$i], $config_text, 1);
        }
        fwrite($config_file, $config_text);
        fclose($config_file);

        $sql_init = explode(';', file_get_contents('install/import.sql'));
        for ($i=0; $i <count($sql_init); $i++) {
            mysql_query($sql_init[$i]);
        }

        $lock_file = fopen('install/install.lock', 'w');
        fwrite($lock_file, 'larrycompress');
        fclose($lock_file);

        $message = 'Install successfully';
    }
?>

<h1 class="text-center"><?php echo $message; ?></h1>

<form method="post" class="form-horizontal" role="form">
    <div class="form-group">
        <label for="server" class="col-sm-2 col-sm-offset-3 control-label">Database server</label>
        <div class="col-sm-4">
            <input type="text" class="form-control" name="server" id="server" placeholder="Database server address">
        </div>
    </div>

    <div class="form-group">
        <label for="username" class="col-sm-2 col-sm-offset-3 control-label">Database username</label>
        <div class="col-sm-4">
            <input type="text" class="form-control" name="username" id="username" placeholder="Database username">
        </div>
    </div>

    <div class="form-group">
        <label for="password" class="col-sm-2 col-sm-offset-3 control-label">Database password</label>
        <div class="col-sm-4">
            <input type="password" class="form-control" name="password" id="password" placeholder="Database password">
        </div>
    </div>

    <div class="form-group">
        <label for="db_name" class="col-sm-2 col-sm-offset-3 control-label">Database name</label>
        <div class="col-sm-4">
            <input type="text" class="form-control" name="db_name" id="db_name" placeholder="Database name">
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-offset-5 col-sm-4">
            <button type="submit" name="submit" class="btn btn-default">Install</button>
        </div>
    </div>
</form>