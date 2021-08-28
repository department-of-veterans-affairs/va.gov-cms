{
  "retry": {
    "count": 0,
    "interval": 300
  },
  "name": $URL,
  "request": {
    "url": $URL,
    "method": "GET",
    "allow_insecure": true
  },
  "subtype": "http",
  "allowFailure": false,
  "extractedValues": [],
  "isCritical": true,
  "assertions": [
    {
      "operator": "lessThan",
      "type": "responseTime",
      "target": 60000
    }
  ]
}
