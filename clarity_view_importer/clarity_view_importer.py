#! /usr/bin/env python3

# Version 0.1.0, 2023-06-01
# Author: rheins@nuro.ai
# Creates BigQuery views in the specififed dataset to facilitate easier Cloud Asset Inventory querying

import argparse
import sys
import glob
import re
from google.cloud import bigquery


def get_file_contents(file):
    f = open(file)
    s = f.read()
    f.close()
    return s


def main():
    # Parse arguments
    parser = argparse.ArgumentParser(
        description='Import BigQuery view definitions for Google Cloud Asset Inventory.')
    parser.add_argument('-p', '--project', type=str,
                        help='GCP project name for the BigQuery Cloud Asset Inventory dataset is hosted', required=True)
    parser.add_argument('-d', '--dataset', type=str,
                        help='BigQuery dataset name where the BigQuery Cloud Asset Inventory is hosted', required=True)
    parser.add_argument('-P', '--view_project', type=str,
                        help='GCP project name where the view will be stored. Default is the value of --project.', required=False)
    parser.add_argument('-D', '--view_dataset', type=str,
                        help='BigQuery dataset name where the view will be stored. Default is the value of --dataset.', required=False)
    parser.add_argument('-v', '--view_directory', type=str, default="./views",
                        help='Directory where view definitions are stored', required=False)
    args = parser.parse_args()

    # Store the views in the Cloud Asset Inventory BigQuery project/dataset by default
    if not args.view_project:
        args.view_project = args.project

    if not args.view_dataset:
        args.view_dataset = args.dataset

    views = glob.glob(f"{args.view_directory}/view_*.txt")

    if not views:
        sys.exit(
            f"No view definitions found in '{args.view_directory}', exiting")

    client = bigquery.Client()

    for view_path in views:
        view_name = ""
        valid_path = re.search(r"/(view_\w+).txt", view_path)
        if valid_path:
            view_name = valid_path.group(1)

            view_id = f"{args.view_project}.{args.view_dataset}.{view_name}"
            print(f"Adding view: {view_id}")

            view_src = get_file_contents(view_path)
            view_src = view_src.replace("$project", args.project)
            view_src = view_src.replace("$dataset", args.dataset)

            bq_view = bigquery.Table(view_id)
            bq_view.view_query = view_src

            bq_view = client.create_table(bq_view)
        else:
            print(f"Invalid view name {view_path}, skipping")


if __name__ == "__main__":
    main()
