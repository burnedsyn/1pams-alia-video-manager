<?php
require_once ALIA_VMS_PLUGIN_PATH.'/vendor/autoload.php'; // Load Composer autoloader
require_once ALIA_VMS_PLUGIN_PATH.'/includes/alia-vms-api.php';
require_once ALIA_VMS_PLUGIN_PATH.'/includes/alia-vms-video-product.php';

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

        $client = get_client();
        // Get account information
        $result = $client->listBuckets();
        //var_dump($result);
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

             
                $test = video_upload_page();

                 /*DEBUTMDOFIF*/
                if (!is_null($test)) {
                    usort($test, function ($a, $b) {
                        $resolutionA = (int) filter_var($a['filename'], FILTER_SANITIZE_NUMBER_INT);
                        $resolutionB = (int) filter_var($b['filename'], FILTER_SANITIZE_NUMBER_INT);
                        return $resolutionA - $resolutionB;
                    });

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

                       
                        /* echo ("<pre>");
                        print_r($groupedArray);
                        echo ("</pre>"); */
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


                    $_SESSION['ovhs3'] = $groupedArray;
                    echo ("<span class=\"bg-success text-white\">Nouveau produit créer avec succès</span>");
                    sleep(10);
                    wp_redirect(home_url()."/wp-admin/admin.php?page=alia-vms-video&tab=tab1");
                    exit;
                }
                /*FINMODIFI*/
                break;
            case "tab2":
                global $wp;
                $url_parts = parse_url( home_url() );
                $current_url =  $url_parts['scheme'] . "://" . $url_parts['host'] . add_query_arg( NULL, NULL );
               
                  
                include_once('vidmanagertab2.php');

                break;
        }

        ?>
    </section>
</div>
<?php
