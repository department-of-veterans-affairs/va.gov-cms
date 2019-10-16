<?php

namespace Drupal\va_gov_consumers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception as GuzzleException;

/**
 * For consuming Facility api data.
 */
class FacilityConsumer {

  /**
   * Pre render for main content block.
   *
   * @param string $facility_id
   *   The facility id.
   */
  public function contentRender($facility_id) {

    $data = FALSE;
    $client = new Client();
    // Make sure we get a good response before heavy lifting.
    try {
      // @TODO  Restore the logic here when the env-API call is finalized.
    }
    // Record any trouble to watchdog.
    catch (GuzzleException $e) {
      watchdog_exception('Facility_content', $e->getMessage());
    }

    return $data;
  }

}
