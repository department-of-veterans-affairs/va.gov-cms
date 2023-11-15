<?php

namespace Drupal\va_gov_preview\EventSubscriber;


use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\feature_toggle\FeatureStatus;
use Drupal\node\NodeInterface;
use Drupal\preprocess_event_dispatcher\Event\PagePreprocessEvent;
use Drupal\va_gov_build_trigger\Form\PreviewForm;
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
   */
  private $entityTypeManager;

  /**
   * The route match interface.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * TRUE if the next preview checkbox feature toggle is enabled.
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
    EntityTypeManager             $entityTypeManager,
    RouteMatchInterface           $route_match,
    FeatureStatus                 $feature_status,
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

    $route_name = $this->routeMatch->getRouteName();
    $language = \Drupal::languageManager()->getCurrentLanguage()->getId();

    // Make sure we aren't on the node form or an excluded type.
    if (($route_name !== 'entity.node.edit_form') && ($language === 'en')) {
      $last_saved_by_an_editor = $node->get('field_last_saved_by_an_editor')->value;
      $vars['page']['last_saved_by_an_editor'] = $last_saved_by_an_editor ? \Drupal::service('date.formatter')->format($last_saved_by_an_editor, 'custom', 'F j Y g:ia') : 'Unknown';

      $button = $this->generatePreviewButton($node);
      $vars['page']['sidebar_second']['#markup'] = $button;
    }
  }

  /**
   * Generate a preview button for a node.
   * @param \Drupal\node\NodeInterface $node
   *    Node.
   */
  protected function generatePreviewButton(NodeInterface $node): string|null {
    // If next-build enabled, use drupal/next generated link. Needs to come first because listing types are enabled here.
    if ($this->next_preview_enabled && $this->checkNextEnabledTypes($node->bundle())) {
      $test = $this->generateNextBuildPreviewLink($node);
      $button = $test;
    }
    // Otherwise return default preview experience.
    else {
      if ($this->checkExcludedTypes($node)) {
        return null;
      }
      // Make sure we aren't on /training-guide.
      $current_uri = \Drupal::request()->getRequestUri();
      if ($current_uri === '/training-guide') {
        return null;
      }

      $node = $this->routeMatch->getParameter('node');
      $nid = $node->id();
      $host = \Drupal::request()->getHost();
      $preview_form = new PreviewForm();
      $url = $preview_form->getEnvironment($host, $nid);
      $button = '<a class="button button--primary js-form-submit form-submit node-preview-button" rel="noopener" target="_blank" href="' . $url . '">' . $this->t('Preview'). '</a>';
    }
    return $button;
  }

  /**
   * Next-build enabled preview requires certain config entities to exist for a node type.
   *
   * @param string $type
   * @return bool
   */
  protected function checkNextEnabledTypes(string $type): bool {
    // Replace this array with a config check.
    $enabled_types = ['news_story', 'story_listing'];
    return in_array($type, $enabled_types);
  }

  /**
   * Existing preview functionality has some conditions.
   *
   * @param NodeInterface $node
   * @return bool
   */
  protected function checkExcludedTypes(NodeInterface $node): bool {
    $exclusion_types_from_config = \Drupal::service('va_gov_backend.exclusion_types')->getExcludedTypes();
    $list_types = [
      // List pages don't play nicely with preview.
      'event_listing',
      'health_services_listing,',
      'leadership_listing',
      'locations_listing',
      'press_releases_listing',
      'publication_listing',
      'story_listing',
    ];
    $exclusion_types = array_merge(array_values($exclusion_types_from_config), array_values($list_types));
    // Exclude staff pages without bios.
    if ($node->bundle() === 'person_profile' && $node->get('field_complete_biography_create')->value === '0') {
      $exclusion_types[] = 'person_profile';
    }

    return (in_array($node->bundle(), $exclusion_types));
  }

  /**
   * Generate a preview link targeting an associated next-build server.
   *
   * @param NodeInterface $node
   * @return string
   */
  protected function generateNextBuildPreviewLink(NodeInterface $node): string {

    return 'https://a-link-from-next' . $node->getTitle();
  }

}
