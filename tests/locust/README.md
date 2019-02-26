
# Locust Load Testing

## Installation

Installation instruction and documentation for Locust can be found at 
https://docs.locust.io/en/latest/index.html

Once installed test can be run either through the command line or in the browser.

## **Running Locust Tests**

### In the browser

`locust -f locustLoginTest.py --host="http://localhost:32776"`

### From command line

`locust -f locustLoginTest.py --host="http://localhost:32776" --no-web  -c 2 -r 1 --run-time 1m
`

Where `-c` is the numer of Locust users to spawn, and `-r` specifies the hatch rate (number of users to spawn per second).
