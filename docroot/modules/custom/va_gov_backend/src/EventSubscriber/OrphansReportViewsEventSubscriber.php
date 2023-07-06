<?php

namespace Drupal\va_gov_backend\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Link;
use Drupal\Core\Render\RendererInterface;
use Drupal\entity_reference_revisions\EntityReferenceRevisionsOrphanPurger;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\va_gov_backend\Service\VaGovUrlInterface;
use Drupal\views_event_dispatcher\Event\Views\ViewsPreRenderEvent;
use Drupal\views_event_dispatcher\Event\Views\ViewsPreViewEvent;
use Drupal\views_event_dispatcher\ViewsHookEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class OrphansReportViewsEventSubscriber.
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
class OrphansReportViewsEventSubscriber implements EventSubscriberInterface {

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
   * The entity reference revisions orphan purger service.
   *
   * @var \Drupal\entity_reference_revisions\EntityReferenceRevisionsOrphanPurger
   */
  protected $purger;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   *  The entity manager.
   */
  private $entityTypeManager;

  /**
   * ExampleViewsEventSubscribers constructor.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\va_gov_backend\Service\VaGovUrlInterface $vaGovUrl
   *   The va.gov URL service.
   * @param \Drupal\entity_reference_revisions\EntityReferenceRevisionsOrphanPurger $purger
   *   The entity reference revisions orphan purger.
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The string entity type service.
   */
  public function __construct(
    RendererInterface $renderer,
    VaGovUrlInterface $vaGovUrl,
    EntityReferenceRevisionsOrphanPurger $purger,
    EntityTypeManager $entity_type_manager) {
    $this->renderer = $renderer;
    $this->vaGovUrl = $vaGovUrl;
    $this->purger = $purger;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      ViewsHookEvents::VIEWS_PRE_VIEW => 'preView',
      ViewsHookEvents::VIEWS_PRE_RENDER => 'preRender',
    ];
  }

  /**
   * Pre view event handler.
   *
   * @param \Drupal\views_event_dispatcher\Event\Views\ViewsPreViewEvent $event
   *   The event.
   */
  public function preView(ViewsPreViewEvent $event): void {
    $view = $event->getView();
    if ($view->id() === 'orphaned_paragraphs') {
      $types = $view->getHandlerTypes();
      $orphan_count = 0;
      $storage = $this->entityTypeManager->getStorage('paragraph');
      $results = $storage->getQuery()->accessCheck(FALSE)->execute();
      $result_chunks = array_chunk($results, 1000);
      foreach ($result_chunks as $chunk) {
        $paragraphs = $storage->loadMultiple($chunk);
        /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
        foreach ($paragraphs as $paragraph) {
          $used = $this->purger->isUsed($paragraph);
          if (!$used) {
            $orphan_count++;
          }
        }
      }
      $var = [
        '#markup' => '<p>' . $orphan_count . ' total orphaned paragraphs</p>',
      ];
      $markup = $this->renderer->renderRoot($var);
      // Get the header handlers, and add our new one.
      $headers = $view->getHandlers('header', 'orphaned_para_page');
      $custom_header = [
        'id' => 'area_text_custom',
        'table' => 'views',
        'field' => 'area_text_custom',
        'relationship' => 'none',
        'group_type' => 'group',
        'admin_label' => '',
        'empty' => '1',
        'content' => $markup,
        'plugin_id' => 'text_custom',
        'weight' => -1,
      ];
      array_unshift($headers, $custom_header);

      // Add the list of headers back in the right order.
      $view->displayHandlers->get('orphaned_para_page')->setOption($types['header']['plural'], $headers);
    }
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
    if ($view->id() === 'orphaned_paragraphs') {
      foreach ($view->result as $key => $value) {
        /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
        $paragraph = $value->_entity;
        $top_parent = va_gov_backend_get_top_parent_entity($paragraph);
        $used = $this->purger->isUsed($paragraph);
        if (!$used && !$top_parent) {
          $link = $this->getTopParentEntityLink($paragraph);
          $str = 'This is an orphan paragraph. ' . $link;
          $value->_entity->set('revision_id', $str);
        }
        elseif (!$used && $top_parent && $top_parent->getEntityTypeId() !== 'paragraph') {
          $str = "This paragraph is connected to {$top_parent->toLink()->toString()}. Moderation state is: {$top_parent->get('moderation_state')->value}.";
          $value->_entity->set('revision_id', $str);
        }
        else {
          unset($view->result[$key]);
        }
      }
    }
  }

  /**
   * Return link when there is no parent entity object.
   *
   * @param \Drupal\paragraphs\Entity\Paragraph $paragraph
   *   The paragraph to find the parent of.
   *
   * @return string
   *   Return a link to the parent entity.
   */
  protected function getTopParentEntityLink(Paragraph $paragraph): string {
    $parent_type = $paragraph->get('parent_type')->value;
    $parent_id = $paragraph->get('parent_id')->value;
    $parent_link = '';
    if ($parent_type !== 'paragraph') {
      $parent_link = Link::createFromRoute($parent_type, "entity.{$parent_type}.edit_form", [$parent_type => $parent_id])->toString();
    }
    return $parent_link;
  }

}
