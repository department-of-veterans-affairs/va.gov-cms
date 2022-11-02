<?php

namespace Drupal\va_gov_workflow_assignments\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Utility\LinkGeneratorInterface;
use Drupal\Core\Link;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\TermInterface;
use Drupal\taxonomy\TermStorageInterface;

/**
 * A service which supplies section hierarchy breadcrumbs for nodes.
 */
class SectionHierarchyBreadcrumb implements SectionHierarchyBreadcrumbInterface {

  /**
   * The taxonomy term storage service.
   *
   * @var \Drupal\taxonomy\TermStorageInterface
   */
  protected $termStorage;

  /**
   * The link generator.
   *
   * `Link::toString()` requests `\Drupal::container()`, so we DI the link
   * generator service and call it directly to generate the link HTML.
   *
   * @var \Drupal\Core\Utility\LinkGeneratorInterface
   */
  protected $linkGenerator;

  /**
   * Constructs a new object.
   *
   * `Link::toString()` requests `\Drupal::container()`, so we DI the link
   * generator service and call it directly to generate the link HTML.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Used to retrieve the taxonomy term storage.
   * @param \Drupal\Core\Utility\LinkGeneratorInterface $linkGenerator
   *   Used to render HTML term links.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    LinkGeneratorInterface $linkGenerator
  ) {
    $this->termStorage = $entityTypeManager->getStorage('taxonomy_term');
    $this->linkGenerator = $linkGenerator;
  }

  /**
   * {@inheritDoc}
   */
  public function getHtmlLinks(NodeInterface $node): array {
    $ownerTerm = $this->getOwnerTerm($node);
    return $this->buildHtmlLinks($ownerTerm);
  }

  /**
   * Returns the "owner" term for this node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   A node with `field_administration`.
   *
   * @return \Drupal\taxonomy\TermInterface|null
   *   A valid section term.
   */
  public function getOwnerTerm(NodeInterface $node): ?TermInterface {
    /** @var \Drupal\Core\Field\EntityReferenceFieldItemListInterface $field_administration */
    $referencedTerms = $node->get('field_administration')->referencedEntities();
    return $referencedTerms[0] ?? NULL;
  }

  /**
   * Constructs a list of breadcrumb links from the term and its parents.
   *
   * @param \Drupal\taxonomy\TermInterface|null $term
   *   A section hierarchy term.
   *
   * @return string[]
   *   The generated links, if any, as an array of strings.
   */
  public function buildHtmlLinks(?TermInterface $term = null): array {
    if (is_null($term)) {
      return [];
    }
    $links = [
      $this->getTermHtmlLink($term),
      ... $this->getParentTermHtmlLinks($term),
    ];
    $links = array_reverse($links);
    return $links;
  }

  /**
   * Returns links to a term's parents.
   *
   * @param \Drupal\taxonomy\TermInterface $term
   *   A taxonomy term.
   *
   * @return string[]
   *   Links (as strings) to the term's parents.
   */
  public function getParentTermHtmlLinks(TermInterface $term): array {
    $callable = [
      $this,
      'getTermHtmlLink',
    ];
    $parents = $this->termStorage->loadAllParents($term->id());
    $parent_links = array_map($callable, $parents);
    return array_values($parent_links);
  }

  /**
   * Returns a section link as a fully-rendered HTML string.
   *
   * @param \Drupal\taxonomy\TermInterface $term
   *   A taxonomy term.
   *
   * @return string
   *   A section link.
   */
  public function getTermHtmlLink(TermInterface $term): string {
    $link = Link::fromTextAndUrl(
      t(':name', [
        ':name' => $term->getName(),
      ]),
      $term->toUrl(),
    );
    return $this->linkGenerator->generateFromLink($link);
  }

}
