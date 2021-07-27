<?php

// phpcs:ignoreFile

/**
* Fast 404 settings:
*
* Fast 404 will do two separate types of 404 checking.
*
* The first is to check for URLs which appear to be files or images. If Drupal
* is handling these items, then they were not found in the file system and are
* a 404.
*
* The second is to check whether or not the URL exists in Drupal by checking
* with the menu router, aliases and redirects. If the page does not exist, we
* will server a fast 404 error and exit.
*
* @see modules/contrib/fast_404/example.settings.fast404.php for updates
*/

# Load the fast_404.inc file. This is needed if you wish to do extension
# checking in settings.php.
if (file_exists($app_root . '/' . $site_path . '/modules/contrib/fast_404/fast404.inc')) {
  include_once $app_root . '/' . $site_path . '/modules/contrib/fast_404/fast404.inc';
}

# Disallowed extensions. Any extension in here will not be served by Drupal and
# will get a fast 404.
# Default extension list, this is considered safe and is even in queue for
# Drupal 8 (see: http://drupal.org/node/76824).
$conf['fast_404_exts'] = '/^(?!robots).*\.(txt|png|gif|jpe?g|css|js|ico|swf|flv|cgi|bat|pl|dll|exe|asp)$/i';

# If you use a private file system use the conf variable below and change the
# 'sites/default/private' to your actual private files path
# $conf['fast_404_exts'] = '/^(?!robots)^(?!sites\/default\/private).*\.(txt|png|gif|jpe?g|css|js|ico|swf|flv|cgi|bat|pl|dll|exe|asp)$/i';

# If you are using the Advanced Help module, the following config may be used
# to allow paths starting with 'help'.
$conf['fast_404_exts'] = '/^(?!help\/)(?!robots).*\.(txt|png|gif|jpe?g|css|js|ico|swf|flv|cgi|bat|pl|dll|exe|asp)$/';

# If you would prefer a stronger version of NO then return a 410 instead of a
# 404. This informs clients that not only is the resource currently not present
# but that it is not coming back and kindly do not ask again for it.
# Reference: http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
# $conf['fast_404_return_gone'] = TRUE;

# Allow anonymous users to hit URLs containing 'imagecache' even if the file
# does not exist. TRUE is default behavior. If you know all imagecache
# variations are already made set this to FALSE.
$conf['fast_404_allow_anon_imagecache'] = TRUE;

# If you use FastCGI, uncomment this line to send the type of header it needs.
# Reference: http://php.net/manual/en/function.header.php
# $conf['fast_404_HTTP_status_method'] = 'FastCGI';

# Extension list requiring whitelisting to be activated **If you use this
# without whitelisting enabled your site will not load!
//$conf['fast_404_exts'] = '/\.(txt|png|gif|jpe?g|css|js|ico|swf|flv|cgi|bat|pl|dll|exe|asp|php|html?|xml)$/i';

# Default fast 404 error message.
$conf['fast_404_html'] = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN" "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><head><title>404 Not Found</title></head><body><h1>Not Found</h1><p>The requested URL "@path" was not found on this server.</p></body></html>';

# Check paths during bootstrap and see if they are legitimate.
$conf['fast_404_path_check'] = FALSE;

# If enabled, you may add extensions such as xml and php to the
# $conf['fast_404_exts'] above. BE CAREFUL with this setting as some modules
# use their own php files and you need to be certain they do not bootstrap
# Drupal. If they do, you will need to whitelist them too.
$conf['fast_404_url_whitelisting'] = FALSE;

# Array of whitelisted files/urls. Used if whitelisting is set to TRUE.
$conf['fast_404_whitelist'] = array('index.php', 'rss.xml', 'install.php', 'cron.php', 'update.php', 'xmlrpc.php');

# Array of whitelisted URL fragment strings that conflict with fast_404.
$conf['fast_404_string_whitelisting'] = array('cdn/farfuture', '/advagg_');

# By default we will show a super plain 404, because usually errors like this are shown to browsers who only look at the headers.
# However, some cases (usually when checking paths for Drupal pages) you may want to show a regular 404 error. In this case you can
# specify a URL to another page and it will be read and displayed (it can't be redirected to because we have to give a 30x header to
# do that. This page needs to be in your docroot.
#$conf['fast_404_HTML_error_page'] = './my_page.html';

# By default the custom 404 page is only loaded for path checking. Load it for all 404s with the below option set to TRUE
$conf['fast_404_HTML_error_all_paths'] = FALSE;

# Call the extension checking now. This will skip any logging of 404s.
# Extension checking is safe to do from settings.php. There are many
# examples of this on Drupal.org.
//fast_404_ext_check();

# Path checking. USE AT YOUR OWN RISK (only works with MySQL).
# Path checking at this phase is more dangerous, but faster. Normally
# Fast_404 will check paths during Drupal boostrap via hook_boot. Checking
# paths here is faster, but trickier as the Drupal database connections have
# not yet been made and the module must make a separate DB connection. Under
# most configurations this DB connection will be reused by Drupal so there
# is no waste.
# While this setting finds 404s faster, it adds a bit more load time to
# regular pages, so only use if you are spending too much CPU/Memory/DB on
# 404s and the trade-off is worth it.
# This setting will deliver 404s with less than 2MB of RAM.
//fast_404_path_check();
