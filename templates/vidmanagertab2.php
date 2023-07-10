<?php
require_once ALIA_VMS_PLUGIN_PATH.'/vendor/autoload.php'; // Load Composer autoloader
require_once ALIA_VMS_PLUGIN_PATH.'/includes/alia-vms-api.php';
require_once ALIA_VMS_PLUGIN_PATH.'/includes/alia-vms-video-product.php';

$active_tab = isset($_GET['tab1']) ? $_GET['tab1'] : 'tab1';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;
?>
<div class="wrap container-fluid">
    <section class="content-header">
        <h1>Video tools</h1>
    </section>
    <section class="nav-tab-wrapper">
            <a href="?page=alia-vms-video&tab=tab2&tab1=tab1" class="nav-tab <?php echo $active_tab === 'tab1' ? 'nav-tab-active' : ''; ?>">Vimeo import</a>
            <a href="?page=alia-vms-video&tab=tab2&tab1=tab2" class="nav-tab <?php echo $active_tab === 'tab2' ? 'nav-tab-active' : ''; ?>">Traitement ai</a>
    </section>
<?php
switch($active_tab){
    case 'tab1':
        //global $wp;
        $url_parts = parse_url( home_url() );
        $current_url =  $url_parts['scheme'] . "://" . $url_parts['host'] . add_query_arg( NULL, NULL );
        $queryString = parse_url($current_url, PHP_URL_QUERY);

        // Parse the query string to get the individual arguments
        parse_str($queryString, $arguments);

        // Access the individual arguments
        $page = isset($arguments['page']) ? $arguments['page'] : '';
        $tab = isset($arguments['tab']) ? $arguments['tab'] : '';
        $tab1 = isset($arguments['tab1']) ? $arguments['tab1'] : '';
        $operation=isset($arguments['operation']) ? $arguments['operation'] : '';
        switch($operation){
            case '':
                
                echo("<div class='wrap container-fluid vig'>");

                $videoDetails=isset($_SESSION['grouped']) ? $_SESSION['grouped'] : '' ;
                if(is_array($videoDetails)){
                    $videoDetails=array_reverse($videoDetails);
                    $i=0;
                    $customNonce= wp_create_nonce("vimeoImportCustomNonce");
                    foreach($videoDetails as $current_video){
                        create_vignette_import($current_video,$i,$customNonce);
                        $i++;
                    }
                    /* echo("</div><pre>");
                    print_r($videoDetails);
                    echo("</pre>");  */
                   
                }else {
                    echo("<h1>ICI CLEAN</h1>");
                }
                break;
        }

        break;
    case 'tab2':

        break;
}
function create_vignette_import($result,$index,$customNonce){
    if(is_array($result)){
        $status='warning';
?>             
                <div id="<?= $index ?>" class="card-item <?= $status ?>">
                <input type="hidden" value="<?= $customNonce ?>">
                    <p class="card-header"> <?= $result['name'] ?></p>
                    <!-- <span class="time">Start : <?= isset($result['start']) ? $result['start'] : '' ?> End : <?= isset($result['end']) ? $result['end'] : '' ?>
                    </span> -->
                    <div class=" pamsitem">
                        <p>uri : <?= $result['uri'] ?></p>
                        <p>Video link : <?= $result['link'] ?></p>
                        <p>Description:<?= nl2br($result['description']) ?> </p>
                    </div>
                    <div class="card-footer">
                    <button class="pams-vimeo-import btn btn-block btn-dark" data-index="<?= $index ?>"><span class="pams-spinner spinner-border spinner-border-sm"></span>Import</button>
                    
                    </div>
                </div>
    
<?php
    }
}
?>
</div>