/**
 * @file
 */

((Drupal) => {
  const title = document.getElementById("edit-title-0-value");
  const titleSetter = () => {
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
      titleSetter();
      title.addEventListener("input", titleSetter);
    },
  };
})(Drupal);
