/**
 * @file
 */

((Drupal) => {
  Drupal.behaviors.vaGovTextFieldTooltips = {
    attach(context) {
      const tooltipFactory = (toolTipText, placementTarget) => {
        if (context === document) {
          const target = context.getElementById(`edit-${placementTarget}`);
          const disabledClass = target.disabled
            ? "disabled-input"
            : "enabled-input";
          const nameButtonContainerDiv = context.createElement("div");
          const nameButton = context.createElement("button");
          nameButtonContainerDiv.className = `tooltip-container-div name-button-container-div ${disabledClass}`;
          nameButton.className = "css-tooltip-toggle";
          nameButton.value = toolTipText;
          nameButton.type = "button";
          nameButton.ariaLabel = "tooltip";
          nameButton.setAttribute("data-tippy", toolTipText);
          nameButton.setAttribute("data-tippy-pos", "right");
          nameButton.setAttribute("data-tippy-animate", "fade");
          nameButton.setAttribute("data-tippy-size", "large");
          nameButtonContainerDiv.appendChild(nameButton);
          target.after(nameButtonContainerDiv);
        }
      };

      const targets = [
        {
          text:
            "Why can’t I edit this?\nThis content is automatically populated from centralized databases, and helps maintain consistent information across all of VA.gov.",
          element: "field-official-name-0-value",
        },
        {
          text:
            "Why can’t I edit this?\nThe common name is set up to keep a consistent format with other Vet Centers across all of VA.gov.",
          element: "title-0-value",
        },
      ];

      targets.forEach((target) => {
        tooltipFactory(target.text, target.element);
      });
    },
  };
})(Drupal);
