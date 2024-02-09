# VA.gov Content Release

va.gov is built using two separate frontend systems with a third being built:

- content-build - This is the frontend for generating static content files. You can read more about the project here:
  https://github.com/department-of-veterans-affairs/content-build
- vets-website - This is the frontend for integrating React widgets. You can read more about the project here:
  https://github.com/department-of-veterans-affairs/vets-website
- next-build - This is the new frontend for generating static content files. You can read more about the project here:
  https://github.com/department-of-veterans-affairs/next-build

In order to allow developers of the CMS to preview changes with different versions of the frontends, we have
developed an on-demand content release workflow that is primarily used on the Tugboat QA servers.

## Content Build Releases

The current static frontend "content-build" can be rebuilt using different versions of content-build and
vets-website.

1. Go to "/admin/content/deploy/git".
2. Check if the "Release State" in the "Status Details" block is set to "Ready". If it isn't then submitting the
   form will do nothing.
2. Choose a version for content-build or leave at default.
3. Choose a version for vets-website or leave at default.
4. Click "Release content" to set the versions of content-build and vets-website as well as queue a
   "va_gov_content_release_request" job.
5. The "va_gov_content_release_request" job will be triggered in the background from a service running
   `scripts/queue_runner/queue_runner.sh` continuously. Locally, the script has to be triggered manually. See the
   [caveats](#caveats) section for more information.
6. The "va_gov_content_release_request" job will create a "va_gov_content_release_dispatch" job that ends up writing
   a "buildrequest" file.
7. The continuously running `scripts/queue_runner/queue_runner.sh` script will look for the "buildrequest" file and
   start a build if found.
8. Back on "/admin/content/deploy/git" you can view the build log via a link in the "Build log" section of the
   "Status Details" block.
9. After the build completes, an event subscriber, `ContinuousReleaseSubscriber`, adds another job to the
   "va_gov_content_release_request" queue if the build ran during business hours and continuous release is enabled in
   settings. This keeps the build process going indefinitely.
10. View the frontend at the provided "Front end link" link in the "Status Details" block.

There is a release state manager that can prevent any of these steps from happening, and you should check out the
`ReleaseStateManager` class for more information and details. There's a lot more to the process than what's outlined
above, but the above process should give you a rudimentary understanding of the way an on-demand release happens.

## Next Build Releases

The upcoming static frontend "next-build" can be rebuilt using different versions of next-build and vets-website. It
is a simpler process than the current content-build workflow.

1. Go to "/admin/content/deploy/next_git".
2. If the form elements are disabled, then a lock file exists preventing another build from being triggered. You
   can skip to step #6.
2. Choose a version for content-build or leave at default.
3. Choose a version for vets-website or leave at default. When content-build is releasing, these form fields might
   be disabled. We can't change the vets-website version while another frontend build is running.
4. Click "Release Content" to set the versions of next-build and vets-website as well as write a "buildrequest" file.
5. A `scripts/queue_runner/next_queue_runner.sh` script continuously runs in the background looking for the
   "buildrequest" file and then start a build if found. Locally, the script has to be triggered manually. See the
   [caveats](#caveats) section for more information.
6. Back on "/admin/content/deploy/git" you can view the build log via a link in the "Status" section of the
   "Next Build Information" block.
7. Once the build completes no new build will be triggered until you click to release content again.
8. View the frontend at the provided "View Preview" link in the "Next Build Information" block.

## Caveats

There are some caveats to the process outlined above.

- **Manually runnning the background script** - On `ddev` the `queue_runner` scripts aren't runnning continuously in
  background jobs. So you must `ddev ssh && ./scripts/queue_runner/` to kick off the content build or next build
  release locally. In the future, it might be a good idea to use `system.d` or `supervisor` or something else to
  keep the background jobs going locally just like on Tugoboat.
