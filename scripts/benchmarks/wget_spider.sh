#!/usr/bin/env bash

# Execute a login on a local Drupal 8/Drupal 9 instance and store the cookies
# for later requests.
#
# The site variable should probably remain as bare-metal as possible.  I don't
# think running this and related script remotely is a good idea; measurements
# could be subject to network conditions and the script could have deleterious
# effects on other systems and inadvertently be perceived as an attack.
#
# Do not run this script on production, and don't run it anywhere that you do
# not have permission to run it.
#
# The username and password are not guaranteed to function in perpetuity, and
# may need to be changed in the event of my untimely death, termination, dis-
# integration, ascension to a higher plane of existence, upload into the sin-
# gularity, or other such event.
#
#
set -e;

site="http://localhost";
name="axcsd452ksey";
password="drupal8";
cookies_file="$(mktemp)";

# Retrieve session cookie.
wget \
  --keep-session-cookies \
  --save-cookies "${cookies_file}" \
  --load-cookies "${cookies_file}" \
  "${site}/user" 2>&1;

# Log in to the site and store the cookies in the cookie file.
wget \
  --keep-session-cookies \
  --save-cookies "${cookies_file}" \
  --load-cookies "${cookies_file}" \
  --post-data="name=${name}&pass=${password}&op=Log%20in&form_id=user_login_form" \
  "${site}/user/login" 2>&1;

# Perform a recursive (one-level deep) fetch of pages available from the /admin
# page.
#
# The `--level` argument controls the depth of recursion.  Increasing it will
# have a dramatic effect on the runtime of the command.
time wget \
  --recursive \
  --level=1 \
  --delete-after \
  --keep-session-cookies \
  --save-cookies="${cookies_file}" \
  --load-cookies="${cookies_file}" \
  --reject-regex="run-cron.*|update.php" \
  --exclude-directories="/user" \
  "${site}/admin";
