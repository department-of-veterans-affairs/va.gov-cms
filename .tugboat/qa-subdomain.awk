{
  # Convert to lowercase.
  str = tolower($0);
  # Replace all special chars with hyphens.
  gsub(/[^a-z0-9]/, "-", str);
  # Deduplicate hyphens.
  gsub(/-+/, "-", str);
  # Remove leading hyphens.
  gsub(/^-/, "", str);
  # Trim the string down to 25 characters.
  str = substr(str, 0, 25);
  # Remove trailing hyphens.
  gsub(/-$/, "", str);
  # Print out the SetEnvIf line.
  printf "SetEnvIf Request_URI \".*\" QA_SUBDOMAIN=%s\n", str
}
