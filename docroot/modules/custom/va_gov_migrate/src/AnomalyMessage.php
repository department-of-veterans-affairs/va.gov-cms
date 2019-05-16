<?php

namespace Drupal\va_gov_migrate;

use Drupal\migrate\Row;
use Drupal\migration_tools\Message;

/**
 * Class AnomalyMessage.
 *
 * @package Drupal\va_gov_migrate
 */
class AnomalyMessage {

  const FONT_AWESOME_NUMBER_CALLOUTS = "Font awesome number callouts (Decision Reviews)";
  const FONT_AWESOME_SNIPPETS = "Font awesome snippets (Decision Reviews)";
  const TWO_COLUMN_CONTENT = "Two-column content";
  const TWO_BUTTONS_SIDE_BY_SIDE = "Two buttons side-by-side";
  const SUBWAY_MAP_WITHOUT_NUMBERS = "Subway map without numbers";
  const EXTRA_BRS_AND_EMPTY_PS_BETWEEN_COMPONENTS = "Extra <br>'s and empty <p>'s between components";
  const FEATURED_NOT_AT_TOP_OF_CONTENT = "Featured - not at top of content";
  const INTRO_TEXT_DOES_NOT_SUPPORT_LINKS = "Intro text does not support links.";
  const INTRO_TEXT_DOES_NOT_SUPPORT_BULLETS = "Intro text does not support bullets";
  const LINK_TEASERS_WITH_AUDIENCE_LABELS = "Link teasers with audience labels";
  const Q_A_REACT_WIDGET_IN_ANSWER = "Q&A - React Widget in answer";
  const ALERTS_TOP_OF_PAGE = "Alerts - top-of-page";
  const ALERTS_IN_BODY = "Alerts - in-body";
  const TABLES = "Tables";
  const FILE_LINKS = "File links (eg PDFs)";
  const INTRO_TEXT_DOES_NOT_SUPPORT_PDF_LINKS = "Intro text does not support pdf links";
  const Q_A_NESTED = "Q&A - nested";
  const ALERTS_BACKGROUND_COLOR_ONLY = "Alerts - background color only";
  const STARRED_DIVIDER = "Starred divider";
  const FEATURED_MORE_THAN_ONE = "Featured - more than one div.feature";
  const Q_A_EXCLUDED_CONTENT = "Q&A - excluded content";
  const HUB_RELATED_BANNER_ALERTS = "Hub-related banner alerts";
  const JUMPLINKS = "Jumplinks";
  const MAJOR_LINKS = "Major links (related links with white background)";
  const MULTI_SELECTABLE = "Accordions are multi-selectable";

  /**
   * Makes a warning message for anomalies.
   *
   * @param string $anomaly_type
   *   The name of the anomaly (corresponds to 'Name' column in airtable).
   * @param string $page_title
   *   The title of the page the anomaly is on.
   * @param string $page_url
   *   The url of the page.
   * @param string $additional_info
   *   Useful information about the anomaly, if any.
   *
   * @throws \Drupal\migrate\MigrateException
   */
  public static function make($anomaly_type, $page_title, $page_url, $additional_info = '') {
    // 'va_gov_migrate.anomaly' is reset to empty in PostRowSave.
    $anomaly = \Drupal::state()->get('va_gov_migrate.anomaly');
    if (empty($anomaly)) {
      $anomaly = [];
    }

    if (empty($anomaly[$anomaly_type])) {
      $anomaly[$anomaly_type] = TRUE;
      \Drupal::state()->set('va_gov_migrate.anomaly', $anomaly);

      Message::make('@anomaly_type anomaly on @title @url -- @additional_info',
        [
          '@anomaly_type' => $anomaly_type,
          '@title' => $page_title,
          '@url' => $page_url,
          '@additional_info' => $additional_info,
        ],
        Message::WARNING
      );
    }
  }

  /**
   * Message::make + limiting messages to 1 per anomaly per page.
   *
   * @param string $message
   *   The message to store in the log.
   * @param array $params
   *   Array of variables to replace in the message on display.
   * @param int $severity
   *   The severity of the message.
   *
   * @throws \Drupal\migrate\MigrateException
   */
  public static function makeCustom($message, array $params, $severity = Message::WARNING) {
    if (empty($params['@anomaly_type'])) {
      Message::make($message, $params, $severity);
    }
    else {
      $anomaly_type = $params['@anomaly_type'];
      // 'va_gov_migrate.anomaly' is reset to empty in PostRowSave.
      $anomaly = \Drupal::state()->get('va_gov_migrate.anomaly');
      if (empty($anomaly)) {
        $anomaly = [];
      }

      if (empty($anomaly[$anomaly_type])) {
        $anomaly[$anomaly_type] = TRUE;
        \Drupal::state()->set('va_gov_migrate.anomaly', $anomaly);
        Message::make($message, $params, $severity);
      }
    }
  }

  /**
   * Convenience function for make().
   *
   * @param string $anomaly_type
   *   The name of the anomaly (corresponds to 'Name' column in airtable).
   * @param \Drupal\migrate\Row $row
   *   The migration row, which contains the page title and url.
   * @param string $additional_info
   *   Useful information about the anomaly, if any.
   *
   * @throws \Drupal\migrate\MigrateException
   */
  public static function makeFromRow($anomaly_type, Row $row, $additional_info = '') {
    self::make($anomaly_type, $row->getSourceProperty('title'), $row->getSourceProperty('url'), $additional_info);
  }

}
