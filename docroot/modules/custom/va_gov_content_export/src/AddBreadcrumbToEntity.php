<?php

namespace Drupal\va_gov_content_export;

use Drupal;
use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\ChainBreadcrumbBuilderInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\Exception\UndefinedLinkTemplateException;
use Drupal\Core\ParamConverter\ParamConverterManagerInterface;
use Drupal\Core\Routing\RouteMatch;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\graphql\GraphQL\Buffers\SubRequestBuffer;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;

/**
 * Class AddBreadcrumbToEntity.
 *
 * @package Drupal\va_gov_content_export
 */
class AddBreadcrumbToEntity {
  /**
   * The breadcrumbBuilder service.
   *
   * @var \Drupal\Core\Breadcrumb\ChainBreadcrumbBuilderInterface
   */
  protected $breadcrumbBuilder;

  /**
   * The routeProvider service.
   *
   * @var \Drupal\Core\Routing\RouteProviderInterface
   */
  protected $routeProvider;

  /**
   * The paramConverterManager service.
   *
   * @var \Drupal\Core\ParamConverter\ParamConverterManagerInterface
   */
  protected $paramConverterManager;

  /**
   * The subrequest buffer service.
   *
   * @var \Drupal\graphql\GraphQL\Buffers\SubRequestBuffer
   */
  protected $subRequestBuffer;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * An array of excluded entity types.
   *
   * @var string[]
   */
  protected static $excludedTypes = [
    'content_moderation_state',
    'paragraph',
    'file',
  ];

  /**
   * AddBreadcrumbToEntity constructor.
   *
   * @param \Drupal\Core\Breadcrumb\ChainBreadcrumbBuilderInterface $breadcrumbBuilder
   *   The breadcrumbBuilder service.
   * @param \Drupal\Core\Routing\RouteProviderInterface $routeProvider
   *   The routeProvider service.
   * @param \Drupal\Core\ParamConverter\ParamConverterManagerInterface $paramConverterManager
   *   The paramConverterManger service.
   * @param \Drupal\graphql\GraphQL\Buffers\SubRequestBuffer $subRequestBuffer
   *   The sub-request buffer service.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The current route match.
   */
  public function __construct(
    ChainBreadcrumbBuilderInterface $breadcrumbBuilder,
    RouteProviderInterface $routeProvider,
    ParamConverterManagerInterface $paramConverterManager,
    SubRequestBuffer $subRequestBuffer,
    RouteMatchInterface $routeMatch
  ) {
    $this->breadcrumbBuilder = $breadcrumbBuilder;
    $this->routeProvider = $routeProvider;
    $this->paramConverterManager = $paramConverterManager;
    $this->subRequestBuffer = $subRequestBuffer;
    $this->routeMatch = $routeMatch;
  }

  /**
   * Alter the entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity to alter.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\ParamConverter\ParamNotConvertedException
   */
  public function alterEntity(ContentEntityInterface $entity) : void {
    if (!$this->shouldEntityBeAltered($entity)) {
      return;
    }

    try {
      $routeName = $entity->toUrl()->getRouteName();
    }
    catch (UndefinedLinkTemplateException $e) {
      Drupal::logger('VA-CMS-EXPORT')->notice('URL not supported for %entity_type', ['%entity_type' => $entity->getEntityTypeId()]);
      return;
    }

    if (!$this->doesRouteMatch($routeName)) {
      return;
    }

    $breadcrumbs = $this->getBreadCrumbForEntity($entity);
    if ($breadcrumbs) {
      $this->addBreadCrumbToEntity($entity, $breadcrumbs);
    }
  }

  /**
   * Should the entity be altered.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity to check.
   *
   * @return bool
   *   TRUE if should be altered, FALSE if not.
   */
  protected function shouldEntityBeAltered(ContentEntityInterface $entity) : bool {
    if (in_array($entity->getEntityTypeId(), static::$excludedTypes, TRUE)) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Does the route match.
   *
   * @param string $route_name
   *   The route_name to check.
   *
   * @return bool
   *   TRUE if route matches, FALSE if route does not match.
   */
  protected function doesRouteMatch(string $route_name) : bool {
    return in_array($route_name, $this->getRoutesToCheck(), TRUE);
  }

  /**
   * Get route types to check. Just nodes for now.
   *
   * @return array
   *   The route type to check.
   */
  protected function getRoutesToCheck() : array {
    return ['entity.node.canonical'];
  }

  /**
   * Get breadcrumbs for the given entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity to get breadcrumbs for.
   *
   * @return \Drupal\Core\Breadcrumb\Breadcrumb
   *   Breadcrumb object.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\ParamConverter\ParamNotConvertedException
   */
  protected function getBreadCrumbForEntity(ContentEntityInterface $entity) : Breadcrumb {
    // Use a subrequest so the route context is set correctly.
    $url = $entity->toUrl();
    $resolve = $this->subRequestBuffer->add($url, function () {
      return $this->breadcrumbBuilder->build($this->routeMatch);
    });

    try {
      $response = $resolve();
      if ($response) {
        return $response->getvalue();
      }
    }
    catch (\Exception $e) {
      watchdog_exception('VA-CMS-EXPORT', $e);
    }

    return $this->buildGenericBreadcrumb($entity);
  }

  /**
   * Builds a generic breadcrumb for the entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The content entity.
   *
   * @return \Drupal\Core\Breadcrumb\Breadcrumb
   *   The breadcrumb object.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\ParamConverter\ParamNotConvertedException
   */
  protected function buildGenericBreadcrumb(ContentEntityInterface $entity) : Breadcrumb {
    $routeParameters = $entity->toUrl()->getRouteParameters();
    $route_name = $entity->toUrl()->getRouteName();
    $route = $this->routeProvider->getRouteByName($route_name);
    $routeParameters[RouteObjectInterface::ROUTE_NAME] = $route_name;
    $routeParameters[RouteObjectInterface::ROUTE_OBJECT] = $route;
    $routeParameters += $route->getDefaults();
    $upcasted_parameters = $this->paramConverterManager->convert($routeParameters);

    $routeMatch = new RouteMatch($route_name, $route, $upcasted_parameters, $routeParameters);
    return $this->breadcrumbBuilder->build($routeMatch);
  }

  /**
   * Add breadcrumbs to the entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity to add breadcrumbs to.
   * @param \Drupal\Core\Breadcrumb\Breadcrumb $breadcrumb
   *   The array of breadcrumbs.
   */
  protected function addBreadCrumbToEntity(ContentEntityInterface $entity, Breadcrumb $breadcrumb) {
    $entity->breadcrumbs = $breadcrumb->getLinks();
  }

}
