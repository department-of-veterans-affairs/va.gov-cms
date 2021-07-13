<?php

/**
 * @file
 * Yet Another One-Time Migration for fixing improperly cloned nodes.
 *
 * The root of this problem is that some parent entities (nodes, etc) share
 * references to paragraphs, e.g.:
 *
 *   1. Node A, Revision 1 will refer to Paragraph I, Revision 1.
 *   2. Node B, Revision 2 will refer to Paragraph I, Revision 2.
 *
 * As Node A and Node B are saved, they will update the default revision of
 * Paragraph I.  The default revision of Paragraph I will thus point to a
 * different node depending on when it is consulted.
 *
 * At present, we're only worried about nodes.
 *
 * This issue can affect paragraphs nested within other paragraphs, but so long
 * as we repair the root paragraph, all child paragraphs will be repaired too.
 */

require_once __DIR__ . '/library.php';

$locked_nids = [];

create_site_alert();
switch_user();

$all_nids = get_currently_improperly_cloned_nodes();
foreach ($all_nids as $key => $nid) {
  try {
    log_message("Processing node $nid (" . ($key + 1) . '/' . count($all_nids) . ")...");
    process_node($nid);
  }
  catch (\Exception $exception) {
    switch ($exception->getCode()) {
      case EXCEPTION_COULD_NOT_LOCK:
        $locked_nids[] = $nid;
        log_message("Continuing...");
        break;

      default:
        throw $exception;
    }
  }
}

if (count($locked_nids)) {
  log_message("The following nodes could not be locked: " . json_encode($locked_nids));
}

delete_site_alert();
