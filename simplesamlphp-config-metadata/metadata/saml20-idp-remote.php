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
                    'X509Certificate' => 'MIIFLzCCBBegAwIBAgIHPQAAAABGDTANBgkqhkiG9w0BAQsFADBHMRMwEQYKCZImiZPyLGQBGRYDZ292MRIwEAYKCZImiZPyLGQBGRYCdmExHDAaBgNVBAMTE1ZBLUludGVybmFsLVMyLUlDQTQwHhcNMjExMjAyMTQyNTE2WhcNMjIxMjI2MTQyNTE2WjCBnzELMAkGA1UEBhMCVVMxEjAQBgoJkiaJk/IsZAEZFgJ2YTETMBEGCgmSJomT8ixkARkWA2dvdjERMA8GA1UECBMIVmlyZ2luaWExEDAOBgNVBAcTB0FzaGJ1cm4xJzAlBgNVBAoTHkRlcGFydG1lbnQgb2YgVmV0ZXJhbnMgQWZmYWlyczEZMBcGA1UEAxMQbG9nb24uaWFtLnZhLmdvdjCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBAL/NMAXlsHJV1JTreWfjHm9a9OAbMVX/bpO2JmpZT9/kM9PWbzk4zOPVTD65jFT2c1nuZvLqoRnKb1TgbPm8DWvh1MFwvwLb1z38Kcjuq2GgdTrNSHsZ089nrW3lSw0Sdp7qnllM8aY2MIU6lAv8/RkzLM6Lvr+/C9OcxzFSnYEytYOw/F9DQtjX3rxFtFM09INKmOsp8zfcvjfrwnFGV0XvIs946j7LmVOPj2epajBDaIdygyOGSPSrbpl694L3yzNv3yexYvLXxQjx1JUAPyMEleWxs2M6teyH9YUU0ahao0D01rUUQWdLMtvH84G0/1Z6RoDsA/rZ1+HyPokRM3UCAwEAAaOCAcUwggHBMBsGA1UdEQQUMBKCEGxvZ29uLmlhbS52YS5nb3YwHQYDVR0OBBYEFEzCIQic7d29E1VmadtWP1xuNv2GMB8GA1UdIwQYMBaAFGi3comeQurkUUUfWKIKJw9zYECjMEYGA1UdHwQ/MD0wO6A5oDeGNWh0dHA6Ly9jcmwucGtpLnZhLmdvdi9wa2kvY3JsL1ZBLUludGVybmFsLVMyLUlDQTQuY3JsMHgGCCsGAQUFBwEBBGwwajBEBggrBgEFBQcwAoY4aHR0cDovL2FpYS5wa2kudmEuZ292L3BraS9haWEvdmEvVkEtSW50ZXJuYWwtUzItSUNBNC5jZXIwIgYIKwYBBQUHMAGGFmh0dHA6Ly9vY3NwLnBraS52YS5nb3YwDAYDVR0TAQH/BAIwADALBgNVHQ8EBAMCBPAwPQYJKwYBBAGCNxUHBDAwLgYmKwYBBAGCNxUIgcjDM4H58AaBpZ8NhOCBCIXCqksGgtrLEYHR8FYCAWQCASswHQYDVR0lBBYwFAYIKwYBBQUHAwIGCCsGAQUFBwMBMCcGCSsGAQQBgjcVCgQaMBgwCgYIKwYBBQUHAwIwCgYIKwYBBQUHAwEwDQYJKoZIhvcNAQELBQADggEBAGj7dyK/xy7mAZs5z0Kq1gXiPVbAM1ODTlwIVOiIBQ6CNN/wvyYaslckNvFOPvgqaJT6tNwEf8hJ3kF4EBnRB2lyQ7qoqhTDCdovg7sh2gyV+JG2KpQL8B2lcUQwZr2aA5Mj9rPups18fA8sG5eGWzc3CT3L979hNwDvwcL5XUbflhwrl9MWs0kzOa8C5bBj9OfaNDqHV3DJywYjK49+EXIM7WV5DRuWqKAbkxukqR3XB1J7pQdXCLuqAUC5vOmK2KvytDGluekq3RGlSOeEWeJAr/iYRRy2a69n4QtAypPJG+y8usVT9lccCtsbvzNW70x1g4tfEGhbAitI235Qi98=',
                ),
        ),
);
