# view_to_snapshot
Script to create static BigQuery tables for each BigQuery view definition to improve query speed and costs. This is best used on data that doesn't change often. It can be used to speed up queries for Cloud Asset Inventory. It optionally stores the view definitions in a GCS bucket to assist with version control.

## Requirements: 
- Python `google-cloud-bigquery` and `google-cloud-storage` modules
- GCP account with `roles/bigquery.jobUser` and `roles/bigquery.dataEditor` predefined IAM roles to create the views.

## Usage:


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