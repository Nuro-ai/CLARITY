<?php

    require('header.php');

    $projectName = @filter($_GET['projectName']);
    $name = @filter($_GET['name']);

    print_title('All External IPs');
    update_title("All External IPs");

    $bigquery_query = new bigquery_query($bigquery_client, 'get all external ips');
    $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
    $datatable->print_table("Addresses", false, false, $bigquery_query->parsed_query_string);

    require('footer.php'); 
?>
