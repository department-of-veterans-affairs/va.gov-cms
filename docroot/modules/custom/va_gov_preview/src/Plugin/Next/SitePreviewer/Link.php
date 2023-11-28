<?php

namespace Drupal\va_gov_preview\Plugin\Next\SitePreviewer;

use Drupal\Core\Entity\EntityInterface;
use Drupal\next\Plugin\SitePreviewerBase;

/**
 * Provides a link to the preview page.
 *
 * @SitePreviewer(
 *  id = "link",
 *  label = "Link to preview",
 *  description = "Displays a link to the preview page."
 * )
 */
class Link extends SitePreviewerBase {

  /**
   * {@inheritdoc}
   */
  public function render(EntityInterface $entity, array $sites) {
    $build = [];

    foreach ($sites as $site) {
      $build[] = [
        '#type' => 'link',
        '#title' => $this->t('Preview'),
        '#url' => $site->getPreviewUrlForEntity($entity),
        '#attributes' => [
          'class' => ['button', 'button--primary', 'node-preview-button'],
          'target' => '_blank',
        ],
      ];
    }

    return $build;
  }

}
