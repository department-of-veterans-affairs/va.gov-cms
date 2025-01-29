<?php

namespace Drupal\va_gov_content_types\Interfaces;

/**
 * An interface for National Outreach Calendar things for Event nodes.
 */
interface EventOutreachInterface {

  /**
   * The 'publish to the national outreach calendar' field name.
   */
  const PUBLISH_TO_OUTREACH_CAL_FIELD = 'field_publish_to_outreach_cal';

  /**
   * The 'field_listing' field name.
   */
  const LISTING_FIELD = 'field_listing';

  /**
   * The 'field_additional_listings' field name.
   */
  const ADDITIONAL_LISTING_FIELD = 'field_additional_listings';

  /**
   * The National Outreach Calendar node id.
   */
  const OUTREACH_CAL_NID = 736;

  /**
   * The 'Outreach Hub' Section term id.
   */
  const OUTREACH_HUB_TID = 7;

}
