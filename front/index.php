<?php

/*
 * @version $Id: HEADER 15930 2011-10-25 10:47:55Z jmd $
  -------------------------------------------------------------------------
  GLPI - Gestionnaire Libre de Parc Informatique
  Copyright (C) 2003-2011 by the INDEPNET Development Team.

  http://indepnet.net/   http://glpi-project.org
  -------------------------------------------------------------------------

  LICENSE

  This file is part of GLPI.

  GLPI is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  GLPI is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with GLPI. If not, see <http://www.gnu.org/licenses/>.
  --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------

include ('../../../inc/includes.php');
include('../inc/networkdevice.class.php');
include ("../inc/logger.class.php");

if ($_SESSION["glpiactiveprofile"]["interface"] == "central") {
    Html::header("LibrenmsConnector", $_SERVER['PHP_SELF']);

    $token = Session::getNewCSRFToken();
    echo "<form method='post'>
        <p><INPUT type='checkbox' name='debug_sync' value='1' " . ((isset($_POST["debug_sync"]) && $_POST["debug_sync"] == 1) ? 'checked' : '') . " > Debug data</p>
        <button class='save' name='execute' type='submit'><p>Run</p></button>
        <input type='hidden' name='_glpi_csrf_token' value='$token'>
    </form>";

    echo "<form method='get' action='exportdata.php'>
        <button class='save' name='export' type='submit'><p>Export as CSV</p></button>
        <input type='hidden' name='_glpi_csrf_token' value='$token'>
    </form>";

    if (isset($_POST["execute"])) {
        echo "<h3>Esecuzione in corso...</h3>";
        execute();
        echo "<h3>Esecuzione completata</h3>";
    }
} else {
    Html::helpHeader("LibrenmsConnector", $_SERVER['PHP_SELF']);
}

function execute() {
    // Enable degug mode
    if (!isset($_POST["debug_sync"])) {
        $_POST["debug_sync"] = 0;
    }
    $debug_sync = ($_POST["debug_sync"] == 1) ? TRUE : FALSE;
    // Load devices from LibreNMS 
    echo ($debug_sync) ? 'Debug attivo<br>Get devices from API<br>' : '';
    $jsonDecode = ApiConfig::getInstance()->executeQuery("devices");
    //echo ($debug_sync) ? 'Device ottenuti<br>' : '';
    if (isset($jsonDecode)) {
        $decodedDevices = $jsonDecode["devices"];
        $hostnames = [];
        $devices = [];
        foreach ($decodedDevices as $jsonDevice) {
            $device = NetworkDevice::createDevice($jsonDevice, $debug_sync);
            $devices[] = $device;
            $hostnames[] = $device->sysName;
        }

        // Print devices found in LibreNMS and corresponding GLPI ones
        echo "<h3>List of devices found in LibreNMS</h3> 
                <table><tr><th>SysName</th><th>GlpiID</th>";
        foreach ($devices as $device) {
            $device->checkUplinkPorts($hostnames);
            echo '<tr><td> ' . $device->sysName . ' </td><td> ' . (($device->glpiID > 0) ? $device->glpiID : 'Not found') . "</td></tr>\n";
        }
        echo "</table>";
        // Search and connect ports
        foreach ($devices as $device) {
            foreach ($device->ports as $port) {
                echo "<h3>" . $port->name . " on " . $port->switchHostname . "</h3><br>";
                echo "<ul>";
                if (isset($port->glpiPortid) && !$port->uplink) {
                    foreach ($port->connectedTo as $connectedDevice) {
                        if (isset($connectedDevice->glpiPortid)) {
                            if ($debug_sync) {
                                $result = NetworkDevice::connect($port, $connectedDevice);
                            } else {
                                $result = FALSE;
                            }
                            switch ($result) {
                                case "insert":
                                    echo "<li>ADDED mac: " . $connectedDevice->mac . "</li>";
                                    Logger::log($port, $connectedDevice, "insert");
                                    break;
                                case "update":
                                    echo "<li>update mac: " . $connectedDevice->mac . "</li>";
                                    Logger::log($port, $connectedDevice, "update");
                                    break;
                                default:
                                    echo "<li>skipped mac: " . $connectedDevice->mac . "</li>";
                                    Logger::log($port, $connectedDevice, "skip");
                                    break;
                            }
                        } else {
                            Logger::log($port, $connectedDevice, "not found");
                        }
                    }
                } elseif ($port->uplink) {
                    echo "<li>UPLINK port connected to: " . $port->uplinkHostname . "</li>";
                }
                echo "</ul>";
            }
        }
    } else {
        echo "<a href='../config.php'><h4>Something goes wrong. Check the configuration</h4></a>";
    }
}

Html::footer();
