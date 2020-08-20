/**
 * @file
 */

(function (w, d, s, l, i) {
  w[l] = w[l] || [];
  w[l].push({
    'gtm.start': new Date().getTime(),
    event: 'gtm.js'
  });
  let env = "5";
  if (drupalSettings.environmentIndicator) {
    env = drupalSettings.environmentIndicator.name === "Production" ? "2" : "5";
  }
  var f = d.getElementsByTagName(s)[0],
    j = d.createElement(s),
    dl = l != 'dataLayer' ? '&l=' + l : '';
  j.async = true;
  j.src = 'https://www.googletagmanager.com/gtm.js?id=' + i + dl + '&gtm_auth=hHvHnY1eIx1IOSJMAnOBLA&gtm_preview=env-' + env + '&gtm_cookies_win=x';
  f.parentNode.insertBefore(j, f);
})(window, document, 'script', 'dataLayer', 'GTM-WQ3DLLB');
