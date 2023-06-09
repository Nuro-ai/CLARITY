# view_to_snapshot
Script to create static BigQuery tables for each BigQuery view definition to improve query speed and costs by precalulating the data on a regular basis. This is best used on data that doesn't change often. It optionally stores the view definitions in a GCS bucket to assist with version control.

 BigQuery [materialized views](https://cloud.google.com/bigquery/docs/materialized-views-intro) serve a similar function but have [limited support](https://cloud.google.com/bigquery/docs/materialized-views-create#supported-mvs) for some SQL functions that are needed by CLARITY.

## Requirements: 
- Python `google-cloud-bigquery` and `google-cloud-storage` modules
- GCP account with `roles/bigquery.jobUser` and `roles/bigquery.dataEditor` predefined IAM roles to create the views.

## Usage:
- Modify the `dataset` variable to point to the dataset where your views are stored (in the format `project.dataset`)
- (Optionally) modify the `gcs_bucket` variable to specify a GCS bucket where view definitions will be stored for version control
- Deploy `view_to_snapshot.py` and `requirements.txt` to a Python 3 Google Cloud Function with an entrypoint of `main()`
- Create a pubsub topic to trigger the Cloud Function
- Create a Cloud Scheduler job to run daily with a target of the pubsub topic and a message body with the word `all`.
- Alternatively, the pubsub topic accepts a string with the name of a specific view to only update a single snapshot



## Benefits of Snapshots:
Standard View Query:
````
SELECT * FROM view_projects

-- 
Elapsed Time: 6 seconds
Slot time consumed: 36 min
Bytes Processed: 18GB
````

Snapshot Query of the Same View:
````
SELECT * FROM snapshot_projects

--
Elapsed Time: < 1 second
Slot time consumed: 236 milliseconds
Bytes Processed: 31KB
````
