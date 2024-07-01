#!/bin/bash

# This script sets the right permissions and ownership on a Drupal installation.
# Loosely based on https://www.drupal.org/node/244924
#
# Copyright (C) 2023  Ricardo Sanz Ante
# This is program is licensed the GPL 3.0. See https://www.gnu.org/licenses/gpl-3.0.html
# This program comes with ABSOLUTELY NO WARRANTY. This is free software, and you
# are welcome to redistribute it under certain conditions;

# Script usage help.
function usage() {
cat <<HELP

  SYNOPSIS

    `basename "$0"` [OPTION]...


  DESCRIPTION

    This script fixes permissions on a Drupal installation. See
    https://www.drupal.org/node/244924

    There are two kinds of files and directories: code and content.

    Code are the Drupal codebase, contrib code and configuration files.
    Those should be writable by the deploy user (the user that handles the
    Drupal files) and readable by the webserver. This allows the deploy user to
    modify the code if needed, but prohibits the webserver (the HTTPD process)
    to modify them, increasing security.

    Content files and directories are those that hold files used generally as
    content: mainly uploaded files by users and generated files by the server.
    These should be readable and writable by both the deploy user (so they can
    manage them if needed) and the webserver (so the webserver can manage them
    as well).

    To allow this, code is owned by the deploy user and belongs to the group
    of the webserver process. The permissions set allows owner to write those
    files and folder but group members can only read them.
    Content files and directories have the same ownership scheme (they are
    owned by the deploy user and belong to the webserver's group) but
    permissions allow both editing and reading.

    -u, --drupal_user=<user>: User that manages the Drupal code, the deploy
        user. Mandatory.

    -g, --httpd_group=<group>: Web server process group. Files will be assigned
        to this group. Defaults to 'www-data'.

    -p, --drupal_path=<path>: Path to the Drupal root directory. Defaults to the
        current directory ('.').

    -f, --files-path=<path>: Additional content files directories. Use as much
        times as you need. Relative paths are relative to the Drupal path. This
        is useful when files directory is not under sites directory, for example
        outside the Drupal root.

    -s, --setgid: Enable setgid on content files directories. When enabled, all
        files and directories created under a setgid directory will be assigned
        to the group of the setgid directory regardless of the process that
        created the file. This is useful if a user different from the HTTPD user
        creates a file inside a content directory. With setgid, the file will
        be part of the HTTPD group, allowing the HTTPD process to edit or remove
        the file if needed. Otherwise, the HTTPD process lacks the permissions
        to do so.

    -n, --dry-run: Perform no action but display information about what would be
        done. Use twice for more information.

    -h, --help: Display this help message.


  EXAMPLES

    `basename "$0"` -u=deploy

      Fix permissions using 'deploy' as owner, and default values for group
          owner and Drupal Path.

    `basename "$0"` -u=deploy -g=www.data

      Fix permissions using 'deploy' as owner and 'www-data' as group owner. Use
      the default path to Drupal.

    `basename "$0"` -u=deploy -g=www.data -p=/var/vhosts/drupal/web

      Fix permissions using 'deploy' as owner and 'www-data' as group owner.
      Finds the Drupal in '/var/vhosts/drupal/web'.

    `basename "$0"` -u=deploy -s

      Fix permissions using 'deploy' as owner, and defaults value for group
      owner and Drupal Path. Sets the setgid bit to the content files
      directories.

    `basename "$0"` -u=deploy -f=../private

      Fix permissions using 'deploy' as owner, and defaults value for group
      owner and Drupal Path. Process an additional content directory at
      '../private', relative to the Drupal path.

    `basename "$0"` -u=deploy -n -n

      Display the list of files and directories that would be fixed, using
      'deploy' as owner, and default values for group owner and Drupal Path.


HELP
}


# Determines if current user is the root user.
#
# Returns:
#  0 if it is the root user.
#  1 if it is not the root user.
function is_root_user() {
  [ "$(id -u)" == 0 ]
}


# Determines if a user exists in the system.
#
# Returns:
#  0 if it is the root user.
#  1 if it is not the root user.
function is_valid_user() {
  [ ! -z "$1" ] && [[ $(id -un "$1" 2> /dev/null) == "$1" ]]
}


# Determines if a path is a Drupal root directory.
#
# The function checks that the path exists, it has a "sites" directory and tries
# to find the ssytem.module file. It detects Drupal 7/8/9/10.
#
# Params:
#  $1 Path to check.
#
# Returns:
#  0 if it is a Drupal root directory.
#  1 if it is not a Drupal root directory.
is_drupal_root() {
  [ ! -z "$1" ]  && [ -d "$1"  ] && [ -d "$1/sites" ] && ([ -f "$1/core/modules/system/system.module" ] || [ -f "$1/modules/system/system.module" ])
}


# Sets the right owners for a given directory.
#
# Find any file or folder that is not owned by the drupal user or its group is
# not the web server group and fixes the ownership.
#
# Params:
#  $1 Path to the directory to process.
#
# Globals:
#  drupal_user: user to own the files and directories.
#  httpd_group: group to own the files and directories.
function fix_ownership() {
  case $simulate in
    0)
    # Real action.
    find "$1" $detected_vendor_path \( ! -user $drupal_user -o ! -group $httpd_group \) \( -type f -o -type d \) -print0 | xargs -r -0 -L20 chown  $drupal_user:$httpd_group
    ;;

    1)
    # Simulate.
    printf "\n    Items with wrong ownership: "
    find "$1" $detected_vendor_path \( ! -user $drupal_user -o ! -group $httpd_group \) \( -type f -o -type d \) -print | wc -l
    ;;

    2)
    # Simulate verbosely.
    printf "\n    Files and directories that would have their ownership fixed: "
    # Use a variable to indent output.
    items=$(find "$1" $detected_vendor_path \( ! -user $drupal_user -o ! -group $httpd_group \) \( -type f -o -type d \) -print)
    items=${items:-None}
    printf "\n      ${items//$'\n'/$'\n'      }\n"
    ;;
  esac
}


# Helper function to set the permissions on code files and folders.
#
# This is an internal function.
#
# Params:
#  $1 Path to the directory to process.
#  $2 Type of element to process. f for files, d for directories.
#  $3 Permissions wanted compatible with chmod . Exmaple: u=rwx,g=rwxs,o=
function fix_code_permission_helper() {
  case $simulate in
    0)
    # Real action.
    find "$1" \( -path "$1"/sites/\*/$file_folder_name -prune \) -o \( -path "$1"/sites/\*/$private_folder_name -prune \) -o \( -type $2 ! -perm $3 -print0 \) | xargs -r -0 -L4 chmod $3
    ;;

    1)
    # Simulate.
    num=$(find "$1" \( -path "$1"/sites/\*/$file_folder_name -prune \) -o \( -path "$1"/sites/\*/$private_folder_name -prune \) -o \( -type $2 ! -perm $3 -print \) | wc -l)
    printf "\n    Code items with wrong permissions: $num"
    ;;

    2)
    # Simulate verbosely.
    printf "\n    Code files and directories that would have their permissions fixed: "
    # Use a variable to indent output.
    items=$(find "$1" \( -path "$1"/sites/\*/$file_folder_name -prune \) -o \( -path "$1"/sites/\*/$private_folder_name -prune \) -o \( -type $2 ! -perm $3 -print \))
    items=${items:-None}
    printf "\n      ${items//$'\n'/$'\n'      }\n"
    ;;
  esac
}


# Helper function to set the permissions on content files and folders.
#
# This is an internal function.
#
# Params:
#  $1 Path to the directory to process.
#  $2 Type of element to process. f for files, d for directories.
#  $3 Permissions wanted compatible with chmod . Exmaple: u=rwx,g=rwxs,o=
function fix_content_permission_helper() {
  case $simulate in
    0)
    # Real action.
    find "$1" -type $2 ! -perm $3 -print0 | xargs -r -0 -L20 chmod $3
    ;;

    1)
    # Simulate.
    num=$(find "$1" -type $2 ! -perm $3 -print | wc -l)
    printf "\n      Content items with wrong permissions: $num"
    ;;

    2)
    # Simulate verbosely.
    printf "\n      Content files and directories that would have their permissions fixed: "
    # Use a variable to indent output.
    items=$(find "$1" -type $2 ! -perm $3 -print)
    items=${items:-None}
    printf "\n        ${items//$'\n'/$'\n'        }\n"
    ;;

  esac
}


# Sets the permissions of a code path.
#
# Params:
#  $1 Path to process.
#
# Globals:
#  code_dir_perms: permissions scheme to use for code directories.
#  code_file_perms permissions scheme to use for code files.
function fix_code_permissions() {

  name=$(basename "$1")
  printf "\n  Setting permissions on code directories to $code_dir_perms under '$name'"
  fix_code_permission_helper "$1" d "$code_dir_perms"

  printf "\n  Setting permissions on code files to $code_file_perms under '$name'"
  fix_code_permission_helper "$1" f "$code_file_perms"


  if [ ! -z "$detected_vendor_path" ]
  then
    printf "\n  Setting permissions on vendor code directories to $code_dir_perms under '$detected_vendor_path'"
    fix_code_permission_helper "$detected_vendor_path" d "$code_dir_perms"

    printf "\n  Removing all permissions on vendor code files to other users ($vendor_code_file_perms) under '$detected_vendor_path'"
    fix_code_permission_helper "$detected_vendor_path" f "$vendor_code_file_perms"
  fi

}


# Sets the permissions of a content path.
#
# Params:
#  $1 Path to process.
#
# Globals:
#  content_dir_perms: permissions scheme to use for content directories.
#  content_file_perms permissions scheme to use for content files.
function fix_content_permissions() {

  name=$(basename "$1")
  printf "\n    Setting permissions on content directories to $content_dir_perms under '$name'"
  fix_content_permission_helper "$1" d "$content_dir_perms"

  printf "\n    Setting permissions on content files to $content_file_perms under '$name'"
  fix_content_permission_helper "$1" f "$content_file_perms"
}



# Main code
###########
#


printf "Script to fix permissions in a Drupal installation"

# Default values.
DEFAULT_DRUPAL_PATH=.
DEFAULT_HTTPD_GROUP="apache"

# Initialize some values.
group_executable_mode=x
additional_files_paths=""
file_folder_name='files'
private_folder_name='private'
simulate=0


# Parse Command Line Arguments
while [ "$#" -gt 0 ]; do
  case "$1" in
    --drupal_path=* | -p=*)
      drupal_path="${1#*=}"
      ;;
    --drupal_user=* | -u=*)
      drupal_user="${1#*=}"
      ;;
    --httpd_group=* | -g=*)
      httpd_group="${1#*=}"
      ;;
    --files-path=* | -f=*)
      # Add a new line if there is any previous element.
      if [ ! -z "$additional_files_paths" ]
      then
        additional_files_paths+='\n'
      fi
      # Add path to the path list.
      additional_files_paths=$(printf "${additional_files_paths}${1#*=}")
      ;;
    --setgid | -s)
      group_executable_mode=xs
      ;;
    --dry-run | -n)
      simulate=$((simulate  + 1))
      ;;
    --help | -h)
      usage
      exit 0
      ;;
    *)
      printf "\nError: Invalid parameter '$1'\n"
      exit 1
  esac
  shift
done


# Initialize undefined values with default values.
if [ -z $drupal_path ]
then
  drupal_path=$DEFAULT_DRUPAL_PATH
  printf "\nUsing default Drupal path '$DEFAULT_DRUPAL_PATH'"
fi

if [ -z $httpd_group ]
then
  httpd_group=$DEFAULT_HTTPD_GROUP
  printf "\nUsing default HTTPD group '$DEFAULT_HTTPD_GROUP'"
fi



# Calculate permissions by object type (directory or file) and function (code or
# content files).
code_dir_perms='u=rwx,g=rx,o='
code_file_perms='u=rw,g=r,o='
vendor_code_file_perms='o='
content_dir_perms="u=rwx,g=rw${group_executable_mode},o="
content_file_perms='ug=rw,o='



# Go to the right place (this a kind of initialization).
cd $drupal_path
complete_drupal_path=$(pwd)

# Check if there's a vendor folder in the upper folder.
[ -d "../vendor" ] && [ -f "../composer.json" ] && detected_vendor_path="../vendor"


# Show current configuration.
#############################
printf "\nRunning configuration:
Path: $complete_drupal_path
Owner user: $drupal_user
Owner group: $httpd_group
Code dirs perms: $code_dir_perms
Code files perms: $code_file_perms
Separated vendor folder detected: ${detected_vendor_path:-"No"}
Content dirs perms: $content_dir_perms
Content files perms: $content_file_perms
File folder name: $file_folder_name
Private files folder name: $private_folder_name
"
if [ ! -z "${additional_files_paths}" ]
then
  printf "Additional content directories to process:\n${additional_files_paths}"
fi


# Sanity checks.
################
#

is_root_user
if [ $? -ne 0 ]
then
  printf "\nError: you must run this script as the root user\n"
  exit 1
fi

is_drupal_root $drupal_path
if [ $? -ne 0 ]
then
  printf "\nError: provided path '$drupal_path' is not the root directory of a Drupal installation\n"
  exit 1
fi


if [ -z $drupal_user ]
then
  printf "\nError: no user provided\n"
  exit 1
fi


is_valid_user $drupal_user
if [ $? -ne 0 ]
then
  printf "\nError: provided user '$drupal_user' is not a valid user\n"
  exit 1
fi



# Do the job
############
#

printf "\n\nAll checks passed, go!"
printf "\nProcessing Drupal installed on '$complete_drupal_path'"

# First, fix ownership.
printf "\nFixing ownership of files and directories"
fix_ownership "$complete_drupal_path"
echo "$additional_files_paths"| while read path; do
  [ -d "$path" ] && fix_ownership "$path"
done

# Second, fix permissions on code.
printf "\nFixing permissions on code files and directories"
fix_code_permissions "$complete_drupal_path"

# Third, fix permissions on content.
printf "\nFixing permissions on content files and directories under 'sites' folder"
find "$complete_drupal_path/sites/" -maxdepth 1 -mindepth 1 -type d| while read site_folder
do
  printf "\n  Checking folder "
  printf $(basename "$site_folder")
  [ -d "$site_folder/$file_folder_name" ] && fix_content_permissions "$site_folder/$file_folder_name"
  [ -d "$site_folder/$private_folder_name" ] && fix_content_permissions "$site_folder/$private_folder_name"
done

[ -z "$additional_files_paths" ] && printf "\nProcessing additional content folders"
echo "$additional_files_paths"| while read path; do
  [ -d "$path" ] && fix_content_permissions "$path"
done

printf "\n\nPermissions and ownership fixed!\n"
