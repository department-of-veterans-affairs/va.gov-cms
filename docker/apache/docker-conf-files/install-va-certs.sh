#!/usr/bin/env bash

set -euo pipefail

#
# Install the VA certs. Place them in a location that is easily
# shared and in the system trust.
#

mkdir -p /usr/local/share/ca-certificates
cd /usr/local/share/ca-certificates/

wget https://cacerts.digicert.com/DigiCertTLSRSASHA2562020CA1-1.crt.pem
wget https://digicert.tbs-certificats.com/DigiCertGlobalG2TLSRSASHA2562020CA1.crt

wget \
--recursive \
--wait 5 \
--random-wait \
--retry-connrefused \
--no-parent \
--no-host-directories \
--cut-dirs 5 \
--accept=".cer" \
http://crl.pki.va.gov/PKI/AIA/

for cert in *.cer
do
  cert_name="${cert%.cer}"
  if file "${cert}" | grep 'PEM'
  then
      \cp -f "${cert}" "${cert_name}.crt"
  else
      openssl x509 -in "${cert}" -inform der -outform pem -out "${cert_name}.crt"
  fi
  rm "${cert}" -f
  chmod 0444 "${cert_name}.crt"
done

ls
