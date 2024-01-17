<?php

namespace Drupal\va_gov_api\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Add "situation_update" paragraph data to the "/jsonapi/banner-alerts" route.
 */
class FullWidthBannerAlertSubscriber implements EventSubscriberInterface {

  /**
   * The entity type manager.
   */
  private EntityTypeManagerInterface $entityTypeManager;

  /**
   * Constructor.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      KernelEvents::RESPONSE => 'onKernelResponse',
    ];
  }

  /**
   * Modify response to add "field_situation_updates" paragraph data.
   */
  public function onKernelResponse(ResponseEvent $event): void {
    // Only modify the "/jsonapi/banner-alerts" route.
    $request = $event->getRequest();
    if ($request->get('_route') !== 'va_gov_api.banner_alerts') {
      return;
    }

    $response = $event->getResponse();
    $decoded_content = json_decode($response->getContent(), TRUE);
    $base_domain = $request->getSchemeAndHttpHost();

    // Loop through data array and add "field_situation_updates" paragraph data.
    foreach ($decoded_content['data'] as $value) {
      if ($situation_updates_meta = $value['relationships']['field_situation_updates']['data']) {
        foreach ($situation_updates_meta as $situation_update_meta) {
          $situation_update_id = $situation_update_meta['meta']['drupal_internal__target_id'];
          /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
          $paragraph = $this->entityTypeManager->getStorage('paragraph')->load($situation_update_id);
          $revision_id = $paragraph->getRevisionId();
          $paragraph_type = $paragraph->getType();
          $uuid = $paragraph->uuid();

          $paragraphData = [
            'type' => 'paragraph--' . $paragraph_type,
            'id' => $uuid,
            'links' => [
              'self' => [
                'href' => "$base_domain/jsonapi/paragraph/$paragraph_type/$uuid?resourceVersion=id%3A$revision_id",
              ],
            ],
            'attributes' => [
              'drupal_internal__id' => $paragraph->id(),
              'drupal_internal__revision_id' => $revision_id,
              'parent_id' => $paragraph->get('parent_id')->getValue()[0],
              'field_datetime_range_timezone' => $paragraph->get('field_datetime_range_timezone')->getValue()[0],
              'field_send_email_to_subscribers' => $paragraph->get('field_send_email_to_subscribers')->getValue()[0],
              'field_wysiwyg' => $paragraph->get('field_wysiwyg')->getValue()[0],
            ],
            'relationships' => [],
          ];

          $decoded_content['included'][] = $paragraphData;
        }
      }
    }

    $content = json_encode($decoded_content);
    $response->setContent($content);
  }

}
