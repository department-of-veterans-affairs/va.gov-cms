/**
 * @file
 */

((Drupal) => {
  Drupal.behaviors.smartDateRecurringInteractions = {
    attach() {
      // Format dates for titles and restrict max date limits in inputs.
      const dateFieldHandler = () => {
        const dateFields = [
          document.getElementById(
            "edit-field-datetime-range-timezone-0-time-wrapper-value-date"
          ),
          document.getElementById(
            "edit-field-datetime-range-timezone-0-time-wrapper-end-value-date"
          ),
          document.getElementById(
            "edit-field-datetime-range-timezone-0-repeat-end-date"
          ),
        ];
        const timeFields = [
          document.getElementById(
            "edit-field-datetime-range-timezone-0-time-wrapper-value-time"
          ),
          document.getElementById(
            "edit-field-datetime-range-timezone-0-time-wrapper-end-value-time"
          ),
        ];
        const today = new Date().toLocaleDateString();
        dateFields.forEach((element) => {
          element.title = `Date (e.g. ${today})`;
        });
        timeFields.forEach((element) => {
          element.title = `Time (e.g. 10:00 AM)`;
        });

        // We will use this for setting our date max values.
        const getYearMonthDay = (increment = "end") => {
          const dateObj = new Date();
          const month = dateObj.getUTCMonth() + 1;
          const formattedMonth = month.toString().padStart(2, "0");
          const day = dateObj.getUTCDate();
          const year =
            increment === "end"
              ? dateObj.getUTCFullYear() + 1
              : dateObj.getUTCFullYear() - 1;
          return {
            full: `${year}-${formattedMonth}-${day}`,
            year: dateObj.getUTCFullYear(),
            maxyear: dateObj.getUTCFullYear() + 1,
            month,
            day,
          };
        };
        // We don't want users inputting dates > 1 year forward.
        const limitDateRangeInputOnKeyUp = (e) => {
          const inputDate = e.srcElement.value.split("-");
          const { maxyear } = getYearMonthDay();
          if (
            inputDate[0] !== undefined &&
            inputDate[1] !== undefined &&
            inputDate[2] !== undefined &&
            inputDate[0] >= 2 &&
            inputDate[0] > maxyear
          ) {
            e.srcElement.value = `${maxyear}-${inputDate[1]}-${inputDate[2]}`;
            if (
              e.srcElement.id ===
              "edit-field-datetime-range-timezone-0-time-wrapper-value-date"
            ) {
              document.getElementById(
                "edit-field-datetime-range-timezone-0-time-wrapper-end-value-date"
              ).value = `${maxyear}-${inputDate[1]}-${inputDate[2]}`;
            }
          }
        };

        dateFields.map((item) => {
          item.min = getYearMonthDay("start").full;
          item.max = getYearMonthDay("end").full;
          item.addEventListener("keyup", limitDateRangeInputOnKeyUp);
          return item;
        });
      };

      const checkInstanceBox = () => {
        if (
          document.getElementById(
            "edit-field-datetime-range-timezone-0-manage-instances"
          )
        ) {
          document.getElementById(
            "edit-field-datetime-range-timezone-0-make-recurring"
          ).checked = true;
        }
      };

      const hideNoneOption = () => {
        const recurringToggle = document.getElementById(
          "edit-field-datetime-range-timezone-0-make-recurring"
        );
        if (recurringToggle) {
          recurringToggle.addEventListener("click", () => {
            const options = document.querySelectorAll(
              "#edit-field-datetime-range-timezone-0-repeat option"
            );
            options[0].style.display = "none";
            if (recurringToggle.checked === false) {
              options[0].selected = true;
            } else {
              options[1].selected = true;
            }
          });
        }
      };

      const createFauxInstancesButton = () => {
        if (
          !document.getElementById(
            "edit-field-datetime-range-timezone-0-manage-instances"
          ) &&
          !document.getElementById("manage-instances-faux-button")
        ) {
          const fauxButton = document.createElement("button");
          fauxButton.innerHTML = "Edit event series";
          fauxButton.disabled = true;
          fauxButton.id = "manage-instances-faux-button";
          fauxButton.classList.add(
            "button",
            "button--small",
            "manage-instances",
            "use-ajax"
          );
          const fauxButtonSpan = document.createElement("span");
          fauxButtonSpan.innerHTML = "Save changes first before editing series";
          fauxButtonSpan.id = "manage-instances-faux-button-span";
          document
            .getElementById("recurring-items-reveal-wrap")
            .before(fauxButton);
          document
            .getElementById("manage-instances-faux-button")
            .after(fauxButtonSpan);
        }
      };

      // Get our "Make recurring" checkboxes.
      const recurringToggles = document.querySelectorAll(
        ".make-recurring-toggle"
      );
      const resetWrapDisplay = (array) => {
        array.forEach((element) => {
          element.style.display = "none";
        });
      };
      // Append the div with recurring options to the bool toggle.
      recurringToggles[0].parentElement.after(
        document.getElementById("recurring-items-reveal-wrap")
      );

      const wrapDisplayHandler = (
        value,
        allWrapsArray,
        daysWrap,
        advancedWhichRepeatWrap
      ) => {
        resetWrapDisplay(allWrapsArray);
        switch (value) {
          case "WEEKLY":
            daysWrap.style.display = "block";
            break;
          case "MONTHLY":
            advancedWhichRepeatWrap.style.display = "block";
            break;

          default:
            break;
        }
      };
      const recurringWatchers = () => {
        checkInstanceBox();
        hideNoneOption();
        dateFieldHandler();
        createFauxInstancesButton();
        recurringToggles.forEach((toggle) => {
          const daysWrap = document.getElementById(
            `edit-field-datetime-range-timezone-0-repeat-advanced-byday--wrapper`
          );
          const advancedWhichRepeatWrap = document.getElementById(
            `repeat-on-the-wrap`
          );
          const allWrapsArray = [daysWrap, advancedWhichRepeatWrap];
          // Disallow toggle off when instances present.
          toggle.addEventListener("click", recurringWatchers);
          if (
            document.getElementById(
              `edit-field-datetime-range-timezone-manage-instances`
            )
          ) {
            toggle.checked = true;
            toggle.disabled = true;
          }
          // Show the instances div when the recurring checkbox is checked.
          if (toggle.checked) {
            document.getElementById(
              "recurring-items-reveal-wrap"
            ).style.display = "block";
          } else {
            document.getElementById(
              "recurring-items-reveal-wrap"
            ).style.display = "none";
          }

          const recurrenceSelectorType = document.getElementById(
            `edit-field-datetime-range-timezone-0-repeat`
          );
          // Show our time repeat increment selection on load.
          wrapDisplayHandler(
            recurrenceSelectorType.value,
            allWrapsArray,
            daysWrap,
            advancedWhichRepeatWrap
          );
          // Show our time repeat increment selection on checnge.
          recurrenceSelectorType.addEventListener("change", (e) => {
            wrapDisplayHandler(
              e.target.value,
              allWrapsArray,
              daysWrap,
              advancedWhichRepeatWrap
            );
          });
        });
      };
      window.addEventListener("DOMContentLoaded", recurringWatchers);
    },
  };
})(Drupal);
