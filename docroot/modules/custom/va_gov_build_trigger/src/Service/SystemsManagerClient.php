<?php

namespace Drupal\va_gov_build_trigger\Service;

use Aws\Ssm\SsmClient;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * A client for interfacing with AWS Systems Manager.
 */
class SystemsManagerClient implements SystemsManagerClientInterface {

  use StringTranslationTrait;

  /**
   * The string translation service.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface
   */
  protected $stringTranslation;

  /**
   * The raw AWS SSM client.
   *
   * @var \Aws\Ssm\SsmClient
   */
  protected $ssmClient;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $stringTranslation
   *   The string translation service.
   * @param \Aws\Ssm\SsmClient $ssmClient
   *   The raw SSM client.
   */
  public function __construct(TranslationInterface $stringTranslation, SsmClient $ssmClient) {
    $this->stringTranslation = $stringTranslation;
    $this->ssmClient = $ssmClient;
  }

  /**
   * {@inheritdoc}
   */
  public function getJenkinsApiToken(): string {
    try {
      $result = $this->ssmClient->getParameter([
        'Name' => '/cms/va-cms-bot/jenkins-api-token',
        'WithDecryption' => TRUE,
      ]);
      return $result['Parameter']['Value'];
    }
    catch (\Exception $exception) {
      $message = $this->t('Failed to retrieve the Jenkins API token.  The error encountered was @message', [
        '@message' => $exception->getMessage(),
      ]);
      throw new \Exception($message);
    }
  }

}
