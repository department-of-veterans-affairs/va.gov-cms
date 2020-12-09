<?php

$config = [

    // This is a authentication source which handles admin authentication.
    'admin' => [
        // The default is to use core:AdminPassword, but it can be replaced with
        // any authentication source.

        'core:AdminPassword',
    ],

    // An authentication source which can authenticate against both SAML 2.0
    // and Shibboleth 1.3 IdPs.
    'default-sp' => [
        'saml:SP',

        // The entity ID of this SP.
        // Can be NULL/unset, in which case an entity ID is generated based on the metadata URL.
        'entityID' => "https://{$_SERVER['SERVER_NAME']}/simplesaml/module.php/saml/sp/metadata.php/default-sp",

        // The entity ID of the IdP this SP should contact.
        // Can be NULL/unset, in which case the user will be shown a list of available IdPs.
        'idp' => 'VA_SSOi_IDP',

        // The URL to the discovery service.
        // Can be NULL/unset, in which case a builtin discovery service will be used.
        'discoURL' => null,

        'NameIDPolicy'         => [
            'Format' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',
            'allowcreate' => 'true',
        ],
        'redirect.sign'        => true,
        'assertion.encryption' => true,
        'sign.logout'          => true,

        'privatekey'           => 'cms.va.gov.private.key',
        'certificate'          => 'cms.va.gov.public.crt',
        // Defaults to SHA1 (http://www.w3.org/2000/09/xmldsig#rsa-sha1)
        'signature.algorithm'  => 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256',

    ],

];
