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

// Non menu entry case
//header("Location:../../central.php");

// Entry menu case
const GLPI_ROOT = '../..';
include (GLPI_ROOT . "/inc/includes.php");
include ("inc/apiconfig.class.php");
Session::checkRight("config", UPDATE);

// To be available when plugin in not activated
Plugin::load('librenmsconnector');

if($_SESSION["glpiactiveprofile"]["interface"] == "central" || true)
{
    Html::header("Librenmsconnector | Config", $_SERVER['PHP_SELF'], "config", "plugins");

    $serverip = ApiConfig::getInstance()->getServerIp();
    $apiKey = ApiConfig::getInstance()->getApiKey();
    $token = Session::getNewCSRFToken(true);

    if(!empty($serverip) && !empty($apiKey))
    {
        echo "<h3>Current server ip </h3><p>$serverip</p><br>
            <h3>Current api key </h3><p>$apiKey</p><br>";
    }
    else
    {
        echo "<h3>No server ip or api key found</h3><br>";
    }
    echo "<form method='post' enctype='multipart/form-data' action='config.php'>
        <p>API key</p>
        <input type='text' name='api-key' placeholder='API key'><br>
        <p>Server IP</p>
        <input type='text' name='server' placeholder='server address'><br>
        <input type='hidden' name='_glpi_csrf_token' value='$token'>
        <button type='submit' class='save' name='save'><p>Save</p></button>
    </form>";

    if(isset($_POST["save"]))
    {
        save($_POST["api-key"], $_POST["server"]);
    }
}
else
{
    Html::helpHeader("Librenmsconnector", $_SERVER['PHP_SELF']);
    echo "siamo qui";
}

Html::footer();

function save($apiKey, $serverip)
{
    global $DB;

    $DB->query("delete from glpi_plugin_librenmsconnector");

    $DB->insert(
        "glpi_plugin_librenmsconnector",
        [
            "api_key" => $apiKey,
            "server_ip" => $serverip
        ]
    );
    ApiConfig::getInstance()->restoreData();
    echo "<h1>Configurazione aggiornata</h1>";
}