<?php
include ('../../../inc/includes.php');

global $DB;

$result = $DB->query("select * from glpi_plugin_librenmsconnector_log order by insert_time desc");

if($result->num_rows > 0)
{

    $delimiter = ",";
    $filename = "export-data_" . date('Y-m-d') . ".csv";
    $f = fopen("php://output", "w");
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '";');
    $fields = array("status", "name", "ip_addr", "switch_name", "switch_port_name", "mac_addr", "insert_time", "description");
    fputcsv($f, $fields, $delimiter);

    foreach ($result as $row)
    {
        $status = $row["status"];
        $name = (isset($row["name"])) ? $row["name"] : "none";
        $ip = (isset($row["ip_addr"])) ? $row["ip_addr"] : "none";
        $switch_name = (isset($row["switch_name"])) ? $row["switch_name"] : "none";
        $switch_port_name = (isset($row["switch_port_name"]))? $row["switch_port_name"] : "none";
        $mac_addr = $row["mac_addr"];
        $insert_time = $row["insert_time"];
        $description = (isset($row["description"])) ? $row["description"] : "none";

        $line = array($status, $name, $ip, $switch_name, $switch_port_name, $mac_addr, $insert_time, $description);
        fputcsv($f, $line, $delimiter);
    }

    fseek($f, 0);

    fpassthru($f);
}
else
{
    echo "No data found (maybe you need to run the plugin first)";
}

exit();