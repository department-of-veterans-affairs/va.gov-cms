{
  # Convert to lowercase.
  str = tolower($0);
  # Replace all special chars with hyphens.
  gsub(/[^a-z0-9]/, "-", str);
  # Deduplicate hyphens.
  gsub(/-+/, "-", str);
  # Remove leading hyphens.
  gsub(/^-/, "", str);
  # Trim the string down to 25 characters because of max hostname label length. If the prefix length changes this must
  # change too.
  # See https://github.com/department-of-veterans-affairs/va.gov-cms/pull/4399#issuecomment-789297526
  str = substr(str, 0, 25);
  # Remove trailing hyphens.
  gsub(/-$/, "", str);
  # Print out the SetEnvIf line.
  printf "SetEnvIf Request_URI \".*\" QA_SUBDOMAIN=%s\n", str
}
