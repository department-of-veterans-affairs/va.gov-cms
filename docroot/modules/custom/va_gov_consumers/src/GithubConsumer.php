<?php

namespace Drupal\va_gov_consumers;

use Drupal\Core\Link;
use Drupal\Core\Url;
use GuzzleHttp\Client;
use GuzzleHttp\Exception as GuzzleException;
use League\CommonMark\CommonMarkConverter;
use Drupal\Core\Cache\CacheBackendInterface;

/**
 * For consuming github repos.
 */
class GithubConsumer {

  /**
   * Pre render for main content block.
   *
   * @param string $data_source
   *   The url to consume.
   * @param string $attribution_source
   *   The repo link for the footer.
   */
  public function contentRender($data_source, $attribution_source = NULL) {
    $build = [];

    // We want to cache so we aren't making a new request every page load.
    if (empty(\Drupal::cache()->get('github_content'))) {
      $text = 'Text is not available.';
      $client = new Client();
      // Make sure we get a good response before heavy lifting.
      try {
        $response = $client->get($data_source);

        if ($response->getStatusCode() === 200) {
          // Convert our markdown to html.
          $data = $response->getBody()->getContents();
          $converter = new CommonMarkConverter();
          $text = $converter->convertToHtml($data);

          // Add link to repo to encourage participation.
          $footer = t('This guide is maintained by an external Github repository.');
          $link = Link::fromTextAndUrl($footer, Url::fromUri($attribution_source, ['attributes' => ['target' => '_blank']]))->toString();

          $text .= $link;
        }

      }
      // Record any trouble to watchdog.
      catch (GuzzleException $e) {
        watchdog_exception('github_content', $e->getMessage());
      }

      $build['content']['#markup'] = $text;
      \Drupal::cache()->set('github_content', $text, CacheBackendInterface::CACHE_PERMANENT);

    }
    else {
      // Already cached, so load our data.
      $cache = \Drupal::cache()->get('github_content');
      $build['content']['#markup'] = $cache->data;

    }
    return $build;

  }

}
