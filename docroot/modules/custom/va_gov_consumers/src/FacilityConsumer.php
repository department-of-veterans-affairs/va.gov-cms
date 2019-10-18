<?php

namespace Drupal\va_gov_consumers;

use Drupal\Component\Serialization\Json;
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
    $data = '';

    if (!empty(getenv('CMS_FACILITY_API_KEY'))) {
      $client = new Client();
      // Make sure we get a good response before heavy lifting.
      try {
        $data_source = getenv('CMS_FACILITY_API_URL') . '/services/va_facilities/v0/facilities/' . $facility_id;
        $response = $client->get($data_source, [
          'headers' => [
            'apikey' => getenv('CMS_FACILITY_API_KEY'),
          ],
        ]);

        if ($response->getStatusCode() === 200) {
          $data = Json::decode($response->getBody());
        }

      }
      // Record any trouble to watchdog.
      catch (GuzzleException $e) {
        watchdog_exception('Facility_content', $e->getMessage());
      }
    }
    return $data;

  }

}
