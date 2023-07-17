<?php

namespace tests\phpunit\va_gov_content_release\functional\Form\Resolver;

use Drupal\va_gov_content_release\EventSubscriber\FormRouteSubscriber;
use Tests\Support\Classes\VaGovExistingSiteBase;
use Drupal\va_gov_build_trigger\Form\BuildTriggerForm;
use Drupal\va_gov_build_trigger\Form\BrdBuildTriggerForm;
use Drupal\va_gov_build_trigger\Form\LocalBuildTriggerForm;
use Drupal\va_gov_build_trigger\Form\TugboatBuildTriggerForm;

/**
 * Functional test of the Form Route Subscriber service.
 *
 * @group functional
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_content_release\EventSubscriber\FormRouteSubscriber
 */
class FormRouteSubscriberTest extends VaGovExistingSiteBase {

  /**
   * Test that the service is available.
   *
   * @covers ::__construct
   */
  public function testConstruct() {
    $formRouteSubscriber = \Drupal::service('va_gov_content_release.form_route_subscriber');
    $this->assertInstanceOf(FormRouteSubscriber::class, $formRouteSubscriber);
  }

  /**
   * Test that the route has been altered.
   *
   * The route is normally using the form base class, so if that is still the
   * case, the route has not been altered and something's broken.
   */
  public function testAlterRoute() {
    $route = $this->container->get('router.route_provider')->getRouteByName('va_gov_content_release.form');
    $form = $route->getDefault('_form');
    $this->assertNotEquals(BuildTriggerForm::class, $form);
    $this->assertContains($form, [
      BrdBuildTriggerForm::class,
      LocalBuildTriggerForm::class,
      TugboatBuildTriggerForm::class,
    ]);
  }

  /**
   * Test that our form test routes are in place.
   *
   * @param string $route
   *   The route name.
   * @param string $class
   *   The form class.
   *
   * @dataProvider formTestRoutesProvider
   */
  public function testFormTestRoutes(string $route, string $class) {
    $prefix = 'va_gov_content_release.form.';
    $route = $this->container->get('router.route_provider')->getRouteByName("{$prefix}{$route}");
    $form = $route->getDefault('_form');
    $this->assertEquals("\\{$class}", $form);
  }

  /**
   * Data provider for testFormTestRoutes.
   *
   * @return array
   *   The data.
   */
  public function formTestRoutesProvider() {
    return [
      ['brd', BrdBuildTriggerForm::class],
      ['local_dev', LocalBuildTriggerForm::class],
      ['tugboat', TugboatBuildTriggerForm::class],
    ];
  }

}
