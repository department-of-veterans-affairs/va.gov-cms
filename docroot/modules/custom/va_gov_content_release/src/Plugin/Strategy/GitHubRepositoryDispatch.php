<?php

namespace Drupal\va_gov_content_release\Plugin\Strategy;

use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\va_gov_content_release\Exception\ContentReleaseInProgressException;
use Drupal\va_gov_content_release\Exception\StrategyErrorException;
use Drupal\va_gov_content_release\GitHub\GitHubRepositoryDispatchInterface;
use Drupal\va_gov_content_release\Reporter\ReporterInterface;
use Drupal\va_gov_content_release\Strategy\Plugin\StrategyPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * GitHub Repository Dispatch strategy.
 *
 * This sends a "repository dispatch" event to GitHub, if there is not already
 * a release in progress.
 *
 * @ContentReleaseStrategy(
 *   id = "github_repository_dispatch",
 *   label = @Translation("GitHub Repository Dispatch")
 * )
 */
class GitHubRepositoryDispatch extends StrategyPluginBase {

  const JOB_LINK = 'https://github.com/department-of-veterans-affairs/content-build/actions/workflows/content-release.yml';

  /**
   * Github repository dispatch service.
   *
   * @var \Drupal\va_gov_content_release\GitHub\GitHubRepositoryDispatchInterface
   */
  protected $gitHubRepositoryDispatch;

  /**
   * {@inheritDoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ReporterInterface $reporter,
    TranslationInterface $stringTranslation,
    GitHubRepositoryDispatchInterface $gitHubRepositoryDispatch
  ) {
    parent::__construct(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $reporter,
      $stringTranslation
    );
    $this->gitHubRepositoryDispatch = $gitHubRepositoryDispatch;
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
      $container->get('va_gov_content_release.github_repository_dispatch')
    );
  }

  /**
   * Build a message to display when the release is submitted.
   *
   * @return string
   *   The message.
   */
  public function buildSubmittedMessage(): string {
    return $this->t('The system started the process of releasing this content to go live on VA.gov. <a href="@job_link">Check status</a>.', [
      '@job_link' => static::JOB_LINK,
    ]);
  }

  /**
   * Build a message to display when a release is already running.
   *
   * @return string
   *   The message.
   */
  public function buildAlreadyInProgressMessage(): string {
    return $this->t('Changes will be included in a content release to VA.gov that\'s already in progress. <a href="@job_link">Check status</a>.', [
      '@job_link' => static::JOB_LINK,
    ]);
  }

  /**
   * Build a message to display when the release fails.
   *
   * @return string
   *   The error message.
   */
  public function buildFailureMessage(): string {
    return $this->t('A content release request has failed with an Exception. Please visit <a href="@job_link">@job_link</a> for more information on the issue. If this is the PROD environment please notify in #cms-support Slack and please email support@va-gov.atlassian.net immediately with the error message you see here.', [
      '@job_link' => static::JOB_LINK,
    ]);
  }

  /**
   * {@inheritDoc}
   */
  public function triggerContentRelease() : void {
    try {
      $this->gitHubRepositoryDispatch->submit();
      $this->reporter->reportInfo($this->buildSubmittedMessage());
    }
    catch (ContentReleaseInProgressException $exception) {
      $this->reporter->reportInfo($this->buildAlreadyInProgressMessage());
      // Do not rethrow the exception, as this is not an error.
    }
    catch (\Throwable $exception) {
      $this->reporter->reportError($this->buildFailureMessage(), $exception);
      throw new StrategyErrorException('Content release failed.', $exception->getCode(), $exception);
    }
  }

}
