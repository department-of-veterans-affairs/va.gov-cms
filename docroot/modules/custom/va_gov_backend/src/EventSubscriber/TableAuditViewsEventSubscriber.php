<?php

namespace Drupal\va_gov_backend\EventSubscriber;

use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\va_gov_backend\Service\VaGovUrlInterface;
use Drupal\views_event_dispatcher\Event\Views\ViewsPreRenderEvent;
use Drupal\views_event_dispatcher\ViewsHookEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class TableAuditViewsEventSubscriber.
 *
 * Don't forget to define your class as a service and tag it as
 * an "event_subscriber":
 *
 * services:
 *  hook_event_dispatcher.example_views_subscribers:
 *   class: Drupal\hook_event_dispatcher\ExampleViewsEventSubscribers
 *   tags:
 *     - { name: event_subscriber }
 */
class TableAuditViewsEventSubscriber implements EventSubscriberInterface {

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The va.gov URL service.
   *
   * @var \Drupal\va_gov_backend\Service\VaGovUrlInterface
   */
  protected $vaGovUrl;

  /**
   * ExampleViewsEventSubscribers constructor.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\va_gov_backend\Service\VaGovUrlInterface $vaGovUrl
   *   The va.gov URL service.
   */
  public function __construct(
    RendererInterface $renderer,
    VaGovUrlInterface $vaGovUrl) {
    $this->renderer = $renderer;
    $this->vaGovUrl = $vaGovUrl;
  }

  /**
   * Pre render event handler.
   *
   * @param \Drupal\views_event_dispatcher\Event\Views\ViewsPreRenderEvent $event
   *   The event.
   *
   * @throws \Exception
   */
  public function preRender(ViewsPreRenderEvent $event): void {
    $view = $event->getView();
    if ($view->id() === 'tables') {
      foreach ($view->result as $value) {
        $node = va_gov_backend_get_parent_node($value->_entity);
        if (empty($node)) {
          $str = 'This is an orphan paragraph.';
          $value->_entity->set('parent_field_name', $str);
        }
        else {
          $link = Link::fromTextAndUrl($node->getTitle(), $node->toUrl())->toRenderable();
          $content_type = $node->type->entity->label();
          $va_gov_url = $this->vaGovUrl->getVaGovFrontEndUrlForEntity($node);
          $va_gov_link = Link::fromTextAndUrl($va_gov_url, Url::fromUri($va_gov_url))->toRenderable();
          $va_gov_link['#attributes'] = ['class' => 'va-gov-url'];
          $section = $node->field_administration->entity;
          $section_link = Link::fromTextAndUrl($section->label(), $section->toUrl())->toRenderable();
          $value->_entity->set('parent_id', $this->renderer->renderRoot($link));
          $value->_entity->set('parent_type', $content_type);
          $value->_entity->set('parent_field_name', $this->renderer->renderRoot($va_gov_link));
          $value->_entity->set('revision_id', $this->renderer->renderRoot($section_link));
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      ViewsHookEvents::VIEWS_PRE_RENDER => 'preRender',
    ];
  }

}
