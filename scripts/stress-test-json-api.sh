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

# Set the total number of requests to make. Default is requests * threads.
total_requests=${total_requests:-$((requests * threads))};

# Set the base URL to use for the requests. Default is http://localhost:8080.
base_url=${base_url:-http://va-gov-cms.ddev.site};

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

# If a SOCKS proxy is set, use it.
if [ -n "${socks_proxy}" ]; then
  echo "Using SOCKS proxy: ${socks_proxy}";
  socks_config="socks5-hostname = ${socks_proxy}";
  socks_option="--socks5-hostname ${socks_proxy}";
fi;

# An array of JSON:API URLs to request.
echo "Getting URLs...";
request_urls=( $(curl --user "${username}:${password}" ${socks_option:-} --silent ${base_url}/jsonapi/node/news_story\?fields%5Bnode--news_story%5D\=path%2Ctitle\&page%5Blimit%5D\=${total_requests} | jq -r '.data[].links.self.href') );
echo "Got ${#request_urls[@]} URLs.";

# Make the request to the server.
do_curl() {
  local config_file="${1}";
  local urls_file="${2}";
  local urls=$(cat "${urls_file}");
  local error_output_file="$(mktemp)";
  local response=$(curl --config "${config_file}" ${urls} 2> "${error_output_file}");
  local exit_code=$?;
  if [[ "${exit_code}" -ne 0 ]]; then
    echo "Error: ${exit_code}";
    cat "${error_output_file}";
  fi;
  failures=0;
  echo "$response" | grep -v '^$' | while read -r line; do
    http_code="$(echo "$line" | awk '{print $1}')";
    if [[ "${http_code}" -le 299 ]]; then
      :
      # Uncomment the following line to see successful requests.
      # echo "$line";
    elif [[ "${http_code}" -ge 400 && "${http_code}" -lt 600 ]]; then
      echo "$line";
      failures=$((failures + 1));
    fi;
  done;
  if [[ "${failures}" -gt 0 ]]; then
    echo "Failures: ${failures}";
    exit 1;
  fi;
}

export -f do_curl;

# Generate the configuration for curl.
configuration="
user = \"${username}:${password}\"
max-time = ${request_timeout}
write-out = \"%{http_code} %{http_connect} %{url_effective} %{time_appconnect} %{time_connect} %{time_pretransfer} %{time_redirect} %{time_starttransfer} %{time_total}\n\"
location-trusted
silent
${socks_config:-}
";

# Run the batches.
for ((i=1; i<=batches; i++)); do

  echo "Running batch ${i} of ${batches}...";

  shuffled_urls=( $(printf "%s\n" "${request_urls[@]}" | shuf) );

  config_file="$(mktemp)";
  echo "${configuration}" > "${config_file}";

  urls_file_prefix="$(mktemp -d)/urls-";

  # Chunk the URLs into individual files.
  url_chunks=();
  chunk_count=$(( ${#request_urls[@]} / requests ));
  for (( j=0; j<chunk_count; j+=1 )); do
    start_index=$(( j * requests ));
    these_request_urls=( "${request_urls[@]:start_index:requests}" );
    urls_file="${urls_file_prefix}${j}";
    for url in "${these_request_urls[@]}"; do
      echo -n "--output /dev/null ${url} " >> "${urls_file}";
    done;
  done;

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
      do_curl "${config_file}" "${urls_file_prefix}{}" ::: "$(seq 0 $((threads - 1)))";

  echo "Batch ${i} of ${batches} complete.";

  sleep 1;

done;
