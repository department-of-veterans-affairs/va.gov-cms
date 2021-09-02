/**
 * @file
 */

((Drupal) => {
  const titleSetter = () => {
    // Drupal only populates the menu title after key input event in title,
    // so this copy and paste is necssary to make this 100% hands off
    // for content entry team.
    const title = document.getElementById("edit-title-0-value");
    title.dispatchEvent(new MouseEvent("click", { shiftKey: true }));
    const menuTitle = document.getElementById("edit-menu-title");
    menuTitle.value = title.value;
    // Disable mouse click / typing in input via disabled-title class.
    // Tell our screenreader item is disabled.
    // Don't set attribute to disabled in alter,
    // because drupal will toss the value on form submit.
    menuTitle.setAttribute("aria-disabled", true);
    menuTitle.classList.add("disabled-title");
  };
  Drupal.behaviors.vaGovSetMenuTitle = {
    attach() {
      window.addEventListener("DOMContentLoaded", titleSetter);
    },
  };
})(Drupal);
