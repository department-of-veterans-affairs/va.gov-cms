<?php

namespace Drupal\va_gov_api\Plugin\jsonapi\FieldEnhancer;

use Drupal\jsonapi_extras\Plugin\jsonapi\FieldEnhancer\UrlLinkEnhancer;
use GuzzleHttp\Psr7\Uri;
use Shaper\Util\Context;

/**
 * Add URL aliases to links, ensuring trailing slash for node links.
 *
 * VA.gov canonical URLs use trailing slashes. When editors create a link using
 * a node reference (stored as `entity:node/{nid}`), Drupal resolves it to a
 * path alias without a trailing slash. This enhancer adds the trailing slash
 * only for `entity:node/*` URIs.
 *
 * @ResourceFieldEnhancer(
 *   id = "va_gov_url_link",
 *   label = @Translation("VA.gov URL for link (link field only)"),
 *   description = @Translation("Use Url for link fields, adding trailing slash for entity:node links.")
 * )
 */
class VaGovUrlLinkEnhancer extends UrlLinkEnhancer {

  /**
   * {@inheritdoc}
   */
  protected function doUndoTransform($data, Context $context) {
    $data = parent::doUndoTransform($data, $context);

    if (isset($data['uri'], $data['url']) && is_string($data['uri']) && is_string($data['url'])) {
      $data['url'] = $this->ensureTrailingSlashForNodeLinks($data['uri'], $data['url']);
    }

    return $data;
  }

  /**
   * Ensures node entity URLs have a trailing slash.
   *
   * This is intentionally conservative: it only modifies resolved URLs that
   * originate from a node entity reference (entity:node/{nid}).
   */
  protected function ensureTrailingSlashForNodeLinks(string $uri, string $url): string {
    if (!str_starts_with($uri, 'entity:node/')) {
      return $url;
    }

    try {
      $urlObject = new Uri($url);
    }
    catch (\InvalidArgumentException $e) {
      return $url;
    }

    $path = $urlObject->getPath();

    // If the URL has no path or already ends in a slash, do nothing.
    if ($path === '' || str_ends_with($path, '/')) {
      return $url;
    }

    return (string) $urlObject->withPath($path . '/');
  }

}
