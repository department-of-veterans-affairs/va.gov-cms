<?php

namespace tests\phpunit\FrontendBuild;

use Drupal\Core\Site\Settings;
use Drupal\user\Entity\User;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Functional test of the Build trigger Form.
 */
class BuildTriggerFormTest extends ExistingSiteBase {

  /**
   * Store the default environment.
   *
   * @var string
   */
  private $defaultEnvironment;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Store the default environment so that we may later revert to it.
    $this->defaultEnvironment = $this->container->get('settings')->getAll()['va_gov_frontend_build_type'];

    $admin = User::load(1);
    $admin->passRaw = 'drupal8';
    $this->drupalLogin($admin);
  }

  /**
   * Test that the correct form is shown on Lando.
   */
  public function testLandoBuildTriggerForm() {
    $this->setupEnvironment('lando');
    $this->visit('/admin/content/deploy');
    $this->assertSession()->pageTextContains('Release content to update the front end of this local environment with the latest published content changes');
    $this->assertSession()->fieldExists('branch');
  }

  /**
   * Test that the correct form is shown on Tugboat.
   */
  public function testTugboatBuildTriggerForm() {
    $this->setupEnvironment('tugboat');
    $this->visit('/admin/content/deploy');
    $this->assertSession()->pageTextContains('Release content to update the front end of this demo environment with the latest published content changes');
    $this->assertSession()->fieldExists('branch');
  }

  /**
   * Test that the correct form is shown on BRD.
   */
  public function testBrdBuildTriggerForm() {
    $this->setupEnvironment('brd');
    $this->visit('/admin/content/deploy');
    $this->assertSession()->pageTextContains('you can perform a manual content release here');
    $this->assertSession()->fieldNotExists('git_ref');
  }

  /**
   * Test that the correct form is shown on Devshop.
   */
  public function testDevshopBuildTriggerForm() {
    $this->setupEnvironment('devshop');
    $this->visit('/admin/content/deploy');
    $this->assertSession()->pageTextContains('A content release for this environment is handled by CMS-CI');
    $this->assertSession()->fieldNotExists('git_ref');
  }

  /**
   * Revert to the default environment when all test are complete.
   *
   * @depends testLandoBuildTriggerForm
   * @depends testTugboatBuildTriggerForm
   * @depends testBrdBuildTriggerForm
   * @depends testDevshopBuildTriggerForm
   */
  public function testRevertToDefaultEnvironment() {
    // This should more properly be run in a tearDown method,
    // but will not work there.
    $this->revertToDefaultEnvironment();
    $this->visit('/admin/content/deploy');
    $this->assertSession()->pageTextContains('Release content');
  }

  /**
   * Fake the current environment.
   *
   * @param string $environment
   *   The environment type.
   */
  private function setupEnvironment($environment) {
    $settings = $this->container->get('settings')->getAll();
    $settings['va_gov_frontend_build_type'] = $environment;
    $this->container->set('settings', new Settings($settings));

    // Rebuild the routing cache so that the correct
    // form class will be discovered.
    \Drupal::service("router.builder")->rebuild();
  }

  /**
   * Revert to the default environment.
   */
  public function revertToDefaultEnvironment() {
    $this->setupEnvironment($this->defaultEnvironment);
  }

}
