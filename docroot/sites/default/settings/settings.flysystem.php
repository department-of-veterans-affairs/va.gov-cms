<?php

/**
 * @file
 * Flysystem configuration.
 */

$bucket = getenv('CMS_S3_FILES_BUCKET') ?: '';
$user_key = getenv('CMS_S3_FILES_KEY') ?: '';
$user_secret = getenv('CMS_S3_FILES_SECRET') ?: '';

// We do not provide a fallback; if the connection cannot be made, the stream
// wrapper should fail.
$schemes = [];

// If we have the information we need, set up an s3 scheme.
if (!empty($bucket) && !empty($user_key) && !empty($user_secret)) {
  $schemes['dsva-cms-s3-files'] = [
    'driver' => 's3',
    'config' => [
      'key'    => $user_key,
      'secret' => $user_secret,
      'region' => 'us-gov-west-1',
      'bucket' => $bucket,
      'options' => [
        'ACL' => 'public-read',
      ],
      'public' => TRUE,
    ],

    'cache' => TRUE,
  ];
}

$settings['flysystem'] = $schemes;
