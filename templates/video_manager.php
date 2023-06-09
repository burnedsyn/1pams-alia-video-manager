<?php
require_once '/var/www/clients/client0/web2/web/wp-content/plugins/1pams-alia-video-manager/vendor/autoload.php'; // Load Composer autoloader
require_once '/var/www/clients/client0/web2/web/wp-content/plugins/1pams-alia-video-manager/includes/alia-vms-api.php';

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'tab1';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;
?>
<div class="wrap">
    <section class="content-header">
        <h1>Video Manager</h1>
    </section>
    <section class="content">
        <?php
        // OVH API credentials
        $accessKeyId = '72382137a8064638a1ebd8ae19f9f3d3';
        $secretAccessKey = '86d6ddece3924d2b9f3b3adda97112a1';
        $region = 'gra'; // e.g., ''
        //$consumerKey = 'user-YDWtFgwJrUXC'; 
        // Create a new S3Client instance
        $client = new S3Client([
            'version' => 'latest',
            'region'  => $region,
            'endpoint' => 'https://s3.gra.io.cloud.ovh.net/', // OVH S3 endpoint
            'credentials' => [
                'key'    => $accessKeyId,
                'secret' => $secretAccessKey,
            ],
        ]);

        // Get account information
        $result = $client->listBuckets();

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
        function video_upload_page()
        {
            if (isset($_POST['submit'])) {
                // Handle form submission
                $files = $_FILES['video_files'];
                $resolutions = $_POST['video_resolutions'];
                $titles = $_POST['video_titles'];
                $descriptions = $_POST['video_descriptions'];
                $prices = $_POST['video_prices'];

                // Create a new instance of Video_Product class
                $video_product = new Video_Product();

                // Call the method to handle video upload and metadata storage
                $video_product->handle_video_upload($files, $resolutions, $titles, $descriptions, $prices);

                echo 'Video uploaded successfully!';
            } else {
                // Display upload form
        ?>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Upload Video</h3>
                    </div>
                    <div class="card-body">
                        <form method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="video_files">Video Files:</label>
                                <input type="file" name="video_files[]" id="video_files" multiple class="form-control-file">
                            </div>

                            <div class="form-group">
                                <label for="video_resolutions">Resolutions:</label>
                                <input type="text" name="video_resolutions[]" id="video_resolutions" multiple class="form-control">
                            </div>

                            <div class="form-group">
                                <label for="video_titles">Titles:</label>
                                <input type="text" name="video_titles[]" id="video_titles" multiple class="form-control">
                            </div>

                            <div class="form-group">
                                <label for="video_descriptions">Descriptions:</label>
                                <textarea name="video_descriptions[]" id="video_descriptions" multiple class="form-control"></textarea>
                            </div>

                            <div class="form-group">
                                <label for="video_prices">Prices:</label>
                                <input type="text" name="video_prices[]" id="video_prices" multiple class="form-control">
                            </div>

                            <div class="card-footer">
                                <input type="submit" name="submit" value="Upload" class="btn btn-primary">
                            </div>
                        </form>
                    </div>
                </div>

        <?php
            }
        }
        switch ($active_tab) {
            case "tab1":
                echo ("<h1>upload new video</h1>");

                /*DEBUTMDOFIF*/
                video_upload_page();
                /*FINMODIFI*/
                break;
            case "tab2":
                echo ("<h1>TAB2</h1>");
                break;
        }

        ?>
    </section>
</div>