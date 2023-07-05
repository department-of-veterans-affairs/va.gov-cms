<?php

namespace Drupal\va_gov_content_release\Plugin\Strategy;

use Drupal\va_gov_content_release\Exception\StrategyErrorException;
use Drupal\va_gov_content_release\LocalFilesystem\LocalFilesystemBuildFileInterface;
use Drupal\va_gov_content_release\Strategy\Plugin\StrategyPluginBase;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\va_gov_content_release\Reporter\ReporterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Local filesystem build file strategy.
 *
 * This places a text file in the local filesystem, if there is not already
 * one present.
 *
 * @ContentReleaseStrategy(
 *   id = "local_filesystem_build_file",
 *   label = @Translation("Local Filesystem Build File")
 * )
 */
class LocalFilesystemBuildFile extends StrategyPluginBase {

  /**
   * Filesystem service.
   *
   * @var \Drupal\va_gov_content_release\LocalFilesystem\LocalFilesystemBuildFileInterface
   */
  protected $localFilesystemBuildFile;

  /**
   * {@inheritDoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ReporterInterface $reporter,
    TranslationInterface $stringTranslation,
    LocalFilesystemBuildFileInterface $localFilesystemBuildFile
  ) {
    parent::__construct(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $reporter,
      $stringTranslation
    );
    $this->localFilesystemBuildFile = $localFilesystemBuildFile;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('va_gov_content_release.reporter'),
      $container->get('string_translation'),
      $container->get('va_gov_content_release.local_filesystem_build_file')
    );
  }

  /**
   * Build a message to display when the release is submitted.
   *
   * @return string
   *   The message.
   */
  public function buildSubmittedMessage(): string {
    return $this->t('A content release request has been submitted.');
  }

  /**
   * Build a message to display when the release fails.
   *
   * @return string
   *   The error message.
   */
  public function buildFailureMessage(): string {
    return $this->t('A content release request has failed.');
  }

  /**
   * {@inheritDoc}
   */
  public function triggerContentRelease() : void {
    try {
      $this->localFilesystemBuildFile->submit();
      $this->reporter->reportInfo($this->buildSubmittedMessage());
    }
    catch (\Throwable $exception) {
      $this->reporter->reportError($exception->getMessage(), $exception);
      throw new StrategyErrorException('A content release request has failed.', $exception->getCode(), $exception);
    }
  }

}
