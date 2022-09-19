<?php

/**
 * @file
 * Flysystem configuration.
 */

$bucket = getenv('CMS_S3_FILES_BUCKET');
$user_key = getenv('CMS_S3_FILES_KEY');
$user_secret = getenv('CMS_S3_FILES_SECRET');

$schemes = [
  'dsva-cms-s3-files' => [
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
  ],
];
$settings['flysystem'] = $schemes;
