<?php

namespace Drupal\va_gov_content_export;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\ChainBreadcrumbBuilderInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\ParamConverter\ParamConverterManagerInterface;
use Drupal\Core\Routing\RouteMatch;
use Drupal\Core\Routing\RouteProviderInterface;
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
   * An array of excluded entity types.
   *
   * @var string[]
   */
  protected static $excludedTypes = [
    'content_moderation_state',
    'paragraph',
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
   */
  public function __construct(
    ChainBreadcrumbBuilderInterface $breadcrumbBuilder,
    RouteProviderInterface $routeProvider,
    ParamConverterManagerInterface $paramConverterManager
  ) {
    $this->breadcrumbBuilder = $breadcrumbBuilder;
    $this->routeProvider = $routeProvider;
    $this->paramConverterManager = $paramConverterManager;
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

    $routeName = $entity->toUrl()->getRouteName();
    if (!$this->doesRouteMatch($routeName)) {
      return;
    }

    $breadcrumbs = $this->getBreadCrumbForEntity($entity, $routeName);
    $this->addBreadCrumbToEntity($entity, $breadcrumbs);
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
   * @param string $route_name
   *   The route name to lookup breadcrumbs for.
   *
   * @return array
   *   Array of breadcrumbs.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\ParamConverter\ParamNotConvertedException
   */
  protected function getBreadCrumbForEntity(ContentEntityInterface $entity, string $route_name) : Breadcrumb {
    $routeParameters = $entity->toUrl()->getRouteParameters();
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
