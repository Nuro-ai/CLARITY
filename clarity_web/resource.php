<?php

    require('header.php');

    print_title('GCP Resource Summary');

    $bigquery_query = new bigquery_query($bigquery_client, 'get resource summary');
    $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
    $datatable->print_table("Resources", false, false, $bigquery_query->parsed_query_string);

    require('footer.php'); 
?>
