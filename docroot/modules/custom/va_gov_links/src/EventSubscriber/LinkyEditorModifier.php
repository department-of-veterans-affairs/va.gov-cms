<?php

namespace Drupal\va_gov_links\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\field_event_dispatcher\Event\Field\WidgetSingleElementTypeFormAlterEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Modify the content of some fields to replace Linky URLs with external URLs.
 *
 * When Linky replaces a URL, it stores the original URL in a Linky entity.
 *
 * The form will then display the Linky path (e.g. `/admin/content/linky/123`),
 * but this is opaque to the editor. Editors should see the destination URL.
 *
 * This event subscriber performs a simple substitution when the form is
 * displayed, so that the editor sees -- and is able to edit -- the
 * destination URL.
 */
class LinkyEditorModifier implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      'hook_event_dispatcher.widget_single_element_text_long.alter' => 'alterWidgetTextLong',
    ];
  }

  /**
   * The Linky storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $linkyStorage;

  /**
   * Constructor for ContentReleaseBrokenLinksSubscriber objects.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->linkyStorage = $entityTypeManager->getStorage('linky');
  }

  /**
   * Handle the widget_single_element_text_long alter event.
   *
   * @param \Drupal\field_event_dispatcher\Event\Field\WidgetSingleElementTypeFormAlterEvent $event
   *   The forwarded event.
   */
  public function alterWidgetTextLong(WidgetSingleElementTypeFormAlterEvent $event): void {
    $element = &$event->getElement();
    if (isset($element['#default_value']) && str_contains($element['#default_value'], '/admin/content/linky/')) {
      $element['#default_value'] = $this->replaceLinkyPaths($element['#default_value']);
    }
  }

  /**
   * Replace Linky paths with external URLs.
   *
   * @param string $text
   *   The text to be modified.
   *
   * @return string
   *   The modified text.
   */
  public function replaceLinkyPaths(string $text): string {
    return preg_replace_callback(
      '#/admin/content/linky/(\d+)#',
      [$this, 'replaceMatch'],
      $text
    );
  }

  /**
   * Replace individual match.
   *
   * @param array $matches
   *   The matches.
   *
   * @return string
   *   The replacement.
   */
  public function replaceMatch(array $matches): string {
    try {
      return $this->getLinkyUrl((int) $matches[1]);
    }
    catch (\Throwable $e) {
      return $matches[0];
    }
  }

  /**
   * Load the Linky entity and return the external URL.
   *
   * @param int $linkyId
   *   The Linky entity ID.
   *
   * @return string
   *   The external URL.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   *   If the Linky entity cannot be loaded.
   */
  public function getLinkyUrl(int $linkyId): string {
    $linky = $this->linkyStorage->load($linkyId);
    if (empty($linky)) {
      throw new \Exception("Linky entity $linkyId not found");
    }
    return $linky->get('link')->getValue()[0]['uri'];
  }

}
