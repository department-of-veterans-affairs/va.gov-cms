# Use of AWS Datasync to Facilitate Accelerated Publishing Operations

The Accelerated Publishing (AP) project has a need to access the Content Management System's (CMS) asset files from a publicly accessible AWS Simple Storage Service (S3) Bucket. The CMS stores assets files which consist of:
images, PDFs, text and other files, on AWS Elastic File System (EFS) filesystems connected to CMS servers. CMS servers store asset files on EFS to facilitate quick deployments, which swap current servers with updated servers,
by mounting and unmounting EFS file systems that effectively acts as persistent file storage not tied to a specific server. To satisfy this need CMS has created infrastructure that utilizes AWS Datasync to transfer files from
EFS to S3 on an automated schedule. This document will describe the architecture, AWS Services, Terraform resources, deployment, and pitfalls.

# Overview
* [Architecture](#rchitecture)
* [AWS Services and Terraform Resources](aws-services-and-terraform-resources)
  * [Datasync](#datasync)
    * EFS Location
    * S3 Location
    * Task
  * [EFS](#efs)
    * Security group rules 
  * [S3](#s3)
    * Asset files bucket 
    * Lambda code bucket 
  * [Identity and Access Management (IAM)](#identity-and-access-management-iam)
    * Datasync S3 bucket access role
      * Permission Policy
    * Lambda function execution role
      * Permission Policy 
  * [Lambda](#lambda)
    * Function
    * Lambda permission 
  * [Cloudwatch](#cloudwatch)
    * Eventbridge
      * Event rule
      * Event target
*  [Deployment](#deployment)
  *  Terraform
  *  Lambda
*  [Pitfalls](#pitfalls)

# Architecture

![image](https://github.com/department-of-veterans-affairs/va.gov-cms/assets/31904439/9618d869-5485-4547-a9ec-f7d301bbbf91)

1. Every 5 minutes Cloudwatch Eventbridge triggers the execution of a Lambda function.
2. A Lambda Function containing a simple Python script using AWS API calls to start a Datasync task execution. Lambda requires permission to execute Datasync tasks.
3. The Datasync task is configured with an EFS location which is specifically targeting the CMS EFS filesystem.
4. The Datasync task is configured with an S3 bucket location and moves files from EFS to the bucket. Datasync Tasks require permissions to put objects in the destination bucket.

# AWS Services and Terraform Resources

## Datasync

Datasync is the core of this architecture that facilitates the automated movement of files between EFS and S3. Terraform resources for this come in 3 parts:

### EFS Location
This requires only three (3) attributes to be defined:
* EFS Mount Target - CMS EFS filesystems have 3 mount targets, one in each VPC subnet. This is simply configured for the mount target in the first subnet.
* Secruity Group ARNs - Security Groups that are associated with the EFS Mount Target.
* Subnet ARN - The ARN of the subnet where the chosen mount target exists.
### S3 Location
This requires only three (3) attributes to be defined:
* S3 Bucket ARN
* Bucket Sub-directory - Or the prefix where files should be copied to. It is currently set to the root.
* Bucket Access Role - IAM role that grants Datasync read and write access to the bucket
### Task
This ties the Datasync locations together and determines which is the source and destination, as well as file transfer settings:
* Destination ARN - CMS Files S3 bucket
* Source ARN - CMS EFS filesystem ARN
* Preserve Deleted Files - Set to remove files from the destination that don't exist in the source.

## EFS
Datasync uses the EFS filesystem used to persist CMS data between deployments as the file sync source.
### Security group rules
The Datasync EFS location is configured to use the pre-existing EFS SG In the `efs.tf` file. However, the rules needed to be modified to allow inbound port 2049 from the SG itself as well as all egress traffic using itself as a source.

## S3
### Asset Files Bucket
The assest files bucket is pre-existing and defiend in `s3.tf` no changes were required for this resource.
### Lambda Code bucket
An additional bucket has been created to faciliate the deployment of the Python script that Lambda uses to start the Datasync Task. Lambda TF resource is configured to source function code from S3.

## IAM
### Datasync S3 bucket access role
DataSync requires access to your S3 bucket. To do this, DataSync assumes an AWS Identity and Access Management (IAM) role with an IAM policy that determines which actions that the role can perform.
#### Permission Policy
The permission policy for this role was copied from the AWS documenation here:
https://docs.aws.amazon.com/datasync/latest/userguide/create-s3-location.html#create-s3-location-access
### Lambda function execution role
Lambda requires a basic execution role to function properly and additional permissions to interact with Datasync service resources.
#### Permission Policy 
In addition to basic execution permissions offered by an AWS managed policy, Lambda also needs to be allowed to start task execution as well as describe EC2 network interfaces.

## Lambda
Instead of relying on Datasync's built-in schedule feature we use Lambda to start the Datasync Task. This is because Datasync is limited to a minimum schedule frequency of one (1) hour. However, Lambda functions can
be triggered at a much high frequency to meet AP's Datasync requirements.
### Function
As stated previously this a simple Python script that uses the Boto3 library to interact with the AWS API. It takes the Datasync task Amazon Resource Name (ARN) as input into Datasync's `start_task_execution` method.
### Lambda Permission
To allow Cloudwatch Eventbridge to trigger the lambda function it must be given explicit permission to do so. This is not done through IAM resources directly but a `aws_lambda_permission` resource that needs the:
* Action - InvokeFunction
* Function Name
* Principal - Calling service
* Source - Cloudwatch event rule ARN
## Cloudwatch
### Eventbridge
This allows for the triggering of the lambda function and then the Datasync task on a frequency far higher than Datasync itself offers.
#### Event rule
Determines how or when events trigger their defined target. This is set to `rate(5 minutes)`
#### Event target
Determines what gets triggered by the defined rule and simply takes the ARN of the resource that should be triggered. In this case, the Lambda function ARN.

# Deployment
## Terraform
Checkout the master branch of the [DevOps](https://github.com/department-of-veterans-affairs/devops) repository then browse to the `terraform/environments` folder. CMS.tf and CMS-Test.tf Terraform modules that reference the CMS
Terraform Infrastructure repository are found in the environments `dsva-vagov-staging` and `dsva-vagov-prod`. Incrementing the version number of the `source` attribute to reflect that lastest release from the CMS TF infrastructure repository 
will make the resource available to apply to the terraform state of each environment.
## Lambda
While the Python script for the lambda function is tracked in the CMS Terraform Infrastructure repository there exists NO automated deployment of this code to lambda. Rather updates and changes should stay tracked in version
control the script must be packaged in a zip archive and uploaded to the lambda deployment S3 bucket defined in the TF Lambda resource. AWS Provides documenation on how to package Python scripts and their dependencies (boto3)
into a zip archive [here](https://github.com/department-of-veterans-affairs/devops)
# Pitfalls

# References
[CMS Terraform Infrastructure Repository](https://github.com/department-of-veterans-affairs/terraform-aws-vsp-cms)
[Original Issue](https://github.com/department-of-veterans-affairs/va.gov-cms/issues/16925)
