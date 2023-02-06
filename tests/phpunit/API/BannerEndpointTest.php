<?php

namespace tests\phpunit\API;

use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * A test to confirm that the banner endpoint is working properly.
 *
 * @group functional
 * @group all
 */
class BannerEndpointTest extends VaGovExistingSiteBase {

  /**
   * Provides data to testBanner().
   */
  public function provideBannerTestData() {
    return [
      'first level path' => [
        '/banner-endpoint-test-path',
        TRUE,
      ],
      'second level path' => [
        '/banner-endpoint-test-path/second-level-path',
        TRUE,
      ],
      'third level path' => [
        '/banner-endpoint-test-path/second-level-path/one-more-segment',
        TRUE,
      ],
      'banner not present' => [
        '/banner-should-not-be-here',
        FALSE,
      ],
    ];
  }

  /**
   * Test the banner endpoint functionality.
   *
   * @dataProvider provideBannerTestData
   */
  public function testBanner($path, $shouldBeIncluded) {
    $author = $this->createUser();

    $banner_data = [
      'field_alert_type' => 'information',
      'title' => 'Test Banner',
      'uid' => $author->id(),
      'field_target_paths' => [],
      'type' => 'banner',
    ];
    if ($shouldBeIncluded) {
      $banner_data['field_target_paths'][] = ['value' => $path];
    }

    $banner = $this->createNode($banner_data);
    $banner->set('moderation_state', 'published')->setPublished(TRUE)->save();

    // This assertion isn't strictly necessary, but we need to have at least one
    // per test. If the request below fails, we wouldn't have one.
    $this->assertTrue($banner->isPublished(), 'banner ' . $banner->id() . ' is published');

    $url = $this->baseUrl;

    // Make sure the banner is found in all of the requests that it _should_ be
    // included in (and not in the places where it should not be).
    $response = \Drupal::httpClient()->get($url . '/jsonapi/banner-alerts?item-path=' . $path);
    $this->assertEquals('200', $response->getStatusCode(), 'request returned status code ' . $response->getStatusCode());

    $json = json_decode($response->getBody());

    $found = FALSE;
    foreach ($json->data as $item) {
      if ((string) $item->attributes->drupal_internal__nid === $banner->id()) {
        $found = TRUE;
        break;
      }
    }

    if ($shouldBeIncluded) {
      $this->assertTrue($found, 'banner id ' . $banner->id() . ' was found in JSON API response for ' . $path);
    }
    else {
      $this->assertFalse($found, 'banner id ' . $banner->id() . ' was not found in JSON API response for ' . $path);
    }
  }

}
