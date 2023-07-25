# CLARITY

The **CL**oud **A**sset **R**epositry and **I**nventory **T**ool for **Y**ou (CLARITY) is a tool to extend Google Cloud Platform's [Cloud Asset Inventory](https://cloud.google.com/asset-inventory/docs/overview) (CAI) for better usability and functionality. It consists of two components:

- **BigQuery Data Views** - a set of helpful views that make it easier to retrieve useful information from the 250+ tables created by the CAI data export process.
- **CLARITY GUI** - a web interface for querying and pivoting among common data types.
- **View to Snapshot** - an optional script to create static BigQuery tables for each BigQuery view definition to improve query speed and costs by precalulating the data on a regular basis.

## Getting Started

### Enable GCP Cloud Asset Inventory BigQuery Export
Follow [this guide](https://cloud.google.com/asset-inventory/docs/exporting-to-bigquery) to export asset metadata for your GCP environment to BigQuery table. You may want to start with a single snapshot to get started and then move to a regularly scheduled snapshot using a Google Cloud Function, such as what is described in this [Medium.com]( https://medium.com/google-cloud/using-gcp-cloud-asset-inventory-export-to-keep-track-of-your-gcp-resources-over-time-20fb6fa63c68) guide. 

### Import the CLARITY Views
These 37 views make it easier to access common data types from your CAI BigQuery tables and are needed for the CLARITY web interface to function. 

Run the latest version of `clarity_view_importer.py` from this repository, specifying the GCP project and BigQuery dataset for your existing CAI data. By default, the views will be created within your CAI dataset but this can be overridden with the `-P` and `-D` options at runtime.

````
# Create views in the cai_project project and cai_dataset dataset
./clarity_view_importer.py -p cai_project -d cai_dataset

Adding cai_project.cai_dataset.view_nodepool
Adding cai_project.cai_dataset.view_k8s_rbac_authorization_cluster_role
Adding cai_project.cai_dataset.view_bucket
Adding cai_project.cai_dataset.view_k8s_deployments
...
````

### (Optional) Create Daily Schedule to Create Snapshots from Views for Better Performance
Follow [this guide](https://github.com/Nuro-ai/CLARITY/tree/main/view_to_snapshot)

### Install the CLARITY Web Interface
- Set up a PHP 8 web server with the [CLARITY source code](https://github.com/Nuro-ai/CLARITY/tree/main/clarity_web) running in the webroot.
- Use Composer to install cloud-bigquery library 1.23 or newer.
- Define the 'bigquery_project' and 'bigquery_dataset' constant variables in config.php to match the values for where your Cloud Asset Inventory BigQuery export is stored.
- Browse to web server URL to access CLARITY UI.


## Contributors
- Kesten Broughton
- Randy Heins
- Jeffrey Zhang

## License
The software is provided by [Nuro](https://nuro.ai) under the Apache Software License agreement. 
