# Security
1.  [Fortify Security Scans](testing.md#fortify-security-scans)
2.  [PII](#pii)


## PII
The Content API has no PII or PHI on it and is a replacement for the public, open source repo https://github.com/department-of-veterans-affairs/vagov-content. A sanitized database is even in a public S3 bucket (for open source development purposes) since all the configuration is already open source here https://github.com/department-of-veterans-affairs/va.gov-cms. The sanitized version removes all email addresses and resets to a commonly known development password.

[Table of Contents](../README.md)
