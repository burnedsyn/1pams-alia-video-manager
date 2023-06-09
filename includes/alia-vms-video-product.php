<?php

// Register custom product type
function custom_product_type($types) {
    $types['video_product'] = 'Video_Product';
    return $types;
}
add_filter('woocommerce_product_types', 'custom_product_type');

// Custom product class
class Video_Product extends WC_Product {

    // Constructor
    public function __construct($product) {
        $this->product_type = 'video_product';
        parent::__construct($product);
    }

    // Define additional product data here

    // Method to handle video file upload and metadata storage
    public function handle_video_upload($files, $resolutions, $titles, $descriptions, $prices) {
        // Handle file upload and storage here
        // Store metadata using post meta or custom database table

        // Example code to create a product post
        $new_product = array(
            'post_title' => $titles[0], // Assuming the first title represents the main video
            'post_content' => $descriptions[0], // Assuming the first description represents the main video
            'post_status' => 'publish',
            'post_type' => 'product'
        );
        $product_id = wp_insert_post($new_product);

        // Set product price
        update_post_meta($product_id, '_regular_price', $prices[0]); // Assuming the first price represents the main video
        update_post_meta($product_id, '_price', $prices[0]); // Assuming the first price represents the main video

        // Set product type to 'video_product'
        wp_set_object_terms($product_id, 'video_product', 'product_type');

        // Save additional files and their metadata
        for ($i = 0; $i < count($files); $i++) {
            $file = $files[$i];
            $resolution = $resolutions[$i];
            $title = $titles[$i];
            $description = $descriptions[$i];
            $price = $prices[$i];

            // Handle each file and its associated metadata
            // Store metadata using post meta or custom database table

            // Example code to store resolution-specific data
            update_post_meta($product_id, 'video_file_' . $resolution, $file);
            update_post_meta($product_id, 'video_title_' . $resolution, $title);
            update_post_meta($product_id, 'video_description_' . $resolution, $description);
            update_post_meta($product_id, 'video_price_' . $resolution, $price);
        }

        // Trigger WooCommerce product save hooks
        do_action('woocommerce_process_product_meta', $product_id);
        do_action('woocommerce_save_product_variation', $product_id);
    }
}

// Example upload page template
function video_upload_page() {
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
        <form method="post" enctype="multipart/form-data">
            <label for="video_files">Video Files:</label>
            <input type="file" name="video_files[]" id="video_files" multiple><br>

            <label for="video_resolutions">Resolutions:</label>
            <input type="text" name="video_resolutions[]" id="video_resolutions" multiple><br>

            <label for="video_titles">Titles:</label>
            <input type="text" name="video_titles[]" id="video_titles" multiple><br>

            <label for="video_descriptions">Descriptions:</label>
            <textarea name="video_descriptions[]" id="video_descriptions" multiple></textarea><br>

            <label for="video_prices">Prices:</label>
            <input type="text" name="video_prices[]" id="video_prices" multiple><br>

            <input type="submit" name="submit" value="Upload">
        </form>
        <?php
    }
}
