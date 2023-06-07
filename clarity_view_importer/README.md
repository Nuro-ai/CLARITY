# CAI View Importer
Script to import BigQuery custom view definitions to facilitate streamlined querying of Google Cloud Asset Inventory data. These views must be created for the CLARITY web interface to function.

## Requirements: 
- Google Cloud Asset Inventory data [exported to BigQuery](https://cloud.google.com/asset-inventory/docs/exporting-to-bigquery)
- Python BigQuery module
- GCP account with `roles/bigquery.jobUser` and `roles/bigquery.dataEditor` predefined IAM roles to create the views.

## Usage:

````
usage: clarity_view_importer.py [-h] -p PROJECT -d DATASET [-P VIEW_PROJECT] [-D VIEW_DATASET] [-v VIEW_DIRECTORY]

Import BigQuery view definitions for Google Cloud Asset Inventory.

optional arguments:
  -h, --help            show this help message and exit
  -p PROJECT, --project PROJECT
                        GCP project name for the BigQuery Cloud Asset Inventory dataset is hosted
  -d DATASET, --dataset DATASET
                        BigQuery dataset name where the BigQuery Cloud Asset Inventory is hosted
  -P VIEW_PROJECT, --view_project VIEW_PROJECT
                        GCP project name where the view will be stored. Default is --project value.
  -D VIEW_DATASET, --view_dataset VIEW_DATASET
                        BigQuery dataset name where the view will be stored. Default is --dataset value.
  -v VIEW_DIRECTORY, --view_directory VIEW_DIRECTORY
                        Directory where view definitions are stored
````

## Example Usage:
````
# Create views in the cai_project project and cai_dataset dataset
./clarity_view_importer.py -p cai_project -d cai_dataset

Adding cai_project.cai_dataset.view_nodepool
Adding cai_project.cai_dataset.view_k8s_rbac_authorization_cluster_role
Adding cai_project.cai_dataset.view_bucket
Adding cai_project.cai_dataset.view_k8s_deployments
...
````

## Example View:
GCS Bucket Inventory
````
SELECT 
bucket.name as bucketPath, 
bucket.resource.data.kind as bucketKind, 
bucket.resource.data.name as bucketName, 
bucket.resource.parent as bucketParent, 
bucket.resource.data.timeCreated as bucketCreation, 
bucket.resource.data.updated as bucketUpdated, 
bucket.resource.data.location as bucketLocation, 
bucket.resource.data.versioning.enabled as bucketVersioning, 
bucket.resource.data.iamConfiguration.publicAccessPrevention as bucketPublicAccessPrevention, 
bucket.resource.data.iamConfiguration.bucketPolicyOnly.enabled as bucketIAMPolicy, 
bucket.resource.data.iamConfiguration.uniformBucketLevelAccess.enabled as bucketUniformAcess, 
bucket.resource.data.locationType as bucketLocationType, 
bucket.resource.data.directory.enabled as bucketDirectoryEnabled, 
project.projectName as projectName
FROM `$project.$dataset.resource_storage_googleapis_com_Bucket` bucket 
JOIN `$project.$dataset.view_project` project ON bucket.resource.parent = project.projectParent
WHERE DATE(bucket.readTime) = DATE_SUB(CURRENT_DATE(), INTERVAL 1 DAY)
````