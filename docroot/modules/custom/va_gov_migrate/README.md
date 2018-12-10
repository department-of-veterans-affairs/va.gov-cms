#VA Migration

##Healthcare Migration
1. Run _lando composer install_
2. Install VA.gov Migrate (va_gov_migrate) and Migrate Tools (migrate_tools) modules
3. Run migration
   * From command line:  
     _lando drush mim va_healthcare_
      
     **or**
   * From Drupal
     1. Go to Structure / Migrations
     2. For Migration Group, 'Migrate VA.gov website', click 'List Migrations'
     3. For Migration, 'Import healthcare pages from va.gov', click 'Execute'
     4. On next page, click 'Execute' again.
 
 Notes: CKEditor is set to strip div tags from body, so anchor links won't work. 
 