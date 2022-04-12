<?php

include ("port.class.php");
include ("apiconfig.class.php");

class NetworkDevice {

    var $hostname;
    var $id;
    var $glpiID;
    var $sysName;
    var $location;
    var $type;
    var $hardware;
    var $SNMPcommunity;
    var $ports = [];

    function __construct($id, $hostname, $sysName, $location, $type, $hardware, $SNMPcommunity) {
        $this->SNMPcommunity = $SNMPcommunity;
        $this->hostname = $hostname;
        $this->id = $id;
        $this->sysName = $sysName;
        $this->location = $location;
        $this->type = $type;
        $this->hardware = $hardware;
        $this->findGlpiId();
        if ($this->glpiID > 0)
            $this->findPorts();
    }

    private function findGlpiId() {
        $networkDevice = new NetworkEquipment();
        $fields = $networkDevice->find("name = " . "'" . $this->sysName . "'");

        foreach ($fields as $field) {
            if (isset($field["id"])) {
                $this->glpiID = $field["id"];
                ;
                break;
            }
        }
    }

    static function createDevice($jsonDevice): NetworkDevice {
        $hostname = "";
        $id = "";
        $sysName = "";
        $location = "";
        $type = "";
        $hardware = "";
        $SNMPcommunity = "";

        foreach ($jsonDevice as $key => $value) {
            switch ($key) {
                case "device_id":
                    $id = $jsonDevice[$key];
                    break;
                case "hostname":
                    $hostname = $jsonDevice[$key];
                    break;
                case "sysName":
                    $sysName = $jsonDevice[$key];
                    break;
                case "location":
                    $location = $jsonDevice[$key];
                    break;
                case "type":
                    $type = $jsonDevice[$key];
                    break;
                case "hardware":
                    $hardware = $jsonDevice[$key];
                    break;
                case "community":
                    $SNMPcommunity = $jsonDevice[$key];
                    break;
            }
        }

        return new NetworkDevice($id, $hostname, $sysName, $location, $type, $hardware, $SNMPcommunity);
    }

    private function findPorts(): void {
        if ($this->type == "network") {
            $jsonPorts = ApiConfig::getInstance()->executeQuery("devices/" . $this->id . "/ports?columns=ifName%2Cport_id")["ports"];
            foreach ($jsonPorts as $key => $value) {
                $portName = $value["ifName"];
                $portId = $value["port_id"];
                $port = Port::networkDevicePort($portName, $portId, "", "", "", $this->sysName, $this->glpiID);
                $this->ports[] = $port;
            }
        }
    }

    public function checkUplinkPorts($hostnames) {
        foreach ($this->ports as $port) {
            $port->isUpLink($hostnames);
        }
    }


    /**
     * find the network port id in the database and checks if is already connected to the correct switch.
     * If not, it will be connected to the correct switch otherwise it will be ignored.
     * If it is not connected to any switch, it will be connected to the correct switch.
     */
    public static function connect(Port $switchPort, Port $endDevicePort): string {
        global $DB;

        //get from database the switch port id and the end device port id based on end device id
        $query = "select networkports_id_1, networkports_id_2 "
                . "from glpi_networkports_networkports "
                . "where networkports_id_2 = " . $endDevicePort->glpiPortid;

        $resultSet = $DB->request($query);
        foreach ($resultSet as $row) {

            //if nothig has changed, return
            if ($row["networkports_id_2"] == $endDevicePort->glpiPortid && $row["networkports_id_1"] == $switchPort->glpiPortid) {
                return "skip";
            }
            //if the switch ports has changed, update to the new switch port
            else if (isset($row["networkports_id_1"]) && isset($row["networkports_id_2"]) && $row["networkports_id_1"] != $switchPort->glpiPortid) {
                $update = "update glpi_networkports_networkports set networkports_id_1 = " . $switchPort->glpiPortid
                        . " where networkports_id_2 = " . $endDevicePort->glpiPortid;
                $result = $DB->query($update);

                //get the switch port name and the hostname of the switch to log it
                if ($result) {
                    $query = "select glpi_networkports.name as portname, glpi_networkequipments.name as hostname "
                            . " from glpi_networkports, glpi_networkequipments "
                            . " where glpi_networkports.id = " . $row["networkports_id_1"]
                            . " and "
                            . " glpi_networkequipments.id = glpi_networkports.items_id";
                    $oldSwitchPort = $DB->request($query);
                    $previousPort = null;
                    $previousHostname = null;
                    foreach ($oldSwitchPort as $oldPort) {
                        $previousPort = $oldPort["portname"];
                        $previousHostname = $oldPort["hostname"];
                    }

                    $description = "moved: " . $endDevicePort->mac . " from " . $previousPort . " on " . $previousHostname
                            . " to " . $switchPort->name . " on " . $switchPort->switchHostname;
                    echo "<li>" . $description . "</li>";
                    Logger::log($switchPort, $endDevicePort, "update", $description);
                    return "update";
                }
            }
        }

        //if the switch port is not connected to the end device, connect it
        $result = $DB->insert(
                "glpi_networkports_networkports",
                [
                    "networkports_id_1" => $switchPort->glpiPortid,
                    "networkports_id_2" => $endDevicePort->glpiPortid
                ]
        );

        if ($result) {
            return "insert";
        }
        return "skip";
    }

    //
//        $connqector = new NetworkPort_NetworkPort();
//        $fields = $connector->find("networkports_id_1 = " . $port1 . " and networkports_id_2 = " . $port2 .
//                                        " or networkports_id_1 = " . $port2 . " and networkports_id_2 = " . $port1);
//        foreach ($fields as $field)
//        {
//            if(isset($field["id"]))
//            {
//                $connector->fields["networkports_id_1"] = $port1;
//                $connector->fields["networkports_id_2"] = $port2;
//                return $connector->addToDB();
//            }
//        }
//        return false;
}