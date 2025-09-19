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
  echo "Processing certificate: ${cert}"
  
  # Check if file is empty or corrupted
  if [ ! -s "${cert}" ]; then
    echo "Warning: ${cert} is empty or corrupted, skipping..."
    rm "${cert}" -f
    continue
  fi
  
  # Try to determine format and convert
  if file "${cert}" | grep -q 'PEM'; then
    echo "${cert} is already in PEM format"
    \cp -f "${cert}" "${cert_name}.crt"
  else
    echo "Converting ${cert} from DER to PEM format"
    # Try DER format first
    if openssl x509 -in "${cert}" -inform der -outform pem -out "${cert_name}.crt" 2>/dev/null; then
      echo "Successfully converted ${cert} from DER format"
    # If DER fails, try PEM format (some files might not be detected correctly by file command)
    elif openssl x509 -in "${cert}" -inform pem -outform pem -out "${cert_name}.crt" 2>/dev/null; then
      echo "Successfully processed ${cert} as PEM format"
    else
      echo "Error: Could not read certificate from ${cert}, skipping..."
      rm "${cert}" -f
      continue
    fi
  fi
  
  # Verify the converted certificate is valid
  if ! openssl x509 -in "${cert_name}.crt" -text -noout >/dev/null 2>&1; then
    echo "Error: Generated ${cert_name}.crt is not a valid certificate, removing..."
    rm "${cert_name}.crt" -f
    rm "${cert}" -f
    continue
  fi
  
  rm "${cert}" -f
  chmod 0444 "${cert_name}.crt"
  echo "Successfully processed ${cert} -> ${cert_name}.crt"
done

ls
