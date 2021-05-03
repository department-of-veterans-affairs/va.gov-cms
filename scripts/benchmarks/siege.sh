#!/usr/bin/env bash

# This script:
# - parses the access log and dumps it to a temporary file
# - installs Siege if necessary
# - executes Siege with a range of concurrent users using the access log
# - prints the results as CSV, suitable for importing into a spreadsheet

set -e;

if [ ! -x "$(command -v siege)" ]; then
  >&2 echo "Siege is not installed or found in \$PATH.  Please run install_siege.sh or install it through some other means.";
  exit -1;
fi

script_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")"; pwd -P)";
siegerc_path="${script_dir}/siegerc";

temporary_dir="$(mktemp -d)";

urls_path="${temporary_dir}/urls.txt";
./dump_access_log_request_paths.sh > "${urls_path}";

for concurrent_users in $(seq 5 5 100); do

  siege_report="$(siege \
    --concurrent=$concurrent_users \
    --delay=5 \
    --time=2M \
    --file="${urls_path}" \
    --rc="${siegerc_path}" \
    2>&1)";

  transactions=$(echo $siege_report | grep -oP 'Transactions:\s+\d+' | grep -oP '\d+');
  availability=$(echo $siege_report | grep -oP 'Availability:\s+\d+.\d+' | grep -oP '\d+.\d+');
  data_transferred=$(echo $siege_report | grep -oP 'Data transferred:\s+\d+.\d+' | grep -oP '\d+.\d+');
  response_time=$(echo $siege_report | grep -oP 'Response time:\s+\d+.\d+' | grep -oP '\d+.\d+');
  transaction_rate=$(echo $siege_report | grep -oP 'Transaction rate:\s+\d+.\d+' | grep -oP '\d+.\d+');
  throughput=$(echo $siege_report | grep -oP 'Throughput:\s+\d+.\d+' | grep -oP '\d+.\d+');
  concurrency=$(echo $siege_report | grep -oP 'Concurrency:\s+\d+.\d+' | grep -oP '\d+.\d+');
  successes=$(echo $siege_report | grep -oP 'Successful transactions:\s+\d+' | grep -oP '\d+');
  failures=$(echo $siege_report | grep -oP 'Failed transactions:\s+\d+' | grep -oP '\d+');
  longest=$(echo $siege_report | grep -oP 'Longest transaction:\s+\d+.\d+' | grep -oP '\d+.\d+');
  shortest=$(echo $siege_report | grep -oP 'Shortest transaction:\s+\d+.\d+' | grep -oP '\d+.\d+');

  # Echo CSV output.
  echo "\"${users}\",\"${transactions}\",\"${availability}\",\"${data_transferred}\",\"${response_time}\",\"${transaction_rate}\",\"${throughput}\",\"${concurrency}\",\"${successes}\",\"${failures}\",\"${longest}\",\"${shortest}\"";

done;
