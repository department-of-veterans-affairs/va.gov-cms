<?php

namespace tests\phpunit\Content;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Url;
use Drupal\Core\Utility\LinkGeneratorInterface;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\TermInterface;
use Drupal\taxonomy\TermStorageInterface;
use Drupal\va_gov_workflow_assignments\Service\SectionHierarchyBreadcrumb;
use Drupal\va_gov_workflow_assignments\Service\SectionHierarchyBreadcrumbInterface;
use Prophecy\Argument;
use Tests\Support\Classes\VaGovUnitTestBase;

/**
 * Tests for the SectionHierarchyBreadcrumb class.
 *
 * @group unit
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_workflow_assignments\Service\SectionHierarchyBreadcrumb
 */
class SectionHierarchyBreadcrumbTest extends VaGovUnitTestBase {

  /**
   * Provide a TermInterface prophecy.
   *
   * The SectionHierarchyBreadcrumb service relies upon the ::getName() and
   * ::toUrl() methods of Term, so their precursors are required here.
   *
   * @param int $tid
   *   Term ID.
   * @param string $name
   *   Term name: something vaguely official.
   * @param string $url
   *   An absolute URL, so we don't trigger Drupal's bootstrap.
   */
  public function getTermProphecy(int $tid, string $name, string $url) {
    $prophecy = $this->prophesize(TermInterface::CLASS);
    $prophecy->id()->willReturn($tid);
    $prophecy->getName()->willReturn($name);
    $prophecy->toUrl()->willReturn(Url::fromUri($url));
    return $prophecy;
  }

  /**
   * Provide an SectionHierarchyBreadcrumb prophecy.
   */
  public function getSectionHierarchyBreadcrumbService(
    ?EntityTypeManagerInterface $entityTypeManager = NULL,
    ?LinkGeneratorInterface $linkGenerator = NULL,
    ?TranslationInterface $stringTranslation = NULL
  ): SectionHierarchyBreadcrumbInterface {
    $entityTypeManager = $entityTypeManager ?? $this->getEntityTypeManagerProphecy()->reveal();
    $linkGenerator = $linkGenerator ?? $this->getLinkGeneratorProphecy()->reveal();
    $stringTranslation = $stringTranslation ?? $this->getStringTranslationProphecy()->reveal();
    return new SectionHierarchyBreadcrumb($entityTypeManager, $linkGenerator, $stringTranslation);
  }

  /**
   * Get a Taxonomy Term storage prophecy.
   *
   * @param \Drupal\taxonomy\TermInterface[] $terms
   *   A list of terms to return in response to `::loadAllParents()`.
   */
  public function getTermStorageProphecy(array $terms = []) {
    $prophecy = $this->prophesize(TermStorageInterface::CLASS);
    $prophecy
      ->loadAllParents(Argument::any())
      ->willReturn($terms);
    return $prophecy;
  }

  /**
   * Get the EntityTypeManagerInterface prophecy.
   */
  public function getEntityTypeManagerProphecy(TermStorageInterface $termStorage = NULL) {
    $termStorage = $termStorage ?? $this->getTermStorageProphecy()->reveal();
    $prophecy = $this->prophesize(EntityTypeManagerInterface::CLASS);
    $prophecy
      ->getStorage(Argument::any())
      ->willReturn($termStorage);
    return $prophecy;
  }

  /**
   * Get a fake node.
   */
  public function getNodeProphecy(TermInterface $section) {
    $prophecy = $this->prophesize(NodeInterface::CLASS);

    $fieldAdministrationProphecy = $this->prophesize(EntityReferenceFieldItemListInterface::CLASS);
    $fieldAdministrationProphecy->referencedEntities()->willReturn([$section]);
    $fieldAdministration = $fieldAdministrationProphecy->reveal();

    $prophecy->get(Argument::type('string'))
      ->willReturn($fieldAdministration);
    return $prophecy;
  }

  /**
   * Get the LinkGeneratorInterface prophecy.
   *
   * @param string[] $htmlLinks
   *   HTML links in the order in which they should be returned.
   */
  public function getLinkGeneratorProphecy(array $htmlLinks = NULL) {
    $htmlLinks = $htmlLinks ?? [
      '<a href="https://www.example.com/">TEST1</a>',
      '<a href="https://www.example.org/">TEST2</a>',
      '<a href="https://www.example.net/">TEST3</a>',
      '<a href="https://www.example.co.uk/">TEST4</a>',
    ];
    $prophecy = $this->prophesize(LinkGeneratorInterface::CLASS);
    // Unfortunately, Drupal::$container will be invoked at multiple stages of
    // the link generation process, so this is difficult to mock accurately.
    // Just rest assured that $linkGenerator->generateFromLink() is being
    // called.
    $prophecy
      ->generateFromLink(Argument::any())
      ->willReturn(... $htmlLinks);
    return $prophecy;
  }

  /**
   * Get the TranslationInterface prophecy.
   */
  public function getStringTranslationProphecy() {
    $prophecy = $this->prophesize(TranslationInterface::CLASS);
    return $prophecy;
  }

  /**
   * Test ::getTermLinkHtml().
   *
   * @covers ::getTermLinkHtml
   */
  public function testGetTermLinkHtml() {
    $service = $this->getSectionHierarchyBreadcrumbService();
    $termProphecy = $this->getTermProphecy(3, 'TEST', 'https://www.example.com/');
    $term = $termProphecy->reveal();
    $this->assertEquals($service->getTermLinkHtml($term), '<a href="https://www.example.com/">TEST1</a>');
  }

  /**
   * Test ::getParentTermLinksHtml().
   *
   * @covers ::getParentTermLinksHtml
   */
  public function testGetParentTermLinksHtml() {
    $service = $this->getSectionHierarchyBreadcrumbService();
    $termProphecy = $this->getTermProphecy(3, 'TEST', 'https://www.example.com/');
    $term = $termProphecy->reveal();
    $this->assertEquals($service->getParentTermLinksHtml($term), []);
  }

  /**
   * Test ::getParentTermLinksHtml().
   *
   * @covers ::getParentTermLinksHtml
   */
  public function testGetParentTermLinksHtml2() {
    $termProphecy = $this->getTermProphecy(3, 'TEST', 'https://www.example.com/');
    $term = $termProphecy->reveal();
    $terms = [
      $this->getTermProphecy(4, 'TEST1', 'https://www.example.com/')->reveal(),
      $this->getTermProphecy(5, 'TEST2', 'https://www.example.org/')->reveal(),
      $this->getTermProphecy(6, 'TEST3', 'https://www.example.net/')->reveal(),
    ];
    $termStorage = $this->getTermStorageProphecy($terms)->reveal();
    $entityTypeManager = $this->getEntityTypeManagerProphecy($termStorage)->reveal();
    $service = $this->getSectionHierarchyBreadcrumbService($entityTypeManager);
    $this->assertEquals($service->getParentTermLinksHtml($term), [
      '<a href="https://www.example.com/">TEST1</a>',
      '<a href="https://www.example.org/">TEST2</a>',
      '<a href="https://www.example.net/">TEST3</a>',
    ]);
  }

  /**
   * Test ::buildLinksHtml().
   *
   * @covers ::buildLinksHtml
   */
  public function testBuildLinksHtml() {
    $termProphecy = $this->getTermProphecy(3, 'TEST', 'https://www.example.com/');
    $term = $termProphecy->reveal();
    $terms = [
      $this->getTermProphecy(4, 'TEST2', 'https://www.example.org/')->reveal(),
      $this->getTermProphecy(5, 'TEST3', 'https://www.example.net/')->reveal(),
      $this->getTermProphecy(6, 'TEST4', 'https://www.example.co.uk/')->reveal(),
    ];
    $termStorage = $this->getTermStorageProphecy($terms)->reveal();
    $entityTypeManager = $this->getEntityTypeManagerProphecy($termStorage)->reveal();
    $service = $this->getSectionHierarchyBreadcrumbService($entityTypeManager);
    // Note reverse order!
    $this->assertEquals($service->buildLinksHtml($term), [
      '<a href="https://www.example.net/">TEST3</a>',
      '<a href="https://www.example.org/">TEST2</a>',
      '<a href="https://www.example.com/">TEST1</a>',
    ]);
  }

  /**
   * Test ::getOwnerTerm().
   *
   * @covers ::getOwnerTerm
   */
  public function testGetOwnerTerm() {
    $termProphecy = $this->getTermProphecy(3, 'TEST', 'https://www.example.com/');
    $term = $termProphecy->reveal();
    $nodeProphecy = $this->getNodeProphecy($term);
    $node = $nodeProphecy->reveal();
    $service = $this->getSectionHierarchyBreadcrumbService();
    $this->assertEquals($service->getOwnerTerm($node), $term);
  }

  /**
   * Test ::getLinksHtml().
   *
   * @covers ::getLinksHtml
   */
  public function testGetLinksHtml() {
    $termProphecy = $this->getTermProphecy(3, 'TEST', 'https://www.example.com/');
    $term = $termProphecy->reveal();
    $nodeProphecy = $this->getNodeProphecy($term);
    $node = $nodeProphecy->reveal();
    $terms = [
      $this->getTermProphecy(4, 'TEST2', 'https://www.example.org/')->reveal(),
      $this->getTermProphecy(5, 'TEST3', 'https://www.example.net/')->reveal(),
      $this->getTermProphecy(6, 'TEST4', 'https://www.example.co.uk/')->reveal(),
    ];
    $termStorage = $this->getTermStorageProphecy($terms)->reveal();
    $entityTypeManager = $this->getEntityTypeManagerProphecy($termStorage)->reveal();
    $service = $this->getSectionHierarchyBreadcrumbService($entityTypeManager);
    // Note reverse order!
    $this->assertEquals($service->getLinksHtml($node), [
      '<a href="https://www.example.net/">TEST3</a>',
      '<a href="https://www.example.org/">TEST2</a>',
      '<a href="https://www.example.com/">TEST1</a>',
    ]);
  }

}
