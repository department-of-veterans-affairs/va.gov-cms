<?php

namespace tests\phpunit\Content;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\NodeInterface;
use Drupal\va_gov_backend\Service\ExclusionTypesInterface;
use Drupal\va_gov_backend\Service\VaGovUrlInterface;
use Drupal\va_gov_workflow_assignments\Plugin\Block\EntityMetaDisplay;
use Drupal\va_gov_workflow_assignments\Service\EditorialWorkflowContentRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;


use Drupal\Core\Site\Settings;
use Drupal\Core\Url;
use Drupal\va_gov_backend\Service\VaGovUrl;
use Drupal\va_gov_build_trigger\Environment\EnvironmentDiscovery;
use GuzzleHttp\ClientInterface;
use Prophecy\Argument;
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

}
