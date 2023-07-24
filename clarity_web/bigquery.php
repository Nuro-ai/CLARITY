<?php

    require('header.php');

    $bqDatasetPath = @filter($_GET['bqDatasetPath']);
    $projectName = @filter($_GET['projectName']);

    if(isset($projectName)){
        $pretty_name = isset($name) ? $name : $projectName;
        update_title($pretty_name);
        print_title('BigQuery Datasets in Project:', $pretty_name);

        $bigquery_query = new bigquery_query($bigquery_client, 'get bigquery datasets by project name', array('projectName' => $projectName));
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("BigQuery Datasets", false, true, $bigquery_query->parsed_query_string);
    } else if(isset($bqDatasetPath)){
            $pretty_name = isset($name) ? $name : $bqDatasetPath;
            update_title($pretty_name);
    
            $bigquery_query = new bigquery_query($bigquery_client, 'get bigquery by dataset path', array('bqDatasetPath' => $bqDatasetPath));
            $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
            $datatable->print_table("Dataset Details", false, true, $bigquery_query->parsed_query_string);

            $bigquery_query = new bigquery_query($bigquery_client, 'get bigquery tables by dataset path', array('bqDatasetPath' => $bqDatasetPath));
            $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
            $datatable->print_table("Dataset Tables", false, true, $bigquery_query->parsed_query_string);

            $bigquery_query = new bigquery_query($bigquery_client, 'get iam by resource path', array('resourcePath' => $bqDatasetPath));
            $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
            $datatable->print_table("IAM Policies", false, true, $bigquery_query->parsed_query_string);
    }  else {
        print_title('All BigQuery Datasets');

        $bigquery_query = new bigquery_query($bigquery_client, 'get all bigquery datasets');
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("Datasets", false, false, $bigquery_query->parsed_query_string);
    }

    require('footer.php'); 
?>
