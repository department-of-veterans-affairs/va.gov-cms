# Getting Started


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

## Launch a local development environment:
* [Get Lando](https://docs.lando.dev/basics/installation.html)
* Fork the repo by pressing the "Fork" button: [github.com/department-of-veterans-affairs/va.gov-cms](https://github.com/department-of-veterans-affairs/va.gov-cms)
* `git clone git@github.com:[YOUR-GIT-USERNAME]/va.gov-cms.git vagov`
* `git remote add upstream git@github.com:department-of-veterans-affairs/va.gov-cms.git`
* `cd vagov`
* `lando start`
* `lando composer install`
* `.scripts/sync-db.sh`
* `.scripts/sync-files.sh`


[Table of Contents](../README.md)
