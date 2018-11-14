#VA Migration

1. Run _lando composer install_
2. Install VA.gov Migrate and Migrate Tools modules
3. Run migration
   * From command line:  
     _lando drush mim va_gov_html_import_  
     **or**
   * From Drupal
     1. Go to Structure / Migrations
     2. For Migration Group, 'Migrate VA.gov website', click 'List Migrations'
     3. For Migration, 'Import selected pages from va.gov', click 'Execute'
     4. On next page, click 'Execute' again.
 4. Go to Content
 5. Confirm that 55 Basic Pages were created
 
 Notes: CKEditor is set to strip div tags from body, so anchor links won't work. 