<?php

namespace tests\phpunit\FrontendBuild;

use Drupal\Core\Site\Settings;
use Drupal\user\Entity\User;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Functional test of the Build trigger Form.
 *
 * @group functional
 * @group all
 */
class BuildTriggerFormTest extends VaGovExistingSiteBase {

  /**
   * Store the default environment.
   *
   * @var string
   */
  private $defaultEnvironment;

  /**
   * {@inheritdoc}
   */
  public function setUp() : void {
    parent::setUp();

    // Store the default environment so that we may later revert to it.
    $settings = $this->container->get('settings')->getAll();
    $this->defaultEnvironment = $settings['va_gov_frontend_build_type'] ?? 'local';

    $admin = User::load(1);
    $admin->passRaw = 'drupal8';
    $this->drupalLogin($admin);
  }

  /**
   * Test that the correct form is shown on local environments.
   */
  public function testLocalBuildTriggerForm() {
    $this->setupEnvironment('local');
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
   * Revert to the default environment when all test are complete.
   *
   * @depends testLocalBuildTriggerForm
   * @depends testTugboatBuildTriggerForm
   * @depends testBrdBuildTriggerForm
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

    // Set a fake token so that the factory will not throw an exception.
    // This is necessary because we're loading different config, and trying to
    // find the token in different methods depending on the environment.
    // This test does not actually attempt to use the token, so it's fine to
    // just set it to a fake value.
    $settings['va_cms_bot_github_auth_token'] = 'fake-token';

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
