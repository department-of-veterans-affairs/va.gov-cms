/**
 * @file
 * A tri-state 'select all options' checkbox behavior.
 *
 * It is derived from the tri-state checkbox example from
 * https://dequeuniversity.com/library/aria/checkbox-tri and made into a Drupal
 * behavior.
 */
// eslint-disable-next-line func-names
(function (Drupal) {
  function initSelectAllOptions(domNode) {
    // Find the select all checkbox within this container.
    const selectAllCheckbox = domNode.querySelector(
      ".select-all-options-checkbox"
    );
    if (!selectAllCheckbox) {
      return;
    }

    // Add ARIA role and attributes
    selectAllCheckbox.setAttribute("role", "checkbox");
    selectAllCheckbox.setAttribute("aria-checked", "false");
    selectAllCheckbox.setAttribute("tabindex", "0");

    // Find all other checkboxes in this container.
    const checkboxNodes = domNode.querySelectorAll(
      'input[type="checkbox"]:not(.select-all-options-checkbox)'
    );

    function updateSelectAllState() {
      let count = 0;
      const total = checkboxNodes.length;

      for (let i = 0; i < checkboxNodes.length; i++) {
        if (checkboxNodes[i].checked) {
          count += 1;
        }
      }

      // Update the select all checkbox state.
      if (count === 0) {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;
        selectAllCheckbox.setAttribute("aria-checked", "false");
      } else if (count === total) {
        selectAllCheckbox.checked = true;
        selectAllCheckbox.indeterminate = false;
        selectAllCheckbox.setAttribute("aria-checked", "true");
      } else {
        selectAllCheckbox.checked = true;
        selectAllCheckbox.indeterminate = true;
        selectAllCheckbox.setAttribute("aria-checked", "mixed");
      }
    }

    function setAllCheckboxes(value) {
      for (let i = 0; i < checkboxNodes.length; i++) {
        checkboxNodes[i].checked = value;
        // Trigger change event on each checkbox to ensure state updates.
        const event = new Event("change", { bubbles: true });
        checkboxNodes[i].dispatchEvent(event);
      }
      updateSelectAllState();
    }

    function onSelectAllChange(event) {
      const { checked } = event.target;
      setAllCheckboxes(checked);
    }

    function onCheckboxChange() {
      updateSelectAllState();
    }

    function onKeydown(event) {
      if (event.key === " ") {
        event.preventDefault();
      }
    }

    function onKeyup(event) {
      if (event.key === " ") {
        event.preventDefault();
        const newState = !selectAllCheckbox.checked;
        selectAllCheckbox.checked = newState;
        setAllCheckboxes(newState);
      }
    }

    function onFocus() {
      selectAllCheckbox.classList.add("focus");
    }

    function onBlur() {
      selectAllCheckbox.classList.remove("focus");
    }

    // Add event listeners
    selectAllCheckbox.addEventListener("change", onSelectAllChange);
    selectAllCheckbox.addEventListener("keydown", onKeydown);
    selectAllCheckbox.addEventListener("keyup", onKeyup);
    selectAllCheckbox.addEventListener("focus", onFocus);
    selectAllCheckbox.addEventListener("blur", onBlur);

    for (let i = 0; i < checkboxNodes.length; i++) {
      checkboxNodes[i].addEventListener("change", onCheckboxChange);
      // Also listen for click events to ensure state updates
      checkboxNodes[i].addEventListener("click", onCheckboxChange);
    }

    // Initial state
    updateSelectAllState();
  }

  Drupal.behaviors.selectAllOptions = {
    attach(context) {
      const containers = context.querySelectorAll(".select-all-options");
      for (let i = 0; i < containers.length; i++) {
        initSelectAllOptions(containers[i].parentNode);
      }
    },
  };
})(Drupal);
