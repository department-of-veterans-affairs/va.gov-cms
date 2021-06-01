<?php

namespace Drupal\va_gov_backend\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Processes Publish Now! requests.
 */
class PublishNowController extends ControllerBase {

  /**
   * AWS SQS Client.
   *
   * @var \Aws\Sqs\SqsClient
   */
  protected $sqsClient;

  /**
   * Settings.
   *
   * @var \Drupal\Core\Site\Settings
   */
  protected $settings;

  /**
   * Publish Now service.
   *
   * @var \Drupal\va_gov_backend\Service\PublishNow
   */
  protected $publishNow;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->sqsClient = $container->get('va_gov_backend.aws_sqs_client');
    $instance->settings = $container->get('settings');
    $instance->publishNow = $container->get('va_gov_backend.publish_now');
    return $instance;
  }

  /**
   * Publish the node now!
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   * @param \Drupal\node\NodeInterface|null $node
   *   The node to publish.
   */
  public function publishNow(Request $request, NodeInterface $node = NULL) {
    if (!$this->publishNow->canPublishNode($node)) {
      throw new \Exception('This node cannot be published in its present form.');
    }
    $queueUrl = $this->settings->get('va_gov_publish_now_queue_url');
    $attributes = [];
    $nid = $node->id();
    $path = $node->toUrl()->toString();
    $body = [
      'nid' => $nid,
      'path' => $path,
    ];
    $response = $this->sqsClient->sendMessage([
      'MessageBody' => json_encode($body),
      'QueueUrl' => $queueUrl,
      'MessageAttributes' => $attributes,
    ]);
    $responseArray = print_r($response->toArray(), TRUE);
    $jsonResponse = json_encode($response->toArray(), NULL, 2);
    $message = <<<EOF
Node $nid ($path) was submitted to the SQS queue.

The raw data returned from AWS is as follows:

<pre>
$responseArray

$jsonResponse
</pre>
EOF;
    return [
      '#type' => 'markup',
      '#markup' => $message,
    ];
  }

}
