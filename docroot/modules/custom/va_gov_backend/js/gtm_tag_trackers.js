/**
 * @file
 */

(function ($, Drupal) {
  Drupal.behaviors.vaGovTagTracker = {
    attach: function (context, settings) {

      // Handle different title data scenarios.
      function titleResolver(data) {
        let title = null;
        if (data) {
          title = data;
          if (data['#markup']) {
            title = data['#markup'];
          }
        }
        return title;
      }

      // Walk the main nav tree from point of click.
      function menuTraverser(item) {
        parentClasses = item.parentNode.className;
        let level4, level3, level2, level1 = null;

        if (parentClasses.includes('menu-level-3')) {
          level4 = item;
          level3 = item.closest('li.menu-item--expanded.menu-level-2').firstElementChild;
          level2 = level3.closest('li.menu-item--expanded.menu-level-1').firstElementChild;
          level1 = level2.closest('li.menu-item--expanded.menu-level-0').firstElementChild;
        }
        if (parentClasses.includes('menu-level-2')) {
          level3 = item;
          level2 = item.closest('li.menu-item--expanded.menu-level-1').firstElementChild;
          level1 = level2.closest('li.menu-item--expanded.menu-level-0').firstElementChild;
        }
        if (parentClasses.includes('menu-level-1')) {
          level2 = item;
          level1 = item.closest('li.menu-item--expanded.menu-level-0').firstElementChild;
        }
        if (parentClasses.includes('menu-level-0')) {
          level1 = item;
        }
        dataCollection['firstSectionLevel'] = level1 ? level1.textContent : '';
        dataCollection['secondSectionLevel'] = level2 ? level2.textContent : '';
        dataCollection['thirdSectionLevel'] = level3 ? level3.textContent : '';
        dataCollection['fourthSectionLevel'] = level4 ? level4.textContent : '';
      }

      // Build our gtm settings push object.
      const dataCollection = {
        'pagePath': settings.gtm_data.pagePath ? settings.gtm_data.pagePath : null,
        'pageTitle': titleResolver(settings.gtm_data.pageTitle),
        'nodeID': settings.gtm_data.nodeID ? settings.gtm_data.nodeID : null,
        'contentTitle': settings.gtm_data.contentTitle ? settings.gtm_data.contentTitle : null,
        'contentType': settings.gtm_data.contentType ? settings.gtm_data.contentType : null,
        'contentOwner': settings.gtm_data.contentOwner ? settings.gtm_data.contentOwner : null,
      }

      // For processing our click events.
      function pushGTM(selector, event, subtype) {
        const editPageTypes = ['content-page', 'bulk-content-page'];
        // Push cms data into dataLayer.
        selector.forEach(function (el, i) {
          $(el, context).once().click(function (e) {
            dataCollection['event'] = event;

            // Special handling for content admin pages.
            if (editPageTypes.includes(subtype)) {
              dataCollection['contentTitle'] = $(el).parent().siblings('.views-field-title').text().trim();
              dataCollection['contentType'] = $(el).parent().siblings('.views-field-type').text().trim();
              dataCollection['contentOwner'] = $(el).parent().siblings('.views-field-field-administration').text().trim();
            }

            // Special handling for menu nav items.
            menuTraverser(el);
            // Unset old Unique ID.
            dataCollection['gtm.uniqueEventId'] = '';
            // Now send it to the dataLayer.
            dataLayer.push(dataCollection);
          });
        });
      }

      // The elements to track.
      const targets = [
        {
          selector: document.querySelectorAll('ul.toolbar-menu.top-level-nav > li > a'),
          event: 'top-level-nav',
          subtype: false
        },
        {
          selector: document.querySelectorAll("ul.toolbar-menu.lower-level-nav li a"),
          event: 'lower-level-nav',
          subtype: false
        },
        {
          // Edit tab on node page.
          selector: document.querySelectorAll('li.tabs__tab a[rel="edit-form"]'),
          event: 'content-edit',
          subtype: 'node-page'
        },
        {
          // Edit button on content view page.
          selector: document.querySelectorAll('.views-field-edit-node a.button'),
          event: 'content-edit',
          subtype: 'content-page'
        },
        {
          // Edit button on bulk content view page.
          selector: document.querySelectorAll('.views-field-operations li.edit > a'),
          event: 'content-edit',
          subtype: 'bulk-content-page'
        },
        {
          selector: document.querySelectorAll('#edit-actions.form-actions [value="Save"]'),
          event: 'content-save',
          subtype: false
        },
        {
          selector: document.querySelectorAll('.node-preview-button'),
          event: 'content-preview',
          subtype: false
        },
        {
          selector: document.querySelectorAll('#edit-actions.form-actions [value="Save and continue editing"]'),
          event: 'content-save-and-continue',
          subtype: false
        },
        {
          selector: document.querySelectorAll('#edit-actions.form-actions a.button:last-child'),
          event: 'content-unlock',
          subtype: false
        },
      ];

      // Send it off to GTM on click.
      targets.forEach(function (e) {
        pushGTM(e.selector, e.event, e.subtype);
      });

      // Send data to GTM onLoad.
      function gtmPageLoadPush() {
        dataCollection['event'] = 'pageLoad';
        dataLayer.push(dataCollection);
      }

      // Three behaviors loaded on edit pages.
      // This causes function to fire three times on page load.
      // Once method used to resolve.
      $(window, context).once('vaGovTagTracker').on('load', function () {
        gtmPageLoadPush();
      });

    }
  };

})(jQuery, Drupal);
