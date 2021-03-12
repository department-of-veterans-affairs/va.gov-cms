#!/bin/bash

echo "Beginning Compare script"
rm -rf docroot/sites/default/files/cms-export-content
rm -rf compare
echo "Running initial export"
lando drush va-gov-cms-export-all-content --process-count=8 --entity-count=500 --delete-existing
mv docroot/sites/default/files/compare/cms-export-content compare/cms-export-content-orig
echo ""
lando drush scr ../scripts/content/VACMS-3450-paragraphs-should-have-a-single-parent.php
lando drush va-gov-cms-export-all-content --process-count=8 --entity-count=500 --delete-existing
mv docroot/sites/default/files/cms-export-content compare/cms-export-content-new
cd compare
diff -ur cms-export-content-orig/ cms-export-content-new/ > cms-export.diff
