<?php

namespace Drupal\content_push_api\Service;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Send request to endpoint.
 */
class Request implements ContainerFactoryPluginInterface {

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $config;

  /**
   * HTTP Client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  private $httpClient;

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  private $logger;

  /**
   * Constructs a Request object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   Config factory.
   * @param \GuzzleHttp\ClientInterface $http_client
   *   HTTP client.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   Logger.
   */
  public function __construct(ConfigFactoryInterface $config, ClientInterface $http_client, LoggerChannelFactoryInterface $logger) {
    $this->config = $config;
    $this->httpClient = $http_client;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static();
  }

  /**
   * Send request to endpoint.
   *
   * @param string $endpoint
   *   Endpoint url.
   * @param array $payload
   *   An array of data to be sent in request body.
   *
   * @return int|null
   *   Response object OR FALSE for exceptions.
   *
   * @throws \GuzzleHttp\Exception\ServerException
   * @throws \GuzzleHttp\Exception\RequestException
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function sendRequest($endpoint, array $payload) {
    // @todo: apikey should have identifier added to queue item instead of one
    // key for all endpoints.
    $apikey = $this->config->get('content_push_api.settings')->get('apikey') ?: FALSE;
    $content_type_header = $this->config->get('content_push_api.settings')->get('header_content_type') ?: FALSE;

    $headers = [
      'apikey' => $apikey,
      'Content-Type' => $content_type_header,
    ];

    $sending_data = Json::encode($payload);

    try {
      $response = $this->httpClient->request('POST', $endpoint, [
        'headers' => $headers,
        'body' => $sending_data,
      ]);
      return $response->getStatusCode();
    }
    catch (ServerException $e) {
      watchdog_exception('content_push_api', $e);
      return FALSE;
    }
    catch (RequestException $e) {
      watchdog_exception('content_push_api', $e);
      return FALSE;
    }
    catch (\Exception $e) {
      watchdog_exception('content_push_api', $e);
      return FALSE;
    }
  }

}
