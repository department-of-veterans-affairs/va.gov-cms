{
  "status": "live",
  "tags": [
    "env:prod",
    $TAG_NAME
  ],
  "locations": [
    "aws:us-east-2",
    "pl:us-gov-west-1-91af3c315093fdf5bb4ff4024f5b3309"
  ],
  "message": "",
  "name": $TEST_NAME,
  "type": "api",
  "subtype": "http",
  "config": {
    "request": {
      "url": $FACILITY_STATUS_URL,
      "method": "GET"
    },
    "assertions": [
      {
        "operator": "lessThan",
        "type": "responseTime",
        "target": 60000
      },
      {
        "operator": "is",
        "type": "statusCode",
        "target": 200
      }
    ]
  },
  "options": {
    "monitor_options": {
      "renotify_interval": 0
    },
    "retry": {
      "count": 0,
      "interval": 300
    },
    "allow_insecure": true,
    "tick_every": 60,
    "min_failure_duration": 900,
    "min_location_failed": 1
  }
}
