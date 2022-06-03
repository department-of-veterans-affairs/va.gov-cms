# Loadtesting with Goose
The loadtest(s) are run using an opensource loadtesting framework called Goose from Tag1 Consulting. Goose was inspired by [Locust](https://locust.io/) and generates 11x load per core as Locust does (per https://book.goose.rs/title-page.html#advantages). Goose can generate an HTML report with detailed metrics including requests per second, average/min/max request times, number of users and more. Goose does not yet have a UI for triggering loadtests but is working towards

## Links/Docs
* https://book.goose.rs (Goose Book, make sure to expand the hamburger menu on the top left)
* https://www.tag1consulting.com/goose-podcasts-blogs-presentations-more (blog posts and videos)
* https://docs.rs/goose/latest/goose (developer docs)
* https://github.com/tag1consulting/goose (source code)
* https://github.com/tag1consulting/goose/issues (upstream issues)

## Setup

### Install Rustup (to make installing Rust easier, it's like `nvm` or `pyenv`)
* Generic Linux:
  `curl --proto '=https' --tlsv1.2 -sSf https://sh.rustup.rs | sh`
* Arch Linux:
  `yay --sync --refresh rustup`
* Mac Homebrew:
  `brew install rustup-init`
* Windows (exe):
  https://rustup.rs

### Install Rust w/Rustup
```shell
rustup install stable
rustup default stable
```

### Install the dependencies
```shell
cd tests/loadtest
cargo build
```

## Run the loadtest locally

```shell
cd tests/loadtest
# Start SOCKS PROXY first
export ADMIN_USERNAME="content_build_api"
export ADMIN_PASSWORD="drupal8"
cargo run --release -- --host https://staging.cms.va.gov --report-file report-file.html --running-metrics 1 --users 125 --hatch-rate 10 --run-time 3m --no-reset-metrics
```

TODO: Add distributed example for multiple workers

## The Loadtests
The loadtests are written in higher level-style Rust code (should be easy enough to comprehend by most JavaScript & PHP engineers).

## Development on loadtests
* Remove the `--release` option during `cargo run` to speed up the build time when testing out new code. Once the new code is working, add back `--release` to generate the maximum amount of load per core. See https://book.goose.rs/getting-started/tips.html#best-practices for how/why the `--release` option is used to generate increased load.
* `--debug-log debug.log`

  To create an HTML file that is viewable in your browser, find the line number and `cat debug.log | head --lines 1 | jq --raw-output .body > page.html` then open with browser. See https://book.goose.rs/getting-started/tips.html#debugging-html-responses & https://book.goose.rs/logging/debug.html.
  ```json
  {"body":"<!DOCTYPE html>\n<html>\n  <head>\n    <title>503 Backend fetch failed</title>\n  </head>\n  <body>\n    <h1>Error 503 Backend fetch failed</h1>\n    <p>Backend fetch failed</p>\n    <h3>Guru Meditation:</h3>\n    <p>XID: 1506620</p>\n    <hr>\n    <p>Varnish cache server</p>\n  </body>\n</html>\n","header":"{\"date\": \"Mon, 19 Jul 2021 09:21:58 GMT\", \"server\": \"Varnish\", \"content-type\": \"text/html; charset=utf-8\", \"retry-after\": \"5\", \"x-varnish\": \"1506619\", \"age\": \"0\", \"via\": \"1.1 varnish (Varnish/6.1)\", \"x-varnish-cache\": \"MISS\", \"x-varnish-cookie\": \"SESSd7e04cba6a8ba148c966860632ef3636=Z50aRHuIzSE5a54pOi-dK_wbxYMhsMwrG0s2WM2TS20\", \"content-length\": \"284\", \"connection\": \"keep-alive\"}","request":{"coordinated_omission_elapsed":0,"elapsed":9162,"error":"503 Service Unavailable: /node/1439","final_url":"http://apache/node/1439","name":"(Auth) comment form","raw":{"body":"","headers":[],"method":"Get","url":"http://apache/node/1439"},"redirected":false,"response_time":5,"status_code":503,"success":false,"update":false,"user":1,"user_cadence":0},"tag":"post_comment: no form_build_id found on node/1439"}
  ```
* `--transaction-log transaction.log`

  https://book.goose.rs/logging/transactions.html
  ```json
  {"elapsed":22060,"name":"(Anon) front page","run_time":97,"success":true,"transaction_index":0,"scenario_index":0,"user":0}
  {"elapsed":22118,"name":"(Anon) node page","run_time":41,"success":true,"transaction_index":1,"scenario_index":0,"user":5}
  {"elapsed":22157,"name":"(Anon) node page","run_time":6,"success":true,"transaction_index":1,"scenario_index":0,"user":0}
  {"elapsed":22078,"name":"(Auth) front page","run_time":109,"success":true,"transaction_index":1,"scenario_index":1,"user":6}
  {"elapsed":22157,"name":"(Anon) user page","run_time":35,"success":true,"transaction_index":2,"scenario_index":0,"user":4}
  ```
