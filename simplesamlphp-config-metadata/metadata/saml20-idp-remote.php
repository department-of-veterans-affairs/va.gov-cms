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
                    'X509Certificate' => 'MIIFBjCCA+6gAwIBAgIHPQAAAAm/rzANBgkqhkiG9w0BAQsFADBKMRMwEQYKCZImiZPyLGQBGRYDZ292MRIwEAYKCZImiZPyLGQBGRYCdmExHzAdBgNVBAMTFlZBLUludGVybmFsLVMyLUlDQTEtdjEwHhcNMTcxMjE5MjI0NzEyWhcNMjAxMjE4MjI0NzEyWjCBmDELMAkGA1UEBhMCVVMxETAPBgNVBAgTCFZpcmdpbmlhMREwDwYDVQQHEwhDdWxwZXBlcjEnMCUGA1UEChMeRGVwYXJ0bWVudCBvZiBWZXRlcmFucyBBZmZhaXJzMRkwFwYDVQQDExBsb2dvbi5pYW0udmEuZ292MR8wHQYJKoZIhvcNAQkBFhBBQ1NBZG1pbnNAdmEuZ292MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAqyhWwhdTx/GAbo+MoHRAs8FrKYqNIIPL5DTlBY/L7T/hFWprsNgaCfRWa4No8CeIHlWkl52rDnUlLOwFY+3Y7TpSijGZ+/IqoHwlv8pRSq3GhmSryjdFNgdDiFS/JsrIc62H3XBJ/Bg8DyPdcxIy+2NvOdrVqKXQ2B87Lh6rKrL9exh+XEfkbeCsuKkw3C3Vw8H+04Teejt4VwId8zhPIVeGFQ4B2RjlfUrgkkI2DAit7KqyrMMRZ5imVRdREfaWn872hl7jFdw9I6j+yAGfqE+EGW91+uEFi/CEBr5/wk6JiiFjwCpVNyU5lbjNp+0ShGFpF6WJUtQVBr7cIlnMdQIDAQABo4IBoDCCAZwwHQYDVR0OBBYEFGJ90w7bYgsWz8mZzDBFhTli2icXMB8GA1UdIwQYMBaAFBtt3+s95eIN7xax0N5fWBpWy+TsMEkGA1UdHwRCMEAwPqA8oDqGOGh0dHA6Ly9jcmwucGtpLnZhLmdvdi9wa2kvY3JsL1ZBLUludGVybmFsLVMyLUlDQTEtdjEuY3JsMHsGCCsGAQUFBwEBBG8wbTBHBggrBgEFBQcwAoY7aHR0cDovL2FpYS5wa2kudmEuZ292L3BraS9haWEvdmEvVkEtSW50ZXJuYWwtUzItSUNBMS12MS5jZXIwIgYIKwYBBQUHMAGGFmh0dHA6Ly9vY3NwLnBraS52YS5nb3YwCwYDVR0PBAQDAgWgMD0GCSsGAQQBgjcVBwQwMC4GJisGAQQBgjcVCIHIwzOB+fAGgaWfDYTggQiFwqpLBoOCn2CB4ItSAgFkAgEVMB0GA1UdJQQWMBQGCCsGAQUFBwMCBggrBgEFBQcDATAnBgkrBgEEAYI3FQoEGjAYMAoGCCsGAQUFBwMCMAoGCCsGAQUFBwMBMA0GCSqGSIb3DQEBCwUAA4IBAQBVCc2Fjrk5+zLI/S7ZG32nIThf/tlUEsAIDcGPEdorOLVIoS5ILiRLWKMBeEzMeaH+rzD4zCkRZd8sME5S32MeDM6BYtkmE/sryYFtWxsmVqAenKcYb27zftTM/oCB2OOySeBFryLWxUbUPjA6iZvLjeLLRBqo8wdEJJWw5H7E6dQdHEz+h+27O58w1/jIpJRu7Qye6jE4i1yd3a7P2LCChH5KqurpztUzQSGylqqujW8Knm+FyM4Ovv+EhHDwgtXXf9b4jw5M+5CaViJW8JrCLFgT6cC9cUmKCvOG33njaB63myK/rrngqioMfxfICI1LEfUfImxfH0wulo9YKdzs',
                ),
        ),
);
