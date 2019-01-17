<?php

namespace Drupal\va_gov_migrate\Plugin\migrate\source;

use Drupal\migration_tools\Message;

/**
 * Collect administration data from front matter.
 *
 * @MigrateSource(
 *  id = "work_areas"
 * )
 */
class WorkAreas extends MetalsmithSource {

  /**
   * {@inheritdoc}
   */
  protected function validateRows() {
    $admin_rows = [
      [
        'acronym' => 'VBA',
        'name' => 'Veterans Benefits Administration',
        'link' => 'https://www.benefits.va.gov/benefits',
        'url' => 'https://www.va.gov/disability/',
      ],
      [
        'acronym' => 'NCA',
        'name' => 'National Cemetery Administration',
        'link' => 'https://www.cem.va.gov/',
        'url' => 'https://www.va.gov/burials-memorials/',
      ],
      [
        'acronym' => 'VHA',
        'name' => 'Veterans Health Administration',
        'link' => 'https://www.va.gov/health/',
        'url' => 'https://www.va.gov/health-care/',
      ],
      [
        'acronym' => 'DVA',
        'name' => 'Department of Veterans Affairs',
        'link' => '',
        'url' => 'https://www.va.gov/records/',
      ],
    ];

    foreach ($admin_rows as &$admin_row) {
      $key = array_search($admin_row['url'], array_column($this->rows, 'url'));

      if ($key === FALSE) {
        Message::make("No markdown file for @url", ['@url' => $admin_row['url']], Message::ERROR);
        continue;
      }

      $row = $this->rows[$key];

      if (array_key_exists('social', $row) && count($row['social']) > 1) {
        $social_media_links = [];
        $social_media_keys =
          [
            'twitter',
            'facebook',
            'youtube',
            'youtube_channel',
            'linkedin',
            'instagram',
          ];
        foreach ($social_media_keys as $key) {
          $social_media_links[$key] = ['value' => ''];
        }

        foreach ($row['social'][1]['subsections'] as $subsection) {
          foreach ($subsection['links'] as $link) {
            switch ($link['icon']) {
              case 'fa-twitter':
                $social_media = 'twitter';
                break;

              case 'fa-facebook':
                $social_media = 'facebook';
                break;

              case 'fa-youtube':
                if (strpos($link['url'], '/channel/') === FALSE) {
                  $social_media = 'youtube';
                }
                else {
                  $social_media = 'youtube_channel';
                  $link['url'] = str_replace('channel/', '', $link['url']);
                }
                break;

              case 'fa-linkedin':
                $social_media = 'linkedin';
                break;

              case 'fa-instagram':
                $social_media = 'instagram';
                break;

              case 'fa-envelope':
              case 'fa-envelope-o':
                $admin_row['email_updates_link_text'] = $link['label'];
                $admin_row['email_updates_url'] = $link['url'];
                break;

              default:
                Message::make('Unexpected social media type @icon on @url',
                  [
                    '@icon' => $link['icon'],
                    '@url' => $row['url'],
                  ], Message::ERROR);
                break;
            }

            if (isset($social_media)) {
              $path_part = parse_url($link['url'], PHP_URL_PATH);
              // Remove leading slash.
              $social_media_links[$social_media]['value'] = substr($path_part, 1);
            }

          }

          $admin_row['social_media_links'] = $social_media_links;
        }
      }
    }
    $this->rows = $admin_rows;
  }

}
