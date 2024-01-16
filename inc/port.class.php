<?php

class Port implements Countable {

    var $name;
    var $mac;
    var $switchHostname;
    var $glpiNetworkDeviceId;
    var $connectedTo = [];
    var $down;
    var $status;
    var $portId;
    var $uplink;
    var $uplinkHostname;
    var $vlan;
    var $glpiPortid;
    var $uniqueMACs = array();
    static $fdb = null;
    static $links = null;

    static function networkDevicePort($name, $portId, $mac, $down, $status, $switchHostname, $glpiNetworkDeviceId): Port {
        $port = new self();
        $port->mac = self::parseMac($mac);
        $port->switchHostname = $switchHostname;
        $port->portId = $portId;
        $port->name = $name;
        $port->status = $status;
        $port->down = $down;
        $port->uplink = false;
        $port->glpiNetworkDeviceId = $glpiNetworkDeviceId;
        $glpiPortid = $port->findGlpiId(true);
        if ($glpiPortid > 0)
            $port->findConnectedDevices();

        return $port;
    }

    static function endDevicePort($mac): Port {
        $port = new self();
        $port->mac = self::parseMac($mac);
        $port->findGlpiId(false);
        return $port;
    }

    private function findGlpiId($networkDevicePort) {
        if (isset($this->glpiNetworkDeviceId) && $networkDevicePort) {
            $port = new NetworkPort();
            $fields = $port->find(array("items_id = '" . $this->glpiNetworkDeviceId . "' and name = '" . $this->name . "'"));
            foreach ($fields as $field) {
                if (isset($field["id"])) {
                    $this->glpiPortid = $field["id"];
                    return $field["id"];
                }
            }
        } else {
            if (isset($this->mac)) {

                $port = new NetworkPort();
                $fields = $port->find(array("mac = '" . $this->mac . "'"), [], 1);
                foreach ($fields as $field) {
                    if (isset($field["id"])) {
                        $this->glpiPortid = $field["id"];
                        $this->glpiNetworkDeviceId = $field["items_id"];
                        break;
                    }
                }
            }
        }
    }

    private function findConnectedDevices() {
        $jsonFdb = self::getFDB();
        if (!empty($jsonFdb))
            foreach ($jsonFdb as $key => $value) {
                if ($value["port_id"] == $this->portId && !in_array($value["mac_address"], $this->uniqueMACs)) {
                    $mac = $value["mac_address"];
                    $this->connectedTo[] = self::endDevicePort($mac);
                    $this->uniqueMACs[] = $mac;
                }
            }
    }

    public function isUpLink($hostnames) {
        $uplinkPorts = self::getLinks();
        foreach ($uplinkPorts as $key => $value) {
            if ($value["local_port_id"] == $this->portId) {
                $connectedTo = strtolower($value["remote_hostname"]);
                if (in_array($connectedTo, $hostnames)) {
                    $this->uplink = true;
                    $this->uplinkHostname = $connectedTo;
                }
            }
        }
    }

    public static function parseMac($mac): string {
        $newMac = substr_replace($mac, ":", 2, 0);
        $newMac = substr_replace($newMac, ":", 5, 0);
        $newMac = substr_replace($newMac, ":", 8, 0);
        $newMac = substr_replace($newMac, ":", 11, 0);
        $newMac = substr_replace($newMac, ":", 14, 0);
        return $newMac;
    }

    private static function getFDB(): array {
        if (self::$fdb == null) {
            self::$fdb = ApiConfig::getInstance()->executeQuery("resources/fdb")["ports_fdb"];
        }
        return self::$fdb;
    }

    private static function getLinks(): array {
        if (self::$links == null) {
            self::$links = ApiConfig::getInstance()->executeQuery("resources/links")["links"];
        }
        return self::$links;
    }

    public function count(): int {
        // TODO: Implement count() method.
        return (int) count(self::$links);
    }
}
