<?php

namespace Drupal\va_gov_header_footer\Traits;

/**
 * Provides centralized menu form alter methods.
 */
trait MenuFormAlter {

  /**
   * Hides the description field on certain menu link forms.
   *
   * @param array $form
   *   The form element array.
   */
  public function hideMenuLinkDescriptionField(array &$form): void {
    $form['description']['#access'] = FALSE;
  }

  /**
   * Hides the attributes form field for home page hub list link form.
   *
   * @param array $form
   *   The form element array.
   *
   * @return $this
   */
  public function hubMenuHideAddtributes(array &$form): static {
    if (!empty($form['options']['attributes'])) {
      $form['options']['attributes']['#access'] = FALSE;
    }
    return $this;
  }

  /**
   * Hides the expanded form field for home page hub list link form.
   *
   * @param array $form
   *   The form element array.
   *
   * @return $this
   */
  public function hubMenuHideExpanded(array &$form): static {
    if (!empty($form['expanded'])) {
      $form['expanded']['#access'] = FALSE;
    }
    return $this;
  }

  /**
   * Hides the view_mode form field for home page hub list link form.
   *
   * @param array $form
   *   The form element array.
   * @param bool $admin
   *   TRUE if current user is an administrator.
   *
   * @return $this
   */
  public function hubMenuHideViewMode(array &$form, bool $admin): static {
    if (!empty($form['view_mode'])) {
      $form['view_mode']['#access'] = $admin;
    }
    return $this;
  }

  /**
   * Hides the parent link form field for home page hub list link form.
   *
   * @param array $form
   *   The form element array.
   * @param bool $admin
   *   TRUE if current user is an administrator.
   *
   * @return $this
   */
  public function hubMenuHideParentLink(array &$form, bool $admin): static {
    if (!empty($form['menu_parent'])) {
      $form['menu_parent']['#access'] = $admin;
    }
    return $this;
  }

}
