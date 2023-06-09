# CLARITY

The **CL**oud **A**sset **R**epositry and **I**nventory **T**ool for **Y**ou (CLARITY) is a tool to extend Google Cloud Platform's [Cloud Asset Inventory](https://cloud.google.com/asset-inventory/docs/overview) (CAI) for better usability and functionality. It consists of two components:

- **BigQuery Data Views** - a set of helpful views that make it easier to retrieve useful information from the 250+ tables created by the CAI data export process.
- **CLARITY GUI** - a web interface for querying and pivoting among common data types.

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

### Install the CLARITY Web Interface
Once the CLARITY Web Interface is available, instructions will be included here.

## Contributors
- Kesten Broughton
- Randy Heins
- Jeffrey Zhang

## License
The software is provided by [Nuro](https://nuro.ai) under the Apache Software License agreement. 