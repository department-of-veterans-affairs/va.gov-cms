/**
 * @file
 */

((Drupal) => {
  Drupal.behaviors.vaGovFixConstraintJumpLink = {
    attach() {
      const jumpLinkCorrect = () => {
        const jumpLink = document.querySelector(
          ".region-highlighted .messages--error ul.item-list__comma-list li a"
        )
          ? document.querySelector(
              ".region-highlighted .messages--error ul.item-list__comma-list li a"
            )
          : null;
        if (jumpLink && jumpLink.innerText === "Paths") {
          jumpLink.setAttribute("href", "#field-target-paths-values");
        }
      };
      window.addEventListener("load", jumpLinkCorrect);
    },
  };
})(Drupal);
