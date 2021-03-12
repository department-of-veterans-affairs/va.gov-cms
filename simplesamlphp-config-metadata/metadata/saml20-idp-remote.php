<?php

/**
 * SAML 2.0 remote IdP metadata for SimpleSAMLphp.
 *
 * Remember to remove the IdPs you don't use from this file.
 *
 * See: https://simplesamlphp.org/docs/stable/simplesamlphp-reference-idp-remote
 */

// For prod.cms.va.gov
// Deployments replace the Location & X509Certificate for lower envs.
$metadata['VA_SSOi_IDP'] = array (
    'entityid' => 'VA_SSOi_IDP',
    'contacts' =>
        array (
        ),
    'metadata-set' => 'saml20-idp-remote',
    'sign.authnrequest' => true,
    'SingleSignOnService' =>
        array (
            0 =>
                array (
                    'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                    'Location' => 'https://logon.iam.va.gov/affwebservices/public/saml2sso',
                ),
            1 =>
                array (
                    'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                    'Location' => 'https://logon.iam.va.gov/affwebservices/public/saml2sso',
                ),
        ),
    'SingleLogoutService' =>
        array (
        ),
    'ArtifactResolutionService' =>
        array (
        ),
    'NameIDFormats' =>
        array (
            0 => 'urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified',
        ),
    'keys' =>
        array (
            0 =>
                array (
                    'encryption' => false,
                    'signing' => true,
                    'type' => 'X509Certificate',
                    'X509Certificate' => 'MIIFLzCCBBegAwIBAgIHPQAAAAAMCzANBgkqhkiG9w0BAQsFADBHMRMwEQYKCZImiZPyLGQBGRYDZ292MRIwEAYKCZImiZPyLGQBGRYCdmExHDAaBgNVBAMTE1ZBLUludGVybmFsLVMyLUlDQTQwHhcNMjAxMjA3MTc0NTI3WhcNMjExMjMxMTc0NTI3WjCBnzELMAkGA1UEBhMCVVMxEjAQBgoJkiaJk/IsZAEZFgJ2YTETMBEGCgmSJomT8ixkARkWA2dvdjERMA8GA1UECBMIVmlyZ2luaWExEDAOBgNVBAcTB0FzaGJ1cm4xJzAlBgNVBAoTHkRlcGFydG1lbnQgb2YgVmV0ZXJhbnMgQWZmYWlyczEZMBcGA1UEAxMQbG9nb24uaWFtLnZhLmdvdjCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBALAT1uSi/QnjUBrYkvaJ3Q2hLYrRRPQgZi85/3diSMV/m8PG1ocGHi6bgbUtLCVbvU2aoC4MEJnUaA1Ied6U+MKysK0WIApH+4+ZraiKOaPkgfKqbDAzUEGgvvylxH8SYEUkDuHS1Go7NFu+v1zx4ajBgrAtWkDYbfddSoHgEyCUITBH5cmEuYtTPRfQdcgCILpZFVImM3N9sj/025gf4jBRL/tgKeWb0Ymv5vb8k+amUERDpU5H8qUgzmLtcjh+uKcpXfVRoAiElEuGjxhYFjZSCvrdaZd67yFJ8iAcjAb0Iif88xwKVqFJ1hkurzjYNCsjxbNouwG5vUs78vDlzwUCAwEAAaOCAcUwggHBMBsGA1UdEQQUMBKCEGxvZ29uLmlhbS52YS5nb3YwHQYDVR0OBBYEFNWlq6p0KpFeBc7wjNpdO+fhU9jPMB8GA1UdIwQYMBaAFGi3comeQurkUUUfWKIKJw9zYECjMEYGA1UdHwQ/MD0wO6A5oDeGNWh0dHA6Ly9jcmwucGtpLnZhLmdvdi9wa2kvY3JsL1ZBLUludGVybmFsLVMyLUlDQTQuY3JsMHgGCCsGAQUFBwEBBGwwajBEBggrBgEFBQcwAoY4aHR0cDovL2FpYS5wa2kudmEuZ292L3BraS9haWEvdmEvVkEtSW50ZXJuYWwtUzItSUNBNC5jZXIwIgYIKwYBBQUHMAGGFmh0dHA6Ly9vY3NwLnBraS52YS5nb3YwDAYDVR0TAQH/BAIwADALBgNVHQ8EBAMCBeAwPQYJKwYBBAGCNxUHBDAwLgYmKwYBBAGCNxUIgcjDM4H58AaBpZ8NhOCBCIXCqksGgtrLEYHR8FYCAWQCASkwHQYDVR0lBBYwFAYIKwYBBQUHAwIGCCsGAQUFBwMBMCcGCSsGAQQBgjcVCgQaMBgwCgYIKwYBBQUHAwIwCgYIKwYBBQUHAwEwDQYJKoZIhvcNAQELBQADggEBAFY1pb1XF9kcqfHtktd5pJevBXVSO4I9SmsHFIMLJFq3/SeqBinrfL7yjGYxkMxMeNqeZoBb3pthaBCk2olpTZibsv0XEsDjMKdbF5LYUUONw/l6tqkVDe0+UUPbPedlUb2gO5sv+8AZG87PWYvRLUUPUnYujOgMJWGF95AOKOhGPM6JmWN9wn9E3SFs0JqHqOsTi+1UHYKdWX3DAQ3J/Rsddutc8ttPZC2iYYJj6AR+8gSXuMpufwWMERpoCMnF271/EVEOlEUyf9ILsiKE21n4l7bsL8rKAfsb0lYsc7k+XJCav2vw6CoLAXKG/emkaXQ9dJ1bG5y8vAwiLmiA/5o=',
                ),
        ),
);
