<?php

class Logger
{
    public static function log(Port $device, Port $connectedDevice, $type, $description = null): void
    {
        global $DB;
         switch ($type)
         {
             case "insert":
                 $query = "insert into glpi_plugin_librenmsconnector_log (status, name, ip_addr, switch_name, switch_port_name, mac_addr) "
                 . "values ('INSERT', null, null, '" . $device->switchHostname . "' , '" . $device->name . "' , '". $connectedDevice->mac . "')" ;
                 $DB->query($query);
                 break;
             case "update":
                 if(!isset($description))
                 {
                     $description = "";
                 }
                 $query = "insert into glpi_plugin_librenmsconnector_log (status, name, ip_addr, switch_name, switch_port_name, mac_addr, description) "
                     . "values ('UPDATE', null, null, '" . $device->switchHostname . "' , '" . $device->name . "' , '". $connectedDevice->mac . "', '" . $description ."' )" ;
                 $DB->query($query);
                 break;
             case "skip":
                 $query = "insert into glpi_plugin_librenmsconnector_log (status, name, ip_addr, switch_name, switch_port_name, mac_addr) "
                     . "values ('SKIP', null, null, '" . $device->switchHostname . "' , '" . $device->name . "' , '". $connectedDevice->mac . "')" ;
                 $DB->query($query);
                 break;
             
             case "not found":
                 $query = "insert into glpi_plugin_librenmsconnector_log (status, name, ip_addr, switch_name, switch_port_name, mac_addr) "
                     . "values ('NOT FOUND', null, null, '" . $device->switchHostname . "' , '" . $device->name . "' , '". $connectedDevice->mac . "')" ;
                 $DB->query($query);
                 break;
             
         }
    }
}