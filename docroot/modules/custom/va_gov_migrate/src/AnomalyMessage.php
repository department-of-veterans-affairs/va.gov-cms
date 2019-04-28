<?php

namespace Drupal\va_gov_migrate;

use Drupal\migration_tools\Message;

/**
 * Class AnomalyMessage.
 *
 * @package Drupal\va_gov_migrate
 */
class AnomalyMessage {

  const FONT_AWESOME_NUMBER_CALLOUTS = "Font awesome number callouts";
  const FONT_AWESOME_SNIPPETS = "Font awesome snippets";
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

  /**
   * Makes a warning message for anomalies.
   *
   * @param string $anomalytype
   *   The name of the anomaly (corresponds to 'Name' column in airtable).
   * @param string $page_title
   *   The title of the page the anomaly is on.
   * @param string $page_url
   *   The url of the page.
   *
   * @throws \Drupal\migrate\MigrateException
   */
  public static function make($anomalytype, $page_title, $page_url) {
    $anomaly = \Drupal::state()->get('va_gov_migrate.anomaly');
    if (empty($anomaly)) {
      $anomaly = [];
    }

    if (empty($anomaly[$anomalytype])) {
      $anomaly[$anomalytype] = TRUE;
      \Drupal::state()->set('va_gov_migrate.anomaly', $anomaly);

      Message::make('@anomaly_type anomaly on @title @url',
        [
          '@anomaly_type' => $anomalytype,
          '@title' => $page_title,
          '@url' => $page_url,
        ],
        Message::WARNING
      );
    }
  }

}
