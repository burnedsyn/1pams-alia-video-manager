<?php
// alia-vms-plugin/includes/alia-vms-api.php
require_once '/var/www/clients/client0/web2/web/wp-content/plugins/1pams-alia-video-manager/vendor/autoload.php'; // Load Composer autoloader
use Vimeo\Vimeo;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;
// OVH API credentials

//$consumerKey = 'user-YDWtFgwJrUXC'; 
// Create a new S3Client instance


function get_client()
{

    $accessKeyId = '72382137a8064638a1ebd8ae19f9f3d3';
    $secretAccessKey = '86d6ddece3924d2b9f3b3adda97112a1';
    $region = 'gra'; // e.g., ''

    $client = new S3Client([
        'version' => 'latest',
        'region'  => $region,
        'endpoint' => 'https://s3.gra.io.cloud.ovh.net/', // OVH S3 endpoint
        'credentials' => [
            'key'    => $accessKeyId,
            'secret' => $secretAccessKey,
        ],
    ]);
    return $client;
}
