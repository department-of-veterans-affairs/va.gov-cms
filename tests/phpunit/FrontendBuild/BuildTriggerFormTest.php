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
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

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
    $this->assertSession()->pageTextContains('Content releases within Lando');
    $this->assertSession()->fieldExists('front_end_branch');
  }

  /**
   * Test that the correct form is shown on Tugboat.
   */
  public function testTugboatBuildTriggerForm() {
    $this->setupEnvironment('tugboat');
    $this->visit('/admin/content/deploy');
    $this->assertSession()->pageTextContains('A content release for this environment is handled by CMS-CI');
    $this->assertSession()->fieldExists('front_end_branch');
  }

  /**
   * Test that the correct form is shown on BRD.
   */
  public function testBrdBuildTriggerForm() {
    $this->setupEnvironment('brd');
    $this->visit('/admin/content/deploy');
    $this->assertSession()->pageTextContains('A content release for this environment will be handled by VFS Jenkins.');
    $this->assertSession()->fieldNotExists('front_end_branch');
  }

  /**
   * Test that the correct form is shown on Devshop.
   */
  public function testDevshopBuildTriggerForm() {
    $this->setupEnvironment('devshop');
    $this->visit('/admin/content/deploy');
    $this->assertSession()->pageTextContains('A content release for this environment is handled by CMS-CI');
    $this->assertSession()->fieldNotExists('front_end_branch');
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

}
