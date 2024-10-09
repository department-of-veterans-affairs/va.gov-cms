# How to patch in this project

We have moved away from pointing our patches to drupal.org in the event of losing access to those remote files. We will continue to contribute any relevant patches there, but we will only point our patches to local files.

## Naming patch files

The naming convention for patches is as follows.

In the case that it is a patch we want to upload to drupal, we want the format to be the issue number followed by a hyphen and then the title of the issue with hyphens inbetween words in all lower case (example: 11111-title-of-the-issue.patch).

In the case that it is a local patch only (one we do not want to submit to drupal), we will follow the repository name, then ticket number, then description with hyphens inbetween all spaces in all lower case (example: VACMS-1111-title-of-the-issue.patch). Note: in the event that the ticket is not longer available but you can track the pull request, insert "PR-####" (example: VACMS-1111-PR-1111-title-of-the-issue.patch).

Replace all special characters (like colons, underscores, and apostraphes) with hyphens to ensure the file name is acceptable. Also, make sure to have an empty line at the bottom of the file to ensure consistency.

## Patching the file

Please follow the guidelines provided by drupal in terms of how to apply and contribute patches [here](https://www.drupal.org/docs/develop/git/using-git-to-contribute-to-drupal/working-with-patches/applying-a-patch-in-a-feature-branch).
