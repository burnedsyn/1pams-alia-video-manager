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
define('ALIA_VMS_PLUGIN_PATH', dirname(ALIA_VMS_PLUGIN_FILE));
// Include the main Alia-vms class
if (!class_exists('AliaVms')) {
    require_once 'vendor/autoload.php'; // Load Composer autoloader

    include_once dirname(__FILE__) . '/includes/alia-vms-database.php';
    include_once dirname(__FILE__) . '/includes/alia-vms-api.php';
    include_once dirname(__FILE__) . '/includes/alia-vms-settings.php';
    include_once dirname(__FILE__) . '/includes/alia-vms-video-product.php';
    
}

//require_once 'vendor/autoload.php'; // Load Composer autoloader
use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class AliaVms
{
    public function __construct()
    {

        register_activation_hook(__FILE__, array($this, 'alia_vms_activate'));
        register_deactivation_hook(__FILE__, array($this, 'alia_vms_deactivate'));
        register_uninstall_hook(__FILE__, array($this, 'alia_vms_uninstall'));
        add_action('admin_enqueue_scripts',  array($this, 'alia_vms_enqueue_scripts'));
        add_action('admin_menu', array($this, 'add_alia_vms_submenu'));
        add_action('plugins_loaded', array($this, 'alia_vms_load_textdomain'));
        // Add custom product type filter
       //add_filter('woocommerce_product_types', array($this, 'custom_product_type'));
        add_action('init',array($this,'sess_start'));
       // add_action('init', array($this,'register_video_file_post_type'));
        add_filter('woocommerce_get_query_vars', array($this, 'add_video_library_endpoint'));

    
    }
    function sess_start() {
        if (!session_id())
        session_start();
    }
    function register_video_file_post_type() {
        $args = array(
            'public' => false,
            'show_ui' => true,
            'label' => 'Video Files',
            // Add any other arguments you need
        );
        register_post_type('video_file', $args);
    }
    
    
    // Register custom product type
    public function custom_product_type($types)
    {
        $types['video_product'] = 'Video_Product';
        return $types;
    }
    // Add ALIA VMS submenu
    public function add_alia_vms_submenu()
    {
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
    public function alia_vms_submenu_callback()
    {
        require_once plugin_dir_path(__FILE__) . 'templates/alia-vms.php';
    }

    // Callback function for Section 1 submenu
    public function user_manager_callback()
    {
        require_once plugin_dir_path(__FILE__) . 'templates/user_manager.php';
    }

    // Callback function for Section 2 submenu
    public function admin_video_callback()
    {
        require_once plugin_dir_path(__FILE__) . 'templates/video_manager.php';
    }



    function alia_vms_enqueue_scripts()
    {

        // Enqueue AdminLTE CSS
        wp_enqueue_style('adminlte-css', 'https://cdn.jsdelivr.net/npm/admin-lte@3.1.0/dist/css/adminlte.min.css');

        // Enqueue DataTables CSS
        wp_enqueue_style('datatables-css', 'https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css');

        // Enqueue jQuery and DataTables JS
        wp_enqueue_script('jquery');
        wp_enqueue_script('datatables-js', 'https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js', array('jquery'), '1.10.25', true);
        wp_enqueue_script('datatables-bootstrap-js', 'https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js', array('jquery', 'datatables-js'), '1.10.25', true);

        // Enqueue your custom JavaScript file
        wp_enqueue_script('custom-js', plugin_dir_url(__FILE__) . 'assets/js/custom-script.js', array('jquery', 'datatables-js', 'datatables-bootstrap-js'), '1.0', true);
        // Google Font: Source Sans Pro
        wp_enqueue_style('source-sans-pro', 'https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback');

         // Font Awesome
        wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css' );
  
        // Ionicons
        wp_enqueue_style('ionicons', 'https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css');

        //custom css
        wp_enqueue_style('1pams-css',plugin_dir_url(__FILE__) . 'assets/css/1pams.css');





        //wp_enqueue_script( 'my-plugin-delete-alert', plugin_dir_url( __FILE__ ) . 'js/delete-alert.js', array( 'jquery' ), '1.0.0', true );
    }

    // Activation hook
    function alia_vms_activate()
    {
        // Code to run on activation
    }


    // Deactivation hook

    function alia_vms_deactivate()
    {
        // Code to run on deactivation
    }


    // Uninstallation hook

    function alia_vms_uninstall()
    {
        // Code to run on uninstallation
    }

    // i18n support

    function alia_vms_load_textdomain()
    {
        load_plugin_textdomain('alia-vms', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }
} //fin class

$alia_vms_plugin = new AliaVms();
