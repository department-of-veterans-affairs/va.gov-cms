<?php

namespace Drupal\va_gov_build_trigger\Plugin\VAGov\Environment;

use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\va_gov_build_trigger\Environment\EnvironmentPluginBase;

/**
 * Devshop Environment Plugin.
 *
 * @Environment(
 *   id = "devshop",
 *   label = @Translation("DevShop")
 * )
 */
class DevShop extends EnvironmentPluginBase {

  /**
   * {@inheritDoc}
   */
  public function getWebUrl(): string {
    return getenv('DRUPAL_ADDRESS') . '/static';
  }

  /**
   * {@inheritDoc}
   */
  public function triggerFrontendBuild(): void {
    // Running in CMS-CI and DevShopTaskApiClient has been loaded, use it.
    $task_json = \DevShopTaskApiClient::create('vabuild');
    $task = json_decode($task_json);
    if (!empty($task->nid)) {
      $vars = [
        '@link' => Link::fromTextAndUrl(t('Deploy Log'), Url::fromUri('http://' . $_SERVER['DEVSHOP_HOSTNAME'] . '/node/' . $task->nid))->toString(),
      ];
      $message = t('VA Web Rebuild & Deploy has been queued. Please see the devshop deploy log for status. @link', $vars);
      $this->messenger()->addStatus($message);
      $this->logger->info($message);
    }
    else {
      // This has failed due to bad devshop setting.
      $message = t('VA Web Rebuild & Deploy has NOT been queued because @method returned no id.', ['@method' => "\DevShopTaskApiClient::create('vabuild')"]);
      $this->webBuildStatus->disableWebBuildStatus();
      $this->messenger()->addError($message);
      $this->logger->error($message);
    }
  }

  /**
   * {@inheritDoc}
   */
  public function shouldTriggerFrontendBuild(): bool {
    return FALSE;
  }

}
