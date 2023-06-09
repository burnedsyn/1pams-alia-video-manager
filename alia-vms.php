<?php
/*
Plugin Name: 1proamonservice - Alia Video Management System
Description: Project Alia video and user management system.
wordpress and woocommerce plugin for video streaming platform 
Version: 1.0.0
Author: Timothee de Almeida Casqueira
Author URI: https://1proamonservice.be
Author MAIL: burnedsyn@gmail.com
Text Domain: alia-vms
Domain Path: /languages/
*/
if (!defined('ABSPATH')) {
    exit; // Prevent direct access to the file
}
// Define ALIA_VMS_PLUGIN_FILE
if (!defined('ALIA_VMS_PLUGIN_FILE')) {
    define('ALIA_VMS_PLUGIN_FILE', __FILE__);
}
// Include the main Alia-vms class
if (!class_exists('AliaVms')) {
    require_once 'vendor/autoload.php'; // Load Composer autoloader
    
    include_once dirname(__FILE__) . '/includes/alia-vms-database.php';

    include_once dirname(__FILE__) . '/includes/alia-vms-api.php';
}

//require_once 'vendor/autoload.php'; // Load Composer autoloader
use Aws\S3\S3Client;
use Aws\Exception\AwsException;
class AliaVms{
    public function __construct()
    {
        
        register_activation_hook( __FILE__, array($this,'alia_vms_activate' ));
        register_deactivation_hook( __FILE__,array($this, 'alia_vms_deactivate' ));
        register_uninstall_hook( __FILE__, array($this,'alia_vms_uninstall') );
        add_action( 'admin_enqueue_scripts',  array($this,'alia_vms_enqueue_scripts' ));
        add_action('admin_menu', array($this, 'add_alia_vms_submenu'));
        add_action( 'plugins_loaded', array($this,'alia_vms_load_textdomain' ));
        // Add custom product type filter
        add_filter('woocommerce_product_types', array($this, 'custom_product_type'));
 

    }

     // Register custom product type
     public function custom_product_type($types) {
        $types['video_product'] = 'Video_Product';
        return $types;
    }
 // Add ALIA VMS submenu
    public function add_alia_vms_submenu() {
        add_menu_page(
            'ALIA VMS',        // Page title
            'ALIA VMS',        // Menu title
            'manage_options',  // Capability required
            'alia-vms',        // Menu slug
            array($this, 'alia_vms_submenu_callback'), // Callback function
            'dashicons-video-alt2', // Icon (replace with appropriate dashicon class)
            30                 // Position in the menu
        );

        // Add submenus
        add_submenu_page(
            'alia-vms',               // Parent slug
            'User Manager',              // Page title
            'User Manager',              // Menu title
            'manage_options',         // Capability required
            'alia-vms-user-manager',     // Menu slug
            array($this, 'user_manager_callback') // Callback function
        );

        add_submenu_page(
            'alia-vms',               // Parent slug
            'VIDEO',              // Page title
            'VIDEO',              // Menu title
            'manage_options',         // Capability required
            'alia-vms-video',     // Menu slug
            array($this, 'admin_video_callback') // Callback function
           
        );

        
    }

    // Callback function for ALIA VMS submenu
    public function alia_vms_submenu_callback() {
        require_once plugin_dir_path(__FILE__) . 'templates/alia-vms.php';
   
    }

    // Callback function for Section 1 submenu
public function user_manager_callback() {
    require_once plugin_dir_path(__FILE__) . 'templates/user_manager.php';
    }

    // Callback function for Section 2 submenu
    public function admin_video_callback() {
        require_once plugin_dir_path(__FILE__) . 'templates/video_manager.php';
   
    }

  

function alia_vms_enqueue_scripts( ) {
    
    wp_enqueue_script('adminlte-script', 'https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js', array('jquery'), '3.2.0', true);
    wp_enqueue_style('adminlte-style', 'https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css', array(), '3.2.0');

    //wp_enqueue_script( 'my-plugin-delete-alert', plugin_dir_url( __FILE__ ) . 'js/delete-alert.js', array( 'jquery' ), '1.0.0', true );
}

// Activation hook
function alia_vms_activate() {
    // Code to run on activation
}


// Deactivation hook

function alia_vms_deactivate() {
    // Code to run on deactivation
}


// Uninstallation hook

function alia_vms_uninstall() {
   // Code to run on uninstallation
}

// i18n support

function alia_vms_load_textdomain() {
    load_plugin_textdomain( 'alia-vms', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

} //fin class

$alia_vms_plugin = new AliaVms();
