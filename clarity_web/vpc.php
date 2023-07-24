<?php

    require('header.php');

    $networkPath = @filter($_GET['networkPath']);
    $networkSelfLink = @filter($_GET['networkSelfLink']);

    $name = @filter($_GET['name']);

    if(isset($networkPath)){
        $pretty_name = isset($name) ? $name : $networkPath;
        update_title($pretty_name);
        print_title('VPC:', $pretty_name);

        $bigquery_query = new bigquery_query($bigquery_client, 'get subnets by networkPath', array('networkPath' => $networkPath));
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("Subnet Listing", false, true, $bigquery_query->parsed_query_string);
    } else if(isset($networkSelfLink)){
        $pretty_name = isset($name) ? $name : $networkSelfLink;
        update_title($pretty_name);
        print_title('VPC:', $pretty_name);

        $bigquery_query = new bigquery_query($bigquery_client, 'get subnets by networkSelfLink', array('networkSelfLink' => $networkSelfLink));
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("Subnet Listing", false, false, $bigquery_query->parsed_query_string);

        $bigquery_query = new bigquery_query($bigquery_client, 'get instances by networkSelfLink', array('networkSelfLink' => $networkSelfLink));
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("Instances on this VPC", false, false, $bigquery_query->parsed_query_string);
    
    }  else {
        print_title('All GCP VPCs');

        $bigquery_query = new bigquery_query($bigquery_client, 'get all vpcs');
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("VPCs", false, false, $bigquery_query->parsed_query_string);

    }

    require('footer.php'); 
?>
