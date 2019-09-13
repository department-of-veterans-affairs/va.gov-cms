# HTTPS Setup

## HTTPS testing (locally/Lando)

You can't test with the VA cert locally using Lando but you can use Lando's self-signed cert. If you need to test the actual cert locally contact the DevOps team to help you setup the vagrant build system to get HTTPS working with VA CA.

To test with Lando's self-signed cert you need to tell your system to trust the Lando Certificate Authority. Instructions are here > https://docs.devwithlando.io/config/security.html

TODO, create upstream PR with `sudo trust anchor --store ~/.lando/certs/lndo.site.pem` for Arch Linux

Note: I had to still import that same CA into Chrome.
Go to chrome://settings/certificates?search=https
Click "Authorities"
Import `.lando\certs\lndo.site.pem`

## HTTPS browser setup for production usage
All computers in VA already have this setup, if you are using a non-VA laptop for development you will need to trust the VA Root Certificate Authority (CA) in your browser(s).

Chrome
* `wget http://crl.pki.va.gov/PKI/AIA/VA/VA-Internal-S2-RCA1-v1.cer`
* Go to chrome://settings/certificates?search=https
* Click "Authorities"
* Click "Import" and select VA-Internal-S2-RCA1-v1.cer file downloaded above

Firefox
* `wget http://crl.pki.va.gov/PKI/AIA/VA/VA-Internal-S2-RCA1-v1.cer`
* `wget http://crl.pki.va.gov/PKI/AIA/VA/VA-Internal-S2-ICA1-v1.cer`
* Go to about:preferences#privacy, scroll to bottom
* Click "View Certificates"
* Click "Authorities" tab
* Click "Import"
* Import both files downloaded above