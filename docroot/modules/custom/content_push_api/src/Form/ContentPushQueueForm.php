<?php

namespace Drupal\content_push_api\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Queue\QueueWorkerManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ContentPushQueueForm.
 *
 * @package Drupal\content_push_api\Form
 */
class ContentPushQueueForm extends FormBase {

  /**
   * Queue factory.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queueFactory;

  /**
   * Queue Worker Manager.
   *
   * @var \Drupal\Core\Queue\QueueWorkerManagerInterface
   */
  protected $queueManager;

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Database
   */
  private $database;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('queue'),
      $container->get('plugin.manager.queue_worker')
    );
  }

  /**
   * ContentPushQueueForm constructor.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   Database connection.
   * @param \Drupal\Core\Queue\QueueFactory $queue
   *   Queue factory.
   * @param \Drupal\Core\Queue\QueueWorkerManagerInterface $queue_manager
   *   Queue manager.
   */
  public function __construct(Connection $database, QueueFactory $queue, QueueWorkerManagerInterface $queue_manager) {
    $this->database = $database;
    $this->queueFactory = $queue;
    $this->queueManager = $queue_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'content_push_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $queue = $this->queueFactory->get('content_push_queue');
    $queue_items = $this->getItems('content_push_queue');

    $rows = [];

    foreach ($queue_items as $item) {
      // @todo: display payload data in readable format.
      $data = [
        '#type' => 'html_tag',
        '#tag' => 'pre' ,
        '#value' => $item->data,
      ];

      $data = \Drupal::service('renderer')->renderPlain($data);

      // @todo: use dependency injection for date.formatter.
      $rows[] = [
        'data' => [
          $item->item_id,
          $data,
          $item->expire ? \Drupal::service('date.formatter')->format($item->expire, 'custom', 'm/d/Y H:i:s') : 0,
          \Drupal::service('date.formatter')->format($item->created, 'custom', 'm/d/Y H:i:s'),
        ],
      ];
    }

    $form['queue_items'] = [
      '#type' => 'table',
      '#tableselect' => FALSE,
      '#header' => [
        'item_id' => $this->t('Item ID'),
        'data' => $this->t('Data'),
        'expired' => $this->t('Expires'),
        'created' => $this->t('Created'),
      ],
      '#rows' => $rows,
      '#empty' => $this->t('No queue items found.'),
    ];

    $form['pager'] = [
      '#type' => 'pager',
    ];

    $form['help'] = [
      '#type' => 'markup',
      '#markup' => $this->t('Submitting this form will process the Content Push Queue which contains @number items.', ['@number' => $queue->numberOfItems()]),
    ];
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Process queue'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $queue_worker = $this->queueManager->createInstance('content_push_queue');
    $queue_worker->processQueue();
  }

  /**
   * Get queue items for display in the UI.
   *
   * @param string $queue_name
   *   Queue name.
   *
   * @return mixed
   *   Queue items.
   */
  public function getItems($queue_name) {
    $query = $this->database->select('queue', 'q');
    $query->addField('q', 'item_id');
    $query->addField('q', 'data');
    $query->addField('q', 'expire');
    $query->addField('q', 'created');
    $query->condition('q.name', $queue_name);
    $query = $query->extend('Drupal\Core\Database\Query\PagerSelectExtender')
      ->limit(10);

    return $query->execute()->fetchAll();
  }

}
