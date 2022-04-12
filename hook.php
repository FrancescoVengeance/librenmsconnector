<?php
/**
 * -------------------------------------------------------------------------
 * LibrenmsConnector plugin for GLPI
 * Copyright (C) 2022 by the LibrenmsConnector Development Team.
 * -------------------------------------------------------------------------
 *
 * MIT License
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * --------------------------------------------------------------------------
 */

/**
 * Plugin install process
 *
 * @return boolean
 */
function plugin_librenmsconnector_install() {
   global $DB;
    $result = false;
    if(!$DB->tableExists("glpi_plugin_librenmsconnector"))
    {
        $query = "create table if not exists glpi_plugin_librenmsconnector(
                    api_key varchar(100) primary key,
                    server_ip char(15) not null         
        )";

        $DB->query($query) or die("Errore nel create glpi_plugin_librenmsconnector" . $DB->error());
        $result = true;
    }
    if(!$DB->tableExists("glpi_plugin_librenmsconnector_log"))
    {
        $query = "create table if not exists glpi_plugin_librenmsconnector_log(
                    status varchar(15) not null,
                    name varchar(30),
                    ip_addr varchar(15),
                    switch_name varchar(30),
                    switch_port_name varchar(20),
                    mac_addr varchar(25) not null,
                    insert_time datetime default NOW(),
                    description text(200)
        )";

        $DB->query($query) or die("Errore nel create glpi_plugin_librenmsconnector_log nel database" . $DB->error());
        $result = true;
    }

    return $result;
}

/**
 * Plugin uninstall process
 *
 * @return boolean
 */
function plugin_librenmsconnector_uninstall() {
   global $DB;

   $result = false;
   if($DB->tableExists("glpi_plugin_librenmsconnector"))
   {
       $query = "drop table glpi_plugin_librenmsconnector";
       $DB->query($query) or die("Errore nel cancellare glpi_plugin_librenmsconnector" . $DB->error());
       $result = true;
   }
   if($DB->tableExists("glpi_plugin_librenmsconnector_log"))
   {
       $DB->query("drop table glpi_plugin_librenmsconnector_log") or die ("Errore" . $DB->error());
       $result = false;
   }
   return $result;
}

//display a new menu entry see setup.php
function plugin_librenmsconnector_redefine_menus($menu)
{
   if(empty($menu)){
      return $menu;
   }

   if(array_key_exists('librenmsconnector', $menu) === false)
   {
      $menu['librenmsconnector'] = [
         'default' => '/plugins/librenmsconnector/front/index.php',
         'title' => __('LibrenmsConnector', 'librenmsconnector'),
         'content' => [true]
      ];
   }

   return $menu;
}
