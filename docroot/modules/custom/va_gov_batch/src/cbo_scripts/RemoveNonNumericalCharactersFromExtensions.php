<?php

namespace Drupal\va_gov_batch\cbo_scripts;

use Drupal\codit_batch_operations\BatchOperations;
use Drupal\codit_batch_operations\BatchScriptInterface;

/**
 * Migrate Staff profile phone field to phone paragraph.
 */
class RemoveNonNumericalCharactersFromExtensions extends BatchOperations implements BatchScriptInterface {

  /**
   * {@inheritdoc}
   */
  public function getTitle(): string {
    return <<<TITLE
    For:
      - VACMS-20371: https://github.com/department-of-veterans-affairs/va.gov-cms/issues/20371.
    TITLE;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription():string {
    return <<<ENDHERE
    Updates the phone extension field in the Phone number paragraph
    to remove non-numerical characters.
    ENDHERE;
  }

  /**
   * {@inheritdoc}
   */
  public function getCompletedMessage(): string {
    return 'Non-numerical characters have been removed with a total @total processed and @completed completed.';
  }

  /**
   * {@inheritdoc}
   */
  public function gatherItemsToProcess(): array {
    return \Drupal::database()
      ->select('paragraph__field_phone_extension', 'p')
      ->fields('p', ['entity_id'])
      ->condition('field_phone_extension_value', '[^0-9]', 'REGEXP')
      ->execute()
      ->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function processOne(string $key, mixed $item, array &$sandbox): string {
    $extension = \Drupal::database()
      ->select('paragraph__field_phone_extension', 'p')
      ->fields('p', ['field_phone_extension_value'])
      ->condition('entity_id', $item)
      ->execute()
      ->fetchCol();

    // If the extension is empty, skip this item.
    if (empty($extension[0])) {
      $message = 'No extension found for paragraph id ' . $item;
      return $message;
    }

    $result = $this->replaceNonNumerals($extension[0]);

    // If the extension has changed, update the paragraph.
    if ($result !== $extension[0]) {
      // Update the extension field with only numbers.
      \Drupal::database()
        ->update('paragraph__field_phone_extension')
        ->fields(['field_phone_extension_value' => $result])
        ->condition('entity_id', $item)
        ->execute();
      $message = "Extension updated for paragraph id $item from $extension[0] to $result";
    }
    else {
      $message = "No changes made for paragraph id $item";
    }

    return $message;
  }

  /**
   * Replaces non-numerical characters in the extension field.
   *
   * @param string $extension
   *   The extension to be updated.
   *
   * @return string
   *   The changed extension
   */
  public static function replaceNonNumerals(string $extension): string {

    $pattern_to_ignore = '/(^\d+[,|;]\s?\d+)|(^\d+\sor\s\d+)|(^\d+\/\d+)|(^\d+\sthen\s\d+)/';

    // If the extension contains a slash or the word "or", we won't change it.
    if (preg_match($pattern_to_ignore, $extension) > 0) {
      return $extension;
    }

    // Remove non-numerical characters from the extension.
    $just_numbers = preg_replace('/[^0-9]/', '', $extension);

    return $just_numbers;
  }

}
