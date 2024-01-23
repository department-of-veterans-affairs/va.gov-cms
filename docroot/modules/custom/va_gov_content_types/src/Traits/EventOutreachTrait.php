<?php

namespace Drupal\va_gov_content_types\Traits;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\va_gov_content_types\Entity\EventInterface;
use Drupal\va_gov_content_types\Interfaces\EventOutreachInterface;

/**
 * A trait for handling of National Outreach Calendar things for Event nodes.
 */
trait EventOutreachTrait {

  use StringTranslationTrait;

  /**
   * Determines if the current user is an 'Outreach Hub' only user.
   *
   * @return bool
   *   TRUE if the current user only has the 'Outreach Hub' section.
   */
  protected function outreachHubOnlyUser(): bool {
    $userPermsService = \Drupal::service('va_gov_user.user_perms');
    $currentUser = \Drupal::currentUser();
    $sections = $userPermsService->getSections($currentUser);
    if (count($sections) === 1 && array_key_first($sections) === EventOutreachInterface::OUTREACH_HUB_TID) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Adds the event to the National Outreach Calendar (event_listing).
   *
   * The purpose of this method is to add the current node event to the National
   * Outreach Calendar (an event listing node) if the $listingField
   * checkbox/boolean has been set, or if the current user is an Outreach Hub
   * user.
   *
   * @param \Drupal\va_gov_content_types\Entity\EventInterface $node
   *   The node to be modified.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function addToNationalOutreachCalendar(EventInterface $node): void {
    if ($node->hasField(EventOutreachInterface::LISTING_FIELD) &&
      $node->hasField(EventOutreachInterface::PUBLISH_TO_OUTREACH_CAL_FIELD) &&
      $node->hasField(EventOutreachInterface::ADDITIONAL_LISTING_FIELD)) {
      if ($node->get(EventOutreachInterface::PUBLISH_TO_OUTREACH_CAL_FIELD)->first()) {
        $addToCalValue = $node->get(EventOutreachInterface::PUBLISH_TO_OUTREACH_CAL_FIELD)->first()->getValue();
        $listings = $node->get(EventOutreachInterface::LISTING_FIELD)->getValue();
        $additionalListings = $node->get(EventOutreachInterface::ADDITIONAL_LISTING_FIELD)->getValue();
        assert(array_key_exists('value', $addToCalValue));
        if ($addToCalValue['value'] === 1 || $this->outreachHubOnlyUser()) {
          // Add to Outreach calendar selected, or user is Outreach Hub only
          // user.
          if (!in_array(EventOutreachInterface::OUTREACH_CAL_NID, array_column(array_merge($listings, $additionalListings), 'target_id'))) {
            $additionalListings[] = [
              'target_id' => EventOutreachInterface::OUTREACH_CAL_NID,
            ];
          }
        }
        else {
          // Checkbox is unset. Ensure that additional listings are removed.
          $additionalListings = [];
        }
        $node->set(EventOutreachInterface::ADDITIONAL_LISTING_FIELD, $additionalListings);
      }
    }
  }

  /**
   * Form changes for 'Publish to National Outreach Calendar' related elements.
   *
   * @param array $form
   *   The form array.
   */
  public function modifyAddToOutreachCalendarElements(array &$form) :void {
    if ($this->outreachHubOnlyUser()) {
      // Disable the checkbox.
      $form[EventOutreachInterface::PUBLISH_TO_OUTREACH_CAL_FIELD]['#disabled'] = TRUE;
      // Set the default value of the checkbox.
      $form[EventOutreachInterface::PUBLISH_TO_OUTREACH_CAL_FIELD]['widget']['value']['#default_value'] = TRUE;
      // Override the field label for the checkbox.
      $form[EventOutreachInterface::PUBLISH_TO_OUTREACH_CAL_FIELD]['widget']['value']['#title'] = $this->t('This event will automatically be published to the National Outreach Calendar');
      // Set the default value to the Outreach cal on the dropdown if is not
      // already set.
      if (empty($form[EventOutreachInterface::LISTING_FIELD]['widget']['#default_value'])) {
        $form[EventOutreachInterface::LISTING_FIELD]['widget']['#default_value'] = EventOutreachInterface::OUTREACH_CAL_NID;
      }
    }
  }

}
