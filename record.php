<?php
    include('config.php');

    if (isset($_POST["submit"])) {
        $allow_suffix = "xml";

        if($_FILES["xml_files"]["error"] > 0){
            echo "Upload error";
            exit();
        }

        $get_suffix = explode(".", $_FILES["xml_files"]["name"]);
        $real_suffix = array_pop($get_suffix);
        if ($real_suffix != $allow_suffix) {
            echo "Illegal suffix";
            exit();
        }

        $xml_files = file_get_contents($_FILES["xml_files"]["tmp_name"]);
        $nmap_result = new SimpleXMLElement($xml_files);
        unlink($_FILES["xml_files"]["tmp_name"]);
        
        $project_name = $_POST['project_name'];
        $project_id = 0;

        $sql_tmp = "SELECT project_id FROM scan_list WHERE project_name='$project_name'";
        $result = mysql_query($sql_tmp);
        while ($row = mysql_fetch_row($result)) {
            $tmp = intval($row[0]);
            if ($tmp > $project_id) {
                $project_id = $tmp;
            }
        }
        $project_id = $project_id + 1;
        
        $scan_info = (string) $nmap_result['args'];
        $scan_time = (string) $nmap_result['start'];
        
        $sql_tmp = "INSERT INTO scan_list(project_name, project_id, scan_time, scan_info) VALUES ('$project_name', $project_id, $scan_time, '$scan_info')";
        $result = mysql_query($sql_tmp);

        $sql_tmp = "SELECT scan_id FROM scan_list WHERE project_name='$project_name' AND project_id=$project_id";
        $result = mysql_query($sql_tmp);
        $scan_id = mysql_fetch_row($result);
        $scan_id = intval($scan_id[0]);
        
        $pattern = '/<host .*?>(.|\n)*?<address .*?>(.|\n)*?<ports>(.|\n)*?<\/ports>/';
        preg_match_all($pattern, $xml_files, $matches, PREG_SET_ORDER);
        foreach ($matches as $host) {
            $host_info = $host[0];
            $port_pattern = '/<port (.|\n)*?<\/port>/';

            if(preg_match_all($port_pattern, $host_info, $port_matches)) {
                $ip_pattern = '/addr="(.*?)"/';
                preg_match($ip_pattern, $host_info, $ip_matches);
                $host_ip = $ip_matches[1];

                $ports = $port_matches[0];
                $port_info = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<ports>";
                foreach ($ports as $port) {
                    $port_info = $port_info . $port . "\n";
                }
                $port_info = $port_info . "</ports>";
                $port_info = addslashes($port_info);

                $sql_tmp = "INSERT INTO host_list(scan_id, host_ip, port_info) VALUES ($scan_id, '$host_ip', '$port_info')";
                $result = mysql_query($sql_tmp);             
            }
        }

        foreach ($nmap_result->host as $host) {
            if ($host->ports->port->count()>0) {
                $host_ip = (string) $host->address['addr'];

                $sql_tmp = "SELECT host_id FROM host_list WHERE scan_id=$scan_id AND host_ip='$host_ip'";
                $result = mysql_query($sql_tmp);
                $host_id = mysql_fetch_row($result);
                $host_id = intval($host_id[0]);

                foreach ($host->ports->port as $port) {
                    $port_num = (int) $port['portid'];
                    $service_name = (string) $port->service['name'];
                    $service_product = (string) $port->service['product'];

                    $sql_tmp = "INSERT INTO port_list(scan_id, host_id, host_ip, port_num, service_name, service_product) VALUES ($scan_id, $host_id, '$host_ip', $port_num, '$service_name', '$service_product')";
                    mysql_query($sql_tmp);
                }
            }
        }
    }

    if (isset($_GET["delect"]) && isset($_GET["scan_id"])) {
        $scan_id = intval($_GET["scan_id"]);
        $sql_tmp = "DELETE FROM port_list WHERE scan_id=$scan_id";
        mysql_query($sql_tmp);
        $sql_tmp = "DELETE FROM host_list WHERE scan_id=$scan_id";
        mysql_query($sql_tmp);
        $sql_tmp = "DELETE FROM scan_list WHERE scan_id=$scan_id";
        mysql_query($sql_tmp);
    }
?>

<h1 class="text-center">XML Output Record</h1>

<form class="form-horizontal" role="form" action="index.php?action=record" method="post" enctype="multipart/form-data">
    <div class="form-group">
        <label for="all_projects" class="col-sm-2 col-sm-offset-3 control-label">All projects</label>
        <div class="col-sm-4">
            <select class="form-control" name="all_projects" onchange="show()">
            <?php
                $sql_tmp = "SELECT DISTINCT project_name FROM scan_list";
                $result = mysql_query($sql_tmp);
                while ($row = mysql_fetch_row($result)) {
                    echo "<option value=\"$row[0]\">$row[0]</option>\n";
                }
            ?> 
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="project_name" class="col-sm-2 col-sm-offset-3 control-label">Project name</label>
        <div class="col-sm-4">
            <input type="text" class="form-control" name="project_name" id="project_name" value="<?php echo isset($_POST["project_name"]) ? $_POST["project_name"] : ""; ?>" placeholder="Project name">
        </div>
    </div>

    <div class="form-group">
        <label for="xml_files" class="col-sm-2 col-sm-offset-3 control-label">XML file</label>
        <div class="col-sm-4">
            <input type="file" name="xml_files" id="xml_files">
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-offset-5 col-sm-4">
            <button type="submit" name="submit" class="btn btn-default">Record</button>
        </div>
    </div>
</form>

<table class="table table-striped table-bordered">
    <thead>
    <tr>
        <th>Project name</th>
        <th>Project id</th>
        <th>Scan time</th>
        <th>Scan info</th>
        <th>Delete</th>
    </tr>
    </thead>
    <tbody>
    <?php
        $sql_tmp = "SELECT * FROM scan_list";
        $result = mysql_query($sql_tmp);
        while ($row = mysql_fetch_row($result)) {
            $row[3] = date("Y-m-d H:i:s", $row[3]);
            echo "<tr><td>$row[1]</td><td>$row[2]</td><td>$row[3]</td><td>$row[4]</td><td><a href=\"index.php?action=record&delect=true&scan_id=$row[0]\">Del</a></td></tr>\n";
        }
    ?>
    </tbody>
</table>

<script type="text/javascript">
    function show() {
        document.getElementsByTagName('input')[0].value = document.getElementsByTagName('select')[0].value;
    }
</script>