<?php
// phpcs:ignoreFile

namespace tests\phpunit\FrontendBuild;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Link;
use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Entity\AbstractEntityEvent;
use Drupal\node\NodeInterface;
use Drupal\user\UserInterface;
use Drupal\va_gov_build_trigger\Environment\EnvironmentDiscovery;
use Drupal\va_gov_build_trigger\EventSubscriber\EntityEventSubscriber;
use Drupal\va_gov_build_trigger\Service\BuildRequester;
use Symfony\Component\DependencyInjection\Container;
use Tests\Support\Classes\VaGovUnitTestBase;

/**
 * Unit test for the entity event subscriber.
 *
 * @group unit
 * @group all
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
    yield 'non-node entity' => [
      'never',
      $user,
      FALSE,
    ];

    $node = $this->getMockBuilder(NodeInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    yield 'content initiated builds requests are disabled' => [
      'disabled',
      $node,
      FALSE,
    ];
  }

  /**
   * Test non-facility content changes.
   */
  public function nonFacilityNodeTestDataProvider() {
    foreach ($this->getTriggerableStatePermutations() as $triggerable_state) {
      foreach ($this->getNonFacilityTypePermutations() as $content_type => $content_type_should_trigger) {
        $node = $this->getMockBuilder(NodeInterface::class)
          ->disableOriginalConstructor()
          ->getMock();

        $original_node = clone $node;
        $node->original = $original_node;

        $node->expects($this->any())
          ->method('isPublished')
          ->willReturn($triggerable_state['is_published']);

        if ($triggerable_state['was_published'] === TRUE) {
          $node->original->expects($this->any())
            ->method('isPublished')
            ->willReturn($triggerable_state['was_published']);
        }

        $node->expects($this->any())
          ->method('getType')
          ->willReturn($content_type);

        $mod_state = new \stdClass();
        $mod_state->value = $triggerable_state['new_moderation_state'];
        if ($triggerable_state['new_moderation_state'] === 'null') {
          $mod_state->value = NULL;
        }

        $node->expects($this->any())
          ->method('get')
          ->with('moderation_state')
          ->willReturn($mod_state);

        $should_build = ($triggerable_state['is_triggerable_state'] && $content_type_should_trigger);

        // If a build should be triggered, we need to include a few more items for
        // the log message formatting. Add it always just in case.
        $link = $this->getMockBuilder(Link::class)
          ->disableOriginalConstructor()
          ->getMock();
        $link->expects($this->any())
          ->method('toString')
          ->willReturn('https://fake.link/');

        $node->expects($this->any())
          ->method('toLink')
          ->withAnyParameters()
          ->willReturn($link);

        $node->expects($this->any())
          ->method('id')
          ->willReturn('12345');

        $description = $content_type . ' node: ';
        foreach ($triggerable_state as $key => $val) {
          $description .= $key . ': ' . $val . '; ';
        }
        print json_encode([
          'description' => $description,
          'should_build' => $should_build,
        ]);
        yield $description => [
          'enabled',
          $node,
          $should_build,
        ];
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

            // These situations are not possible and will confuse the event subscriber.
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

            $node = $this->getMockBuilder(NodeInterface::class)
              ->disableOriginalConstructor()
              ->getMock();

            $original_node = clone $node;
            $node->original = $original_node;

            $node->expects($this->any())
              ->method('isPublished')
              ->willReturn($triggerable_state['is_published']);

            if ($triggerable_state['was_published'] === TRUE) {
              $node->original->expects($this->any())
                ->method('isPublished')
                ->willReturn($triggerable_state['was_published']);
            }

            $node->expects($this->any())
              ->method('getType')
              ->willReturn($content_type);

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
              $node->expects($this->any())
                ->method('hasField')
                ->with('field_operating_status_facility')
                ->willReturn(TRUE);

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
              $node->expects($this->any())
                ->method('hasField')
                ->with('field_operating_status_facility')
                ->willReturn(FALSE);
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

            $node->expects($this->any())->method('get')->willReturnMap($node_get_map);
            $node->original->expects($this->any())->method('get')->willReturnMap($original_node_get_map);

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

            // If a build should be triggered, we need to include a few more items for
            // the log message formatting. Add it always just in case.
            $link = $this->getMockBuilder(Link::class)
              ->disableOriginalConstructor()
              ->getMock();
            $link->expects($this->any())
              ->method('toString')
              ->willReturn('https://fake.link/');

            $node->expects($this->any())
              ->method('toLink')
              ->withAnyParameters()
              ->willReturn($link);

            $node->expects($this->any())
              ->method('id')
              ->willReturn('12345');

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

            yield $description => [
              'enabled',
              $node,
              $should_build,
            ];

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
              // facilityChangedStatus() skips further checks if field_operating_status_facility doesn't exist.
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
   * Get all possible permutations of the values that we care about in isTriggerableState().
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
            )
           {
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
   * Mock an EnvironmentDiscovery instance.
   *
   * @param string $mode
   *   Whether or not environment discovery indicates content edits should trigger builds.
   *
   * @return \Drupal\va_gov_build_trigger\Environment\EnvironmentDiscovery
   *   (mocked)
   */
  protected function getEnvironmentDiscovery(string $mode) {
    $environmentDiscovery = $this->getMockBuilder(EnvironmentDiscovery::class)
      ->disableOriginalConstructor()
      ->getMock();

    switch ($mode) {
      case 'never':
        $environmentDiscovery->expects($this->never())
          ->method('contentEditsShouldTriggerFrontendBuild');
        break;

      case 'disabled':
        $environmentDiscovery->expects($this->any())
          ->method('contentEditsShouldTriggerFrontendBuild')
          ->willReturn(FALSE);
        break;

      case 'enabled':
      default:
        $environmentDiscovery->expects($this->any())
          ->method('contentEditsShouldTriggerFrontendBuild')
          ->willReturn(TRUE);
        break;
    }

    return $environmentDiscovery;
  }

  /**
   * Mock a build requester.
   */
  protected function getBuildRequester($should_request_build) {
    $buildRequester = $this->getMockBuilder(BuildRequester::class)
      ->disableOriginalConstructor()
      ->getMock();

    if ($should_request_build) {
      $buildRequester->expects($this->once())
        ->method('requestFrontendBuild')
        ->with($this->callback(function ($reason) {
          $contains_str = str_contains($reason, 'A content release was triggered by a change');
          $this->assertTrue($contains_str);
          return $contains_str;
        }));
    }
    else {
      $buildRequester->expects($this->never())
        ->method('requestFrontendBuild');
    }

    return $buildRequester;
  }

  /**
   * Wrap an entity in an entity event.
   */
  protected function getEntityEvent($entity) {
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
   * There is another data provider in this test class (facilityNodeTestDataProvider)
   * that currently fails in 40 cases.
   *
   * @dataProvider stopEarlyTestDataProvider
   * @dataProvider nonFacilityNodeTestDataProvider
   * @dataProvider facilityNodeTestDataProvider
   */
  public function testAutomaticBuildTriggering(
    string $environmentDiscoveryMode,
    EntityInterface $entity,
    bool $buildRequesterShouldFire
  ) {

    $eventName = EntityHookEvents::ENTITY_INSERT;

    $environmentDiscovery = $this->getEnvironmentDiscovery($environmentDiscoveryMode);
    $buildRequester = $this->getBuildRequester($buildRequesterShouldFire);
    $event = $this->getEntityEvent($entity);

    $eventSubscriber = new EntityEventSubscriber($buildRequester, $environmentDiscovery);
    $method = EntityEventSubscriber::getSubscribedEvents()[$eventName];

    call_user_func([$eventSubscriber, $method], $event);

  }

}
