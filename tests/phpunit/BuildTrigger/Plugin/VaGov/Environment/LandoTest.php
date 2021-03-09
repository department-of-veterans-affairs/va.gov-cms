<?php

namespace tests\phpunit\BuildTrigger\Plugin\VaGov\Environment;

use Drupal\Core\Site\Settings;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\va_gov_build_trigger\FrontendBuild\Command\QueueInterface;
use Prophecy\Argument;

/**
 * Tests of the Lando environment plugin.
 *
 * @coversDefaultClass \Drupal\va_gov_build_trigger\Plugin\VAGov\Environment\Lando
 */
class LandoTest extends EntityKernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'advancedqueue',
    'datetime',
    'field',
    'node',
    'system',
    'user',
    'va_gov_build_trigger',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Nodes for testing last-built date update.
    static::installEntitySchema('node');
    \Drupal::moduleHandler()
      ->loadInclude('node', 'install');
    NodeType::create([
      'type' => 'build_time_recorder_test_node',
      'label' => 'Build Time Recorder Test Node',
    ])
      ->save();
    FieldStorageConfig::create([
      'field_name' => 'field_page_last_built',
      'entity_type' => 'node',
      'type' => 'datetime',
      'settings' => [
        'datetime_type' => 'datetime',
      ],
      'cardinality' => 1,
    ])
      ->save();
    FieldConfig::create([
      'field_name' => 'field_page_last_built',
      'entity_type' => 'node',
      'bundle' => 'build_time_recorder_test_node',
      'label' => 'Page Last Built',
    ])
      ->save();
    static::createUser([], [
      'access content',
      'administer site configuration',
    ]);
    $this->node1 = Node::create([
      'type' => 'build_time_recorder_test_node',
      'title' => '1',
      'field_page_last_built' => 1,
    ])
      ->save();

    // Mock settings.
    $settings['va_gov_frontend_url'] = 'http://va-gov-cms.lndo.site/static';
    $settings['va_gov_frontend_build_type'] = 'va_gov_frontend_build_type';
    $this->container->set('settings', new Settings($settings));
  }

  /**
   * Test the client's request dispatch.
   *
   * @covers ::triggerFrontendBuild
   */
  public function testTriggerFrontendBuild() {
    // Mock command builder.
    $queueProphecy = $this->prophesize(QueueInterface::CLASS);
    $queueProphecy
      ->enqueueCommands(Argument::type('array'))
      ->shouldBeCalledTimes(1);
    $this->container->set('va_gov_build_trigger.frontend_build.command.queue', $queueProphecy->reveal());

    $plugin = $this->container->get('plugin.manager.va_gov.environment')->createInstance('lando');
    $plugin->triggerFrontendBuild();
  }

}
