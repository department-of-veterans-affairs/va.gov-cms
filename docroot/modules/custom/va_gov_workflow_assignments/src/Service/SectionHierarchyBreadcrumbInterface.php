<?php

namespace Drupal\va_gov_workflow_assignments\Service;

use Drupal\node\NodeInterface;

/**
 * Describes a service that can retrieve section hierarchy breadcrumbs.
 */
interface SectionHierarchyBreadcrumbInterface {

  /**
   * Retrieves breadcrumb links.
   *
   * These will be returned in order of proximity, i.e.
   *
   * [
   *   <parent-link>,
   *   <grandparent-link>,
   *   <great-grandparent-link>,
   *   ...
   * ]
   *
   * The individual links will be returned as a string like:
   *
   * <a href="/section/vha/vamc-facilities/va-huntington-health-care"
   *   hreflang="en">VA Huntington health care</a>
   *
   * corresponding to the output of \Drupal\Core\Link::toString().
   *
   * @param \Drupal\node\NodeInterface $node
   *   A node with the `field_administration` field.
   *
   * @return string[]
   *   An array of links, as HTML strings.
   */
  public function getLinksHtml(NodeInterface $node): array;

}
