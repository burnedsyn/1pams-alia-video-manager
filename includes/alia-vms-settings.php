<?php
class alia_vms_settings{
    private $access_key;
    private $region;
    private $secret_key;
    private $endpoint_name;
    private $vimeo_active;
    private $vimeo_token;
    private $vimeo_clientId;
    private $vimeo_client_secret;
    private $openai_token;
    private $openai_active;
    
    public function __construct()  {
        $this->alia_vms_ovh_settings_init();
        $this-> alia_vms_vimeo_settings_init();
        $this->alia_vms_openai_settings_init();
        add_action('admin_init', 'alia_vms_openai_settings_init');
        add_action('admin_init', 'alia_vms_ovh_settings_init');
        add_action('admin_init', 'alia_vms_vimeo_settings_init');

        add_action('admin_menu','alia_vms_register_openai_settings_menu');
        add_action('admin_menu','alia_vms_register_vimeo_settings_menu');
        add_action('admin_menu', 'alia_vms_register_ovh_settings_menu');
    }
    function alia_vms_openai_settings_init(){
        register_setting('alia_openai_settings', 'alia_vms_openai_token');
        register_setting( 'alia_openai_settings', 'alia_vms_openai_active');
        add_settings_section(
            'alia_vms_open_settings_section',
            __('Openai API Settings', 'alia-vms'),
            'alia_vms_openai_settings_section_callback',
            'alia_vms_openai_settings'
        );
        add_settings_field(
            'alia_vms_openai_token_field',
            __('Openai Token', 'alia-vms'),
            'alia_vms_openai_token_render',
            'alia_vms_openai_settings',
            'alia_vms_openai_settings_section'
        );
        add_settings_field(
            'alia_vms_openai_token_field',
            __('Openai Active', 'alia-vms'),
            'alia_vms_openai_active_render',
            'alia_vms_openai_settings',
            'alia_vms_openai_settings_section'
        );

    }
    public function alia_vms_openai_token_render() {
   
        $value= get_option('alia_vms_openai_token');
        $this->openai_token =esc_attr($value);
       // echo '<input type="text" name="alia_vms_ovh_access_key" value="' . esc_attr($value) . '">';
        $group= <<< EOF
        <div class="form-group">
        <label for="alia_vms_vimeo_token">Openai Token:</label>
        <input type="text" style='width:500px;' name="alia_vms_openai_token" id="alia_vms_openai_token" value="$this->openai_token" class="form-control" placeholder="Enter your openai token">
        </div>
    
    EOF;
    
    
        echo $group;
    }

    public function alia_vms_openai_active_render() {
   
        $value= get_option('alia_vms_openai_active');
        $this->openai_active =esc_attr($value);
       // echo '<input type="text" name="alia_vms_ovh_access_key" value="' . esc_attr($value) . '">';
        $group= <<< EOF
        <div class="form-group">
        <label for="alia_vms_openai_active">Openai Is active:</label>
        <input type="checkbox"  name="alia_vms_openai_active" id="alia_vms_openai_active" $this->openai_active class="form-control" >
        </div>
    
    EOF;
    
    
        echo $group;
    }

    function alia_vms_vimeo_settings_init(){
        register_setting('alia_vimeo_settings', 'alia_vms_vimeo_active');
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
            __('Vimeo Active', 'alia-vms'),
            'alia_vms_vimeo_active_render',
            'alia_vms_vimeo_settings',
            'alia_vms_vimeo_settings_section'
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
            __('Client Id', 'alia-vms'),
            'alia_vms_vimeo_clientId_render',
            'alia_vms_vimeo_settings',
            'alia_vms_vimeo_settings_section'
        );
        add_settings_field(
            'alia_vms_vimeo_client_secret_field',
            __('Client secret', 'alia-vms'),
            'alia_vms_vimeo_client_secret_render',
            'alia_vms_vimeo_settings',
            'alia_vms_vimeo_settings_section'
        );
    
    }
    public function alia_vms_vimeo_active_render() {
   
        $value= get_option('alia_vms_vimeo_active');
        $this->vimeo_active =esc_attr($value);
       // echo '<input type="text" name="alia_vms_ovh_access_key" value="' . esc_attr($value) . '">';
        $group= <<< EOF
        <div class="form-group">
        <label for="alia_vms_vimeo_active">Vimeo Is active:</label>
        <input type="checkbox"  name="alia_vms_vimeo_active" id="alia_vms_vimeo_active" $this->vimeo_active class="form-control" >
        </div>
    
    EOF;
    
    
        echo $group;
    }

    public function alia_vms_vimeo_token_render() {
   
        $value= get_option('alia_vms_vimeo_token');
        $this->vimeo_token =esc_attr($value);
       // echo '<input type="text" name="alia_vms_ovh_access_key" value="' . esc_attr($value) . '">';
        $group= <<< EOF
        <div class="form-group">
        <label for="alia_vms_vimeo_token">Vimeo Token:</label>
        <input type="text" name="alia_vms_vimeo_token" id="alia_vms_vimeo_token" style='width:500px;' value="$this->vimeo_token" class="form-control" placeholder="Enter your vimeo token">
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
        <label for="alia_vms_vimeo_token">Client Secret:</label>
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
        <label for="alia_vms_vimeo_client_id">Client Id:</label>
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
    $this->access_key =esc_attr($value);
   // echo '<input type="text" name="alia_vms_ovh_access_key" value="' . esc_attr($value) . '">';
    $group= <<< EOF
    <div class="form-group">
    <label for="alia_vms_ovh_access_key">Access Key:</label>
    <input type="text" name="alia_vms_ovh_access_key" id="alia_vms_ovh_access_key" value="$this->access_key" class="form-control" placeholder="Enter OVH S3 Access Key">
    </div>

EOF;


    echo $group;
}

function alia_vms_ovh_region_render() {
    $value = get_option('alia_vms_ovh_region');
    $this->region =esc_attr($value);
    $group= <<< EOF
    <div class="form-group">
    <label for="alia_vms_ovh_region">Region:</label>
    <input type="text" name="alia_vms_ovh_region" id="alia_vms_ovh_region" value="$this->region" class="form-control" placeholder="Enter OVH S3 Region">
    </div>

EOF;


    echo $group;
}

function alia_vms_ovh_secret_key_render() {
    $value = get_option('alia_vms_ovh_secret_key');
    $this->secret_key =esc_attr($value);
    $group= <<< EOF
    <div class="form-group">
    <label for="alia_vms_ovh_secret_key">Secret Key:</label>
    <input type="text" name="alia_vms_ovh_secret_key" id="alia_vms_ovh_secret_key" value="$this->secret_key" class="form-control" placeholder="Enter OVH S3 Secret Key">
    </div>

EOF;


    echo $group;
}

function alia_vms_ovh_endpoint_name_render() {
    $value = get_option('alia_vms_ovh_endpoint_name');
    $this->endpoint_name =esc_attr($value);
    $group= <<< EOF
    <div class="form-group">
    <label for="alia_vms_ovh_endpoint_name">Endpoint Name:</label>
    <input type="text" name="alia_vms_ovh_endpoint_name" id="alia_vms_ovh_endpoint_name" value="$this->endpoint_name" class="form-control" placeholder="Enter OVH S3 Endpoint Name">
    </div>

EOF;


    echo $group;
}


function alia_vms_ovh_settings_section_callback() {
    echo __('Enter your OVH S3 credentials.', 'alia-vms');
}
function alia_vms_openai_settings_section_callback() {
    echo __('Enter your openai credentials.', 'alia-vms');
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
function alia_vms_register_openai_settings_menu() {
    add_submenu_page(
        'alia_vms',
        __('Alia VMS Openai Settings', 'alia-vms'),
        __('Openai API', 'alia-vms'),
        'manage_options',
        'alia_vms_openai_settings',
        'alia_vms_openai_settings_page'
    );
}
}

?>