<?php

namespace Drupal\va_gov_backend\EventSubscriber;

use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
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
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      ViewsHookEvents::VIEWS_PRE_RENDER => 'preRender',
    ];
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
        /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
        $paragraph = $value->_entity;
        $tree = [];
        $top_parent = va_gov_backend_get_top_parent_entity($paragraph, $tree);
        if ($top_parent) {
          $is_in_current_node = $this->checkNodeRevisionUsesParagraph($top_parent, $tree);
          if ($is_in_current_node) {
            if ($top_parent instanceof NodeInterface) {
              $node = $top_parent;
              $link = Link::fromTextAndUrl($node->getTitle(), $node->toUrl())->toRenderable();
              $content_type = $node->getType();
              $va_gov_url = $this->vaGovUrl->getVaGovFrontEndUrlForEntity($node);
              $va_gov_link = Link::fromTextAndUrl($va_gov_url, Url::fromUri($va_gov_url))->toRenderable();
              $va_gov_link['#attributes'] = ['class' => 'va-gov-url'];
              $section = $node->field_administration->entity;
              $section_link = Link::fromTextAndUrl($section->label(), $section->toUrl())->toRenderable();
              $value->_entity->set('parent_id', $this->renderer->render($link));
              $value->_entity->set('parent_type', $content_type);
              $value->_entity->set('parent_field_name', $this->renderer->render($va_gov_link));
              $value->_entity->set('revision_id', $this->renderer->render($section_link));
            }
            // This can be expanded in the event that a table is used
            // in entities besides nodes.
            else {
              $str = 'This paragraph\'s top level parent is not a node.';
              $value->_entity->set('parent_field_name', $str);
            }
          }
          else {
            $str = 'This paragraph is not in the current revision of its top level parent. It is safe to ignore.';
            $value->_entity->set('field_table', [
              'caption' => $str,
              'format' => 'rich_text',
              'value' => [[$str]],
            ]);
          }
        }
      }
    }
  }

  /**
   * Check if a node revision uses a paragraph.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node to check.
   * @param array $tree
   *   The tree of entities that the paragraph belongs to, contains the
   *   field_name and the revision_id of all entities in the tree.
   *
   * @return bool
   *   TRUE if the node revision uses the paragraph.
   */
  protected function checkNodeRevisionUsesParagraph(NodeInterface $node, array $tree): bool {
    $uses_revisions = [];
    foreach ($tree as $value) {
      if ($node->hasField($value['field_name'])) {
        $field = $node->get($value['field_name'])->getValue();
        foreach ($field as $item) {
          if ($item['target_revision_id'] === $value['revision_id']) {
            $uses_revisions[] = TRUE;
          }
          else {
            $uses_revisions[] = FALSE;
          }
        }
      }
      else {
        $uses_revisions[] = FALSE;
      }
    }
    if (in_array(TRUE, $uses_revisions)) {
      return TRUE;
    }
    return FALSE;
  }

}
