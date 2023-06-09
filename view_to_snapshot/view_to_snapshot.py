#! /usr/bin/env python3

# Version 0.1.0, 2023-06-09

# Author: rheins@nuro.ai
# view_to_snapshot.py - Cloud function triggered by PubSub to create static daily tables for
# all views defined within the Cloud Asset Inventory project to increase performance.
# The script also optionally writes the current views to a GCS bucket for version control.
# NOTE: Replace the value of "dataset" and "gcs_bucket" below

from google.cloud import bigquery
from google.cloud import storage
import re
import os
import base64

def bq(query):
    client = bigquery.Client()
    query_job = client.query(query)

    try:
        results = query_job.result()
        return results
    except BadRequest as e:
        for e in job.errors:
            print('ERROR: {}'.format(e['message']))
        return False

def write_gcs_object(bucket_name, object_name, content):
    storage_client = storage.Client()
    bucket = storage_client.bucket(bucket_name)
    object = bucket.blob(object_name)

    with object.open("w") as f:
        f.write(content)


def main(event, context):
    # This function should be called as a Cloud Function by passing a string via pubsub
    #   When the string = "all" - convert all views to a snapshot
    #   When the string starts with "view_" - convert just that view to a snapshot
    selected_view = base64.b64decode(event['data']).decode('utf-8')
    if not selected_view:
        selected_view = "all"

    # Name of GCS bucket for writing log views for version control (OPTIONAL)
    gcs_bucket = False

    # Define the dataset where views are saved in the format of $PROJECT.$DATASET
    # Example: cai-project.cai-dataset
    dataset = "__REPLACE__$PROJECT.$DATASET__REPLACE__"

    query = f"""
        SELECT table_name, view_definition
        FROM `{dataset}.INFORMATION_SCHEMA.VIEWS`
        """
    views = bq(query)

    for view in views:
        view_name = view['table_name']
        view_sql = view['view_definition']

        # Remove comments from view definition
        view_sql_cleaned = re.sub("--.*", "", view_sql)
        view_sql_cleaned = os.linesep.join(
            [s for s in view_sql_cleaned.splitlines() if s])

        if view_name == selected_view or selected_view == "all":
            match = re.match("^view_(.+)", view_name)
            if match:
                view_root = match.group(1)
                snapshot_name = "snapshot_{}".format(view_root)
                snapshot_sql = "CREATE OR REPLACE TABLE `{}.{}` AS ({})".format(
                    dataset, snapshot_name, view_sql_cleaned)
                print(f"Creating snapshot '{snapshot_name}' in dataset '{dataset}'")
                bq(snapshot_sql)

                # Write view SQL to GCS bucket for version control
                if gcs_bucket:
                    saved_view_name = "{}.txt".format(view_name)
                    write_gcs_object(gcs_bucket, saved_view_name, view_sql)


if __name__ == '__main__':
     print("Error: This should be run as a Cloud Function with main() as the entrypoint")