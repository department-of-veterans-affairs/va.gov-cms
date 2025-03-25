<?php

namespace Drupal\va_gov_batch\cbo_scripts;

use Drupal\codit_batch_operations\BatchOperations;
use Drupal\codit_batch_operations\BatchScriptInterface;
use Drupal\menu_link_content\Entity\MenuLinkContent;

/**
 * For VACMS-20606.
 *
 * @see https://github.com/department-of-veterans-affairs/va.gov-cms/issues/20606
 */
class ChangeManilaMenuContent extends BatchOperations implements BatchScriptInterface {

  /**
   * {@inheritdoc}
   */
  public function getTitle():string {
    return "Changes the menu of the Manila content";
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription():string {
    return <<<ENDHERE
    Move all Manila content to the VA Manila health care menu.
    ENDHERE;
  }

  /**
   * {@inheritdoc}
   */
  public function getCompletedMessage(): string {
    return '@total menu item updates were attempted. @completed were completed.';
  }

  /**
   * {@inheritdoc}
   */
  public function getItemType(): string {
    return 'menu_item';
  }

  /**
   * The Manila menu from which we are moving menu items.
   *
   * @var string
   */
  protected $sourceMenu = 'manila-va-clinic';

  /**
   * The Manila menu to which we are moving menu items.
   *
   * @var string
   */
  protected $destinationMenu = 'va-manila-health-care';

  /**
   * {@inheritdoc}
   */
  public function gatherItemsToProcess(): array {
    // Get all Manila menu items.
    $query = \Drupal::entityQuery('menu_link_content')
      ->accessCheck(FALSE)
      ->condition('menu_name', $this->sourceMenu);
    $menu_link_ids = $query->execute();

    return $menu_link_ids;

  }

  /**
   * {@inheritdoc}
   */
  public function processOne(string $key, mixed $item, array &$sandbox): string {
    // Change each Manila menu item.
    try {
      $menu_link = MenuLinkContent::load($item);
      $menu_link->set('menu_name', $this->destinationMenu);
      // Reset parent.
      $menu_link->set('parent', '');
      $menu_link->save();
      $message = "$item moved from $this->sourceMenu to $this->destinationMenu";
      $this->batchOpLog->appendLog($message);
    }
    catch (\Exception $e) {
      $message = $e->getMessage();
      $this->batchOpLog->appendError("Could not move the menu item. The error is $message");
    }
    return "Item $item was processed.";

  }

}
