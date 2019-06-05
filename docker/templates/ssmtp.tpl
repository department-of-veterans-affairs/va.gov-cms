# The user that gets all the mails (UID < 1000, usually the admin)
root=postmaster

# The mail server (where the mail is sent to)
mailhub={{ SMTP_RELAY_HOST }}:{{ SMTP_RELAY_HOST_PORT }}

# The address where the mail appears to come from.
rewriteDomain={{ SMTP_FROM_HOSTNAME }}

# The full hostname.  Must be correctly formed, fully qualified domain name.
hostname=localhost

# Use SSL/TLS before starting negotiation
UseTLS=No
UseSTARTTLS=negotiation

# Email 'From header's can override the default domain?
FromLineOverride=yes