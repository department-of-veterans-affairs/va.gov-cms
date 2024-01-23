<?php

namespace Drupal\va_gov_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Core\Path\PathValidatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Returns responses for Content API routes.
 */
class BannerAlertsController extends ControllerBase {

  /**
   * The serializer service.
   *
   * @var \Symfony\Component\Serializer\SerializerInterface
   */
  protected $serializer;

  /**
   * Constructor.
   *
   * @param \Symfony\Component\Serializer\SerializerInterface $serializer
   *   The serializer service.
   * @param \Drupal\Core\Path\PathMatcherInterface $path_matcher
   *   The path matcher service.
   * @param \Drupal\Core\Path\PathValidatorInterface $path_validator
   *   The path validator service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(
    SerializerInterface $serializer,
    PathMatcherInterface $path_matcher,
    PathValidatorInterface $path_validator,
    EntityTypeManagerInterface $entity_type_manager) {
    $this->serializer = $serializer;
    $this->pathMatcher = $path_matcher;
    $this->pathValidator = $path_validator;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('serializer'),
      $container->get('path.matcher'),
      $container->get('path.validator'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Get banner alerts by path.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The banner alerts.
   */
  public function bannerAlertsByPath(Request $request) {
    $path = $request->get('path');
    if (!$path) {
      return new JsonResponse(['error' => 'No path provided.']);
    }

    $banners = $this->collectBannerData($path);
    $promo_banners = $this->collectPromoBannerData($path);
    $full_width_banner_alerts = $this->collectFullWidthBannerAlertData($path);

    return new JsonResponse([
      'data' => array_merge($banners, $promo_banners, $full_width_banner_alerts),
    ]);
  }

  /**
   * Collect `banner` entities to be returned in the response.
   *
   * Given a path, retrieves any `banner` that should show there, constructs a
   * ResponseObject for it, and adds it to cacheableDependencies.
   *
   * @param string $path
   *   The path to the item to find banners for.
   */
  protected function collectBannerData(string $path) {
    $node_storage = $this->entityTypeManager->getStorage('node');

    // Get all published banner nodes.
    $banner_nids = $node_storage->getQuery()
      ->condition('type', 'banner')
      ->condition('status', TRUE)
      ->accessCheck(FALSE)
      ->execute();
    /** @var \Drupal\node\NodeInterface[] $banners */
    $banners = $node_storage->loadMultiple(array_values($banner_nids) ?? []);

    // Filter the banner list to just the ones that should be displayed for the
    // provided item path.
    $banners = array_filter($banners, function ($item) use ($path) {
      // PathMatcher expects a newline delimited string for multiple paths.
      $patterns = '';
      foreach ($item->field_target_paths->getValue() as $target_path) {
        $patterns .= $target_path['value'] . "\n";
      }

      return $this->pathMatcher->matchPath($path, $patterns);
    });

    // Add the banners to the response.
    $banner_data = [];
    foreach ($banners as $entity) {
      $banner_data[] = $this->serializer->normalize($entity);
    }
    return $banner_data;
  }

  /**
   * Collect `promo_banner` entities to be returned in the response.
   *
   * Given a path, retrieves any `promo_banner` that should show there,
   *  constructs a ResponseObject for it, and adds it to cacheableDependencies.
   *
   * @param string $path
   *   The path to the item to find promo_banners for.
   */
  protected function collectPromoBannerData(string $path) {
    $node_storage = $this->entityTypeManager->getStorage('node');

    // Get all published promo_banner nodes.
    $promo_banner_nids = $node_storage->getQuery()
      ->condition('type', 'promo_banner')
      ->condition('status', TRUE)
      ->accessCheck(FALSE)
      ->execute();
    /** @var \Drupal\node\NodeInterface[] $promo_banners */
    $promo_banners = $node_storage->loadMultiple(array_values($promo_banner_nids) ?? []);

    // Filter the promo_banner list to just the ones that should be displayed
    // for the provided item path.
    $promo_banners = array_filter($promo_banners, function ($item) use ($path) {
      // PathMatcher expects a newline delimited string for multiple paths.
      $patterns = '';
      foreach ($item->field_target_paths->getValue() as $target_path) {
        $patterns .= $target_path['value'] . "\n";
      }

      return $this->pathMatcher->matchPath($path, $patterns);
    });

    // Add the promo_banners to the response.
    $promo_banner_data = [];
    foreach ($promo_banners as $entity) {
      $promo_banner_data[] = $this->serializer->normalize($entity);
    }
    return $promo_banner_data;
  }

  /**
   * Collect `full_width_banner_alert` entities to be returned in the response.
   *
   * Given a path, retrieves any `full_width_banner_alert` that should show
   * there, constructs a ResponseObject for it, and adds it to
   * cacheableDependencies.
   *
   * @param string $path
   *   The path to the item to find full_width_banner_alerts for.
   */
  protected function collectFullWidthBannerAlertData(string $path) {
    // Find the first fragment of the path; this will correspond to a facility,
    // if this is a facility page of some kind.
    $region_fragment = '__not_a_real_url';
    $path_pieces = explode("/", $path);
    if (count($path_pieces) > 1) {
      $region_fragment = "/" . $path_pieces[1];
    }

    // Resolve the region fragment to a URL object.
    $url = $this->pathValidator->getUrlIfValidWithoutAccessCheck($region_fragment);
    if ($url === FALSE || !$url->isRouted() || !isset($url->getRouteParameters()['node'])) {
      // If the alias is invalid, it's not a routed URL, or there is not a node
      // in the route params, there's not much else that can be done here.
      return;
    }

    // Load the system that we found.
    $node_storage = $this->entityTypeManager->getStorage('node');
    $system_nid = $url->getRouteParameters()['node'];
    /** @var \Drupal\node\NodeInterface $system_node */
    $system_node = $node_storage->load($system_nid);

    // If it's not a published VAMC system node, bail early.
    if (is_null($system_node) || $system_node->getType() != 'health_care_region_page' || $system_node->isPublished() === FALSE) {
      return;
    }

    // Find all operating status nodes which have this system as their office.
    $operating_status_nids = $node_storage->getQuery()
      ->condition('type', 'vamc_operating_status_and_alerts')
      ->condition('status', TRUE)
      ->condition('field_office', $system_node->id())
      ->accessCheck(FALSE)
      ->execute();

    // If there are no operating status nids, bail.
    if (count($operating_status_nids) === 0) {
      return;
    }

    // Find any facility banners connected to the operating status nodes.
    $facility_banner_nids = $node_storage->getQuery()
      ->condition('type', 'full_width_banner_alert')
      ->condition('status', TRUE)
      ->condition('field_banner_alert_vamcs', array_values($operating_status_nids), 'IN')
      ->accessCheck(FALSE)
      ->execute();

    /** @var \Drupal\node\NodeInterface[] $facility_banners */
    $facility_banners = $node_storage->loadMultiple($facility_banner_nids);

    // Add the banners to the response.
    $full_width_banner_alert_data = [];
    foreach ($facility_banners as $entity) {
      $full_width_banner_alert_data[] = $this->serializer->normalize($entity);

      // Override field_situation_updates with referenced paragraph data.
      $situation_updates = $entity->get('field_situation_updates')->referencedEntities();
      $situation_update_data = [];
      foreach ($situation_updates as $situation_update) {
        $situation_update_data[] = $this->serializer->normalize($situation_update);
      }
      $full_width_banner_alert_data['field_situation_updates'] = $situation_update_data;
    }
    return $full_width_banner_alert_data;
  }

}
