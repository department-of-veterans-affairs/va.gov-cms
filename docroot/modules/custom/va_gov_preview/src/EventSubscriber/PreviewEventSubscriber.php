<?php

namespace Drupal\va_gov_preview\EventSubscriber;


use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Entity\EntityViewEvent;
use Drupal\feature_toggle\FeatureStatus;
use Drupal\node\NodeInterface;
use Drupal\preprocess_event_dispatcher\Event\PagePreprocessEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * VA.gov Lovell Entity Event Subscriber.
 */
class PreviewEventSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * The Feature toggle name for outreach checkbox.
   */
  const NEXT_PREVIEW_FEATURE_NAME = 'feature_next_story_preview';

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   *  The entity manager.
   */
  private $entityTypeManager;

  /**
   * The route match interface.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * TRUE if the outreach checkbox feature toggle is enabled.
   *
   * @var bool
   */
  private bool $next_preview_enabled;

  /**
   * Constructs the EventSubscriber object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   The string entity type service.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Provides an interface for classes representing the result of routing.
   */
  public function __construct(
    EntityTypeManager   $entityTypeManager,
    RouteMatchInterface $route_match,
    FeatureStatus       $feature_status,
  ) {
    $this->entityTypeManager = $entityTypeManager;
    $this->routeMatch = $route_match;
    $this->next_preview_enabled = $feature_status->getStatus(self::NEXT_PREVIEW_FEATURE_NAME);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
//      EntityHookEvents::ENTITY_VIEW => 'generatePreviewButton',
      PagePreprocessEvent::name() => 'preprocessPage',
    ];
  }

  /**
   * Preprocess a node page to display the preview button.
   *
   * @param \Drupal\preprocess_event_dispatcher\Event\PagePreprocessEvent $event
   *   Event.
   */
  public function preprocessPage(PagePreprocessEvent $event): void {
    /** @var \Drupal\preprocess_event_dispatcher\Variables\PageEventVariables $variables */
    $variables = $event->getVariables();
    $node = $variables->getNode();
    $vars = &$variables->getRootVariablesByReference();

    if ($node === NULL) {
      return;
    }

    $vars['page']['sidebar_second']['#markup'] = '<a>foo bar</a>';

    dpm($event);
    dpm($vars);
  }

  /**
   * Generate a preview button for a node.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityViewEvent $event
   *   The event.
   */
  public function generatePreviewButton(EntityViewEvent $event): void {
    $entity = $event->getEntity();

    if ($entity instanceof NodeInterface) {
      $route_name = $this->routeMatch->getRouteName();
      $language = \Drupal::languageManager()->getCurrentLanguage()->getId();

      if (($route_name !== 'entity.node.edit_form') && ($language === 'en')) {
        dpm('event subscribed successfully');
        dpm($event);

        $build = &$event->getBuild();
        $build['extra_markup'] = [
          '#region' => 'sidebar_second',
          '#markup' => 'this is extra markup',
        ];

        dpm($build);
      }
    }
  }


}
