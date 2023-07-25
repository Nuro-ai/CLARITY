<?php

    require('header.php');

    $subnetId = @filter($_GET['subnetId']);
    $subnetSelfLink = @filter($_GET['subnetSelfLink']);
    $name = @filter($_GET['name']);

    if(isset($subnetId)){
        $pretty_name = isset($name) ? $name : $subnetId;
        update_title($pretty_name);
        print_title('Subnet:', $pretty_name);

        $bigquery_query = new bigquery_query($bigquery_client, 'get subnet by ID', array('subnetId' => $subnetId));
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("Subnet Details", false, true, $bigquery_query->parsed_query_string);

        $bigquery_query = new bigquery_query($bigquery_client, 'get instances by subnet ID', array('subnetId' => $subnetId));
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("Instances on this Subnet", false, true, $bigquery_query->parsed_query_string);
    } else if(isset($subnetSelfLink)){
        $pretty_name = isset($name) ? $name : $subnetSelfLink;
        update_title($pretty_name);
        print_title('Subnet:', $pretty_name);

        $bigquery_query = new bigquery_query($bigquery_client, 'get subnet by subnetSelfLink', array('subnetSelfLink' => $subnetSelfLink));
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("Subnet Details", false, true, $bigquery_query->parsed_query_string);

        $bigquery_query = new bigquery_query($bigquery_client, 'get instances by subnetSelfLink', array('subnetSelfLink' => $subnetSelfLink));
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("Instances on this Subnet", false, true. $bigquery_query->parsed_query_string);

    } else {
        print_title('All GCP Subnets');

        $bigquery_query = new bigquery_query($bigquery_client, 'get all subnets');
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("Subnets", false, false, $bigquery_query->parsed_query_string);

    }

    require('footer.php'); 
?>
