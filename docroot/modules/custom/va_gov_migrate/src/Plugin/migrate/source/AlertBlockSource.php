<?php

namespace Drupal\va_gov_migrate\Plugin\migrate\source;

use Drupal\migration_tools\Message;
use QueryPath\DOMQuery;
use Drupal\migration_tools\StringTools;
use Drupal\migration_tools\Obtainer\ObtainHtml;
use Michelf\MarkdownExtra;
use Drupal\migrate\MigrateException;

/**
 * Gets blocks from pages referenced by metalsmith files.
 *
 * This source requires a list of urls that are either metalsmith files
 * or a github directory listing of metalsmith files.
 *
 * @MigrateSource(
 *  id = "alert_block"
 * )
 */
class AlertBlockSource extends MetalsmithSource {

  /**
   * Remove rows with duplicate alert titles and make sure they're identical.
   */
  protected function validateRows() {
    $unique_rows = [];

    foreach ($this->rows as $row) {
      $alert_title = $row['alert_title'];

      // If we've already got that title, make sure the blocks are identical.
      if (in_array($alert_title, array_column($unique_rows, 'alert_title'))) {
        $key = array_search($alert_title, array_column($unique_rows, 'alert_title'));
        $old_alert = preg_replace('/>\s+</m', '><', $unique_rows[$key]['alert_body']);
        $new_alert = preg_replace('/>\s+</m', '><', $row['alert_body']);
        if (Stringtools::superTrim($old_alert) != Stringtools::superTrim($new_alert)) {
          Message::make('Alert boxes with the same title, but different bodies on @url1 and @url2',
            [
              '@url1' => $unique_rows[$key]['url'],
              '@url2' => $row['url'],
            ],
            Message::ERROR);
        }
        elseif ($row['alert_type'] != $unique_rows[$key]['alert_type']) {
          Message::make('Alert boxes with the same title and body, but different type: @type1 on @url1 and @type2 on @url2',
            [
              '@url1' => $unique_rows[$key]['url'],
              '@url2' => $row['url'],
              '@type1' => $unique_rows[$key]['alert_type'],
              '@type2' => $row['alert_type'],
            ],
            Message::ERROR);
        }
      }
      // Otherwise, just add this one to the list.
      else {
        $unique_rows[] = $row;
      }
    }

    $this->rows = $unique_rows;
  }

  /**
   * {@inheritdoc}
   */
  protected function addRow($url, $path) {
    $page_content = '';
    if (!($row = $this->readMetalsmithFile($url, $page_content)) || empty($page_content)) {
      return;
    }

    $row = [];

    // Turn page content into DOM query.
    try {
      $query_path = htmlqp(mb_convert_encoding($page_content, "HTML-ENTITIES", "UTF-8"));
    }
    catch (\Exception $e) {
      throw new MigrateException('Failed to instantiate QueryPath: ' . $e->getMessage());
    }
    // Sometimes queryPath fails.  So one last check.
    if (empty($query_path) || !is_object($query_path)) {
      throw new MigrateException("Failed to initialize QueryPath.");
    }

    // Remove wrappers added by htmlqp().
    while (in_array($query_path->tag(), ['html', 'body'])) {
      $query_path = $query_path->children();
    }

    $alerts = $query_path->find('.usa-alert');
    /** @var \QueryPath\DOMQuery $alert */
    foreach ($alerts as $alert) {
      $row['alert_type'] = $this->getAlertType($alert);

      // Sometimes expander trigger is nested inside heading.
      $title_path = $alert->find('.usa-alert-heading');
      $trigger_path = $title_path->find('#crisis-expander-link')->remove();
      $row['alert_title'] = strip_tags(ObtainHtml::trimAtBr($title_path->html()));
      // The heading is nested within the body, so remove it.
      $alert->find('.usa-alert-heading')->remove();

      // If we found a trigger path add it to the body.
      if ($trigger_path->count()) {
        $trigger_path->insertBefore($alert->find('.expander-content'));
      }

      $body = MarkdownExtra::defaultTransform($alert->find('.usa-alert-body')->innerHTML());
      // Markdown extra encodes html and wraps it in code and pre tags.
      $body = str_replace(['<code>', '</code>', '<pre>', '</pre>'], '', $body);
      $row['alert_body'] = html_entity_decode($body);

      $this->setPagePath($path, $row);

      $this->rows[] = $row;

    }
  }

  /**
   * Get the drupal alert type from the html class.
   *
   * @param \QueryPath\DOMQuery $alert
   *   The query path for the alert.
   *
   * @return mixed|string
   *   The drupal list key for the alert type.
   */
  public function getAlertType(DOMQuery $alert) {
    // Classes mapped to CMS list keys.
    $types = [
      'usa-alert-success' => 'success',
      'usa-alert-warning' => 'warning',
      'usa-alert-error' => 'error',
      'usa-alert-info' => 'information',
      'usa-alert-continue' => 'continue',
    ];

    foreach ($types as $class => $type) {
      if ($alert->hasClass($class)) {
        return $type;
      }
    }

    \Drupal::logger('va_gov_migrate')->error('No alert type found for alert, @heading',
      [
        '@heading' => $alert->find('.usa-alert-heading')->text(),
      ]
    );

    // Let's make success the default.
    return 'success';
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['alert_title']['type'] = 'string';
    return $ids;
  }

}
