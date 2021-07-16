#!/usr/bin/env bash

# This script extracts request paths from the Apache acesss log for use by a
# load testing application, e.g. Siege.  The output should normally be re-
# directed into a file.
#
# In our current config, the seventh column of the access_log is the request
# path.
#
# Paths are read from the file randomly, so we can `sort` and `uniq` the file.
#
# Some requests come in for the path '*', which is not useful, so we extract
# it explicitly.
#
# It's not terribly useful to request static files, as they're probably going
# to be handled by the static file cache or even a separate application
# entirely (e.g. Varnish), so we strip them out as well.
#
# Finally, we prefix each path with the local base URL, because Siege does
# not accept a base URL parameter or otherwise handle mere paths.
cat /var/log/httpd/access_log \
  | awk '{ print $7 }' \
  | sort \
  | uniq \
  | grep -v '\*' \
  | grep -vE '(jpe?g|png|gif|pdf)$' \
  | sed -e 's#^#http://localhost#'
