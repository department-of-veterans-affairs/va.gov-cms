<?php

namespace Drupal\va_gov_backend\Plugin\views\access;

use Drupal\Core\Session\AccountInterface;
use Drupal\views\Plugin\views\access\AccessPluginBase;
use Symfony\Component\Routing\Route;

/**
 * Special access for pdf audit.
 *
 * @ingroup views_access_plugins
 *
 * @ViewsAccess(
 *   id = "php_audit_access",
 *   title = @Translation("PHP Audit Access"),
 *   help = @Translation("Access will be granted to those who need access.")
 * )
 */
class PdfAuditAccess extends AccessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function summaryTitle() {
    return $this->t('Customised Settings for the PDF Audit');
  }

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account) {
    $roles = [
      'administrator',
      'content_admin',
      'content_editor',
      'content_publisher',
      'content_reviewer',
    ];
    $account_roles = $account->getRoles();
    foreach ($roles as $role) {
      if (in_array($role, $account_roles)) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function alterRouteDefinition(Route $route) {
    $route->setRequirement('_access', 'TRUE');
  }

}
