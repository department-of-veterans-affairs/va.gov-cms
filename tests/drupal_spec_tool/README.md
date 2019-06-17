# Behat Feature Organization

Behat feature files that should run in all environments should go into the 'features-default' directory.

Behat feature files that can only run in certain environments should be symlinked from the 'features' directory. 
See the contents of the 'features/drupal-spec-tool' directory for example. menus.feature and views.feature files are configured to run in dev and local only.
