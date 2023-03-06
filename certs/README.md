# Self-signed certificates

_For development environment only_

The VA uses self-signed certificates with a non-globally trusted Root Certificate
authority.

VA-Internal-S2-RCA-combined.pem contains the following certs (as of 2023-01-19):
* VA-Internal-S2-RCA1-v1.pem
* VA-Internal-S2-RCA2.pem


Used in [the Drupal client](https://github.com/department-of-veterans-affairs/vets-website/blob/main/src/site/stages/build/drupal/api.js)'s `proxyFetch` function when using the SOCKS proxy.
