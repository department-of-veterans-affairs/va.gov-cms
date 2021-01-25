<?php

namespace Drupal\va_gov_notifications\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\flag\FlagServiceInterface;
use Drupal\node\NodeInterface;
use Drupal\user\UserInterface;

/**
 * Makes decisions about whether flags should be set, unset, etc.
 */
class Flagging implements FlaggingInterface {
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
  public function setEditedFlag(NodeInterface $node, UserInterface $user): void {
    $flag = $this->flagService->getFlagById('edited');
    if (!$user->isAnonymous() && !$this->flagService->getFlagging($flag, $node, $user)) {
      $this->flagService->flag($flag, $node, $user);
    }
  }

}
