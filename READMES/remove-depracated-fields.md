# Removing deprecated fields

1. Remove all graphql, template, and transformer references from vets-website codebase. Example Pr: https://github.com/department-of-veterans-affairs/vets-website/pull/15791
2. After #1 is merged and deployed to prod, remove field instance from va.gov-cms config. Example Pr: https://github.com/department-of-veterans-affairs/va.gov-cms/pull/4093
3. After #2 is merged and deployed to prod, remove empty field tables from db. Example Pr: https://github.com/department-of-veterans-affairs/va.gov-cms/pull/4116
```
/**
 * Remove nickname tables from db.
 */
function va_gov_backend_update_8010() {

  $tables = [
    'node__field_nickname_for_this_facility',
    'node_revision__field_nickname_for_this_facility',
  ];

  $schema = Database::getConnection()->schema();

  foreach ($tables as $table) {
    $schema->dropTable($table);
    Drupal::logger('va_gov_backend')->log(LogLevel::INFO, 'Deleted "%table" from db', [
      '%table' => $table,
    ]);
  }
}
```

## Related

1. [Content model changes](https://github.com/department-of-veterans-affairs/va.gov-team/blob/master/platform/cms/product-team-support/content-model-changes.md)
