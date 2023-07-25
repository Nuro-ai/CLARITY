# CLARITY
A PHP web interface to query Google Cloud Platform [Cloud Asset Inventory](https://cloud.google.com/asset-inventory/docs/overview) data stored in BigQuery.

![SCR-20230725-hscd-2](https://github.com/Nuro-ai/CLARITY/assets/103699918/47515bcb-8bf2-4e0a-a578-d615923a2206)

## Requirements: 
- PHP 8 or greater
- PHP cloud-bigquery library, v1.23 or newer

## Usage:
- Enable GCP Cloud Asset Inventory [exports to BigQuery](https://cloud.google.com/asset-inventory/docs/exporting-to-bigquery) and run on a recurring basis (daily, weekly).
- Run [clarity_view_importer.py](https://github.com/Nuro-ai/CLARITY/tree/main/clarity_view_importer) to set up BigQuery views for CAI.
- Run [view_to_snapshot.py](https://github.com/Nuro-ai/CLARITY/tree/main/view_to_snapshot) to convert the views to static tables for faster/cheaper queries. This should be done after CAI export takes place to keep the data current. 
- Set up a PHP 8 web server with the CLARITY source code running in the webroot.
- Use Composer to install cloud-bigquery library 1.23 or newer.
- Define the 'bigquery_project' and 'bigquery_dataset' constant variables in config.php to match the values for where your Cloud Asset Inventory BigQuery export is stored.
- Browse to web server URL to access CLARITY UI.
