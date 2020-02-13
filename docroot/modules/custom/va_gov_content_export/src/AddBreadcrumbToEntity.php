<?php


namespace Drupal\va_gov_content_export;


use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\ChainBreadcrumbBuilderInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\ParamConverter\ParamConverterManagerInterface;
use Drupal\Core\Routing\RouteMatch;
use Drupal\Core\Routing\RouteProviderInterface;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;

class AddBreadcrumbToEntity {
  /**
   * @var \Drupal\Core\Breadcrumb\ChainBreadcrumbBuilderInterface
   */
  protected $breadcrumbBuilder;

  /**
   * @var \Drupal\Core\Routing\RouteProviderInterface
   */
  protected $routeProvider;

  /**
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
   * @param \Drupal\Core\Routing\RouteProviderInterface $routeProvider
   * @param \Drupal\Core\ParamConverter\ParamConverterManagerInterface $paramConverterManager
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

  protected function shouldEntityBeAltered(ContentEntityInterface $entity) : bool {
    if (in_array($entity->getEntityTypeId(), static::$excludedTypes, TRUE)) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Does the route match.
   */
  protected function doesRouteMatch(string $route_name) : bool {
    return in_array($route_name, $this->getRoutesToCheck(), TRUE);
  }

  protected function getRoutesToCheck() : array {
    return ['entity.node.canonical'];
  }

  /**
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   * @param string $route_name
   *
   * @return array
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

  protected function addBreadCrumbToEntity(ContentEntityInterface $entity, Breadcrumb $breadcrumb) {
    /** @var \Drupal\Core\Link[] va_breadcrumb */
    $entity->breadcrumbs = $breadcrumb->getLinks();
  }
}
