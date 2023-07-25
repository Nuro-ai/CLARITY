<?php

    require('header.php');

    $sqlName = @filter($_GET['sqlName']);
    $projectName = @filter($_GET['projectName']);

    if(isset($sqlName)){
        $pretty_name = isset($name) ? $name : $sqlName;
        update_title($pretty_name);
        print_title('CloudSQL Instance:', $pretty_name);

        $bigquery_query = new bigquery_query($bigquery_client, 'get cloudsql by name', array('cloudSQLName' => $sqlName));
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("", false, true);

        if(isset($bigquery_query->results[0]['sqlPath'])){
            $sqlPath = $bigquery_query->results[0]['sqlPath'];
            die($sqlPath);
            $bigquery_query = new bigquery_query($bigquery_client, 'get iam by resource path', array('resourcePath' => $sqlPath));
            $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
            $datatable->print_table("IAM Policies", false, true, $bigquery_query->parsed_query_string);
        }
    } else if(isset($projectName)){
            $pretty_name = isset($name) ? $name : $projectName;
            update_title($pretty_name);
            print_title('CloudSQL Instances in Project:', $pretty_name);
    
            $bigquery_query = new bigquery_query($bigquery_client, 'get cloudsql by project', array('projectName' => $projectName));
            $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
            $datatable->print_table("CloudSQL Instances", false, true, $bigquery_query->parsed_query_string);
    }  else {
        print_title('All CloudSQL Instances');
        update_title("CloudSQL");

        $bigquery_query = new bigquery_query($bigquery_client, 'get all cloudsql');
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("CloudSQL Instances", false, false, $bigquery_query->parsed_query_string);
    }

    require('footer.php'); 
?>
