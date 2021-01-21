<?php

namespace Drupal\va_gov_notifications\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\flag\FlagServiceInterface;
use Drupal\node\NodeInterface;
use Drupal\user\UserInterface;

/**
 * Makes decisions about whether flags should be set, unset, etc.
 */
class FlagDecisions implements FlagDecisionsInterface {
  /**
   * The flag service.
   *
   * @var \Drupal\flag\FlagServiceInterface
   */
  protected $flagService;

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new FlagDecisionsService object.
   */
  public function __construct(
    FlagServiceInterface $flagService,
    EntityTypeManagerInterface $entityTypeManager
  ) {
    $this->flagService = $flagService;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public function shouldSetEditedFlag(NodeInterface $node, UserInterface $user) {
    if ($user->isAnonymous()) {
      return FALSE;
    }
    $flag = $this->flagService->getFlagById('edited');
    if ($this->flagService->getFlagging($flag, $node, $user)) {
      return FALSE;
    }
    return TRUE;
  }

}
