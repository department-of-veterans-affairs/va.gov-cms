#!/bin/bash

repetitions=${1:-10}

run_test() {
  lando drush cr;
  lando behat --tags="content_editing";
}

test_script() {
  total_elapsed_time=0;
  min_elapsed_time=100;
  max_elapsed_time=0;
  for i in $(seq 1 $repetitions); do
    start_time=$(date +%s);
    run_test;
    end_time=$(date +%s);
    elapsed_time=$(echo $end_time - $start_time | bc -l);
    total_elapsed_time=$(echo $elapsed_time + $total_elapsed_time | bc -l);
    if [ $elapsed_time -lt $min_elapsed_time ]; then
      min_elapsed_time=$elapsed_time;
    fi;
    if [ $elapsed_time -gt $max_elapsed_time ]; then
      max_elapsed_time=$elapsed_time;
    fi;
    echo "Iteration $i: $elapsed_time";
  done;
  average_elapsed_time=$(echo $total_elapsed_time / $repetitions | bc -l);
  echo "Average: $average_elapsed_time";
  echo "Minimum: $min_elapsed_time";
  echo "Maximum: $max_elapsed_time";
}

time test_script;
