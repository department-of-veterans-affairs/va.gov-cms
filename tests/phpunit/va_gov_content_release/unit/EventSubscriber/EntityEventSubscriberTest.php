<?php

namespace tests\phpunit\va_gov_content_release\unit\EventSubscriber;

use Drupal\Core\Link;
use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Entity\AbstractEntityEvent;
use Drupal\Core\Entity\EntityInterface;
use Drupal\user\UserInterface;
use Drupal\va_gov_content_release\EntityEvent\Strategy\Plugin\StrategyPluginManagerInterface;
use Drupal\va_gov_content_release\EntityEvent\Strategy\Plugin\StrategyPluginInterface;
use Drupal\va_gov_content_release\EntityEvent\Strategy\Resolver\ResolverInterface;
use Drupal\va_gov_content_release\Request\RequestInterface;
use Drupal\va_gov_content_release\EventSubscriber\EntityEventSubscriber;
use Drupal\va_gov_content_types\Entity\VaNodeInterface;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\Container;
use Tests\Support\Classes\VaGovUnitTestBase;

/**
 * Unit test for the entity event subscriber.
 *
 * @group unit
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_content_release\EventSubscriber\EntityEventSubscriber
 */
class EntityEventSubscriberTest extends VaGovUnitTestBase {

  /**
   * {@inheritDoc}
   */
  public function setUp() : void {
    parent::setUp();
    $container = new Container();
    $container->set('string_translation', $this->getStringTranslationStub());
    \Drupal::setContainer($container);
  }

  /**
   * The hook events.
   *
   * @return array
   *   The hook events.
   */
  public function getHookEvents() {
    return [
      EntityHookEvents::ENTITY_DELETE,
      EntityHookEvents::ENTITY_INSERT,
      EntityHookEvents::ENTITY_UPDATE,
    ];
  }

  /**
   * Make sure the right events are subscribed to (but not the handler name).
   */
  public function testGetSubscribedEvents() {
    $subscribed_events = EntityEventSubscriber::getSubscribedEvents();
    $this->assertArrayHasKey(EntityHookEvents::ENTITY_DELETE, $subscribed_events);
    $this->assertArrayHasKey(EntityHookEvents::ENTITY_INSERT, $subscribed_events);
    $this->assertArrayHasKey(EntityHookEvents::ENTITY_UPDATE, $subscribed_events);
  }

  /**
   * Test early-exit cases for the event subscriber.
   *
   * Additional fields are intentionally omitted from the entities that are
   * passed here.
   */
  public function stopEarlyTestDataProvider() {
    $user = $this->getMockBuilder(UserInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    foreach ($this->getHookEvents() as $event_name) {
      yield 'non-node entity ' . $event_name => [
        $event_name,
        $user,
        FALSE,
        $event_name,
      ];
    }

    $node = $this->getMockBuilder(VaNodeInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    foreach ($this->getHookEvents() as $event_name) {
      yield 'non-published node ' . $event_name => [
        $event_name,
        $node,
        FALSE,
        $event_name,
      ];
    }
  }

  /**
   * Test non-facility content changes.
   */
  public function nonFacilityNodeTestDataProvider() {
    foreach ($this->getTriggerableStatePermutations() as $triggerable_state) {
      foreach ($this->getNonFacilityTypePermutations() as $content_type => $content_type_should_trigger) {
        foreach ($this->getHookEvents() as $event_name) {

          $nodeProphecy = $this->prophesize(VaNodeInterface::class);

          $originalNodeProphecy = $this->prophesize(VaNodeInterface::class);

          $nodeProphecy->isPublished()->willReturn($triggerable_state['is_published']);

          if ($triggerable_state['was_published'] === TRUE) {
            $originalNodeProphecy->isPublished()->willReturn($triggerable_state['was_published']);
          }

          $nodeProphecy->getType()->willReturn($content_type);

          $mod_state = new \stdClass();
          $mod_state->value = $triggerable_state['new_moderation_state'];
          if ($triggerable_state['new_moderation_state'] === 'null') {
            $mod_state->value = NULL;
          }

          $nodeProphecy->getModerationState()->willReturn($mod_state);

          $should_build = ($triggerable_state['is_triggerable_state'] && $content_type_should_trigger);

          // If a build should be triggered, we need to include a few more items
          // for the log message formatting. Add it always just in case.
          $link = $this->getMockBuilder(Link::class)
            ->disableOriginalConstructor()
            ->getMock();
          $link->expects($this->any())
            ->method('toString')
            ->willReturn('https://fake.link/');

          $nodeProphecy->toLink(Argument::any())->willReturn($link);

          $nodeProphecy->id()->willReturn('12345');

          $originalNode = $originalNodeProphecy->reveal();
          $nodeProphecy->getOriginal()->willReturn($originalNode);
          $node = $nodeProphecy->reveal();

          $description = $content_type . ' node: ';
          foreach ($triggerable_state as $key => $val) {
            $description .= $key . ': ' . $val . '; ';
          }
          $description .= $event_name;
          yield $description => [
            'enabled',
            $node,
            $should_build,
            $event_name,
          ];
        }
      }
    }
  }

  /**
   * Test facility-driven content changes.
   */
  public function facilityNodeTestDataProvider() {
    foreach ($this->getTriggerableStatePermutations() as $triggerable_state) {
      foreach ($this->getFacilityTypePermutations() as $content_type => $content_type_should_trigger) {
        foreach ($this->getFacilityModerationStatusChangePermutations() as $facility_moderation_status_change) {
          foreach ($this->getFacilityStatusFieldPermutations() as $facility_status) {
            foreach ($this->getHookEvents() as $event_name) {

              // These situations are not possible and will confuse the event
              // subscriber.
              if (
                ($triggerable_state['is_published'] === TRUE && $facility_moderation_status_change['new_state'] !== 'published') ||
                ($triggerable_state['was_published'] === TRUE && $facility_moderation_status_change['old_state'] !== 'published') ||
                ($triggerable_state['is_published'] === FALSE && $facility_moderation_status_change['new_state'] === 'published') ||
                (($facility_moderation_status_change['old_state'] !== $facility_moderation_status_change['new_state']) !== $facility_status['facility_status_field_changed']) ||
                ($facility_moderation_status_change['new_state'] !== $triggerable_state['new_moderation_state']) ||
                ($triggerable_state['was_published'] === FALSE && $facility_moderation_status_change['old_state'] === 'published')
              ) {
                continue;
              }

              $nodeProphecy = $this->prophesize(VaNodeInterface::class);

              $originalNodeProphecy = $this->prophesize(VaNodeInterface::class);

              $nodeProphecy->isPublished()->willReturn($triggerable_state['is_published']);

              if ($triggerable_state['was_published'] === TRUE) {
                $originalNodeProphecy->isPublished()->willReturn($triggerable_state['was_published']);
              }

              $nodeProphecy->getType()->willReturn($content_type);

              $mod_state = new \stdClass();
              $mod_state->value = $triggerable_state['new_moderation_state'];
              if ($triggerable_state['new_moderation_state'] === 'null') {
                $mod_state->value = NULL;
              }

              $old_mod_state = new \stdClass();
              $old_mod_state->value = $facility_moderation_status_change['old_state'];

              $node_get_map = [
                ['moderation_state', $mod_state],
              ];
              $original_node_get_map = [
                ['moderation_state', $old_mod_state],
              ];

              if ($facility_status['has_field_operating_status_facility']) {
                $nodeProphecy->hasField('field_operating_status_facility')->willReturn(TRUE);

                $field_val = new \stdClass();
                $field_val->value = 'operating_status';
                $node_get_map[] = [
                  'field_operating_status_facility',
                  $field_val,
                ];

                if ($facility_status['field_operating_status_facility_changed']) {
                  $old_field_val = new \stdClass();
                  $old_field_val->value = 'different_operating_status';
                  $original_node_get_map[] = [
                    'field_operating_status_facility',
                    $old_field_val,
                  ];
                }
                else {
                  $original_node_get_map[] = [
                    'field_operating_status_facility',
                    $field_val,
                  ];
                }
              }
              else {
                $nodeProphecy->hasField('field_operating_status_facility')->willReturn(FALSE);
              }

              if ($facility_status['has_field_operating_status_more_info']) {
                $field_val = new \stdClass();
                $field_val->value = 'operating_status';
                $node_get_map[] = [
                  'field_operating_status_more_info',
                  $field_val,
                ];

                if ($facility_status['field_operating_status_more_info_changed']) {
                  $old_field_val = new \stdClass();
                  $old_field_val->value = 'operating_status';
                  $original_node_get_map[] = [
                    'field_operating_status_more_info',
                    $old_field_val,
                  ];
                }
                else {
                  $original_node_get_map[] = [
                    'field_operating_status_more_info',
                    $field_val,
                  ];
                }
              }

              $nodeProphecy->get(Argument::type('string'))->will(function ($args) {
                return $node_get_map[$args[0]][1];
              });
              $originalNodeProphecy->get(Argument::type('string'))->will(function ($args) {
                return $original_node_get_map[$args[0]][1];
              });

              $is_triggerable_state = $triggerable_state['is_triggerable_state'];
              $has_status_related_change = $facility_status['facility_status_field_changed'];
              $oldstate_was_draft = $facility_moderation_status_change['old_state'] === 'draft';
              $oldstate_was_archived = $facility_moderation_status_change['old_state'] === 'archived';
              $oldstate_was_published = $facility_moderation_status_change['old_state'] === 'published';
              $newstate_is_archived = $facility_moderation_status_change['new_state'] === 'archived';
              $archived_from_published = ($oldstate_was_published && $newstate_is_archived);

              $facility_changed_status = (
                ($content_type_should_trigger === TRUE && $facility_status['has_field_operating_status_facility'] === TRUE) &&
                ($is_triggerable_state && ($has_status_related_change || $oldstate_was_draft || $oldstate_was_archived || $archived_from_published))
              );

              $should_build = ($is_triggerable_state && $facility_changed_status);

              // If a build should be triggered, we need to include a few more
              // items for the log message formatting. Add it just in case.
              $link = $this->getMockBuilder(Link::class)
                ->disableOriginalConstructor()
                ->getMock();
              $link->expects($this->any())
                ->method('toString')
                ->willReturn('https://fake.link/');

              $nodeProphecy->toLink()->willReturn($link);

              $nodeProphecy->id()->willReturn('12345');

              $description = $content_type . ' node: ';
              foreach ($triggerable_state as $key => $val) {
                $description .= $key . ': ' . $val . '; ';
              }
              foreach ($facility_status as $key => $val) {
                $description .= $key . ': ' . $val . '; ';
              }
              foreach ($facility_moderation_status_change as $key => $val) {
                $description .= $key . ': ' . $val . '; ';
              }

              $originalNode = $originalNodeProphecy->reveal();
              $nodeProphecy->getOriginal()->willReturn($originalNode);
              $node = $nodeProphecy->reveal();
              $description .= $event_name;

              yield $description => [
                'enabled',
                $node,
                $should_build,
                $event_name,
              ];
            }
          }
        }
      }
    }
  }

  /**
   * Get all possible permutations of facility status changes.
   */
  protected function getFacilityModerationStatusChangePermutations() {
    foreach (['draft', 'archived', 'published'] as $new_state) {
      foreach (['draft', 'archived', 'published'] as $old_state) {
        yield [
          'new_state' => $new_state,
          'old_state' => $old_state,
        ];
      }
    }
  }

  /**
   * Return all possible facility status field permutations.
   */
  protected function getFacilityStatusFieldPermutations() {
    foreach ([0 => FALSE, 1 => TRUE] as $w => $has_field_operating_status_facility) {
      foreach ([0 => FALSE, 1 => TRUE] as $x => $has_field_operating_status_more_info) {
        foreach ([0 => FALSE, 1 => TRUE] as $y => $field_operating_status_facility_changed) {
          foreach ([0 => FALSE, 1 => TRUE] as $z => $field_operating_status_more_info_changed) {
            // Skip impossible cases.
            if (
              // Can't have a changed value if the field doesn't exist.
              (!$has_field_operating_status_facility && $field_operating_status_facility_changed) ||
              // Can't have a changed value if the field doesn't exist.
              (!$has_field_operating_status_more_info && $field_operating_status_more_info_changed) ||
              // If we don't have either field, why bother.
              (!$has_field_operating_status_facility && !$has_field_operating_status_more_info) ||
              // Our content has either both fields or neither.
              ($has_field_operating_status_facility !== $has_field_operating_status_more_info)
            ) {
              continue;
            }

            yield [
              'has_field_operating_status_facility' => $has_field_operating_status_facility,
              'has_field_operating_status_more_info' => $has_field_operating_status_more_info,
              'field_operating_status_facility_changed' => $field_operating_status_facility_changed,
              'field_operating_status_more_info_changed' => $field_operating_status_more_info_changed,
              // facilityChangedStatus() skips further checks if
              // field_operating_status_facility doesn't exist.
              'facility_status_field_changed' => ($has_field_operating_status_facility) && ($field_operating_status_facility_changed || $field_operating_status_more_info_changed),
            ];

          }
        }
      }
    }
  }

  /**
   * Returns content type => is facility type.
   */
  protected function getFacilityTypePermutations() {
    return [
      'somerandomtype' => FALSE,
      'health_care_local_facility' => TRUE,
      'vet_center_cap' => TRUE,
      'vet_center_outstation' => TRUE,
      'vet_center' => TRUE,
    ];
  }

  /**
   * Returns content type => is triggerable, non facility type.
   */
  protected function getNonFacilityTypePermutations() {
    return [
      'somerandomtype' => FALSE,
      'banner' => TRUE,
      'full_width_banner_alert' => TRUE,
    ];
  }

  /**
   * Get permutations of the values that we care about in isTriggerableState().
   *
   * This could probably be simplified, but I mapped it out in a spreadsheet.
   */
  protected function getTriggerableStatePermutations() {
    foreach ([0 => FALSE, 1 => TRUE] as $i => $is_published) {
      foreach ([0 => FALSE, 1 => TRUE] as $j => $was_published) {
        foreach (['published', 'archived', 'null'] as $new_moderation_state) {

          $has_been_published = $is_published || $was_published;
          $is_triggerable_state = FALSE;

          if (
            ($is_published && $new_moderation_state !== 'published') ||
            (!$is_published && $new_moderation_state === 'published')
          ) {
            continue;
          }
          if (
            ($new_moderation_state === 'published') ||
            ($has_been_published && ($new_moderation_state === 'archived')) ||
            ($is_published && ($new_moderation_state === 'null')) ||
            ($was_published && !$is_published && ($new_moderation_state === 'null'))
          ) {
            $is_triggerable_state = TRUE;
          }

          yield [
            'is_published' => $is_published,
            'was_published' => $was_published,
            'new_moderation_state' => $new_moderation_state,
            'is_triggerable_state' => $is_triggerable_state,
          ];

        }
      }
    }
  }

  /**
   * Wrap an entity in an entity event.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to wrap.
   *
   * @return \Drupal\core_event_dispatcher\Event\Entity\AbstractEntityEvent
   *   The wrapped entity.
   */
  protected function getEntityEvent(EntityInterface $entity) {
    $entityEvent = $this->getMockBuilder(AbstractEntityEvent::class)
      ->disableOriginalConstructor()
      ->getMock();

    $entityEvent->expects($this->any())
      ->method('getEntity')
      ->willReturn($entity);

    return $entityEvent;
  }

  /**
   * Test automatic build triggering when entities are changed.
   *
   * Since all the events in entity event subscriber use the same handler, this
   * test only exercises the ENTITY_INSERT event.
   *
   * @todo get facilityNodeTestDataProvider working properly.
   *
   * There is another data provider in this test class
   * (facilityNodeTestDataProvider) that currently fails in 40 cases.
   *
   * @param string $strategyId
   *   The strategy ID to use.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to test.
   * @param bool $shouldTriggerContentRelease
   *   Whether or not the entity should trigger a content release.
   * @param string $eventName
   *   The event name to test, e.g. ENTITY_INSERT.
   *
   * @dataProvider stopEarlyTestDataProvider
   * @dataProvider nonFacilityNodeTestDataProvider
   * @dataProvider facilityNodeTestDataProvider
   */
  public function testAutomaticBuildTriggering(
    string $strategyId,
    EntityInterface $entity,
    bool $shouldTriggerContentRelease,
    string $eventName
  ) {

    $strategyPluginProphecy = $this->prophesize(StrategyPluginInterface::class);
    $strategyPluginProphecy->shouldTriggerContentRelease($entity)
      ->willReturn($shouldTriggerContentRelease);
    $strategyPluginProphecy->getReasonMessage(Argument::any())
      ->willReturn('Test reason message');
    $strategyPlugin = $strategyPluginProphecy->reveal();

    $strategyPluginManagerProphecy = $this->prophesize(StrategyPluginManagerInterface::class);
    $strategyPluginManagerProphecy->getStrategy($strategyId)
      ->willReturn($strategyPlugin);
    $strategyPluginManager = $strategyPluginManagerProphecy->reveal();

    $strategyResolverProphecy = $this->prophesize(ResolverInterface::class);
    $strategyResolverProphecy->getStrategyId()
      ->willReturn($strategyId);
    $strategyResolver = $strategyResolverProphecy->reveal();

    $requestProphecy = $this->prophesize(RequestInterface::class);
    $requestProphecy->submitRequest(Argument::type('string'));
    $request = $requestProphecy->reveal();

    $event = $this->getEntityEvent($entity);

    $eventSubscriber = new EntityEventSubscriber($strategyPluginManager, $strategyResolver, $request);
    $method = EntityEventSubscriber::getSubscribedEvents()[$eventName];

    call_user_func([$eventSubscriber, $method], $event);
  }

}
