# Restore the Database from Backups
## Overview
This document provides instructions on how to restore a database from backups. It is essential to ensure that you have the necessary permissions and access to the backup files before proceeding.
## Prerequisites
- Access to AWS S3 bucket where backups are stored.
- Access to EC2 instance where commands are being run.

## Relevant Files
- `/var/www/cms/scripts/sync-db.sh`

## Steps to Restore Database
1. Find the backup you want to restore in https://us-gov-west-1.console.amazonaws-us-gov.com/s3/buckets/dsva-vagov-prod-cms-backup?region=us-gov-west-1&prefix=database/&showversions=false
2. Download the backup file to your local machine or directly to the EC2 instance.
   - If downloading to the EC2 instance, use `aws s3 cp s3://dsva-vagov-prod-cms-backup/database/your-backup-file.sql.gz /path/to/destination/` Note: Replace `your-backup-file.sql.gz` with the actual backup file name and `/path/to/destination/` with the desired path on the EC2 instance. This will also require authenticating with AWS CLI, which can be done using `aws configure` or by setting up an IAM role for the EC2 instance.
   - If downloading to your local machine, use the AWS CLI or the AWS S3 console to download the file.
   - If you are using the AWS CLI, you can run:
     ```bash
     aws s3 cp s3://dsva-vagov-prod-cms-backup/database/your-backup-file.sql.gz /path/to/local/destination/
     ```
3. If you downloaded the backup file to your local machine, upload to the sanitized bucket: https://us-gov-west-1.console.amazonaws-us-gov.com/s3/buckets/dsva-vagov-prod-cms-backup-sanitized?region=us-gov-west-1&tab=objects Note: Make sure you create a /temp folder to upload it into. Note: Make sure the file is publicly available so that the EC2 instance can access it. Everyone should be able to read it.
4. Connect to ec2 instance.
   `ssm-session vagov-prod cms auto` or connect via the Connect button in the AWS console.
5. Modify the `sync-db.sh` script to point to the backup file you want to restore. The script is located at `/var/www/cms/scripts/sync-db.sh`. You can use `vi` or `vim` to edit the file.
   - Find the line that specifies the `db_path` and change it to the path of your backup file.
   - Example:
     ```bash
     db_path="temp/your-backup-file.sql.gz"
     ```
6. Run the `sync-db.sh` script to restore the database.
   ```bash
   cd /var/www/cms/scripts/
   ./sync-db.sh
   ```
7. Monitor the output of the script to ensure that the restoration process completes successfully.
8. Once the script has completed, verify that the database has been restored correctly by checking the data in the application or running queries against the database.
9. Remove the backup file from the EC2 instance if it is no longer needed to free up space.
   ```bash
   rm /var/www/cms/.dumps/your-backup-file.sql.gz
   ```
10. If you uploaded the backup file to the sanitized bucket, make sure to remove it from there as well to maintain security and compliance.
    ```bash
    aws s3 rm s3://dsva-vagov-prod-cms-backup-sanitized/temp/your-backup-file.sql.gz
    ```
    Or do it via the AWS console by navigating to the sanitized bucket and deleting the file.
## Important Notes
