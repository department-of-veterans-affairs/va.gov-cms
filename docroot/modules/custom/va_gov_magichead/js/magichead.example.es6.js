/**
 * @file
 * DO NOT USE IN PRODUCTION.
 * These are example Magichead behaviors. Because it is highly likely we will
 * need to adapt tableDrag core behavior in the future, I wanted to preserve
 * knowledge I gained by studying the tableDrag.js API.
 */
((Drupal) => {
  /**
   * Demonstrates the isValidSwap() method override.
   *
   * If a swap is invalid, the user experience is that the dragged row does not
   * move at all.
   *
   * @return {boolean} true if swap is valid.
   */
  function isValidSwap() {
    // Do some business logic/calculations/what have you.
    return true;
  }

  /**
   * Demonstrates how to add an inline error message when a row is dropped.
   *
   * @return {null} returns null to match parent method.
   */
  function onDrop() {
    // The below is hacky proof of concept only. It doesn't care if there are
    // any actual errors, it flags an error every time a row is dropped. It also
    // adds a new error element each time, and doesn't clean up after itself.
    // This makes the drag/drop UX pretty bad, and can break it easily, so don't
    // use this code at all. it is for demonstration purposes only.
    const dragObject = this;
    const rowElement = dragObject.rowObject.element;
    rowElement.classList.add("error");
    const newElement = document.createElement("div");
    newElement.textContent = "Max Depth Exceeded! 😡";
    newElement.classList.add("form-item__error-message");
    rowElement.insertAdjacentElement("afterend", newElement);
    return null;
  }

  Drupal.behaviors.vaGovMagicheadMagichead = {
    attach() {
      if (typeof Drupal.tableDrag === "undefined") {
        return;
      }

      // The Table element (Drupal\Core\Render\Element\Table) defines a
      // pre_render method, Table::preRenderTable(), which calls
      // drupal_attach_tabledrag(). drupal_attach_tabledrag() takes #tabledrag
      // render array properties from the provided element and attaches them as
      // objects in drupalSetting. On the frontend core
      // tableDrag.js::initTableDrag() is called (part of tableDrag.js's
      // attach() method) and instantiates new Drupal.tableDrag objects by
      // pulling all the tableDrag objects out of drupalSettings. The instances
      // are then preserved in the Drupal global object at the namespace
      // Drupal.tableDrag[instance-name] (The instance-name is derived from the
      // field name in the case of magichead). In the below code we dig the
      // instantiated tableDrag object out of the Drupal global and assign it to
      // a local variable `tableDrag`. Doing this gives us access to the
      // tableDrag.js API, and it's related prototype methods.
      const tables =
        document.querySelectorAll(
          "div.field--type-magichead table.field-multiple-table.draggable-table"
        ) || [];
      tables.forEach((element) => {
        const id = element.getAttribute("id");
        if (Drupal.tableDrag[id]) {
          const tableDrag = Drupal.tableDrag[id];
          // tableDrag instance method overrides:
          tableDrag.row.prototype.isValidSwap = isValidSwap;
          tableDrag.onDrop = onDrop;
        }
      });
    },
  };
})(Drupal);
