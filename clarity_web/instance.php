<?php

    require('header.php');

    $instancePath = @filter($_GET['instancePath']);

    $name = @filter($_GET['name']);

    if(isset($instancePath)){
        $pretty_name = isset($name) ? $name : $instancePath;
        update_title($pretty_name);
        print "<h2>Compute Instance: <small class='text-muted'>{$pretty_name}</a></small></h2>";

        $bigquery_query = new bigquery_query($bigquery_client, 'get instance by path', array('instancePath' => $instancePath));
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("", false, true, $bigquery_query->parsed_query_string);

        $bigquery_query = new bigquery_query($bigquery_client, 'get iam by resource path', array('resourcePath' => $instancePath));
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("IAM Policies", false, true, $bigquery_query->parsed_query_string);
    } else {
        update_title('All Compute Instances');
        print_title('All Compute Instances');

        $bigquery_query = new bigquery_query($bigquery_client, 'get all instances');
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("Instances", false, false, $bigquery_query->parsed_query_string);

    }

    require('footer.php'); 
?>
