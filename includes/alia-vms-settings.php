<?php
class alia_vms_settings{
    private $access_key;
    private $region;
    private $secret_key;
    private $endpoint_name;
    private $vimeo_token;
    private $vimeo_clientId;
    private $vimeo_client_secret;
    
    public function __construct()  {
        $this->alia_vms_ovh_settings_init();
        $this-> alia_vms_vimeo_settings_init();
        add_action('admin_init', 'alia_vms_ovh_settings_init');
        add_action('admin_init', 'alia_vms_vimeo_settings_init');
        add_action('admin_menu','alia_vms_register_vimeo_settings_menu');
        add_action('admin_menu', 'alia_vms_register_ovh_settings_menu');
    }
    function alia_vms_vimeo_settings_init(){
        register_setting('alia_vimeo_settings', 'alia_vms_vimeo_token');
        register_setting('alia_vimeo_settings', 'alia_vms_vimeo_client_id');
        register_setting('alia_vimeo_settings', 'alia_vms_vimeo_secret_key');
        add_settings_section(
            'alia_vms_vimeo_settings_section',
            __('Vimeo API Settings', 'alia-vms'),
            'alia_vms_vimeo_settings_section_callback',
            'alia_vms_vimeo_settings'
        );
        add_settings_field(
            'alia_vms_vimeo_token_field',
            __('Vimeo Token', 'alia-vms'),
            'alia_vms_vimeo_tokenn_render',
            'alia_vms_vimeo_settings',
            'alia_vms_vimeo_settings_section'
        );
        add_settings_field(
            'alia_vms_vimeo_clientId_field',
            __('Vimeo Client Id', 'alia-vms'),
            'alia_vms_vimeo_clientId_render',
            'alia_vms_vimeo_settings',
            'alia_vms_vimeo_settings_section'
        );
        add_settings_field(
            'alia_vms_vimeo_client_secret_field',
            __('Vimeo Client Id', 'alia-vms'),
            'alia_vms_vimeo_client_secret_render',
            'alia_vms_vimeo_settings',
            'alia_vms_vimeo_settings_section'
        );
    
    }

    public function alia_vms_vimeo_token_render() {
   
        $value= get_option('alia_vms_vimeo_token');
        $this->vimeo_token =esc_attr($value);
       // echo '<input type="text" name="alia_vms_ovh_access_key" value="' . esc_attr($value) . '">';
        $group= <<< EOF
        <div class="form-group">
        <label for="alia_vms_vimeo_token">Vimeo Token:</label>
        <input type="text" name="alia_vms_vimeo_token" id="alia_vms_vimeo_token" value="$this->vimeo_token" class="form-control" placeholder="Enter your vimeo token">
        </div>
    
    EOF;
    
    
        echo $group;
    }
    public function alia_vms_vimeo_client_secret_render() {
   
        $value= get_option('alia_vms_vimeo_client_secret');
        $this->vimeo_client_secret =esc_attr($value);
       // echo '<input type="text" name="alia_vms_ovh_access_key" value="' . esc_attr($value) . '">';
        $group= <<< EOF
        <div class="form-group">
        <label for="alia_vms_vimeo_token">Vimeo Client Secret:</label>
        <input type="text" name="alia_vms_vimeo_client_secret" id="alia_vms_vimeo_client_secret" value="$this->vimeo_client_secret" class="form-control" placeholder="Enter your vimeo Client Secret">
        </div>
    
    EOF;
    
    
        echo $group;
    }    
    public function alia_vms_vimeo_clientId_render() {
   
        $value= get_option('alia_vms_vimeo_client_id');
        $this->vimeo_clientId =esc_attr($value);
       // echo '<input type="text" name="alia_vms_ovh_access_key" value="' . esc_attr($value) . '">';
        $group= <<< EOF
        <div class="form-group">
        <label for="alia_vms_vimeo_client_id">Vimeo Client Id:</label>
        <input type="text" name="alia_vms_vimeo_client_id" id="alia_vms_vimeo_client_id" value="$this->vimeo_clientId" class="form-control" placeholder="Enter your vimeo Client Id">
        </div>
    
    EOF;
    
    
        echo $group;
    }
    

    function alia_vms_ovh_settings_init() {
    register_setting('alia_ovh_settings', 'alia_vms_ovh_access_key');
    register_setting('alia_ovh_settings', 'alia_vms_ovh_secret_key');
    register_setting('alia_ovh_settings', 'alia_vms_ovh_endpoint_name');
    register_setting('alia_ovh_settings', 'alia_vms_ovh_region');
    
    add_settings_section(
        'alia_vms_ovh_settings_section',
        __('OVH S3 Settings', 'alia-vms'),
        'alia_vms_ovh_settings_section_callback',
        'alia_vms_ovh_settings'
    );
    add_settings_field(
        'alia_vms_ovh_region_field',
        __('REGION', 'alia-vms'),
        'alia_vms_ovh_region_render',
        'alia_vms_ovh_settings',
        'alia_vms_ovh_settings_section'
    );

    add_settings_field(
        'alia_vms_ovh_access_key_field',
        __('Access Key', 'alia-vms'),
        'alia_vms_ovh_access_key_render',
        'alia_vms_ovh_settings',
        'alia_vms_ovh_settings_section'
    );

    add_settings_field(
        'alia_vms_ovh_secret_key_field',
        __('Secret Key', 'alia-vms'),
        'alia_vms_ovh_secret_key_render',
        'alia_vms_ovh_settings',
        'alia_vms_ovh_settings_section'
    );

    add_settings_field(
        'alia_vms_ovh_endpoint_name_field',
        __('Ovh Endpoint', 'alia-vms'),
        'alia_vms_ovh_endpoint_name_render',
        'alia_vms_ovh_settings',
        'alia_vms_ovh_settings_section'
    );

}

public function alia_vms_ovh_access_key_render() {
   
    $value= get_option('alia_vms_ovh_access_key');
    $access_key =esc_attr($value);
   // echo '<input type="text" name="alia_vms_ovh_access_key" value="' . esc_attr($value) . '">';
    $group= <<< EOF
    <div class="form-group">
    <label for="alia_vms_ovh_access_key">Access Key:</label>
    <input type="text" name="alia_vms_ovh_access_key" id="alia_vms_ovh_access_key" value="$access_key" class="form-control" placeholder="Enter OVH S3 Access Key">
    </div>

EOF;


    echo $group;
}

function alia_vms_ovh_region_render() {
    $value = get_option('alia_vms_ovh_region');
    $region =esc_attr($value);
    $group= <<< EOF
    <div class="form-group">
    <label for="alia_vms_ovh_region">Region:</label>
    <input type="text" name="alia_vms_ovh_region" id="alia_vms_ovh_region" value="$region" class="form-control" placeholder="Enter OVH S3 Region">
    </div>

EOF;


    echo $group;
}

function alia_vms_ovh_secret_key_render() {
    $value = get_option('alia_vms_ovh_secret_key');
    $secret_key =esc_attr($value);
    $group= <<< EOF
    <div class="form-group">
    <label for="alia_vms_ovh_secret_key">Secret Key:</label>
    <input type="text" name="alia_vms_ovh_secret_key" id="alia_vms_ovh_secret_key" value="$secret_key" class="form-control" placeholder="Enter OVH S3 Secret Key">
    </div>

EOF;


    echo $group;
}

function alia_vms_ovh_endpoint_name_render() {
    $value = get_option('alia_vms_ovh_endpoint_name');
    $endpoint_name =esc_attr($value);
    $group= <<< EOF
    <div class="form-group">
    <label for="alia_vms_ovh_endpoint_name">Endpoint Name:</label>
    <input type="text" name="alia_vms_ovh_endpoint_name" id="alia_vms_ovh_endpoint_name" value="$endpoint_name" class="form-control" placeholder="Enter OVH S3 Endpoint Name">
    </div>

EOF;


    echo $group;
}


function alia_vms_ovh_settings_section_callback() {
    echo __('Enter your OVH S3 credentials.', 'alia-vms');
}

function alia_vms_ovh_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php _e('Alia VMS OVH Settings', 'alia-vms'); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('alia_ovh_settings');
            do_settings_sections('alia_vms_ovh_settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function alia_vms_register_ovh_settings_menu() {
    add_submenu_page(
        'alia_vms',
        __('Alia VMS OVH Settings', 'alia-vms'),
        __('OVH S3', 'alia-vms'),
        'manage_options',
        'alia_vms_ovh_settings',
        'alia_vms_ovh_settings_page'
    );
}
 function alia_vms_register_vimeo_settings_menu() {
    add_submenu_page(
        'alia_vms',
        __('Alia VMS VIMEO Settings', 'alia-vms'),
        __('VIMEO API', 'alia-vms'),
        'manage_options',
        'alia_vms_vimeo_settings',
        'alia_vms_vimeo_settings_page'
    );
}
}

?>