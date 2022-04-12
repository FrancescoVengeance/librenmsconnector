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

define('PLUGIN_LIBRENMSCONNECTOR_VERSION', '0.3');

define('PLUGIN_NAME', 'librenmsconnector');

/**
 * Init hooks of the plugin.
 * REQUIRED
 *
 * @return void
 */
function plugin_init_librenmsconnector() {
   global $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['csrf_compliant'][PLUGIN_NAME] = true;
   
   //adds a menu entry in the top bar
   $PLUGIN_HOOKS['redefine_menus'][PLUGIN_NAME] = 'plugin_librenmsconnector_redefine_menus';
   //Plugin::registerClass('librenmsconnector', array('addtabon' => array('tools')));
   //$PLUGIN_HOOKS["menu_toadd"][PLUGIN_NAME] = ['tools'  => 'librenmsconnector'];
	//$PLUGIN_HOOKS['config_page'][PLUGIN_NAME] = 'front/index.php';
   //$PLUGIN_HOOKS['menu'][PLUGIN_NAME]= true;



   //display config page
   if (Session::haveRight('config', UPDATE)) {
      $PLUGIN_HOOKS['config_page'][PLUGIN_NAME] = 'config.php';
   }
}


/**
 * Get the name and the version of the plugin
 * REQUIRED
 *
 * @return array
 */
function plugin_version_librenmsconnector() {
   return [
      'name'           => 'LibrenmsConnector',
      'version'        => PLUGIN_LIBRENMSCONNECTOR_VERSION,
      'author'         => 'Francesco Esposito',
      'license'        => 'GPLv3',
      'homepage'       => 'https://github.com/FrancescoVengeance/GLPILibreNMSConnector.git',
      'requirements'   => [
         'glpi' => [
            'min' => '9.5',
         ]
      ]
   ];
}

/**
 * Check pre-requisites before install
 * OPTIONNAL, but recommanded
 *
 * @return boolean
 */
function plugin_librenmsconnector_check_prerequisites() {

   return true;
}

/**
 * Check configuration process
 *
 * @param boolean $verbose Whether to display message on failure. Defaults to false
 *
 * @return boolean
 */
function plugin_librenmsconnector_check_config($verbose = false) {
   if (true) { // Your configuration check
      return true;
   }

   if ($verbose) {
      echo __('Installed / not configured', 'librenmsconnector');
   }
   return false;
}
