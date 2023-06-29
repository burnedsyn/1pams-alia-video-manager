<?php
require_once '/var/www/clients/client0/web2/web/wp-content/plugins/1pams-alia-video-manager/includes/alia-vms-settings.php';
require_once '/var/www/clients/client0/web2/web/wp-content/plugins/1pams-alia-video-manager/includes/alia-vms-api.php';
require_once '/var/www/clients/client0/web2/web/wp-content/plugins/1pams-alia-video-manager/includes/alia-vms-video-product.php';

$vms_settings = new alia_vms_settings();
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'tab1';
?>
<div class="wrap">
    <h1>Alia VMS</h1>
    <h2 class="nav-tab-wrapper">
        <a href="?page=alia-vms&tab=tab1" class="nav-tab <?php echo $active_tab === 'tab1' ? 'nav-tab-active' : ''; ?>">Vimeo</a>
        <a href="?page=alia-vms&tab=tab2" class="nav-tab <?php echo $active_tab === 'tab2' ? 'nav-tab-active' : ''; ?>">OVH</a>
    </h2>

    <?php
    if ($active_tab === 'tab1') {
        // Content for Tab 1
        echo '<h2>VIMEO content</h2>';

        $vtoken = get_option('alia_vms_vimeo_token');
        $vclientId = get_option('alia_vms_vimeo_client_id');
        $vclientSecret = get_option('alia_vms_vimeo_client_secret');
        $vimeo = new \Vimeo\Vimeo($vclientId, $vclientSecret, $vtoken);
        $videoTitles = array();
        // Make a request to retrieve the user's videos
        $response = $vimeo->request('/users/134724345/videos', ['per_page' => 100]);
        /* echo("<pre>");
        print_r($response);
        echo("</pre>");  */
        // Check if the request was successful
        if ($response['status'] === 200) {
            // Extract the videos from the response
            $videos = $response['body']['data'];
            $videoDetails = array_map(function($video) {
                return [
                    'name' => $video['name'],
                    'uri' => $video['uri'],
                    'link' => $video['link'],
                    'description' => $video['description'],
                ];
            }, $videos);
        
            

            // Output the video details
            echo("<pre>");
            print_r($videoDetails);
            echo("</pre>");
        } else {
            // Handle the error
            echo 'Error: ' . $response['body']['error'];
        }
        

        /*submited form */
        if (isset($_POST['submit'])) {
            // Verify the nonce to ensure the request is legitimate
            if (!wp_verify_nonce($_POST['alia_vms_vimeo_settings_nonce'], 'alia_vms_vimeo_settings')) {
                die('Security check failed. Please try again.');
            }

            update_option('alia_vms_vimeo_token', sanitize_text_field($_POST['alia_vms_vimeo_token']));
            update_option('alia_vms_vimeo_client_id', sanitize_text_field($_POST['alia_vms_vimeo_client_id']));
            update_option('alia_vms_vimeo_client_secret', sanitize_text_field($_POST['alia_vms_vimeo_client_secret']));
        }
    ?>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Enter Your Vimeo Connection Settings</h3>

            </div>
            <!-- /.card-header -->
            <!-- form start -->
            <form role="form" method="post" action="">
                <div class="card-body">
                    <?php
                    // Display nonce field for security
                    wp_nonce_field('alia_vms_vimeo_settings', 'alia_vms_vimeo_settings_nonce');
                    ?>
                    <?php $vms_settings->alia_vms_vimeo_token_render(); ?>
                    <?php $vms_settings->alia_vms_vimeo_clientId_render(); ?>
                    <?php $vms_settings->alia_vms_vimeo_client_secret_render(); ?>
                </div>
                <!-- /.card-body -->

                <div class="card-footer">
                    <button type="submit" name="submit" class="btn btn-primary">Save Settings</button>
                </div>
            </form>
        </div>
    <?php
    } elseif ($active_tab === 'tab2') {

        // Update the plugin options with the submitted data
        if (isset($_POST['submit'])) {
            // Verify the nonce to ensure the request is legitimate
            if (!wp_verify_nonce($_POST['alia_vms_ovh_settings_nonce'], 'alia_vms_ovh_settings')) {
                die('Security check failed. Please try again.');
            }

            update_option('alia_vms_ovh_endpoint', sanitize_text_field($_POST['alia_vms_ovh_endpoint']));
            update_option('alia_vms_ovh_access_key', sanitize_text_field($_POST['alia_vms_ovh_access_key']));
            update_option('alia_vms_ovh_secret_key', sanitize_text_field($_POST['alia_vms_ovh_secret_key']));
            update_option('alia_vms_ovh_region', sanitize_text_field($_POST['alia_vms_ovh_region']));
        }

        // Retrieve the OVH S3 credentials from the 'Alia VMS OVH Settings' page
        $endpoint = get_option('alia_vms_ovh_endpoint');
        $access_key = get_option('alia_vms_ovh_access_key');
        $secret_key = get_option('alia_vms_ovh_secret_key');
        $region = get_option('alia_vms_ovh_region');




        // Content for Tab 2
    ?>
        <h2>OVH Settings</h2>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Enter Your OVH S3 Connection Settings</h3>

            </div>
            <!-- /.card-header -->
            <!-- form start -->
            <form role="form" method="post" action="">
                <div class="card-body">
                    <?php
                    // Display nonce field for security
                    wp_nonce_field('alia_vms_ovh_settings', 'alia_vms_ovh_settings_nonce');
                    ?>

                    <div class="form-group">
                        <label for="alia_vms_ovh_endpoint">Endpoint:</label>
                        <input type="text" name="alia_vms_ovh_endpoint" id="alia_vms_ovh_endpoint" value="<?php echo esc_attr($endpoint); ?>" class="form-control" placeholder="Enter OVH S3 Endpoint URL">
                    </div>
                    <?php $vms_settings->alia_vms_ovh_access_key_render(); ?>
                    <div class="form-group">
                        <label for="alia_vms_ovh_secret_key">Secret Key:</label>
                        <input type="text" name="alia_vms_ovh_secret_key" id="alia_vms_ovh_secret_key" value="<?php echo esc_attr($secret_key); ?>" class="form-control" placeholder="Enter OVH S3 Secret Key">
                    </div>
                    <div class="form-group">
                        <label for="alia_vms_ovh_region">Region:</label>
                        <input type="text" name="alia_vms_ovh_region" id="alia_vms_ovh_region" value="<?php echo esc_attr($region); ?>" class="form-control" placeholder="Enter OVH S3 Region">
                    </div>
                </div>
                <!-- /.card-body -->

                <div class="card-footer">
                    <button type="submit" name="submit" class="btn btn-primary">Save Settings</button>
                </div>
            </form>
        </div>
    <?php }
    ?>
</div>

</div>