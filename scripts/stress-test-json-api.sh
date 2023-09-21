#!/usr/bin/env bash

# This script is used to stress test JSON:API and the CMS infrastructure.
#
# Usage: export base_url="${TUGBOAT_DEFAULT_SERVICE_URL}"; export batches=20; export threads=20; export requests=250; ./scripts/stress-test-json-api.sh
#
# It uses GNU Parallel to make requests in parallel. You can install GNU
# Parallel on macOS using Homebrew: `brew install parallel`, or on Linux using
# your package manager.

# Set the number of batches to run. Default is 1.
batches=${batches:-1};

# Set the number of threads to use. Default is 10.
threads=${threads:-10};

# Set the number of requests to make per thread. Default is 1000.
requests=${requests:-1000};

# Set the base URL to use for the requests. Default is http://localhost:8080.
base_url=${base_url:-http://va-gov-cms.ddev.site};

# An array of JSON:API paths to request.
request_paths=(
  jsonapi/node/news_story/d73c16a0-6266-4d88-9b3e-71a9c83fee25
  jsonapi/node/news_story/ac2f3901-773f-46d2-9b17-4dc99e2eb6b3
  jsonapi/node/news_story/69aef601-434b-457f-9527-25e0bef929c7
  jsonapi/node/news_story/984d08a4-1c8e-4c84-b39e-47e79a177f6e
  jsonapi/node/news_story/8efb39d7-6636-4911-8483-2aa67a4d3a2d
  jsonapi/node/news_story/a3e8b710-8771-423c-a22c-25112b86dff0
  jsonapi/node/news_story/2b919af5-b3b2-4c29-8353-1c862f5b942f
  jsonapi/node/news_story/c45aa237-9f68-4f25-87db-94c679ec9300
  jsonapi/node/news_story/33ba2a68-6894-45d8-ac4a-541a265b27fa
  jsonapi/node/news_story/786bd16d-c257-43d0-9096-6cd68083302f
  jsonapi/node/news_story/85b69f7d-46ba-4814-a282-a9f020e0978a
  jsonapi/node/news_story/6add6fd7-d1ba-4b33-a8dc-b68977a7b7b6
  jsonapi/node/news_story/230ac3a7-c585-4dee-be19-443b59b8532e
  jsonapi/node/news_story/45a5939c-a9d8-4b58-8005-27ec9eeea819
  jsonapi/node/news_story/8236d4d9-4876-4384-ab89-2a96233c77f2
  jsonapi/node/news_story/f7ed3d54-e29a-4c33-998a-c5256754769a
  jsonapi/node/news_story/68871358-aede-47e2-956e-62954420f133
  jsonapi/node/news_story/4aea7f6b-7d9b-4bbf-96d4-6ec9acf7a449
  jsonapi/node/news_story/8ecf726b-a5ed-43ea-8117-07eefd2ea544
  jsonapi/node/news_story/5f8211f2-2643-45e9-ae0f-aa7f0c88b681
  jsonapi/node/news_story/45809e5f-8c4b-4510-adaf-f9c9439ef8a5
  jsonapi/node/news_story/24811f89-6f3e-400a-b000-d71ed294c266
  jsonapi/node/news_story/83f50f81-d5e8-43dd-a5f0-5021dc9099a3
  jsonapi/node/news_story/a6bb5968-9bb9-4d65-8df6-8e521f5cc1d3
  jsonapi/node/news_story/132e4f8a-3ace-4452-b3fb-e9f50c246051
  jsonapi/node/news_story/bdc9c23d-d48a-4692-b96a-3a6b72d81096
  jsonapi/node/news_story/256f9dea-a6e1-472e-936d-14af007ab9ca
  jsonapi/node/news_story/bcd9677e-0b1e-44ec-aa6e-d0922375ab2b
  jsonapi/node/news_story/04aaa2ab-f6e4-4da1-9f16-b1a06ec738b4
  jsonapi/node/news_story/55e8b30e-579b-4bc7-9b84-83b23aa84b3a
  jsonapi/node/news_story/b32bb4b5-9b7e-42a5-8810-f1dcd38a841a
  jsonapi/node/news_story/2e66843f-2223-4469-9de7-366d1c83456e
  jsonapi/node/news_story/ff51ad74-563c-4c74-af5d-dc83f1bcc99d
  jsonapi/node/news_story/812523df-4078-43ba-8412-64fc012fd9d8
  jsonapi/node/news_story/3aee8cf0-8403-4c44-b8a0-8c7f36c4d03a
  jsonapi/node/news_story/b0209b96-1a92-41b9-90b2-e938eb391661
  jsonapi/node/news_story/76ba8a38-5d2a-4918-937a-da135a892640
  jsonapi/node/news_story/2cd028f1-9df3-48de-8eca-055e3a567441
  jsonapi/node/news_story/a9f2d3b3-1c43-4b5e-ac66-12b92fd8aadf
  jsonapi/node/news_story/7db1a56b-166e-4672-9741-6f968d5446fa
  jsonapi/node/news_story/0cffc531-fb1a-4371-baf1-28be3721f439
  jsonapi/node/news_story/9d44f010-58b6-4cf2-9132-bcb6412bf6a9
  jsonapi/node/news_story/c77a2cb5-eec4-4f1f-bac7-939b09eda4dd
  jsonapi/node/news_story/85bcf641-e4a9-45de-9c8b-dfd311abdaca
  jsonapi/node/news_story/f09ac486-ccf6-4f9f-a353-4326355d26d0
  jsonapi/node/news_story/1a2abc26-8a17-4c2c-804f-3e24fefb79da
  jsonapi/node/news_story/ce4f179f-31ac-4c0e-9802-8a2bb5540ca5
  jsonapi/node/news_story/eef2ac4d-508b-4721-8013-370dfd90eb79
  jsonapi/node/news_story/bbc1062d-6e1e-4405-a388-fc045b71a19c
  jsonapi/node/news_story/48b368b4-732e-4049-87ce-7b74b9df16e6
  jsonapi/node/news_story/f3f35ac8-e3c7-4490-8c52-ac82c237a373
  jsonapi/node/news_story/565f2e7b-a518-4f7f-90bc-c4e6a6245a4f
  jsonapi/node/news_story/55a4f116-39d9-4674-9e99-6413e823ff2e
  jsonapi/node/news_story/829ad724-97a0-47d3-a008-1b8514b6322c
  jsonapi/node/news_story/da693e8b-6d04-4ac3-9e65-734878b24c41
  jsonapi/node/news_story/182619aa-de02-4b8d-b9db-4c120842da82
  jsonapi/node/news_story/546f3c0a-680c-4ce3-9f8e-869e396caa44
  jsonapi/node/news_story/75ab4c64-1aa3-4372-84d7-de79a28c830f
  jsonapi/node/news_story/d33ae077-b1f1-4411-85c9-d02e3ff7f476
  jsonapi/node/news_story/988952cc-0a31-4285-964e-bdffc8220bf1
  jsonapi/node/news_story/f9efb0b4-0e14-43b0-ad82-87a6f738f5d1
)

# Duplicate the array until it is at least as long as the number of threads.
while [ ${#request_paths[@]} -lt $requests ]; do
  request_paths+=("${request_paths[@]}");
done;

# Set the timeout for each request. Default is 60 seconds.
request_timeout=${request_timeout:-60};

# Set the timeout for the entire batch. Default is request_timeout * the number
# of requests per thread.
batch_timeout=${timeout:-$((request_timeout * requests))};

# Set the number of seconds to wait between each thread. Default is 0 seconds.
thread_wait=${thread_wait:-0};

# Set the username for basic auth.
username=${username:-nathan.douglas@agile6.com};

# Set the password for basic auth.
password=${password:-drupal8};

# Set the command to clear the cache.
clear_cache_command=${clear_cache_command:-drush cr};

# Make the request to the server.
do_curl() {
  local config_file="${1}";
  local urls_file="${2}";
  local urls="$(cat "${urls_file}")";
  curl --config "${config_file}" ${urls};
}

export -f do_curl;

# Run the batches.
for ((i=1; i<=batches; i++)); do

  echo "Running batch ${i} of ${batches}...";

  shuffled_paths=( $(printf "%s\n" "${request_paths[@]}" | shuf) );

  configuration="
user = \"${username}:${password}\"
max-time = ${request_timeout}
write-out = \"%{http_code}\n\"
location-trusted
silent
";

  # Add all of the URLs to the configuration.
  for ((j=1; j<=requests; j++)); do
    urls="${urls:-}--output /dev/null ${base_url}/${shuffled_paths[${j}]} ";
  done;

  config_file="$(mktemp)";
  urls_file="$(mktemp)";

  echo "${configuration}" > "${config_file}";
  echo "${urls}" > "${urls_file}";

  # Clear cache.
  echo "Clearing cache...";
  ${clear_cache_command};

  # Run the threads.
  echo "Running ${threads} threads...";
  time \
    parallel \
      -j "${threads}" \
      --no-notice \
      --line-buffer \
      --timeout "${batch_timeout}" \
      --halt now,fail=1 \
      --delay "${thread_wait}" \
      do_curl "${config_file}" "${urls_file}" ::: "$(seq 1 ${threads})";

  echo "Batch ${i} of ${batches} complete.";

  sleep 1;

done;
