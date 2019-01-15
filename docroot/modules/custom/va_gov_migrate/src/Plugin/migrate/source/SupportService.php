<?php

namespace Drupal\va_gov_migrate\Plugin\migrate\source;

use Drupal\migration_tools\Message;

/**
 * Gets support service info from metalsmith files.
 *
 * This source requires a list of urls that are either metalsmith files
 * or a github directory listing of metalsmith files.
 *
 * @MigrateSource(
 *  id = "support_service"
 * )
 */
class SupportService extends MetalsmithSource {

  /**
   * An array of associative arrays to populate Support Service nodes.
   *
   * @var array
   */
  protected $serviceRows;

  /**
   * {@inheritdoc}
   */
  protected function validateRows() {
    // Get all the support service rows.
    foreach ($this->rows as $row) {
      if (!isset($row['social'])) {
        continue;
      }

      foreach ($row['social'][0]['subsections'] as $subsection) {
        foreach ($subsection['links'] as $service) {
          $this->serviceRows[] = [
            'service_name' => isset($service['label']) ? $service['label'] : $service['title'],
            'service_url' => $service['url'],
            'service_number' => isset($service['number']) ? $service['number'] : '',
            'url' => $row['url'],
          ];
        }
      }
    }

    // Remove duplicates.
    $unique_rows = [];
    foreach ($this->serviceRows as $service_row) {
      if (($key = array_search($service_row['service_name'], array_column($unique_rows, 'service_name'))) === FALSE) {
        $unique_rows[] = $service_row;
      }
      else {
        if ($service_row['service_url'] != $unique_rows[$key]['service_url']) {
          Message::make('Support Service, "@name", links to @link1 on @url1 and @link2 on @url2',
            [
              '@name' => $service_row['service_name'],
              '@link1' => $service_row['service_url'],
              '@url1' => $service_row['url'],
              '@link2' => $unique_rows[$key]['service_url'],
              '@url2' => $unique_rows[$key]['url'],
            ], Message::ERROR);
        }
      }
    }
    $this->rows = $unique_rows;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['service_name']['type'] = 'string';
    return $ids;
  }

}
