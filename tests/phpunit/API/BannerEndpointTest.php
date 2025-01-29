<?php

namespace tests\phpunit\API;

use Drupal\paragraphs\Entity\Paragraph;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * A test to confirm that the banner endpoint is working properly.
 *
 * @group functional
 * @group all
 */
class BannerEndpointTest extends VaGovExistingSiteBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() : void {
    parent::setUp();
    $this->drupalLogin($this->createUser(['access content']));
  }

  /**
   * Test that the banner endpoint handles full width alert banners.
   */
  public function testFullWidthAlertBanner() {
    // Look for a 'full_width_banner_alert' node.
    $node_storage = \Drupal::entityTypeManager()->getStorage('node');
    $banner_nids = $node_storage->getQuery()
      ->condition('type', 'full_width_banner_alert')
      // ->condition('status', TRUE)
      ->exists('field_banner_alert_vamcs')
      ->range(0, 1)
      ->accessCheck(FALSE)
      ->execute();

    // Load and publish the node.
    $banner_nid = reset($banner_nids);
    $banner = $node_storage->load($banner_nid);

    // Get 'field_banner_alert_vamcs' data.
    $banner_alert_vamcs = $banner->get('field_banner_alert_vamcs')->getValue();

    $banner->set('status', TRUE);
    $banner->save();

    // Look for a published node of type "vamc_operating_status_and_alerts".
    $system_nids = $node_storage->getQuery()
      ->condition('type', 'vamc_operating_status_and_alerts')
      ->condition('status', TRUE)
      ->condition('nid', $banner_alert_vamcs[0]['target_id'])
      ->range(0, 1)
      ->accessCheck(FALSE)
      ->execute();

    // Get first array value.
    $system_nid = reset($system_nids);

    // Load the entity.
    $system_node = $node_storage->load($system_nid);
    // Get entity id from "field_office" field.
    $office_nid = $system_node->get('field_office')->target_id;
    $path_alias = \Drupal::database()->select('path_alias', 'pa')
      ->fields('pa', ['alias'])
      ->condition('path', '/node/' . $office_nid)
      ->execute()
      ->fetchField();

    // Create 'situation_update' paragraph.
    $situation_update = Paragraph::create([
      'type' => 'situation_update',
      'status' => 1,
      'field_datetime_range_timezone' => [
        [
          'value' => '2024-01-23T12:22:00+00:00',
          'end_value' => '2024-01-23T13:22:00+00:00',
        ],
      ],
      'field_send_email_to_subscribers' => [
        [
          'value' => FALSE,
        ],
      ],
      'field_wysiwyg' => [
        [
          'value' => '<p>!!!: Situation updates are included in the response.</p>',
          'format' => 'rich_text',
        ],
      ],
    ]);

    // Add 'situation_update' paragraph to 'field_situation_updates' field.
    $banner->get('field_situation_updates')->appendItem($situation_update);
    $banner->save();

    // Visit the banner endpoint using the path alias.
    $this->visit('/api/v1/banner-alerts?path=' . $path_alias);
    $this->assertEquals(200, $this->getSession()->getStatusCode());

    $json = json_decode($this->getSession()->getPage()->getContent(), TRUE);

    // Confirm that the banner is included in the response.
    $this->assertIsArray($json['data']);

    $filtered_nodes = array_filter($json['data'], function ($item) use ($banner) {
      return isset($item['nid']) && $item['nid'] == $banner->id();
    });
    $this->assertNotEmpty($filtered_nodes, 'Node with nid ' . $banner->id() . ' found in response.');

    // Confirm that the string "65130" is in the response.
    $this->assertStringContainsString(
      '!!!: Situation updates are included in the response.',
      $this->getSession()->getPage()->getContent()
    );

    // Delete the paragraph.
    $situation_update->delete();
  }

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
    $banner_data = [
      'field_alert_type' => 'information',
      'title' => 'Test Banner',
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

    // Make sure the banner is found in all the requests that it _should_ be
    // included in (and not in the places where it should not be).
    $response = \Drupal::httpClient()->get($url . '/api/v1/banner-alerts?path=' . $path);
    $this->assertEquals('200', $response->getStatusCode(), 'request returned status code ' . $response->getStatusCode());

    $json = json_decode($response->getBody(), TRUE);

    $filtered_nodes = array_filter($json['data'], function ($item) use ($banner) {
      return isset($item['nid']) && $item['nid'] == $banner->id();
    });

    if ($shouldBeIncluded) {
      $this->assertNotEmpty($filtered_nodes, 'Node with nid ' . $banner->id() . ' found in response.');
    }
    else {
      $this->assertEmpty($filtered_nodes, 'Node with nid ' . $banner->id() . ' not found in response.');
    }
  }

  /**
   * Provides data to testPromoBanner().
   */
  public function providePromorBannerTestData() {
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
   * Test that promo banners are included in the banner endpoint response.
   *
   * @dataProvider providePromorBannerTestData
   */
  public function testPromoBanner($path, $shouldBeIncluded) {
    $banner_data = [
      'field_promo_type' => 'announcement',
      'title' => 'Test Promo Banner',
      'field_link' => [
        ['uri' => 'internal:/node/50621'],
      ],
      'field_target_paths' => [],
      'type' => 'promo_banner',
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

    // Make sure the banner is found in all the requests that it _should_ be
    // included in (and not in the places where it should not be).
    $response = \Drupal::httpClient()->get($url . '/api/v1/banner-alerts?path=' . $path);
    $this->assertEquals('200', $response->getStatusCode(), 'request returned status code ' . $response->getStatusCode());

    $json = json_decode($response->getBody(), TRUE);

    $filtered_nodes = array_filter($json['data'], function ($item) use ($banner) {
      return isset($item['nid']) && $item['nid'] == $banner->id();
    });

    if ($shouldBeIncluded) {
      $this->assertNotEmpty($filtered_nodes, 'Node with nid ' . $banner->id() . ' found in response.');
    }
    else {
      $this->assertEmpty($filtered_nodes, 'Node with nid ' . $banner->id() . ' not found in response.');
    }

  }

}
