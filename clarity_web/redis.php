<?php

    require('header.php');

    $projectName = @filter($_GET['projectName']);
    $redisPath = @filter($_GET['redisPath']);
    $name = @filter($_GET['name']);


    if(isset($redisPath)){
        $pretty_name = isset($name) ? $name : $redisPath;
        update_title($pretty_name);
        print_title('Redis Instance Details:', $pretty_name);

        $bigquery_query = new bigquery_query($bigquery_client, 'get redis by path', array('redisPath' => $redisPath));
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("Instance", false, true, $bigquery_query->parsed_query_string);

    } else if(isset($projectName)){
        $pretty_name = $projectName;
        update_title($pretty_name);
        print_title('Redis Instances in Project:', $pretty_name);

        $bigquery_query = new bigquery_query($bigquery_client, 'get redis by project', array('projectName' => $projectName));
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("Instances", false, true, $bigquery_query->parsed_query_string);
    }  else {
        print_title('All Memorystore Redis Instances');
        update_title("All Memorystore Redis Instances");

        $bigquery_query = new bigquery_query($bigquery_client, 'get all redis');
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("Redis Instances", false, false, $bigquery_query->parsed_query_string);
    }

    require('footer.php'); 
?>
