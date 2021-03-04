/**
 * @file
 */

((Drupal, drupalSettings) => {
  /**
   * Behaviors for parent menu selector on node forms.
   * */
  Drupal.behaviors.parentMenuSelector = {
    attach(context) {
      const currentUserRoles = drupalSettings.vagovmenuaccess.currentuserroles;
      const adminRoles = ["content_admin", "administrator"];
      const adminTest = adminRoles.some((role) =>
        currentUserRoles.includes(role)
      );

      function menuSelectHandler() {
        // Grab our roles, menus, and current role settings.
        const approvedRoles = drupalSettings.vagovmenuaccess.allowedroles;
        const approvedMenusObj = drupalSettings.vagovmenuaccess.allowedmenus;
        const approvedMenus = Object.entries(approvedMenusObj);

        // When the menu link box is checked, fire logic.
        const parentOptions = context.querySelectorAll(
          "#edit-menu-menu-parent option"
        );
        let menuTest;
        let roleTest;
        parentOptions.forEach((opt) => {
          // Returns true if the user has an approved role.
          roleTest = approvedRoles.some((role) =>
            currentUserRoles.includes(role)
          );
          // Returns true if we hit an allowed vamc menu.
          menuTest = approvedMenus.some((menu) => opt.value.includes(menu[0]));
          // If an option doesn't pass the checks, set it to disabled.
          if (!roleTest || (opt && opt.value && !menuTest)) {
            opt.setAttribute("disabled", "");
          }
        });
      }
      // If user is an admin, bypass restriction logic.
      if (!adminTest) {
        document.addEventListener("readystatechange", menuSelectHandler);
      }
    },
  };
})(Drupal, drupalSettings);
