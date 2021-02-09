<?php

namespace tests\phpunit\FrontendBuild;

use Drupal\Core\Site\Settings;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests of the BRD environment plugin.
 *
 * @coversDefaultClass \Drupal\va_gov_build_trigger\Plugin\VAGov\Environment\BRD
 */
class BrdEnvironmentTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'va_gov_build_trigger',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $settings['jenkins_build_env'] = 'TEST';
    $settings['jenkins_build_job_host'] = 'http://jenkins.vfs.va.gov';
    $settings['va_cms_bot_github_username'] = 'va-cms-bot';
    $settings['va_gov_frontend_url'] = 'https://staging.va.gov';
    $settings['va_gov_frontend_build_type'] = 'brd';
    $settings['jenkins_build_job_path'] = '/job/builds/job/vets-website-content-vagov' . $settings['jenkins_build_env'];
    $settings['jenkins_build_job_params'] = '/buildWithParameters?deploy=true';
    $settings['jenkins_build_job_url'] = $settings['jenkins_build_job_host'] . $settings['jenkins_build_job_path'] . $settings['jenkins_build_job_params'];
    $this->container->set('settings', new Settings($settings));
  }

}
