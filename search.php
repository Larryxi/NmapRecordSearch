<h1 class="text-center">Nmap Result Search</h1>
<form class="form-horizontal" role="form" action="index.php?action=search" method="post">
    <div class="form-group">
        <label for="project_name" class="col-sm-2 col-sm-offset-3 control-label">Project name</label>
        <div class="col-sm-4">
            <input type="text" class="form-control" name="project_name" id="project_name" value="<?php echo isset($_POST['project_name']) ? $_POST['project_name'] : ''; ?>" placeholder="Project name">
        </div>
    </div>

    <div class="form-group">
        <label for="project_id" class="col-sm-2 col-sm-offset-3 control-label">Project id</label>
        <div class="col-sm-4">
            <input type="text" class="form-control" name="project_id" id="project_id" value="<?php echo isset($_POST['project_id']) ? $_POST['project_id'] : ''; ?>" placeholder="Project id" >
        </div>
    </div>

    <div class="form-group">
        <label for="search_type" class="col-sm-2 col-sm-offset-3 control-label">Search type</label>
        <div class="col-sm-4">
            <select class="form-control" name="search_type">
                <option value="port_num">port_num</option>
                <option value="service_name">service_name</option>
                <option value="service_product">service_product</option>
                <option value="host_ip">host_ip</option>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="search_content" class="col-sm-2 col-sm-offset-3 control-label">Search ontent</label>
        <div class="col-sm-4">
            <input type="text" class="form-control" name="search_content" id="search_content" placeholder="Search content">
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-offset-5 col-sm-4">
            <button type="submit" name="submit" class="btn btn-default">Search</button>
        </div>
    </div>
</form>

<?php
    include("config.php");

    if (isset($_POST["submit"])) {
        $project_name = $_POST["project_name"];
        $project_id = $_POST["project_id"];
        $sql_tmp = "SELECT scan_id FROM scan_list WHERE project_name='$project_name' AND project_id=$project_id";
        $result = mysql_query($sql_tmp);
        $row = mysql_fetch_row($result);
        $scan_id = $row[0];

        $search_type = $_POST["search_type"];
        $search_content = $_POST["search_content"];
    
        $sql_tmp = "SELECT host_id, host_ip, port_num, service_name, service_product FROM port_list WHERE $search_type LIKE '%$search_content%'";
        $result = mysql_query($sql_tmp);
        if ($result && mysql_num_rows($result) > 0) {
            echo "<table class=\"table table-striped table-bordered\"><tr><th>Host_ip</th><th>Port_num</th><th>service_name</th><th>service_product</th></tr>";
            while ($row = mysql_fetch_row($result)) {
                echo "<tr><td><a href=\"index.php?action=search&host_id=$row[0]\">$row[1]</a></td><td>$row[2]</td><td>$row[3]</td><td>$row[4]</td>\n";
            }
            echo "</table>";
        }
    }

    if (isset($_GET["host_id"])) {
        $host_id = $_GET["host_id"];
        $sql_tmp = "SELECT port_info FROM host_list WHERE host_id=$host_id";
        $result = mysql_query($sql_tmp);
        if ($result && $row = mysql_fetch_row($result)) {
            $port_info = $row[0];
            $ports = new SimpleXMLElement($port_info);
            $table_row = "<table class=\"table table-striped table-bordered\"><tr><th>Port</th><th>Content</th></td>";
            foreach ($ports->port as $port) {
                $table_row = $table_row . "<tr><td>";
                foreach ($port->attributes() as $key => $value) {
                    $table_row = $table_row . "$key: $value<br />";
                }
                $table_row = $table_row . "</td><td>";
                foreach ($port->children() as $children) {
                    $table_row = $table_row . "Children name: <br />" . $children->getName() . "<br />Children attributes: <br />";
                    foreach ($children->attributes() as $key => $value) {
                        $table_row = $table_row . "$key: $value<br />";
                    }
                    $table_row = $table_row . "<br />";
                }
                $table_row = $table_row . "</td></tr>\n";
            }
            echo $table_row . "</table>";
        }
    }
?>