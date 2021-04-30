## Memcache Benchmarks
_(The following is copied from comments on [this issue](https://github.com/department-of-veterans-affairs/va.gov-cms/issues/5083), posted here for a blow-by-blow explanation on each script.  These are general performance scripts, but it might be helpful to remember that the scripts were written, and measurements recorded, originally in the context of comparing performance between memcache and non-memcache configurations.)_

Results of testing with and without the memcache configuration in place using Siege.

I ran Siege with up to 100 users with memcache enabled, but haven't done past 50 without memcache enabled.  After seeing the results from 5-50, I wondered whether it was worth going further.  I think Siege has a code-imposed limit of 256, but since we're downloading and compiling Siege anyway, that can be bypassed pretty easily.

I wrote [this script](./wget_spider.sh) to load up pages -- it's not really a benchmark, but I think it does a reasonable job of requesting commonly accessed resources:

```bash
#!/bin/bash
site="http://localhost";
name="axcsd452ksey";
password="drupal8";
cookies_file="$(mktemp)";

wget \
  --keep-session-cookies \
  --save-cookies "${cookies_file}" \
  --load-cookies "${cookies_file}" \
  "${site}/user" 2>&1;

wget \
  --keep-session-cookies \
  --save-cookies "${cookies_file}" \
  --load-cookies "${cookies_file}" \
  --post-data="name=${name}&pass=${password}&op=Log%20in&form_id=user_login_form" \
  "${site}/user/login" 2>&1;

time wget \
  --recursive \
  -l 1 \
  --delete-after \
  --keep-session-cookies \
  --save-cookies="${cookies_file}" \
  --load-cookies="${cookies_file}" \
  --reject-regex="run-cron.*|update.php" \
  --exclude-directories="/user" \
  "${site}/admin";
```

The last command there being the most useful one, using the cookies retrieved in the previous two requests to retrieve all of the pages linked from `/admin`, but excluding some ones like `/user/logout` that would thwart us.  I was doing `-l 3`, `-l 2`, but finally only `-l 1` was limited enough to get done in reasonable time.

It takes about 9 minutes both before and after implementing memcache.  That's not surprising because this isn't really a benchmark, and is just a prefatory operation, and most of the run time seems to be time-to-first-byte kinda issues.  The actual use of `wget` is just to organically generate a list of accessed paths that we can then use with `siege` to do a more realistic benchmark.

This command (and [associated script](dump_access_log_request_paths.sh):

```bash
cat /var/log/httpd/access_log | awk '{ print $7 }' | sort | uniq | grep -v '\*' | grep -vE '(jpe?g|png|gif|pdf)$' | sed -e 's#^#http://localhost#' > urls.txt
```

grabs the Apache access log, prints the eighth column (which is the request path), sorts the paths, filters out duplicates (and some dumb paths and static files that don't tell us anything), and then injects a hostname at the start of each line because Siege doesn't understand paths alone and doesn't seem to have any sort of `--base-url` parameter.

The resulting file had about 2000 URLs in it, which is probably good enough.

Siege doesn't have a package in `yum`, so we gotta download and compile it:

```bash
cd /tmp
siege_version="4.0.9"
wget http://download.joedog.org/siege/siege-${siege_version}.tar.gz
tar -zxvf siege-${siege_version}.tar.gz
cd siege-${siege_version}
./configure
make && make install
```

And, because I couldn't get Siege to handle cookies from wget, the following siegerc:

```ini
login-url = http://localhost/user/login POST name= axcsd452ksey&pass=drupal8&op=Log%20in&form_id=user_login_form
```

Running the following command:

```bash
/usr/local/bin/siege --concurrent=5 --delay=5 --time=2M --file=urls.txt --rc=./siegerc -v
```

yields (eventually) the following report:

```
Transactions:            200 hits
Availability:         100.00 %
Elapsed time:         119.31 secs
Data transferred:        55.75 MB
Response time:            0.62 secs
Transaction rate:         1.68 trans/sec
Throughput:           0.47 MB/sec
Concurrency:            1.05
Successful transactions:         158
Failed transactions:             0
Longest transaction:          6.52
Shortest transaction:         0.00
```

At 10 concurrent requests:

```
Transactions:            409 hits
Availability:         100.00 %
Elapsed time:         119.33 secs
Data transferred:       112.24 MB
Response time:            0.65 secs
Transaction rate:         3.43 trans/sec
Throughput:           0.94 MB/sec
Concurrency:            2.24
Successful transactions:         284
Failed transactions:             0
Longest transaction:          5.34
Shortest transaction:         0.00
```

So from here we can increase the concurrency to get a picture of how the server behaves under increasingly severe strain.

```bash
for i in $(seq 15 5 100); do /usr/local/bin/siege --concurrent=$i --delay=5 --time=2M --file=urls.txt --rc=./siegerc; done;
```

So I did this with and without memcache and tabulated the results in this Sheet: [Comparison of 5-50 simultaneous users](https://docs.google.com/spreadsheets/d/19Pd-HEZ901aRgzjgGTzOCKtnxjLL3q58E-GxD_MZrRc/edit?usp=sharing)

Caches are all warmed, etc, so this is best-case vs. best-case performance.

Tomorrow I'll do the web build comparisons, since I think that should be comparatively simple.  I was hoping for some clear indications of how memcache would improve the CMS' responsiveness as the number of editors increases over the coming months.

Continuing on, Monday and yesterday I re-recorded the results of a siege with the aforementioned general setup, more-or-less head-to-head, with concurrencies from 5 to 250 simultaneous users.

[Raw CSV in this gist](https://gist.github.com/ndouglas/ed6fd9f934cbb8b07849943ebcba0bff)

I updated [the spreadsheet above](https://docs.google.com/spreadsheets/d/19Pd-HEZ901aRgzjgGTzOCKtnxjLL3q58E-GxD_MZrRc/edit#gid=731562912) with more graphs.  I think over all, once you reach a certain number of concurrent users Memcache seems to show a 5-8% performance improvement.

I then ran frontend builds with the following scripts to gather some numbers for different scenarios:

## Sequential Builds with a Cold Cache
```bash
PATH=$PATH:/usr/local/bin:/var/www/cms/bin
export NODE_ENV=production;
export NODE_TLS_REJECT_UNAUTHORIZED=0;
for i in $(seq 1 5); do
  drush cr > /dev/null;
  build_response=$(yarn build:content \
    --drupal-address=https://test.staging.cms.va.gov \
    --pull-drupal \
    --no-drupal-proxy \
    --buildtype=vagovdev \
    --api=https://dev-api.va.gov \
    --asset-source=$(git rev-parse --verify HEAD));
  graphql_time=$(printf "%s" "${build_response}" | grep -oP 'queries in \d+s' | grep -oP '\d+');
  graphql_pages=$(printf "%s" "${build_response}" | grep -oP 'with \d+ pages' | grep -oP '\d+');
  build_time=$(printf "%s" "${build_response}" | grep -oP 'Done in \d+\.\d+s' | grep -oP '\d+\.\d+');
  echo "\"${i}\",\"${graphql_time}\",\"${graphql_pages}\",\"${build_time}\"";
done;
```

## Sequential Builds with a Warm Cache
```bash
PATH=$PATH:/usr/local/bin:/var/www/cms/bin
export NODE_ENV=production;
export NODE_TLS_REJECT_UNAUTHORIZED=0;
for i in $(seq 1 5); do
  # drush cr > /dev/null; (I spared no expense)
  build_response=$(yarn build:content \
    --drupal-address=https://test.staging.cms.va.gov \
    --pull-drupal \
    --no-drupal-proxy \
    --buildtype=vagovdev \
    --api=https://dev-api.va.gov \
    --asset-source=$(git rev-parse --verify HEAD));
  graphql_time=$(printf "%s" "${build_response}" | grep -oP 'queries in \d+s' | grep -oP '\d+');
  graphql_pages=$(printf "%s" "${build_response}" | grep -oP 'with \d+ pages' | grep -oP '\d+');
  build_time=$(printf "%s" "${build_response}" | grep -oP 'Done in \d+\.\d+s' | grep -oP '\d+\.\d+');
  echo "\"${i}\",\"${graphql_time}\",\"${graphql_pages}\",\"${build_time}\"";
done;
```
## Parallel (simultaneously dispatched) Builds with a Cold Cache
```bash
do_web_build() {
  build_response=$(yarn build:content \
    --drupal-address=https://test.staging.cms.va.gov \
    --pull-drupal \
    --no-drupal-proxy \
    --buildtype=vagovdev \
    --api=https://dev-api.va.gov \
    --asset-source=$(git rev-parse --verify HEAD));
  graphql_time=$(printf "%s" "${build_response}" | grep -oP 'queries in \d+s' | grep -oP '\d+');
  graphql_pages=$(printf "%s" "${build_response}" | grep -oP 'with \d+ pages' | grep -oP '\d+');
  build_time=$(printf "%s" "${build_response}" | grep -oP 'Done in \d+\.\d+s' | grep -oP '\d+\.\d+');
  echo "\"${i}\",\"${graphql_time}\",\"${graphql_pages}\",\"${build_time}\"";
}
export -f do_web_build;
drush cr
echo '
do_web_build
do_web_build
do_web_build
do_web_build
do_web_build
' | parallel
```

## Parallel (simultaneously dispatched) Builds with a Warm Cache
```bash
echo '
do_web_build
do_web_build
do_web_build
do_web_build
do_web_build
' | parallel
```

## Parallel (offset dispatch) Builds with a Cold Cache
```bash
drush cr
echo '
do_web_build
sleep 5 && do_web_build
sleep 10 && do_web_build
sleep 15 && do_web_build
sleep 20 && do_web_build
' | parallel
```

These numbers end up being far more interesting:

![Screen Shot 2021-04-28 at 9 28 02 AM](https://user-images.githubusercontent.com/1318579/116411957-32c46780-a804-11eb-9c07-4a3928e6b577.png)

There's very little difference between life with and without Memcache on a warm cache.  As we noticed in the original hackathon, though, there's a substantial difference between the two configurations with a cold cache.  And also as observed, parallel builds seriously affect build time, but in all three situations Memcache shows a 20-35% performance gain.

I found very little variation in the build times of the sequential builds, so I didn't think it worthwhile to do longer (_n_>5) measurements.  Only the offset parallel builds varied meaningfully, and even then it was a \~\<=10% variance I think, and there are a ton of variables there.

So TL;DR: I have some stuff to show off, and happy to dig deeper or revisit if there are flaws with this methodology.
