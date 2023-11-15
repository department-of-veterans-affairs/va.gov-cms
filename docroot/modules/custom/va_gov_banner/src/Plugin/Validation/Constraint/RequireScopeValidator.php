<?php

namespace Drupal\va_gov_banner\Plugin\Validation\Constraint;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\va_gov_user\Service\UserPermsService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the RequireScope constraint.
 */
class RequireScopeValidator extends ConstraintValidator implements ContainerInjectionInterface {
  /**
   * The User Perms service.
   *
   * @var \Drupal\va_gov_user\Service\UserPermsService
   */
  protected $userPermsService;

  /**
   * Constructs the ConstraintValidator object.
   *
   * @param \Drupal\va_gov_user\Service\UserPermsService $user_perms_service
   *   The user perms service.
   */
  public function __construct(UserPermsService $user_perms_service) {
    $this->userPermsService = $user_perms_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('va_gov_user.user_perms')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function validate($item, Constraint $constraint) {
    /** @var \Drupal\va_gov_banner\Plugin\Validation\Constraint\RequireScope $constraint */
    if ($item->getEntity()->get('moderation_state')->getString() !== 'published') {
      return;
    }
    $path = trim($item->getEntity()->get('field_target_paths')->getString());
    if (empty($path)) {
      $this->userPermsService->hasAdminRole()
      ? $this->context->addViolation($constraint->noPathsAdmin)
      : $this->context->addViolation($constraint->noPathsNonAdmin);
    }
  }

}
