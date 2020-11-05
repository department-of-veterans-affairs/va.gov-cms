# HTTPS Setup

## HTTPS browser setup for production usage
All computers in VA already have this setup, if you are using a non-VA laptop for development you will need to trust the VA Root Certificate Authority (CA) in your browser(s).

### Download certificates, right click "save as"
* http://crl.pki.va.gov/PKI/AIA/VA/VA-Internal-S2-RCA1-v1.cer
* http://crl.pki.va.gov/PKI/AIA/VA/VA-Internal-S2-ICA1-v1.cer

### On OSX
1. Open Keychain Access
1. Go to Certificates (under Category in left sidebar)
1. Select "Import Items..." from File menu. (Shift-Command-I)
1. Select the two .cer files above.
1. They should now appear in your list of certificates
1. For each certificate: 1) File > Get info  2) Under Trust > When using this certificate, select "Always Trust". 3) Close the Get info window, which will prompt a password save. 
1. You may need to restart your browser.

### Linux:

Chrome
1. Go to chrome://settings/certificates?search=https
1. Click "Authorities"
1. Click "Import" and select VA-Internal-S2-RCA1-v1.cer file downloaded above

Firefox
1. Go to about:preferences#privacy, scroll to bottom
1. Click "View Certificates"
1. Click "Authorities" tab
1. Click "Import"
1. Import both files downloaded above

## HTTPS testing (locally/Lando)

You can't test with the VA cert locally using Lando but you can use Lando's self-signed cert. If you need to test the actual cert locally contact the DevOps team to help you setup the vagrant build system to get HTTPS working with VA CA.

To test with Lando's self-signed cert you need to tell your system to trust the Lando Certificate Authority. Instructions are here > https://docs.devwithlando.io/config/security.html

TODO, create upstream PR with `sudo trust anchor --store ~/.lando/certs/lndo.site.pem` for Arch Linux

Note: I had to still import that same CA into Chrome.
Go to chrome://settings/certificates?search=https
Click "Authorities"
Import `.lando\certs\lndo.site.pem`
