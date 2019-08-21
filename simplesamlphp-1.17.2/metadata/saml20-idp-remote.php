<?php
/**
 * SAML 2.0 remote IdP metadata for SimpleSAMLphp.
 *
 * Remember to remove the IdPs you don't use from this file.
 *
 * See: https://simplesamlphp.org/docs/stable/simplesamlphp-reference-idp-remote
 */

/*
 *  For test1.cms.va.gov

$metadata['VA_SSOi_IDP'] = array(
    'entityid' => 'VA_SSOi_IDP',
    'contacts' =>
        array(),
    'metadata-set' => 'saml20-idp-remote',
    'sign.authnrequest' => TRUE,
    'SingleSignOnService' =>
        array(
            0 =>
                array(
                    'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                    'Location' => 'https://logon.int.iam.va.gov/affwebservices/public/saml2sso',
                ),
            1 =>
                array(
                    'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                    'Location' => 'https://logon.int.iam.va.gov/affwebservices/public/saml2sso',
                ),
        ),
    'SingleLogoutService' =>
        array(),
    'ArtifactResolutionService' =>
        array(),
    'NameIDFormats' =>
        array(
            0 => 'urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified',
        ),
    'keys' =>
        array(
            0 =>
                array(
                    'encryption' => FALSE,
                    'signing' => TRUE,
                    'type' => 'X509Certificate',
                    'X509Certificate' => 'MIIFaTCCBFGgAwIBAgIHPQAAABzf+TANBgkqhkiG9w0BAQsFADBKMRMwEQYKCZImiZPyLGQBGRYDZ292MRIwEAYKCZImiZPyLGQBGRYCdmExHzAdBgNVBAMTFlZBLUludGVybmFsLVMyLUlDQTEtdjEwHhcNMTgwNDI2MjEyNzA5WhcNMjEwNDI1MjEyNzA5WjCBrjELMAkGA1UEBhMCVVMxETAPBgNVBAgTCFZpcmdpbmlhMRAwDgYDVQQHEwdBc2hidXJuMSwwKgYDVQQKEyNVLlMuIERlcGFydG1lbnQgb2YgVmV0ZXJhbnMgQWZmYWlyczEMMAoGA1UECxMDSUFNMR0wGwYDVQQDExRsb2dvbi5zcWEuaWFtLnZhLmdvdjEfMB0GCSqGSIb3DQEJARYQYWNzYWRtaW5zQHZhLmdvdjCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBALLAprGIhlcVevcIEYpywX4b9ZFXtbqdGJLHESXxInjXi8zJJpoBEfCBrij8oapFxf7vp6Y396BjP/F0CqXh42aOul9BDI24Zkrc/OXowPe+lbaws6CWLdeuN7xsiqIPpiso9DHp5HBz6SdiQBQhJ9dPfSp9hWrYfW2EpBKqDSBF7Z7CqFlGsCZy8ZTMaF8fHj4rIh7zspW9P6mKkILbiWDE2QTqs4OjT/Qg3U2N2kxlZHATQ9nMleKtwI7jfKCXHulzGZ1Sl7t3+uAo+aFCjltvsePFMnpg6H7AjubOLJHYvz5eueD6iW+Mcrh2faWFe1ATCysww1YXpceQTQwxvycCAwEAAaOCAe0wggHpMAsGA1UdDwQEAwIFoDAdBgNVHSUEFjAUBggrBgEFBQcDAgYIKwYBBQUHAwEwSwYDVR0RBEQwQoIUbG9nb24uc3FhLmlhbS52YS5nb3aCFGxvZ29uLmludC5pYW0udmEuZ292ghRsb2dvbi5kZXYuaWFtLnZhLmdvdjAdBgNVHQ4EFgQUchVKYBHKuIZpMkVP07UvxiApSa8wHwYDVR0jBBgwFoAUG23f6z3l4g3vFrHQ3l9YGlbL5OwwSQYDVR0fBEIwQDA+oDygOoY4aHR0cDovL2NybC5wa2kudmEuZ292L3BraS9jcmwvVkEtSW50ZXJuYWwtUzItSUNBMS12MS5jcmwwewYIKwYBBQUHAQEEbzBtMEcGCCsGAQUFBzAChjtodHRwOi8vYWlhLnBraS52YS5nb3YvcGtpL2FpYS92YS9WQS1JbnRlcm5hbC1TMi1JQ0ExLXYxLmNlcjAiBggrBgEFBQcwAYYWaHR0cDovL29jc3AucGtpLnZhLmdvdjA9BgkrBgEEAYI3FQcEMDAuBiYrBgEEAYI3FQiByMMzgfnwBoGlnw2E4IEIhcKqSwaDgp9ggeCLUgIBZAIBFTAnBgkrBgEEAYI3FQoEGjAYMAoGCCsGAQUFBwMCMAoGCCsGAQUFBwMBMA0GCSqGSIb3DQEBCwUAA4IBAQCaxymM63A9tft11E6l98Rn5N6FfOGe8iICf4jBY16N8i696HHQVzUIY7xu8buP4DEWeiVgHJESdOdvvOT+0OTr3VMxiHDXVxvzoaaO/QLN01FqGF/cKbieHP/wJzRJzeX40DtPe5MMXu5UUj2HRnfhvGRUCsoZ7MaF4JJgYvGG521RC9PwK1rcGtAnSZl4GNg41h50qJ5NZKv6eIRSh1sUOc/ylxwtLYH2xeQx3zESxrSKcNMQCHfjr0POx+NNBit8KDRbH27AGVeazut1Zk+tFXu0kXqfqqdV9YFW9YBdEntj2tPfzc9j4sMMktBrFA6NHIwltyEKC/xmvhK0rhtW',
                ),
        ),
);
 */

// For staging.cms.va.gov
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
                    'Location' => 'https://logon.preprod.iam.va.gov/affwebservices/public/saml2sso',
                ),
            1 =>
                array (
                    'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                    'Location' => 'https://logon.preprod.iam.va.gov/affwebservices/public/saml2sso',
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
            0 => 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',
        ),
    'keys' =>
        array (
            0 =>
                array (
                    'encryption' => false,
                    'signing' => true,
                    'type' => 'X509Certificate',
                    'X509Certificate' => 'MIIFRTCCBC2gAwIBAgIHPQAAACB/ZDANBgkqhkiG9w0BAQsFADBKMRMwEQYKCZImiZPyLGQBGRYDZ292MRIwEAYKCZImiZPyLGQBGRYCdmExHzAdBgNVBAMTFlZBLUludGVybmFsLVMyLUlDQTEtdjEwHhcNMTgwNjE4MjExMTQ1WhcNMjEwNjE3MjExMTQ1WjCBsjELMAkGA1UEBhMCVVMxETAPBgNVBAgTCFZpcmdpbmlhMRAwDgYDVQQHEwdBc2hidXJuMSwwKgYDVQQKEyNVLlMuIERlcGFydG1lbnQgb2YgVmV0ZXJhbnMgQWZmYWlyczEMMAoGA1UECxMDSUFNMSEwHwYDVQQDExhsb2dvbi5wcmVwcm9kLmlhbS52YS5nb3YxHzAdBgkqhkiG9w0BCQEWEGFjc2FkbWluc0B2YS5nb3YwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQDO+en/aiQ5/fs9mQ0LfGi00VCvta0IiXQTSSc7XdKHqsgJLfJ2oaq6JEkppWvtS0+pa3aZfrTU4Erd2mZ9Ur99IwIM0ZJHCNICGbKB/yA6nbFuu53Xo2P0Y7gLyhmTNlGOTdlln85Em6QRrrBGvZvxGVX/hthuCtnTv1u+Mhd5feXc7liWLgI6kpHKTnFPiYIq4BAtu6KKoKrAQfM0BzKbKCc8+f/r1Ju4Ma+IdFYuP1TG14YH88FK+y/GdZ/EAEbZOPVxKNif8hyVwCa6nwvK8S/gtEGNDh5tt+hs3SBBmuvBTE7qdbuSnJNNteyBXzhvHbBGD/r3jY3EOJaBXqf1AgMBAAGjggHFMIIBwTALBgNVHQ8EBAMCBaAwHQYDVR0lBBYwFAYIKwYBBQUHAwIGCCsGAQUFBwMBMCMGA1UdEQQcMBqCGGxvZ29uLnByZXByb2QuaWFtLnZhLmdvdjAdBgNVHQ4EFgQU7UpWRSA6BzdCpUR/wKN3EMvwSIowHwYDVR0jBBgwFoAUG23f6z3l4g3vFrHQ3l9YGlbL5OwwSQYDVR0fBEIwQDA+oDygOoY4aHR0cDovL2NybC5wa2kudmEuZ292L3BraS9jcmwvVkEtSW50ZXJuYWwtUzItSUNBMS12MS5jcmwwewYIKwYBBQUHAQEEbzBtMEcGCCsGAQUFBzAChjtodHRwOi8vYWlhLnBraS52YS5nb3YvcGtpL2FpYS92YS9WQS1JbnRlcm5hbC1TMi1JQ0ExLXYxLmNlcjAiBggrBgEFBQcwAYYWaHR0cDovL29jc3AucGtpLnZhLmdvdjA9BgkrBgEEAYI3FQcEMDAuBiYrBgEEAYI3FQiByMMzgfnwBoGlnw2E4IEIhcKqSwaDgp9ggeCLUgIBZAIBFTAnBgkrBgEEAYI3FQoEGjAYMAoGCCsGAQUFBwMCMAoGCCsGAQUFBwMBMA0GCSqGSIb3DQEBCwUAA4IBAQAsrFZmAF9OSWsm4YmxTqwAbLW8zppGvh6rhLHWHazGkIh6WlQqar7lu5KuJmIkTmH5dbT1XFnjHKTUZFI5PT3afVqpyDEVcg6McY7fxBOfC0tj8GHUrZWDsOKgA4Shr4QlFMYcawY92Mv0KmAOFuXr+ybF8WGX1zNbF/P3qbywxShLJgSqUa8pP+eE1EO8pm447Ruj6e3odRzL/yi3qOJJccJWHh5E5McQqrijsnmknbZ/KuavSiHEqHz73i9DrWnYCHtDa0mljsIa3LFOx7RVjNhqVta0ME5eCoz7XeJ8KoFKPKHAY2XoDfGDg6JwM6pWDLyDazc1DkV+lBZd9s8m',
                ),
        ),
);
