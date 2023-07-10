<?php
// 1pams-vms/includes/alia-vms-upcloud.php

require_once ALIA_VMS_PLUGIN_PATH . '/vendor/autoload.php';;
require_once ALIA_VMS_PLUGIN_PATH.'/includes/alia-vms-api.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class upcloud
{
    private $s3Client;
    
    public function __construct()
    {
        
        $this->s3Client = get_client();
        
    }

/**
 * Upload files to OVH S3.
 *
 * @param string $baseDirectory    Base directory for all files and directories
 * @param string $sourceDirectory  Source directory to upload
 * @param array  $excludeFiles     Array of filenames to exclude (optional)
 *
 * @return array|bool   Array of uploaded file information or false on error
 */
public function uploadFilesToS3($bucket,$baseDirectory, $sourceDirectory, $excludeFiles = [])
{
    try {
        $results = [];

        // Create the base directory on S3 if it doesn't exist
        $this->s3Client->createMultipartUpload([
            'Bucket' => $bucket,
            'Key' => $baseDirectory . '/',
        ]);

        // Iterate over files and directories in the specified directory and its sub-folders
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourceDirectory, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            // Exclude the specified files
            if (!empty($excludeFiles) && in_array($file->getFilename(), $excludeFiles)) {
                continue;
            }

            // Object key in OVH S3 bucket (use relative path as the object key)
            $relativePath = substr($file->getPathname(), strlen($sourceDirectory) + 1);
            $objectKey = $baseDirectory . '/' . str_replace('\\', '/', $relativePath);

            // Create the directory on S3 if it's a directory
            if ($file->isDir()) {
                $this->s3Client->createMultipartUpload([
                    'Bucket' => $bucket,
                    'Key' => $objectKey . '/',
                ]);

                continue;
            }

            // Check if the file is readable before attempting to upload
            if (!is_readable($file->getPathname())) {
                continue;
            }

            // Upload the file to OVH S3
            $this->s3Client->putObject([
                'Bucket' => $bucket,
                'Key' => $objectKey,
                'SourceFile' => $file->getPathname(),
            ]);

            $results[] = [
                'folder' => $file->getPath(),
                'filename' => $file->getBasename(),
                'extension' => $file->getExtension(),
                'bucket' => $bucket,
                'bucket_key' => $objectKey,
                'url' => $this->s3Client->getObjectUrl($bucket, $objectKey)
            ];
        }

        return $results;
    } catch (AwsException $e) {
        // Handle any errors that occurred during the upload
        error_log("Error: {$e->getMessage()}");
        return false;
    }
} 

   /*  public function uploadToS3($sourcePath, $bucket, $key)
    {
        try {
            $result = $this->s3Client->putObject([
                'Bucket' => $bucket,
                'Key' => $key,
                'SourceFile' => $sourcePath,
            ]);

            return $result->get('ObjectURL');
        } catch (AwsException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function getTotalMbLoaded($bucket)
    {
        try {
            $objects = $this->s3Client->listObjects([
                'Bucket' => $bucket,
            ]);

            $totalSize = 0;
            foreach ($objects['Contents'] as $object) {
                $totalSize += $object['Size'];
            }

            return $totalSize / (1024 * 1024);
        } catch (AwsException $e) {
            error_log($e->getMessage());
            return false;
        }
    } */
}
