<?php

    require('header.php');

    print_title('All GCP Projects');

    $bigquery_query = new bigquery_query($bigquery_client, 'get all projects');
    $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
    $datatable->print_table("Project Summary", false, false, $bigquery_query->parsed_query_string);
?>

<?php require('footer.php'); ?>