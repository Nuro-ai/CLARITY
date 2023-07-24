<?php

    require('header.php');

    update_title('GCP Logging Summary');
    print_title('GCP Logging Summary');

    $bigquery_query = new bigquery_query($bigquery_client, 'get project logging summaries');
    $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
    $datatable->print_table("Projects", false, true, $bigquery_query->parsed_query_string);

    require('footer.php'); 
?>
