<?php
require_once '/var/www/clients/client0/web2/web/wp-content/plugins/1pams-alia-video-manager/vendor/autoload.php'; // Load Composer autoloader
require_once '/var/www/clients/client0/web2/web/wp-content/plugins/1pams-alia-video-manager/includes/alia-vms-api.php';
require_once '/var/www/clients/client0/web2/web/wp-content/plugins/1pams-alia-video-manager/includes/alia-vms-video-product.php';
       
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'tab1';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;
?>
<div class="wrap container-fluid">
    <section class="content-header">
        <h1>Video Manager</h1>
    </section>
    <section class="content container-fluid ">
        <?php
       
        $client=get_client();
        // Get account information
        $result = $client->listBuckets();
        var_dump($result);
        // Output the account information
        $test = $result['Buckets'][0]['Name'];
        echo '<div class="card-success">';
        echo '<div class="card-body">';
        echo '<h5 class="card-title">Connected to OVH Container</h5>';
        echo '<p class="card-text"><span class="btn-success">' . $test . '</span> Connection status Ok</p>';
        echo '</div>';
        echo '</div>';
        ?>
        <h2 class="nav-tab-wrapper">
            <a href="?page=alia-vms-video&tab=tab1" class="nav-tab <?php echo $active_tab === 'tab1' ? 'nav-tab-active' : ''; ?>">New Video</a>
            <a href="?page=alia-vms-video&tab=tab2" class="nav-tab <?php echo $active_tab === 'tab2' ? 'nav-tab-active' : ''; ?>">Manage Video</a>
        </h2>
        <?php

        switch ($active_tab) {
            case "tab1":
                

                /*DEBUTMDOFIF*/
                $test=video_upload_page();
                IF( !is_null($test)) {
                    var_dump($test);
                    $_SESSION['ovhs3']=$test;
                }
                /*FINMODIFI*/
                break;
            case "tab2":
                echo ("<h1>TAB2</h1>");
                $product_id=187;
                $video_directory_url = get_post_meta($product_id, 'video_directory_url', true);
                print_r($_SESSION['data']);
                print_r($_SESSION['ovhs3']);
                print_r($_SESSION['transcoded']);
                if (!empty($video_directory_url)) {
                    echo $video_directory_url;
                }
                else {
                    echo ("<h1>MERDE</h1>");
                }
                break;
        }

        ?>
    </section>
</div>
<?php 