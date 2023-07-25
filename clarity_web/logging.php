<?php

    require('header.php');

    $logSinkName = @filter($_GET['logSinkName']);
    $logSinkPath = @filter($_GET['logSinkPath']);
    $projectName = @filter($_GET['projectName']);

    if(isset($logSinkPath)){
        $pretty_name = isset($name) ? $name : $logSinkPath;

        update_title('Log Sink Detail');
        print_title('Log Sink Detail:', $pretty_name);

        $bigquery_query = new bigquery_query($bigquery_client, 'get log sink by path', array('logSinkPath' => $logSinkPath));
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("Log Sink Details", false, true, $bigquery_query->parsed_query_string);

        $bigquery_query = new bigquery_query($bigquery_client, 'get exclusion filters by log sink path', array('logSinkPath' => $logSinkPath));
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("Exclusion Filters", false, true, $bigquery_query->parsed_query_string);
    
    } else { 
        print_title('All Log Sinks');
        update_title('Logging');


        $bigquery_query = new bigquery_query($bigquery_client, 'get all log sinks to pubsub');
        testprint($bigquery_query->results);
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("Log Sinks Routing to PubSubs", false, false, $bigquery_query->parsed_query_string);

        $bigquery_query = new bigquery_query($bigquery_client, 'get all log sinks to log buckets');
        testprint($bigquery_query->results);

        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("Log Sinks Routing to Log Buckets", false, false, $bigquery_query->parsed_query_string);
    }

    require('footer.php'); 
?>
