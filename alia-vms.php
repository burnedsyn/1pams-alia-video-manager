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

require_once dirname(__FILE__) . '/vendor/autoload.php'; // Load Composer autoloader

include_once dirname(__FILE__) . '/includes/alia-vms-database.php';
include_once dirname(__FILE__) . '/includes/alia-vms-api.php';
include_once dirname(__FILE__) . '/includes/alia-vms-settings.php';
include_once dirname(__FILE__) . '/includes/alia-vms-video-product.php';



//require_once 'vendor/autoload.php'; // Load Composer autoloader
use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Vimeo\Vimeo;

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
        add_action('init', array($this, 'sess_start'));
        // add_action('init', array($this,'register_video_file_post_type'));
        //add_filter('woocommerce_get_query_vars', array($this, 'add_video_library_endpoint'));


        //manage the video import from vimeo
        add_action('wp_ajax_pams_vimeo_import', array($this, 'pams_vimeo_import'));
        add_action('wp_ajax_nopriv_pams_vimeo_import', array($this, 'pams_vimeo_import'));


        //manage status information of vimeo import
        add_action('wp_ajax_pams_vimeo_import_status', array($this, 'pams_vimeo_import_status'));
        add_action('wp_ajax_nopriv_pams_vimeo_import_status', array($this, 'pams_vimeo_import_status'));

        //manage export vimeo import to ovh cloud object
        add_action('wp_ajax_pams_upcloud', array($this, 'pams_upcloud'));
        add_action('wp_ajax_nopriv_pams_upcloud', array($this, 'pams_upcloud'));
    }
    public function pams_upcloud()
    {
        //var_dump($_POST);
        $data = $_POST;

        $nonce=$data['nonce'];
        if (!wp_verify_nonce($nonce, "vimeoImportCustomNonce")) {
            wp_send_json_error('Invalid nonce.');
        }
        $directory = urldecode(strval(dirname($data["logpath"])));

        $directory=$directory.'/';
        $title=urldecode($data['title']);
        

       // $exfile1 = $directory . '/' . $data["Process"]['file'];$exfile1,
       /*  $exfile2 = $directory . "/1pams.log";
        $exfile3 = $directory . "/conversion.log"; */
        $exfile = [];
        
        $bucket = "aliavideo";
        $result=0;
        try {
            $uploader = new upcloud();
            $result = $uploader->uploadFilesToS3($bucket, $title, $directory, $exfile);
           
        } catch (AwsException $e) {
            // Handle any errors that occurred during the upload
            error_log("Error: {$e->getMessage()}");
            return false;
        }

        if (!is_null($result)) {
            usort($result, function ($a, $b) {
                $resolutionA = (int) filter_var($a['filename'], FILTER_SANITIZE_NUMBER_INT);
                $resolutionB = (int) filter_var($b['filename'], FILTER_SANITIZE_NUMBER_INT);
                return $resolutionA - $resolutionB;
            });

            $groupedArray = [];

            if (!is_null($result)) {
                foreach ($result as $element) {
                    preg_match('/(\d+)p/', $element['filename'], $matches);
                    $resolution = isset($matches[1]) ? $matches[1] : null;

                    if (strpos($element['folder'], 'HLS') !== false) {
                        if (strpos($element['filename'], 'm3u8') !== false) {
                            $groupedArray['HLS']['m3u8'] = $element;
                        } else {
                            $groupedArray['HLS']['segments'][] = $element;
                        }
                    } elseif (strpos($element['folder'], 'DASH') !== false) {
                        if (strpos($element['filename'], 'mpd') !== false) {
                            $groupedArray['DASH']['mpd'] = $element;
                        } else {
                            $groupedArray['DASH']['segments'][] = $element;
                        }
                    } elseif ($resolution) {
                        if (!isset($groupedArray[$resolution . 'p'])) {
                            $groupedArray[$resolution . 'p'] = [];
                        }
                        $groupedArray[$resolution . 'p'][] = $element;
                    } else {
                        $groupedArray['other'][] = $element; // Classify as 'other'
                    }
                }
                ksort($groupedArray);

               
                /*  echo ("<pre>");
                print_r($groupedArray);
                echo ("</pre>");  */
                // $_SESSION['ovhs3']=$groupedArray;

                $logFile = strval($groupedArray['1p'][0]['folder']) . '/1pams.log';
                $image_path = strval($groupedArray['1p'][0]['folder']) . '/thumbnail.jpg';
                $product_id = (int) file_get_contents($logFile);
                $prod = wc_get_product($product_id);
                // Step 3: Upload the image and set it as the product image
                $attachment = array(
                    'post_mime_type' => 'image/jpeg', // Replace with the appropriate mime type if the image is not a JPEG
                    'post_title' => basename($image_path),
                    'post_content' => '',
                    'post_status' => 'inherit'
                );

                $attachment_id = wp_insert_attachment($attachment, $image_path, $product_id);

                if (!is_wp_error($attachment_id)) {
                    require_once(ABSPATH . 'wp-admin/includes/image.php');
                    $attachment_data = wp_generate_attachment_metadata($attachment_id, $image_path);
                    wp_update_attachment_metadata($attachment_id, $attachment_data);

                    $prod->set_image_id($attachment_id);
                    $prod->save();
                }
                update_post_meta($product_id, 'video_data', $groupedArray);
                $prod->save();
            }
        }
        
        $returnDataJson = json_encode($groupedArray);
        wp_send_json_success($returnDataJson);
        wp_die();
    }
    public function pams_vimeo_import()
    {
        // Retrieve the data sent from the JavaScript function
        $data = $_POST['data'];

        // Access the individual values
        $index = $data['index'];
        $uri = $data['uri'];
        $videoLink = $data['videoLink'];
        $description = $data['description'];
        $title = $data['title'];
        $nonce = $data['nonce'];
        // Process the data or perform any necessary actions
        if (!wp_verify_nonce($nonce, "vimeoImportCustomNonce")) {
            wp_send_json_error('Invalid nonce.');
        }
        $vtoken = get_option('alia_vms_vimeo_token');
        $vclientId = get_option('alia_vms_vimeo_client_id');
        $vclientSecret = get_option('alia_vms_vimeo_client_secret');
        $vimeo = new \Vimeo\Vimeo($vclientId, $vclientSecret, $vtoken);
        // Download the video
        $response = $vimeo->request($uri);
        /* foreach($response as $index2 => $value2){
        echo($index2.''.$value2);
      } */
        $downloadLinks = $response['body']['download'];
        $downloadlink = '';
        foreach ($downloadLinks as $vid) {
            if ($vid['rendition'] === '1080p') {
                $downloadlink = $vid['link'];
                break;
            }
        }
        //file_put_contents('path/to/save/video.mp4', fopen($downloadLink, 'r'));
        $newProduct = new VideoProductUploader();
        $response = $newProduct->handle_vimeo_import($downloadlink, $title, $description);

        $response2 = urldecode($response['file_Path']);
        $response['file_Path'] = $response2;
        $returnData = json_encode($response);
        echo ("$returnData");
        //wp_send_json_success($response);
        // Always use wp_die() to end AJAX requests
        wp_die();
    }
    function pams_vimeo_import_status($logpath)
    {
        $fileProcessed = $_POST['logpath'];
        $index = $_POST['index'];

        $outputlog = pathinfo($fileProcessed, PATHINFO_DIRNAME) . '/conversion.log';
        $prodlog = pathinfo($fileProcessed, PATHINFO_DIRNAME) . '/1pams.log';
        $pamsdata = file_get_contents($prodlog);
        $logData = file_get_contents($outputlog);
        $result = array();

        $logs = explode("\n", $logData);
        foreach ($logs as $log) {
            $logParts = explode(';', $log);
            $result['date'] = isset($logParts[0]) ? trim($logParts[0]) : '';
            $result['level'] = isset($logParts[1]) ? trim($logParts[1]) : '';
            $message = isset($logParts[2]) ? $logParts[2] : '';
            $workParts = explode(':', $message);

            $result['operation'] = isset($workParts[0]) ? trim($workParts[0]) : '';
            $result['opStatus'] = isset($workParts[1]) ? trim($workParts[1]) : '';
            $result['data'] = isset($workParts[2]) ? trim($workParts[2]) : '';
            $currentconv = 0;
            switch ($result['opStatus']) {
                case 'start':
                    switch ($result['operation']) {

                        case "Process":
                            $file = pathinfo($result['data'], PATHINFO_FILENAME) . '.' . pathinfo($result['data'], PATHINFO_EXTENSION);
                            $timeLine['Process'] = array();
                            $timeLine['Process']['product'] = $pamsdata;
                            $timeLine['Process']['file'] = $file;
                            $datetime = DateTime::createFromFormat('Y-m-d H:i:s,u', $result['date']);
                            $time = $datetime->format('H:i:s');
                            $timeLine['Process']['start'] = $time;
                            $timeLine['Process']['status'] = $result['opStatus'];

                            break;
                        case "Conversion":
                            $resultdata = explode('to', $result['data']);
                            $source = pathinfo($resultdata[0], PATHINFO_FILENAME) . '.' . pathinfo($resultdata[0], PATHINFO_EXTENSION);
                            $file = pathinfo($resultdata[1], PATHINFO_FILENAME) . '.' . pathinfo($resultdata[1], PATHINFO_EXTENSION);
                            $tdata = explode("_", $file);
                            $i = $tdata[1];
                            $datetime = DateTime::createFromFormat('Y-m-d H:i:s,u', $result['date']);
                            $time = $datetime->format('H:i:s');
                            $timeLine['Conversion']["$i"]['file'] = $file;
                            $timeLine['Conversion']["$i"]['start'] = $time;

                            $timeLine['Conversion']["$i"]['status'] = $result['opStatus'];

                            break;
                        case "HLS generation":
                            $resultdata = explode(' ', $result['data']);
                            $i = $resultdata[2];
                            $datetime = DateTime::createFromFormat('Y-m-d H:i:s,u', $result['date']);
                            $time = $datetime->format('H:i:s');
                            $timeLine["HLS generation"][$i]['start'] = $time;
                            $timeLine["HLS generation"][$i]['status'] = $result['opStatus'];
                            break;
                        case "DASH generation":
                            $resultdata = explode(' ', $result['data']);
                            $i = $resultdata[2];
                            $datetime = DateTime::createFromFormat('Y-m-d H:i:s,u', $result['date']);
                            $time = $datetime->format('H:i:s');
                            $timeLine["DASH generation"][$i]['start'] = $time;
                            $timeLine["DASH generation"][$i]['status'] = $result['opStatus'];
                            break;
                    } //switch OPERATION
                    break;
                case 'done':
                    switch ($result['operation']) {

                        case "Process":
                            $datetime = DateTime::createFromFormat('Y-m-d H:i:s,u', $result['date']);
                            $time = $datetime->format('H:i:s');
                            $timeLine['Process']['end'] = $time;
                            $timeLine['Process']['status'] = $result['opStatus'];

                            break;
                        case "Conversion":
                            $resultdata = explode('to', $result['data']);
                            $source = pathinfo($resultdata[0], PATHINFO_FILENAME) . '.' . pathinfo($resultdata[0], PATHINFO_EXTENSION);
                            $file = pathinfo($resultdata[1], PATHINFO_FILENAME) . '.' . pathinfo($resultdata[1], PATHINFO_EXTENSION);
                            $tdata = explode("_", $file);
                            $i = $tdata[1];
                            $datetime = DateTime::createFromFormat('Y-m-d H:i:s,u', $result['date']);
                            $time = $datetime->format('H:i:s');
                            $timeLine['Conversion']["$i"]['end'] = $time;
                            $timeLine['Conversion']["$i"]['status'] = $result['opStatus'];
                            $timeLine['Conversion']["$i"]['data'] = $result['data'];
                            break;
                        case "HLS generation":
                            $resultdata = explode(' ', $result['data']);
                            $i = $resultdata[2];
                            $datetime = DateTime::createFromFormat('Y-m-d H:i:s,u', $result['date']);
                            $time = $datetime->format('H:i:s');
                            $timeLine["HLS generation"][$i]['end'] = $time;
                            $timeLine["HLS generation"][$i]['status'] = $result['opStatus'];
                            break;
                        case "DASH generation":
                            $resultdata = explode(' ', $result['data']);
                            $i = $resultdata[2];
                            $datetime = DateTime::createFromFormat('Y-m-d H:i:s,u', $result['date']);
                            $time = $datetime->format('H:i:s');
                            $timeLine["DASH generation"][$i]['end'] = $time;
                            $timeLine["DASH generation"][$i]['status'] = $result['opStatus'];
                            break;
                    } //switch OPERATION
                    break;
                case 'error':

                    break;
            }
        } //foreach

        $opstatus = $timeLine['Process']['status'];
        if ($opstatus != 'done') $op = 'conversion';
        else $op = 'process';
        $returnData = [
            'index' => $index,
            'operation' => $op,
            'status' => $opstatus,
            'fullLog' => $timeLine
        ];
        $returnDataJson = json_encode($returnData);
        wp_send_json_success($returnDataJson);
        // Always use wp_die() to end AJAX requests
        wp_die();
    }

    function sess_start()
    {
        if (!session_id())
            session_start();
    }
    function register_video_file_post_type()
    {
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
        wp_enqueue_script('jquery', 'https://code.jquery.com/jquery-3.6.0.min.js', array(), '3.6.0', true);
        wp_enqueue_script('datatables-js', 'https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js', array('jquery'), '1.10.25', true);
        wp_enqueue_script('datatables-bootstrap-js', 'https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js', array('jquery', 'datatables-js'), '1.10.25', true);

        // Enqueue your custom JavaScript file
        wp_enqueue_script('custom-js', plugin_dir_url(__FILE__) . 'assets/js/custom-script.js', array('jquery', 'datatables-js', 'datatables-bootstrap-js'), '1.0', true);
        // Google Font: Source Sans Pro
        wp_enqueue_style('source-sans-pro', 'https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback');

        // Font Awesome
        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css');

        // Ionicons
        wp_enqueue_style('ionicons', 'https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css');

        //custom css
        wp_enqueue_style('1pams-css', plugin_dir_url(__FILE__) . 'assets/css/1pams.css');





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
