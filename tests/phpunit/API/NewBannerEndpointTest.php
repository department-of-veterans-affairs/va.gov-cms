<?php

namespace Tests\API;

use Drupal\paragraphs\Entity\Paragraph;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * A test to confirm that the banner endpoint is working properly.
 *
 * @group foo-functional
 * @group all
 */
class NewBannerEndpointTest extends VaGovExistingSiteBase {

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
          'value' => '<p>blah blah blah</p>',
          'format' => 'rich_text',
        ],
      ],
    ]);

    // Look for a 'full_width_banner_alert' node.
    $node_storage = \Drupal::entityTypeManager()->getStorage('node');
    $banner_nids = $node_storage->getQuery()
      ->condition('type', 'full_width_banner_alert')
      // ->condition('status', TRUE)
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
      ->condition('id', $banner_alert_vamcs[0]['target_id'])
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
    $this->assertStringContainsString('"nid":' . $banner->id(), $this->getSession()->getPage()->getContent());

    // Delete the paragraph.
    $situation_update->delete();
  }

}
