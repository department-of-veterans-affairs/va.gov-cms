<?php

/**
 * @file
 * Print a list of URLs on www.va.gov that aren't in the CMS.
 */

$sitemap_url = 'https://www.va.gov/sitemap.xml';
$sitemap_xml = simplexml_load_file($sitemap_url);
$sitemap_array = json_decode(json_encode($sitemap_xml), TRUE);
$aliasRows = \Drupal::database()->query('SELECT alias FROM {path_alias};')->fetchAll();
$aliases = array_map(function ($aliasRow) {
  return trim($aliasRow->alias, " \n\r\t\v\x00/");
}, $aliasRows);
$aliases = array_filter($aliases);
$urlObjects = $sitemap_array['url'];
$urlObjects = array_filter($urlObjects, function ($urlObject) {
  return strpos($urlObject['loc'], 'https://www.va.gov') !== FALSE;
});
$paths = array_map(function ($urlObject) {
  return trim(str_replace('https://www.va.gov', '', $urlObject['loc']), " \n\r\t\v\x00/");
}, $urlObjects);
$paths = array_filter($paths);
$paths = array_filter($paths, function ($path) {
  return TRUE
    && strpos($path, 'events/past-events') === FALSE
    && strpos($path, 'events/page') === FALSE
    && strpos($path, 'stories/page') === FALSE
    && strpos($path, 'news-releases/page') === FALSE
    && strpos($path, '-health-care/status') === FALSE;
});
foreach ($paths as $path) {
  if (!in_array($path, $aliases)) {
    echo "https://www.va.gov/${path}\n";
  }
}
