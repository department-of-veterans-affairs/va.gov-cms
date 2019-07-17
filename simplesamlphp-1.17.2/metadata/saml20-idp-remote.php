<?php
/**
 * SAML 2.0 remote IdP metadata for SimpleSAMLphp.
 *
 * Remember to remove the IdPs you don't use from this file.
 *
 * See: https://simplesamlphp.org/docs/stable/simplesamlphp-reference-idp-remote
 */


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
