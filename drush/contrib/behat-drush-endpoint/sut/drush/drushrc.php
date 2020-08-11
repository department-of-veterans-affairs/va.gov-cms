<?php

// Add our commandfile into the cached commandfile context. Only do this for testing.
$drush_extension_namespace = '\\Drush\\Commands\\behat_drush_endpoint\\BehatDrushEndpointCommands';
$drush_extension_filepath = dirname(dirname(__DIR__)) . '/BehatDrushEndpointCommands.php';
$annotation_commandfiles = drush_get_context('DRUSH_ANNOTATED_COMMANDFILES');
$annotation_commandfiles[$drush_extension_filepath] = $drush_extension_namespace;
$annotation_commandfiles[$drush_hook_filepath] = $drush_extension_namespace;
drush_set_context('DRUSH_ANNOTATED_COMMANDFILES', $annotation_commandfiles);
