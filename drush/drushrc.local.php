<?php

$env_type = getenv('CMS_ENVIRONMENT_TYPE');

// Set base url for lando environment.
if (isset($env_type) && $env_type === 'lando') {
  $options['uri'] = 'https://va-gov-cms.lndo.site';
}
