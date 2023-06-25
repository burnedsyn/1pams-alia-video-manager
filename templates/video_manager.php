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
                    usort($test, function($a, $b) {
                        $resolutionA = (int) filter_var($a['filename'], FILTER_SANITIZE_NUMBER_INT);
                        $resolutionB = (int) filter_var($b['filename'], FILTER_SANITIZE_NUMBER_INT);
                        return $resolutionA - $resolutionB;
                    });
                $_SESSION['ovhs3']=$test;

                }
                /*FINMODIFI*/
                break;
            case "tab2":
                echo ("<h1>TAB2</h1>");
                $product_id=195;
                $video_directory_url = get_post_meta($product_id, 'video_data', true);
                 $test=$_SESSION['ovhs3'];
               /* usort($test, function($a, $b) {
                    $resolutionA = (int) filter_var($a['filename'], FILTER_SANITIZE_NUMBER_INT);
                    $resolutionB = (int) filter_var($b['filename'], FILTER_SANITIZE_NUMBER_INT);
                    return $resolutionA - $resolutionB;
                }); 
                $groupedArray = [];
                IF( !is_null($test)) {
                    foreach ($test as $element) {
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
                            $groupedArray[$resolution . 'p'][] = $element;
                        }
                    }
                    ksort($groupedArray);
                   */

                   $groupedArray = [];

if (!is_null($test)) {
    foreach ($test as $element) {
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

              
    echo("<pre>");
   print_r($groupedArray);
   echo("</pre>");
   // $_SESSION['ovhs3']=$groupedArray;
   $logFile =strval($groupedArray['1p'][0]['folder']).'/1pams.log'; // Replace with the actual path to the 1pams.log file
$product_id = (int) file_get_contents($logFile);
update_post_meta($product_id, 'video_data', $groupedArray);
}
                //print_r($test);
               /*  print_r($_SESSION['data']);
                print_r($_SESSION['ovhs3']);
                print_r($_SESSION['transcoded']); */
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