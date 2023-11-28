<?php

namespace Drupal\va_gov_preview\EventSubscriber;

use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Http\RequestStack;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\feature_toggle\FeatureStatus;
use Drupal\next\NextEntityTypeManagerInterface;
use Drupal\next\NextSettingsManagerInterface;
use Drupal\node\NodeInterface;
use Drupal\preprocess_event_dispatcher\Event\PagePreprocessEvent;
use Drupal\va_gov_backend\Service\ExclusionTypes;
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
   * The request stack.
   *
   * @var \Drupal\Core\Http\RequestStack
   */
  protected $requestStack;

  /**
   * The language manager interface.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * The VA gov backend exclusion types service.
   *
   * @var \Drupal\va_gov_backend\Service\ExclusionTypes
   */
  protected $exclusionTypes;

  /**
   * The next entity type manager.
   *
   * @var \Drupal\next\NextEntityTypeManagerInterface
   */
  protected NextEntityTypeManagerInterface $nextEntityTypeManager;

  /**
   * The next settings manager.
   *
   * @var \Drupal\next\NextSettingsManagerInterface
   */
  protected NextSettingsManagerInterface $nextSettingsManager;

  /**
   * TRUE if the next preview checkbox feature toggle is enabled.
   *
   * @var bool
   */
  private bool $nextPreviewEnabled;

  /**
   * Constructs the EventSubscriber object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   The string entity type service.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Interface for classes representing the result of routing.
   * @param \Drupal\Core\Http\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Datetime\DateFormatter $date_formatter
   *   The date formatter.
   * @param \Drupal\va_gov_backend\Service\ExclusionTypes $exclusion_types
   *   The va_gov_backend exclusion types service.
   * @param \Drupal\next\NextEntityTypeManagerInterface $next_entity_type_manager
   *   Interface for retrieving all connected Next.js sites.
   * @param \Drupal\next\NextSettingsManagerInterface $next_settings_manager
   *   Interface for retrieving specific Next.js site settings.
   * @param \Drupal\feature_toggle\FeatureStatus $feature_status
   *   Interface for checking CMS feature flags.
   */
  public function __construct(
    EntityTypeManager $entityTypeManager,
    RouteMatchInterface $route_match,
    RequestStack $request_stack,
    LanguageManagerInterface $language_manager,
    DateFormatter $date_formatter,
    ExclusionTypes $exclusion_types,
    NextEntityTypeManagerInterface $next_entity_type_manager,
    NextSettingsManagerInterface $next_settings_manager,
    FeatureStatus $feature_status,
  ) {
    $this->entityTypeManager = $entityTypeManager;
    $this->routeMatch = $route_match;
    $this->requestStack = $request_stack;
    $this->languageManager = $language_manager;
    $this->dateFormatter = $date_formatter;
    $this->exclusionTypes = $exclusion_types;
    $this->nextEntityTypeManager = $next_entity_type_manager;
    $this->nextSettingsManager = $next_settings_manager;
    $this->nextPreviewEnabled = $feature_status->getStatus(self::NEXT_PREVIEW_FEATURE_NAME);
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
    $language = $this->languageManager->getCurrentLanguage()->getId();

    // Make sure we aren't on the node form or an excluded type.
    if (($route_name !== 'entity.node.edit_form') && ($language === 'en')) {
      $last_saved_by_an_editor = $node->get('field_last_saved_by_an_editor')->value;
      $vars['page']['last_saved_by_an_editor'] = $last_saved_by_an_editor ? $this->dateFormatter->format($last_saved_by_an_editor, 'custom', 'F j Y g:ia') : 'Unknown';

      $button = $this->generatePreviewButton($node);
      $vars['page']['sidebar_second']['#markup'] = $button;
    }
  }

  /**
   * Generate a preview button for a node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Node.
   */
  protected function generatePreviewButton(NodeInterface $node): string|null {
    // Needs to come first because listing types are allowed here.
    if ($this->nextPreviewEnabled && $this->checkNextEnabledTypes($node->bundle())) {
      $url = $this->generateNextBuildPreviewLink($node);
    }
    // Otherwise return default preview experience.
    else {
      if ($this->checkExcludedTypes($node)) {
        return NULL;
      }
      // Make sure we aren't on /training-guide.
      $current_uri = $this->requestStack->getCurrentRequest()->getRequestUri();
      if ($current_uri === '/training-guide') {
        return NULL;
      }

      $node = $this->routeMatch->getParameter('node');
      $nid = $node->id();
      $host = $this->requestStack->getCurrentRequest()->getHost();
      $preview_form = new PreviewForm();
      $url = $preview_form->getEnvironment($host, $nid);
    }
    return '<a class="button button--primary js-form-submit form-submit node-preview-button" rel="noopener" target="_blank" href="' . $url . '">' . $this->t('Preview') . '</a>';
  }

  /**
   * Next-build enabled preview requires certain config entities to exist.
   *
   * @param string $type
   *   The node type.
   *
   * @return bool
   *   TRUE if the node type has a corresponding next config entity.
   */
  protected function checkNextEnabledTypes(string $type): bool {
    // @todo Replace this array with a proper config check.
    $enabled_types = ['news_story', 'story_listing'];
    return in_array($type, $enabled_types);
  }

  /**
   * Existing preview functionality has some conditions.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node type.
   *
   * @return bool
   *   TRUE if the node type is excluded from preview.
   */
  protected function checkExcludedTypes(NodeInterface $node): bool {
    $exclusion_types_from_config = $this->exclusionTypes->getExcludedTypes();
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
   * @param \Drupal\node\NodeInterface $node
   *   Node.
   *
   * @return string
   *   Preview link with required URL query params.
   */
  protected function generateNextBuildPreviewLink(NodeInterface $node): string {
    $sites = $this->nextEntityTypeManager->getSitesForEntity($node);

    $url = $sites['next_build_preview_server']->getPreviewUrlForEntity($node);

    return $url->toString();
  }

}
