<?php

namespace tests\phpunit\Content;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityTypeStorageInterface;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Url;
use Drupal\Core\Utility\LinkGeneratorInterface;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\TermInterface;
use Drupal\taxonomy\TermStorageInterface;
use Drupal\va_gov_backend\Service\ExclusionTypesInterface;
use Drupal\va_gov_backend\Service\VaGovUrlInterface;
use Drupal\va_gov_workflow_assignments\Plugin\Block\EntityMetaDisplay;
use Drupal\va_gov_workflow_assignments\Service\EditorialWorkflowContentRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Tests\Support\Classes\VaGovUnitTestBase;

/**
 * A test to confirm the proper functioning of the EntityMetaDisplay block.
 *
 * @group unit
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_workflow_assignments\Plugin\Block\EntityMetaDisplay
 */
class EntityMetaDisplayTest extends VaGovUnitTestBase {

  /**
   * Build a container containing all of the services we need :( .
   *
   * @return \Symfony\Component\DependencyInjection\ContainerInterface
   *   A container.
   */
  public function getContainer(
    RouteMatchInterface $routeMatch,
    EntityTypeManagerInterface $entityTypeManager,
    ExclusionTypesInterface $exclusionTypes,
    VaGovUrlInterface $vaGovUrl,
    EditorialWorkflowContentRepositoryInterface $ewcRepository,
    RendererInterface $renderer,
    LinkGeneratorInterface $linkGenerator
  ): ContainerInterface {
    $containerProphecy = $this->prophesize(ContainerInterface::CLASS);
    $containerProphecy->get('current_route_match')->willReturn($routeMatch);
    $containerProphecy->get('entity_type.manager')->willReturn($entityTypeManager);
    $containerProphecy->get('va_gov_backend.exclusion_types')->willReturn($exclusionTypes);
    $containerProphecy->get('va_gov_backend.va_gov_url')->willReturn($vaGovUrl);
    $containerProphecy->get('va_gov_workflow_assignments.editorial_workflow')->willReturn($ewcRepository);
    $containerProphecy->get('renderer')->willReturn($renderer);
    $containerProphecy->get('link_generator')->willReturn($linkGenerator);
    return $containerProphecy->reveal();
  }

  /**
   * Build a taxonomy term.
   */
  public function getTerm(int $tid, string $name, string $url) {
    $termProphecy = $this->prophesize(TermInterface::CLASS);
    $termProphecy->id()->willReturn($tid);
    $termProphecy->getName()->willReturn($name);
    $urlProphecy = $this->prophesize(Url::CLASS);
    $urlProphecy->toString()->willReturn($url);
    $urlObject = $urlProphecy->reveal();
    $termProphecy->toUrl()->willReturn($urlObject);
    return $termProphecy->reveal();
  }

  /**
   * Test ::build().
   *
   * @covers ::build
   * @dataProvider dataProviderBuild
   */
  public function testBuild(
    ?array $expected = NULL,
    ?NodeInterface $node = NULL,
    ?NodeInterface $node_revision = NULL,
    ?bool $isExcluded = FALSE,
    ?int $latestPublishedRevisionId = NULL,
    ?int $latestArchivedRevisionId = NULL,
    ?bool $isLive = FALSE
  ) {
    // `current_route_match`.
    $routeMatchProphecy = $this->prophesize(RouteMatchInterface::CLASS);
    if (!is_null($node)) {
      $routeMatchProphecy->getParameter('node')->willReturn($node);
    }
    if (!is_null($node_revision)) {
      $routeMatchProphecy->getParameter('node_revision')->willReturn($node_revision);
    }
    $routeMatch = $routeMatchProphecy->reveal();

    // `entity_type.manager`.
    $entityTypeStorageProphecy = $this->prophesize(EntityTypeStorageInterface::CLASS);
    $entityTypeStorageProphecy->willImplement(EntityStorageInterface::CLASS);
    $entityTypeProphecy = $this->prophesize(EntityTypeInterface::CLASS);
    $entityTypeProphecy->willImplement(EntityInterface::CLASS);
    $entityTypeProphecy->label()->willReturn('TEST');
    $entityType = $entityTypeProphecy->reveal();
    if (!is_null($node)) {
      $entityTypeStorageProphecy->load($node->bundle())->willReturn($entityType);
    }
    $entityTypeStorage = $entityTypeStorageProphecy->reveal($entityType);
    $entityTypeManagerProphecy = $this->prophesize(EntityTypeManagerInterface::CLASS);
    $entityTypeManagerProphecy->getStorage('node_type')->willReturn($entityTypeStorage);
    $termStorageProphecy = $this->prophesize(EntityStorageInterface::CLASS);
    $termStorageProphecy->willImplement(TermStorageInterface::CLASS);
    $termStorageProphecy->loadAllParents(Argument::any())->willReturn([
      $this->getTerm(1, 'Test Term 1', '/term/1'),
      $this->getTerm(2, 'Test Term 2', '/term/2'),
      $this->getTerm(3, 'Test Term 3', '/term/3'),
      $this->getTerm(4, 'Test Term 4', '/term/4'),
    ]);
    $termStorage = $termStorageProphecy->reveal();
    $entityTypeManagerProphecy->getStorage('taxonomy_term')->willReturn($termStorage);
    $entityTypeManager = $entityTypeManagerProphecy->reveal();

    // `va_gov_backend.exclusion_types`.
    $exclusionTypesProphecy = $this->prophesize(ExclusionTypesInterface::CLASS);
    $exclusionTypesProphecy->typeIsExcluded(Argument::type('string'))->willReturn($isExcluded);
    $exclusionTypes = $exclusionTypesProphecy->reveal();

    // `va_gov_backend.va_gov_url`.
    $vaGovUrlProphecy = $this->prophesize(VaGovUrlInterface::CLASS);
    $vaGovUrlProphecy->getVaGovFrontEndUrlForEntity($node)->willReturn('https://www.example.org/');
    $vaGovUrlProphecy->vaGovFrontEndUrlForEntityIsLive($node)->willReturn($isLive);
    $vaGovUrl = $vaGovUrlProphecy->reveal();

    // `va_gov_workflow_assignments.editorial_workflow`.
    $ewcRepositoryProphecy = $this->prophesize(EditorialWorkflowContentRepositoryInterface::CLASS);
    $ewcRepositoryProphecy->getLatestPublishedRevisionId($node)->willReturn((int) $latestPublishedRevisionId);
    $ewcRepositoryProphecy->getLatestArchivedRevisionId($node)->willReturn((int) $latestArchivedRevisionId);
    $ewcRepository = $ewcRepositoryProphecy->reveal();

    // `renderer`.
    $rendererProphecy = $this->prophesize(RendererInterface::CLASS);
    $renderer = $rendererProphecy->reveal();

    // `link_generator`.
    $linkGeneratorProphecy = $this->prophesize(LinkGeneratorInterface::CLASS);
    $linkGenerator = $linkGeneratorProphecy->reveal();

    $container = $this->getContainer(
      $routeMatch,
      $entityTypeManager,
      $exclusionTypes,
      $vaGovUrl,
      $ewcRepository,
      $renderer,
      $linkGenerator
    );
    $entityMetaDisplay = EntityMetaDisplay::create($container, [], 'entity_metadata_display', [
      'provider' => 'deez tests',
    ]);
    $translationProphecy = $this->prophesize(TranslationInterface::CLASS);
    $translation = $translationProphecy->reveal();
    $entityMetaDisplay->setStringTranslation($translation);
    $actual = $entityMetaDisplay->build();
    $this->assertEquals($expected, $actual);
  }

  /**
   * Data Provider for testBuild().
   */
  public function dataProviderBuild() {
    $nodeProphecy = $this->prophesize(NodeInterface::CLASS);
    $nodeTypeProphecy = $this->prophesize(EntityTypeInterface::CLASS);
    $nodeType = $nodeTypeProphecy->reveal();
    $nodeProphecy->bundle()->willReturn('test_bundle');
    $nodeProphecy->getEntityType()->willReturn($nodeType);
    $term = $this->getTerm(0, 'Test Term 0', 'https://www.va.gov/term/0');
    $fieldItemListProphecy = $this->prophesize(EntityReferenceFieldItemListInterface::CLASS);
    $fieldItemListProphecy->referencedEntities()->willReturn([
      $term,
    ]);
    $fieldItemList = $fieldItemListProphecy->reveal();
    $nodeProphecy->get('field_administration')->willReturn($fieldItemList);
    $node = $nodeProphecy->reveal();

    $nodeRevisionProphecy = $this->prophesize(NodeInterface::CLASS);
    $nodeRevisionProphecy->getEntityType()->willReturn($nodeType);
    $nodeRevisionProphecy->get('field_administration')->willReturn($fieldItemList);
    $nodeRevision = $nodeRevisionProphecy->reveal();

    return [
      // If we don't have a node path parameter, we shouldn't return anything.
      [],
      // A minimally valid non-live node.
      [
        [
          '#attached' => [
            'library' => [
              0 => 'va_gov_workflow_assignments/ewa_style',
            ],
          ],
          '#cache' => [
            'max-age' => 300,
          ],
          '#markup' => '<div><span class="va-gov-entity-meta__title"><strong>Owner: </strong></span><span class="va-gov-entity-meta__content"> »  » </span></div>',
        ],
        $node,
        $node,
        FALSE,
        457,
      ],
      // A minimally valid live node.
      [
        [
          '#markup' => '<div><span class="va-gov-entity-meta__title"><strong>Owner: </strong></span><span class="va-gov-entity-meta__content"> »  » </span></div>',
        ],
        $node,
        $node,
        TRUE,
        457,
      ],
      // A minimally valid live node.
      [
        [
          '#markup' => '<div><span class="va-gov-entity-meta__title"><strong>Owner: </strong></span><span class="va-gov-entity-meta__content"> »  » </span></div>',
        ],
        $node,
        $nodeRevision,
        TRUE,
        457,
      ],
    ];
  }

}
