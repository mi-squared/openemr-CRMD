<?php
/**
 * plugin API and plugin system
 *
 * Copyright (C) 2015 Medical Information Integration <info@mi-squared.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package plugin
 * @author  Ken Chapple <ken@mi-squared.com>
 * @link    http://www.mi-squared.com
 */

/**
 *
 * Plugin action refernce
 *
 */


/**
 *
 * @var Triggered at the end of the patient report, before the closing </html> tag.
 * This can be used to insert a javascript to alter the appearance or behavior of
 * the report.
 *
 * @param none
 */
define( "PATIENT_REPORT_BEFORE_HTML_END", "patient_report_before_html_end" );

/**
 *
 * @var Triggered after the appointment widget on the demographics page
 *
 * @param none
 */
define( "DEMOGRAPHICS_AFTER_APPOINTMENT_WIDGET", "demographics_after_appointment_widget" );

/**
 *
 * Triggered before the closing html tag of left nav
 *
 * @param none
 */
define( "LEFT_NAV_BEFORE_HTML_END", "left_nav_before_html_end" );

/**
 *
 * @var Triggered after globals have been initialized so plugin can add
 * configuration to Administration > Globals settings screen.
 *
 * @param The Array of globals metadata
 */
define( "GLOBALS_INIT", "globals_init" );

/**
 *
 * @var Triggered at the end of the user add form is saving so the 
 * plugin can add any custom fields
 *
 * @param none
 */
define( "USERGROUP_ADMIN_ADD", "usergroup_admin_add" );

/**
 *
 * @var Triggered at the end of the user edit form is saving so the 
 * plugin can add any custom fields
 *
 * @param The array of row data from the database for the user currently being edited
 */
define( "USERGROUP_ADMIN_EDIT", "usergroup_admin_edit" );

/**
 *
 * @var Triggered when the user add/edit form is saving so the plugin can save
 * any custom fields
 *
 * @param The Array POST data from the user add/edit form
 */
define( "USERGROUP_ADMIN_SAVE", "usergroup_admin_save" );

/**
 *
 * Triggered when plugin is registered through the module installer
 *
 * @param $name Name of the plugin (the plugins dirname)
 */
define( "REGISTER_PLUGIN", "register_plugin" );

/**
 *
 * Triggered when plugin is installed through the module installer
 *
 * @param $name Name of the plugin (the plugins dirname)
 */
define( "INSTALL_PLUGIN", "install_plugin" );

/**
 *
 * Triggered when plugin is enabled through the module installer
 *
 * @param $name Name of the plugin (the plugins dirname)
 */
define( "ENABLE_PLUGIN", "enable_plugin" );

/**
 *
 * Triggered when plugin is disabled through the module installer
 *
 * @param $name Name of the plugin (the plugins dirname)
 */
define( "DISABLE_PLUGIN", "disable_plugin" );

/**
 *
 * @var Triggered after all enabled plugin's start.php files
 * have been included.
 *
 * @param $args An array of components
 */
define( "PLUGINS_STARTED", "plugins_started" );

/**
 * Plugin API
 * 
 * 
 */

function init_plugin_system()
{
    $system = PluginSystem::getInstance();
    $system->init();
}

function add_action( $key, $callback )
{
    $system = PluginSystem::getInstance();
    $system->addAction( $key, $callback );
}

function do_action( $key, & $args = null )
{
    $system = PluginSystem::getInstance();
    $system->doAction( $key, $args );
}

function register_plugin( $name ) 
{
    $system = PluginSystem::getInstance();
    $system->registerPlugin( $name );
}

function install_plugin( $name )
{
    $system = PluginSystem::getInstance();
    $system->installPlugin( $name );
}

function enable_plugin( $name )
{
    $system = PluginSystem::getInstance();
    $system->enablePlugin( $name );
}

function disable_plugin( $name )
{
    $system = PluginSystem::getInstance();
    $system->disablePlugin( $name );
}

/**
 *  Implementation of plugin system
 *
 */
class PluginSystem
{
    protected $pluginDir = '';
    protected $actions = array();
    protected $components = array();
    protected $plugins = array();
    protected $activeModules = array();

    /**
     * Returns the PluginSystem instance of this class.
     *
     * @staticvar Singleton $instance The PluginSystem instances of this class.
     *
     * @return Singleton The PluginSystem instance.
    */
    public static function getInstance()
    {
        static $instance = null;
        if (null === $instance) {
            $instance = new static();
        }

        return $instance;
    }

    /**
     * Protected constructor to prevent creating a new instance of the
     * PluginSystem via the `new` operator from outside of this class.
     */
    protected function __construct()
    {
        
    }

    public function init()
    {
        // Fetch the active modules if the module talbe exists
        $result = sqlStatement( "SHOW TABLES LIKE ?", array( 'modules' ) );
        $tableExists = sqlNumRows( $result ) > 0;
        if ( $tableExists ) {
            $sql = "SELECT * FROM modules WHERE mod_active = ?";
            $result = sqlStatement( $sql, array( 1 ) );
            while ( $row = sqlFetchArray( $result ) ) {
                $this->activeModules[]= $row;
            }
        }
        
        // Locate vendor directory and init autolaoder
        $vendorDir = realpath( __DIR__."/../vendor" );
        require_once "$vendorDir/autoload.php";

        // Get the plugin directory
        $composerJson = file_get_contents( realpath( __DIR__."/../composer.json" ) );
        if ( $composerJson === false ) {
            throw new \Exception( "$composerJsonPath/composer.json is not readable" );
        }
        $composer = json_decode( $composerJson, true );
        $pluginDir = __DIR__."/../".$composer['extra']['plugin-dir'];
        $this->pluginDir = $pluginDir;
        
        // Search for active components
        // Run their start.php scripts, but only *if* the plugin is active
        foreach ( $this->activeModules as $component ) {
            $subdir = $component['mod_directory'];
            $location = "$pluginDir/$subdir";
            if ( file_exists( "$location/start.php" ) ) {
                include_once "$location/start.php";
            } else {
                // No start file? Probaly mispelled or forgotten
                error_log( "No start.php file found for openemr-plugin $subdir" );
            }
        }
        
        // Notify observers that the plugins have all been started
        $this->doAction( PLUGINS_STARTED, $components );
    }
    
    public function getPluginPath( $name )
    {
        
        $subdir = "$this->pluginDir/$name";
        return $subdir;
    }
    
    public function pluginIsActive( $name )
    {
        foreach ( $this->activeModules as $activeModule ) {
            if ( $name == $activeModule['directory'] ) {
                return true;
            }
        }       
        
        return false;
    }
    
    public function registerPlugin( $name )
    {
        $subdir = $this->getPluginPath( $name );
        if ( file_exists( "$subdir/start.php" ) ) {
            include_once "$subdir/start.php";
            $this->doAction( REGISTER_PLUGIN, $name );
        }
    }
    
    public function installPlugin( $name )
    {
        $subdir = $this->getPluginPath( $name );
        if ( file_exists( "$subdir/start.php" ) ) {
            include_once "$subdir/start.php";
            $this->doAction( INSTALL_PLUGIN, $name );
        }
    }
    
    public function enablePlugin( $name )
    {
        $subdir = $this->getPluginPath( $name );
        if ( file_exists( "$subdir/start.php" ) ) {
            include_once "$subdir/start.php";
            $this->doAction( ENABLE_PLUGIN, $name );
        }
    }
    
    public function disablePlugin( $name )
    {
        $subdir = $this->getPluginPath( $name );
        if ( file_exists( "$subdir/start.php" ) ) {
            include_once "$subdir/start.php";
            $this->doAction( DISABLE_PLUGIN, $name );
        }
    }

    public function addAction( $actionKey, $callback, $priority = 0 )
    {
        if ( !is_array($this->actions[$actionKey]) ) {
            $this->actions[$actionKey] = array();
        }
        $action = new stdClass();
        $action->callback = $callback;
        $action->priority = $priority;
        $this->actions[$actionKey][] = $action;
    }

    public function doAction( $actionKey, & $args = null )
    {
        $actions = $this->actions[$actionKey];
        if ( count( $actions ) ) {
            // sort actions by priority
            usort( $actions, array( "PluginSystem", "comparePriority" ) );
            foreach ( $actions as $action ) {
                $callback = $action->callback;
                $ret = $callback( $args, $actionKey );
                ob_flush();
            }
        }  
    }
    
    public static function comparePriority( $a, $b ) 
    {
        if ( $a->priority == $b->priority ) {
            return 0;
        }
        
        return ( $a->priority > $b->priority ) ? +1 : -1;
    }
    
    /**
     * Private clone method to prevent cloning of the instance of the
     * PluginSystem instance.
     *
     * @return void
     */
    private function __clone()
    {
    }

    /**
     * Private unserialize method to prevent unserializing of the PluginSystem
     * instance.
     *
     * @return void
     */
    private function __wakeup()
    {
    }
}
