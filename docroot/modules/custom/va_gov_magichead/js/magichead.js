/**
 * @file
 * Magichead behaviors.
 */
(function (Drupal, $) {

  function isValidSwap() {return true;}

  function onDrop() {
    // todo: the below is hacky proof of concept only.
    // it doesn't care if things are actually errors
    // it adds a new element each time, doesn't clean up after itself
    var dragObject = this;
    var $rowElement = $(dragObject.rowObject.element);
    $rowElement.get(0).classList.add('error');
    var newElement = document.createElement('div');
    newElement.textContent = 'HIHIHI';
    newElement.classList.add('form-item__error-message');
    $rowElement.get(0).insertAdjacentElement('afterend', newElement);
    console.log($rowElement);
    return null;
  }

  Drupal.behaviors.vaGovMagicheadMagichead = {
    attach: function (context, settings) {
      if (typeof Drupal.tableDrag === "undefined") {
        return;
      }

      const tables =
        document.querySelectorAll(
          "div.field--type-magichead table.field-multiple-table.draggable-table"
        ) || [];
      tables.forEach(function (element, index) {
        const id = element.getAttribute('id');
        if (Drupal.tableDrag[id]) {
          const tableDrag = Drupal.tableDrag[id];
          // tableDrag instance method overrides:
          // tableDrag.row.prototype.isValidSwap = isValidSwap;
          tableDrag.onDrop = onDrop;
        }
      });
      // var tableDrag = Drupal.tableDrag["field-va-benefit-eligibility-ov-values"];
      // tableDrag.onDrop = function () {```
      //   window.alert(Drupal.t('You cannot move here'));
      //
      // };
      // var dropRow = Drupal.tableDrag.prototype.dropRow;
      // Drupal.tableDrag.prototype.dropRow = function (event, self) {
      //   console.log("You dropeed a row");
      //   dropRow.apply(event, self);
      // };

      // tableDrag.row.prototype.isValidSwap = function (row) {
      //   return false;
      // };

    },
    // try isValidSwap()?



  };
})(Drupal, jQuery);
