<?php
// alia-vms-plugin/includes/alia-vms-api.php
require_once '/var/www/clients/client0/web2/web/wp-content/plugins/1pams-alia-video-manager/vendor/autoload.php'; // Load Composer autoloader
use Vimeo\Vimeo;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;
