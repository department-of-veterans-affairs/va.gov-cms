# VA Gov Post API

This module is the local counterpart to the contrib post_api module.

This module handles the queueing and posting of the following:

* Facility data changes to the Facility API.
* Service detail changes to the Facility API.
* Notifying Slack if an item in the queue fails to process.

## Key paths

  * The queue /admin/config/post-api/queue
  * Force queuing of all facilities or services /admin/config/va-gov-post-api/facility-force-queue
  * Configuration /admin/config/va-gov-post-api/config

## Debugging
  Force pushes generate a log of all items processed for adding to the queue.
  /sites/default/files/post_api_force_queue.<Y-m-d--H>.log


https://www.drupal.org/project/post_api
