<?php

namespace Drupal\va_gov_content_release\FrontendVersionSearch;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\va_gov_content_release\Frontend\FrontendInterface;
use Drupal\va_gov_git\BranchSearch\BranchSearchInterface;
use Drupal\va_gov_github\Api\Client\ApiClientInterface;

/**
 * The FrontendVersion service.
 *
 * This service allows (in some environments) control over the version of the
 * frontend that is used to perform content releases.
 */
class FrontendVersionSearch implements FrontendVersionSearchInterface {

  /**
   * The branch search service for `content-build`.
   *
   * @var \Drupal\va_gov_git\BranchSearch\BranchSearchInterface
   */
  protected $cbBranchSearch;

  /**
   * The API client for `content-build`.
   *
   * @var \Drupal\va_gov_github\Api\Client\ApiClientInterface
   */
  protected $cbApiClient;

  /**
   * The branch search service for `vets-website`.
   *
   * @var \Drupal\va_gov_git\BranchSearch\BranchSearchInterface
   */
  protected $vwBranchSearch;

  /**
   * The API client for `vets-website`.
   *
   * @var \Drupal\va_gov_github\Api\Client\ApiClientInterface
   */
  protected $vwApiClient;

  /**
   * The logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructor.
   *
   * @param \Drupal\va_gov_git\BranchSearch\BranchSearchInterface $cbBranchSearch
   *   The branch search service for `content-build`.
   * @param \Drupal\va_gov_github\Api\Client\ApiClientInterface $cbApiClient
   *   The API client for `content-build`.
   * @param \Drupal\va_gov_git\BranchSearch\BranchSearchInterface $vwBranchSearch
   *   The branch search service for `vets-website`.
   * @param \Drupal\va_gov_github\Api\Client\ApiClientInterface $vwApiClient
   *   The API client for `vets-website`.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   The logger factory service.
   */
  public function __construct(
    BranchSearchInterface $cbBranchSearch,
    ApiClientInterface $cbApiClient,
    BranchSearchInterface $vwBranchSearch,
    ApiClientInterface $vwApiClient,
    LoggerChannelFactoryInterface $loggerFactory
  ) {
    $this->cbBranchSearch = $cbBranchSearch;
    $this->cbApiClient = $cbApiClient;
    $this->vwBranchSearch = $vwBranchSearch;
    $this->vwApiClient = $vwApiClient;
    $this->logger = $loggerFactory->get('va_gov_content_release');
  }

  /**
   * Get the branch search service for the given frontend.
   *
   * @param \Drupal\va_gov_content_release\Frontend\FrontendInterface $frontend
   *   The frontend.
   *
   * @return \Drupal\va_gov_git\BranchSearch\BranchSearchInterface
   *   The branch search service.
   */
  protected function getBranchSearch(FrontendInterface $frontend) : BranchSearchInterface {
    switch (TRUE) {
      case $frontend->isContentBuild():
        return $this->cbBranchSearch;

      case $frontend->isVetsWebsite():
        return $this->vwBranchSearch;

      default:
        throw new \InvalidArgumentException('Invalid frontend: ' . $frontend->getRawValue());
    }
  }

  /**
   * Get the API client for the given frontend.
   *
   * @param \Drupal\va_gov_content_release\Frontend\FrontendInterface $frontend
   *   The frontend.
   *
   * @return \Drupal\va_gov_github\Api\Client\ApiClientInterface
   *   The API client.
   */
  protected function getApiClient(FrontendInterface $frontend) : ApiClientInterface {
    switch (TRUE) {
      case $frontend->isContentBuild():
        return $this->cbApiClient;

      case $frontend->isVetsWebsite():
        return $this->vwApiClient;

      default:
        throw new \InvalidArgumentException('Invalid frontend: ' . $frontend->getRawValue());
    }
  }

  /**
   * {@inheritDoc}
   */
  public function getMatchingReferences(FrontendInterface $frontend, string $query, int $count) : array {
    $results = [];
    $branches = $this->getMatchingBranches($frontend, $query);
    for ($i = 0; $i < $count; $i++) {
      if (!empty($branches[$i])) {
        $results[] = [
          'label' => "BRANCH {$branches[$i]}",
          'value' => "BRANCH {$branches[$i]} ({$branches[$i]})",
        ];
      }
    }

    $pullRequests = $this->getMatchingPullRequests($frontend, $query);
    for ($i = 0; $i < $count; $i++) {
      if (!empty($pullRequests['items'][$i])) {
        $item = $pullRequests['items'][$i];
        $results[] = [
          'label' => "PR {$item['number']} ({$item['title']})",
          'value' => "PR {$item['number']} - {$item['title']} ({$item['number']})",
        ];
      }
    }

    return $results;
  }

  /**
   * {@inheritDoc}
   */
  public function getMatchingBranches(FrontendInterface $frontend, string $query) : array {
    try {
      return $this->getBranchSearch($frontend)->getRemoteBranchNamesContaining($query);
    }
    catch (\Throwable $exception) {
      $this->logger->error('Error searching for branches: @message', [
        '@message' => $exception->getMessage(),
      ]);
      return [];
    }
  }

  /**
   * {@inheritDoc}
   */
  public function getMatchingPullRequests(FrontendInterface $frontend, string $query) : array {
    $results = [];

    try {
      $results = $this->getApiClient($frontend)->searchPullRequests($query);
    }
    catch (\Throwable $exception) {
      $this->logger->error('Error searching for pull requests: @message', [
        '@message' => $exception->getMessage(),
      ]);
    }

    return $results;
  }

}
