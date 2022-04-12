<?php

class ApiConfig {

    private static $instance = null;
    private $serverIp; //= "100.100.201.98/api/v0/";
    private $apiKey; //= "40e1d3572181097d240f08b7b2cff9b0";

    private function __construct() {
        $this->fetchData();
    }

    private function fetchData() {
        global $DB;
        $result = $DB->request([
            "FROM" => "glpi_plugin_librenmsconnector",
            "WHERE" => "",
            "LIMIT" => 1
        ]);

        foreach ($result as $row) {
            $this->apiKey = $row["api_key"];
            $this->serverIp = $row["server_ip"];
            break;
        }
    }

    public static function getInstance(): ApiConfig {
        if (self::$instance == null) {
            self::$instance = new ApiConfig();
        }
        return self::$instance;
    }

    public function executeQuery($route) {
        $url = "curl -H 'X-Auth-Token: " . $this->apiKey . "' " . $this->serverIp . "/api/v0/" . "$route";
        $json = shell_exec($url . " > output.json");
        return json_decode(file_get_contents("output.json"), true);
    }

    public function restoreData(): void {
        $this->fetchData();
    }

    public function getApiKey(): string {
        return ($this->apiKey) ? $this->apiKey : "";
    }

    public function getServerIp(): string {
        return ($this->serverIp) ? $this->serverIp : "";
    }
}