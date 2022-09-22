<?php

// phpcs:ignoreFile

/**
 * @file
 * This is the VA.gov-CMS Settings Simulator™.
 *
 * Its purpose is to preview how settings files are read and processed for each
 * environment and validate the resulting configuration against an existing
 * baseline.
 * 
 * Run it with `php settings_simulator.php [ENV]`, where:
 * - ENV is an environment name normally provided by the CMS_ENVIRONMENT_TYPE 
 *   environment variable (e.g. "local", "tugboat", "prod").
 * 
 * WARNING: Note that this script may have unintended consequences when run
 * locally!
 * 
 * It will create files in a variety of locations to see which are and are not
 * included, which are and are not git-ignored, etc.  It will normally delete
 * them, but it's possible that it will fail to do so.  If these files are not
 * git-ignored, they will need to be manually deleted.
 */

// Some initial configuration.
$out = trim(shell_exec('git rev-parse --show-toplevel'));
$sites_path = "${out}/docroot/sites";

// Retrieve arguments.
$env_type = $argv[1] ?? getenv('CMS_ENVIRONMENT_TYPE');
putenv("CMS_ENVIRONMENT_TYPE={$env_type}");

// Populate some variables to simulate a satisfying Drupal experience.
// This will vary from environment to environment.
$app_root = '/var/www/html/docroot';
// This should remain constant across environments.
$site_path = 'sites/default';

// Let's create some settings files in a variety of locations and see which
// are included, which are git-ignored, etc.
$local_settings_files = [
  'sites/local.settings.php',
  'sites/settings.local.php',
  'sites/default/local.settings.php',
  'sites/default/settings.local.php',
  'sites/default/settings/local.settings.php',
  'sites/default/settings/settings.local.php',
  'sites/default/settings/settings.ddev.php',
  'sites/default/settings/settings.personal.php',
];
$local_settings_files_to_remove = [];
$local_settings_data = [];
foreach ($local_settings_files as $local_settings_file) {
  // All roads lead to Rome; all settings_files begin with $app_root.
  $full_path = $app_root . '/' . $local_settings_file;
  $exists = file_exists($full_path);
  if (!$exists) {
    $local_settings_files_to_remove[] = $full_path;
    file_put_contents($full_path, '<?php ');
  }
  // If the following does not return NULL, this file is git-ignored.
  $is_gitignored = shell_exec("git check-ignore {$full_path}") != NULL;

  $local_settings_data[] = [
    'path' => $local_settings_file,
    'exists' => $exists,
    'is_gitignored' => $is_gitignored,
  ];
}

// Let's do the same with some container YAML files.
$local_container_yamls = [
  'sites/local.services.yml',
  'sites/services.local.yml',
  'sites/default/local.services.yml',
  'sites/default/services.local.yml',
  'sites/default/services/local.services.yml',
  'sites/default/services/services.local.yml',
  'sites/default/services/services.ddev.yml',
  'sites/default/services/services.personal.yml',
];
$local_container_yamls_to_remove = [];
$local_container_yaml_data = [];
foreach ($local_container_yamls as $local_container_yaml) {
  $full_path = $app_root . '/' . $local_container_yaml;
  $exists = file_exists($full_path);
  if (!$exists) {
    $local_container_yamls_to_remove[] = $full_path;
    file_put_contents($full_path, '# Empty file.');
  }
  // If the following does not return NULL, this file is git-ignored.
  $is_gitignored = shell_exec("git check-ignore {$full_path}") != NULL;

  $local_container_yaml_data[] = [
    'path' => $local_container_yaml,
    'exists' => $exists,
    'is_gitignored' => $is_gitignored,
  ];
}

// First, include the existing settings.php file.
require_once $sites_path . '/default/settings.php';

// Remove files we created above.
foreach ($local_settings_files_to_remove as $full_path) {
  unlink($full_path);
}
foreach ($local_container_yamls_to_remove as $full_path) {
  unlink($full_path);
}

// Now, which settings files did we include?
$included_settings_files = get_included_files();
// Remove first entry (which will be this very file).
$included_settings_files = array_slice($included_settings_files, 1);

echo "\n\nSettings files that were actually included:\n";

print_r($included_settings_files);

$format_string = "%60s %15s %15s %15s\n";

echo "\n\nPossible local override settings files:\n";

printf($format_string, "File Path", "Exists", "Is Git-Ignored", "Is Included");

function format_bool($bool) {
  return !!$bool ? 'TRUE' : 'FALSE';
}

foreach ($local_settings_data as $data) {
  $is_included = array_filter($included_settings_files, function ($included_path) use ($data) {
    return strpos($included_path, $data['path']) !== FALSE;
  });
  printf($format_string, $data['path'], format_bool($data['exists']), format_bool($data['is_gitignored']), format_bool(count($is_included)));
}

// Which container YAML files will we use?
$container_yamls = $settings['container_yamls'];

echo "\n\nContainer YAML files that were actually included:\n";

print_r($container_yamls);

echo "\n\nPossible local override container YAML files:\n";

printf($format_string, "File Path", "Exists", "Is Git-Ignored", "Is Included");

foreach ($local_container_yaml_data as $data) {
  $is_included = array_filter($container_yamls, function ($included_path) use ($data) {
    return strpos($included_path, $data['path']) !== FALSE;
  });
  printf($format_string, $data['path'], format_bool($data['exists']), format_bool($data['is_gitignored']), format_bool(count($is_included)));
}
