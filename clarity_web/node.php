<?php

    require('header.php');

    $clusterName = @filter($_GET['clusterName']);
    $clusterPath = @filter($_GET['clusterPath']);
    $nodepoolName = @filter($_GET['nodepoolName']);
    $nodepoolPath = @filter($_GET['nodepoolPath']);
    $name = @filter($_GET['name']);
    $projectName = @filter($_GET['projectName']);

    print_title('All Node Pools');

    $bigquery_query = new bigquery_query($bigquery_client, 'get all nodepools');
    $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
    $datatable->print_table("Node Pools", false, false, $bigquery_query->parsed_query_string);
 
    require('footer.php'); 
?>
