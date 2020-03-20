(function ($, Drupal) {
  Drupal.behaviors.vaGovTagTracker = {
    attach: function (context, settings) {
      // Build our gtm settings push object.
      const dataCollection = {
        'pagePath': settings.gtm_data.pagePath ? settings.gtm_data.pagePath : null,
        'pageTitle': settings.gtm_data.pageTitle ? settings.gtm_data.pageTitle : null,
        'firstSectionLevel': settings.gtm_data.firstSectionLevel ? settings.gtm_data.firstSectionLevel : null,
        'secondSectionLevel': settings.gtm_data.secondSectionLevel ? settings.gtm_data.secondSectionLevel : null,
        'thirdSectionLevel': settings.gtm_data.thirdSectionLevel ? settings.gtm_data.thirdSectionLevel : null,
        'fourthSectionLevel': settings.gtm_data.fourthSectionLevel ? settings.gtm_data.foourthSectionLevel : null,
        'nodeID': settings.gtm_data.nodeID ? settings.gtm_data.nodeID : null,
        'contentTitle': settings.gtm_data.contentTitle ? settings.gtm_data.contentTitle : null,
        'contentType': settings.gtm_data.contentType ? settings.gtm_data.contentType : null,
        'contentOwner': settings.gtm_data.contentOwner ? settings.gtm_data.contentOwner : null,
      }

      // For processing our click events.
      const pushGTM = (selector, event) => {
        // Push cms data into dataLayer.
        selector.forEach(el => {
          // Using jQuery begrudgingly. ES6 doesn't have a nice once() method.
          $(el, context).once().click(function (e) {
            dataCollection['event'] = event;
            dataLayer.push(dataCollection);
          });
        });
      }

      // The elements to track.
      const targets = [
        {
          selector: document.querySelectorAll('ul.toolbar-menu.top-level-nav > li > a'),
          event: 'top-level-nav'
        },
        {
          selector: document.querySelectorAll('ul.toolbar-menu.lower-level-nav li a'),
          event: 'lower-level-nav'
        },
        {
          // Edit tab on node page.
          selector: document.querySelectorAll('li.tabs__tab a[rel="edit-form"]'),
          event: 'content-edit'
        },
        {
          // Edit button on content view page.
          selector: document.querySelectorAll('.views-field-edit-node a.button'),
          event: 'content-edit'
        },
        {
          // Edit button on bulk content view page.
          selector: document.querySelectorAll('.views-field-operations li.dropbutton-action a'),
          event: 'content-edit'
        },
        {
          selector: document.querySelectorAll('#edit-actions.form-actions [value="Save"]'),
          event: 'content-save'
        },
        {
          selector: document.querySelectorAll('.node-preview-button'),
          event: 'content-preview'
        },
        {
          selector: document.querySelectorAll('#edit-actions.form-actions [value="Save and continue editing"]'),
          event: 'content-save-and-continue'
        },
        {
          selector: document.querySelectorAll('#edit-actions.form-actions a.button:last-child'),
          event: 'content-unlock'
        },
      ];

      // Send it off to GTM on click.
      targets.forEach(e => {
        pushGTM(e.selector, e.event);
      });

      // Send data to GTM onLoad.
      const gtmPageLoadPush = () => {
        dataCollection['event'] = 'pageLoad';
        dataLayer.push(dataCollection);
      }

      window.addEventListener('load', gtmPageLoadPush);

    }
  };

})(jQuery, Drupal);
