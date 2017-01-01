<?php
    $server = '#larrycompress#';
    $username = '#larrycompress#';
    $password = '#larrycompress#';
    $db_name = '#larrycompress#';

    $link = mysql_connect($server, $username, $password) or die('Connection Error: ' . mysql_error());
    mysql_select_db($db_name, $link) or die('Select db Error: ' . mysql_error());

    date_default_timezone_set("Etc/GMT-8");
?>
