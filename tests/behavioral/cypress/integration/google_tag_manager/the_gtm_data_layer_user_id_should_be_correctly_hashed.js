import { Then } from "cypress-cucumber-preprocessor/steps";

Then(`the GTM data layer user id should be correctly hashed`, () => {
  cy.get('@uid')
    .should('exist')
    .then((uid) => {
      cy.drupalDrushEval(`echo \\Drupal\\Component\\Utility\\Crypt::hashBase64((string)${uid});`).then((result) => {
        cy.window().then((window) => {
          const expected = result.stdout;
          const actual = window.drupalSettings.gtm_data.userId;
          cy.wrap(actual).should('eq', expected);
        });
      });
    });
});



/**


  /**
   * Check that the dataLayer value is set correctly.
   *
   * @Given the GTM data layer user id should be correctly hashed
  public function googleTagManagerUserIdShouldBeCorrectlyHashed() {
    $property_value = $this->getGoogleTagManagerValue('userId');
    "echo Drupal\Component\Utility\Crypt::hashBase64((string)uid);"
    if ($hashed_value != $property_value) {
      throw new \Exception("The userId value was \"{$property_value}\" , but it should be \"{$hashed_value}\".");
    }
  }


use Drupal\Component\Utility\Crypt;

  /**
   * Check that the Google Tag Manager dataLayer value is set.
   *
   * @Given the GTM data layer value for :arg1 should be set
  public function googleTagManagerValueShouldBeSet($key) {
    $property_value = $this->getGoogleTagManagerValue($key);
    if (empty($property_value)) {
      throw new \Exception("The data layer value for \"{$key}\" should be set.");
    }
  }

  /**
   * Check that the Google Tag Manager dataLayer value is set correctly.
   *
   * @Given the GTM data layer value for :arg1 should be set to :arg2
  public function googleTagManagerValueShouldBeSetTo($key, $value) {
    $property_value = $this->getGoogleTagManagerValue($key);
    if ($value != $property_value) {
      throw new \Exception("The data layer value for \"{$key}\" should be {$value}, but it is actually {$property_value}.");
    }
  }

  /**
   * Check that the dataLayer value is not set.
   *
   * @Given the GTM data layer value for :arg1 should be unset
   * @Given the GTM data layer value for :arg1 should not be set
  public function googleTagManagerValueShouldBeUnset($key) {
    if ($this->hasGoogleTagManagerValue($key)) {
      $value = $this->getGoogleTagManagerValue($key);
      if (!empty($value)) {
        throw new \Exception("The data layer value for \"{$key}\" should not be set, but it is set to \"{$value}\".");
      }
    }
  }

  /**
   * Indicate whether the dataLayer has a value for the specified key.
   *
   * @param string $key
   *   The dataLayer key.
   *
   * @return mixed
   *   Some value.
   *
   * @throws \Exception
  protected function hasGoogleTagManagerValue($key) {
    $drupal_settings = $this->getDrupalSettings();
    $gtm_data = $drupal_settings['gtm_data'];
    return isset($gtm_data[$key]);
  }

  /**
   * Get Google Tag Manager dataLayer value for specified key.
   *
   * @param string $key
   *   The dataLayer key.
   *
   * @return mixed
   *   Some value.
   *
   * @throws \Exception
  protected function getGoogleTagManagerValue($key) {
    $drupal_settings = $this->getDrupalSettings();
    $gtm_data = $drupal_settings['gtm_data'];
    if (isset($gtm_data[$key])) {
      return $gtm_data[$key];
    }
    throw new \Exception($key . ' not found.');
  }

  /**
   * Ensure workbench access sections are empty.
   *
   * @Given my workbench access sections are not set
  public function myWorkbenchAccessSectionsAreNotSet() {
    $user = user_load($this->getUserManager()->getCurrentUser()->uid);
    $section_scheme = \Drupal::entityTypeManager()->getStorage('access_scheme')->load('section');
    $section_storage = \Drupal::service('workbench_access.user_section_storage');
    $current_sections = $section_storage->getUserSections($section_scheme, $user);
    if (!empty($current_sections)) {
      $section_storage->removeUser($section_scheme, $user, $current_sections);
      drupal_flush_all_caches();
    }
  }

  /**
   * Sets workbench access sections explicitly.
   *
   * @Then my workbench access sections are set to :arg1
  public function myWorkbenchAccessSectionsAreSetTo($new_sections) {
    $this->myWorkbenchAccessSectionsAreNotSet();
    $user = user_load($this->getUserManager()->getCurrentUser()->uid);
    $section_scheme = \Drupal::entityTypeManager()->getStorage('access_scheme')->load('section');
    $section_storage = \Drupal::service('workbench_access.user_section_storage');
    $section_storage->addUser($section_scheme, $user, explode(',', $new_sections));
    drupal_flush_all_caches();
  }
*/
