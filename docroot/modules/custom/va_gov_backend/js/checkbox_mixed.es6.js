/**
 * @file
 * A "select mixed options" checkbox behavior.
 *
 * Modified from https://www.w3.org/WAI/content-assets/wai-aria-practices/patterns/checkbox/examples/js/checkbox-mixed.js to work as Drupal behaviors.
 */

function createCheckboxMixed(domNode) {
  const mixedNode = domNode.querySelector('[role="checkbox"]');
  const checkboxNodes = domNode.querySelectorAll('input[type="checkbox"]');

  // Private functions.
  function updateCheckboxStates() {
    for (let i = 0; i < checkboxNodes.length; i++) {
      const checkboxNode = checkboxNodes[i];
      checkboxNode.setAttribute("data-last-state", checkboxNode.checked);
    }
  }

  function updateMixed() {
    let count = 0;

    for (let i = 0; i < checkboxNodes.length; i++) {
      if (checkboxNodes[i].checked) {
        count += 1;
      }
    }

    if (count === 0) {
      mixedNode.setAttribute("aria-checked", "false");
    } else if (count === checkboxNodes.length) {
      mixedNode.setAttribute("aria-checked", "true");
    } else {
      mixedNode.setAttribute("aria-checked", "mixed");
      updateCheckboxStates();
    }
  }

  function anyLastChecked() {
    let count = 0;

    for (let i = 0; i < checkboxNodes.length; i++) {
      if (checkboxNodes[i].getAttribute("data-last-state") === "true") {
        count += 1;
      }
    }

    return count > 0;
  }

  function setCheckboxes(value) {
    for (let i = 0; i < checkboxNodes.length; i++) {
      const checkboxNode = checkboxNodes[i];

      switch (value) {
        case "last":
          checkboxNode.checked =
            checkboxNode.getAttribute("data-last-state") === "true";
          break;

        case "true":
          checkboxNode.checked = true;
          break;

        default:
          checkboxNode.checked = false;
          break;
      }
    }
    updateMixed();
  }

  function toggleMixed() {
    const state = mixedNode.getAttribute("aria-checked");
    let action;

    if (state === "false") {
      action = anyLastChecked() ? "last" : "true";
    } else if (state === "mixed") {
      action = "true";
    } else {
      action = "false";
    }

    setCheckboxes(action);
    updateMixed();
  }

  // Event handlers.
  function onMixedKeydown(event) {
    if (event.key === " ") {
      event.preventDefault();
    }
  }

  function onMixedKeyup(event) {
    switch (event.key) {
      case " ":
        toggleMixed();
        event.stopPropagation();
        break;

      default:
        break;
    }
  }

  // Set up event listeners.
  mixedNode.addEventListener("keydown", onMixedKeydown);
  mixedNode.addEventListener("keyup", onMixedKeyup);
  mixedNode.addEventListener("click", toggleMixed);
  mixedNode.addEventListener("focus", () => mixedNode.classList.add("focus"));
  mixedNode.addEventListener("blur", () => mixedNode.classList.remove("focus"));

  for (let i = 0; i < checkboxNodes.length; i++) {
    const checkboxNode = checkboxNodes[i];

    checkboxNode.addEventListener("click", (event) => {
      event.currentTarget.setAttribute(
        "data-last-state",
        event.currentTarget.checked
      );
      updateMixed();
    });

    checkboxNode.addEventListener("focus", (event) => {
      event.currentTarget.parentNode.classList.add("focus");
    });

    checkboxNode.addEventListener("blur", (event) => {
      event.currentTarget.parentNode.classList.remove("focus");
    });

    checkboxNode.setAttribute("data-last-state", checkboxNode.checked);
  }

  // Initialize state.
  updateMixed();
}

// eslint-disable-next-line func-names
(function (Drupal) {
  Drupal.behaviors.selectAllOptions = {
    attach(context) {
      once("checkbox-mixed", ".checkbox-mixed", context).forEach((element) => {
        createCheckboxMixed(element);
      });
    },
  };
})(Drupal, once);
