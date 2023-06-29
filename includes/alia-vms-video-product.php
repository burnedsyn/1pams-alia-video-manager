<?php
require_once '/var/www/clients/client0/web2/web/wp-content/plugins/1pams-alia-video-manager/includes/alia-vms-api.php';
require_once '/var/www/clients/client0/web2/web/wp-content/plugins/1pams-alia-video-manager/includes/alia-vms-video-transcoder.php';
require_once '/var/www/clients/client0/web2/web/wp-content/plugins/1pams-alia-video-manager/includes/alia-vms-upcloud.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class VideoProductUploader
{
    public function handle_video_upload($files,  $titles, $descriptions, $prices)
    {
        $file = $files;
        
        $title = $titles;
        $description = $descriptions;
        $price = $prices;
        $product_video_file = array();
        // Create a new product
        $product = new WC_Product();

        // Set product data
        $product->set_name($title);
        $product->set_description($description);
        $product->set_regular_price($price);
        $product->set_price($price);
        $product->set_sku($this->generate_sku()); // Generate a unique SKU
        $product->set_virtual(true);
        $product->set_downloadable(true);

        // Set product category
        $category = get_term_by('slug', 'alia', 'product_cat'); // Replace 'video_category' with the slug of your desired category
        if ($category) {
            $product->set_category_ids(array($category->term_id));
        }

        // Set product date
        $current_date = current_time('mysql');
        $product->set_date_created($current_date);
        $product_id = $product->save();
        // Save the base file in the upload directory
        $file_path = $this->save_base_file($file, $product_id);

        // Transcode video and upload to OVH Object Storage
        $bucketdata = $this->transcode_and_upload($file_path, $product_id);

        $directory_url = $bucketdata;

        $downloadable_files = array(
            'file_name' => array(
                'name' => $title,
                'file' => $directory_url
            )
        );
        update_post_meta($product_id, '_downloadable_files', $downloadable_files);

        // Save video directory URL
        update_post_meta($product_id, 'video_directory_url', $bucketdata);

        $product->save();

        return $directory_url;
    }



    // Method to save the base file
    private function save_base_file($file, $product_id)
    {
        $upload_dir = wp_upload_dir();
        $title =get_the_title($product_id);
        $test = $upload_dir['basedir'] . '/video_upload/' . $title;
        $base_dir = trailingslashit($test);

        // Create the video_uploads directory if it doesn't exist

        if (!file_exists($base_dir)) {
            wp_mkdir_p($base_dir);
        }
        chmod($base_dir, 0775);
        $hls_dir = $base_dir . 'HLS';
        if (!file_exists($hls_dir)) {
            wp_mkdir_p($hls_dir);
        }
        chmod($hls_dir, 0775);
        $DASH_dir = $base_dir . 'DASH';
        if (!file_exists($DASH_dir)) {
            wp_mkdir_p($DASH_dir);
        }
        chmod($DASH_dir, 0775);
        $base_filename = sanitize_file_name($file['video_files']['name']);
        $base_path = trailingslashit($base_dir) . $base_filename;
        // Move the uploaded file to the video_uploads directory


        $savedFile = move_uploaded_file($file['video_files']['tmp_name'], $base_path);
        if (!$savedFile) {
            wp_die("unable to save the video file !");
        }
        return $base_path;
    }


    private function transcode_and_upload($file_path, $product_id)
    {
        // Perform transcoding and upload to OVH Object Storage
        // Use the $file_path to access the base file for transcoding and uploading
        $transcoder = new Alia_VMS_Transcoder();
        $transcoded = $transcoder->transcode_video($file_path, $product_id);

        /* $upcloud = new upcloud();
        $folder=strval($transcoded["Process"]["outputDir"]);
       
        $excludingFile=$transcoded["Process"]["file"];
        $uploadedFiles = $upcloud->uploadFilesToS3($folder, $excludingFile);
        
        
        $_SESSION['transcoded']=$transcoded;
        $_SESSION['ovhs3']=$uploadedFiles;   */
        // After transcoding and uploading, return the URL of the directory or bucket
        $directory_url = $transcoded;
        return $directory_url;
    }

    private function generate_sku()
    {
        $random_number = mt_rand(1000, 9999); // Generate a random 4-digit number
        return 'VIDEO-' . $random_number;
    }
}

// Example upload page template
function video_upload_page()
{
    if (isset($_GET['upload'])) {


        $data = $_SESSION['data'];
        $directory = strval($data["Process"]["outputDir"]);

        $titlear = explode('/', $directory);

        $title = $titlear[count($titlear) - 1];

        $exfile1 = $directory . '/' . $data["Process"]['file'];
        $exfile2 = $directory . "/1pams.log";
        $exfile3 = $directory . "/conversion.log";
        $exfile = [$exfile1, $exfile2, $exfile3];
        $bucket = "aliavideo";
        try {
            $uploader = new upcloud();
            $result = $uploader->uploadFilesToS3($bucket, $title, $directory, $exfile);
        } catch (AwsException $e) {
            // Handle any errors that occurred during the upload
            error_log("Error: {$e->getMessage()}");
            return false;
        }

        return $result;
    } //upload
    if (isset($_GET['conversion'])) {
        $text = urlencode(strval($_GET['conversion']));

        $transcoder = new Alia_VMS_Transcoder();
        $verif = $transcoder->get_vidStatus($text);

        // print_r($verif);

    }
    if (isset($_POST['submit'])) {
        // Handle form submission
        $files = $_FILES;
        
        $titles = $_POST['video_titles'];
        $descriptions = $_POST['video_descriptions'];
        $prices = $_POST['video_prices'];

        // Create a new instance of VideoProductUploader class
        $transcoder = new VideoProductUploader();

        // Call the method to handle video upload and metadata storage
        $transcoder->handle_video_upload($files,  $titles, $descriptions, $prices);
    } else {
        // Display upload form
        ?>
<div class="card">
  <div class="card-header bg-navy text-white">
    <h3 class="card-title">Add new video</h3>
  </div>
  <div class="card-body">
    <?php
    if (!isset($_GET['conversion']) && !isset($_GET['upload'])) {
    ?>
    <form method="post" enctype="multipart/form-data">
      <div class="form-group bg-navy text-white">
        <label for="video_files">Video Files:</label>
        <input type="file" name="video_files" id="video_files" class="form-control-file">
      </div>
      <div class="form-group  bg-navy text-white">
        <label for="video_titles">Titles:</label>
        <input type="text" name="video_titles" id="video_titles" class="form-control">
      </div>
      <div class="form-group bg-navy text-white">
        <label for="video_descriptions">Descriptions:</label>
        <?php
        $content = ''; // The initial content for the rich editor
        $editor_id = 'video_descriptions'; // The ID of the textarea

        // Arguments for the wp_editor() function
        $settings = array(
          'textarea_name' => 'video_descriptions', // The name attribute of the textarea
          'textarea_rows' => 5, // The number of rows for the textarea
          'media_buttons' => false, // Hide media buttons
          'editor_class' => 'wp-editor-container',//custom css class
        );

        // Output the rich editor
        wp_editor($content, $editor_id, $settings);
        ?>
      </div>
      <div class="form-group bg-navy text-white">
        <label for="video_prices">Prices:</label>
        <input type="text" name="video_prices" id="video_prices" class="form-control">
      </div>
      <div class="form-group">
        <input type="submit" name="submit" value="Upload" class="btn btn-primary">
      </div>
    </form>
    <?php
    }
    ?>
  </div>
</div>


        
<?php
        }
    }

