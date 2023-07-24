<?php

    require('header.php');

    $podName = @filter($_GET['podName']);
    $podPath = @filter($_GET['podPath']);
    $clusterPath = @filter($_GET['clusterPath']);
    $clusterName = @filter($_GET['clusterName']);
    $projectPods = @filter($_GET['projectPods']);
    $systemPods = @filter($_GET['systemPods']);


    $name = @filter($_GET['name']);
    $projectName = @filter($_GET['projectName']);

    if(isset($podPath)){
        $pretty_name = isset($name) ? $name : $podPath;
        update_title($pretty_name);
        print_title('Kubernetes Pod:', $pretty_name);

        $bigquery_query = new bigquery_query($bigquery_client, 'get pod by path', array('podPath' => $podPath));
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("Pod Details", false, true, $bigquery_query->parsed_query_string);

    } else if(isset($clusterPath)){
        if(preg_match("/projects\/(.+?)\/.+\/clusters\/(.+)/", $clusterPath, $m)){
            $pretty_name = $m[2];
        } else {
            $pretty_name = $clusterPath;
        }
        update_title($pretty_name);
        print_title('Pods in Cluster:', $pretty_name);

        $bigquery_query = new bigquery_query($bigquery_client, 'get project pods by cluster path', array('clusterPath' => $clusterPath));
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("Project Pods", false, true, $bigquery_query->parsed_query_string);

        $bigquery_query = new bigquery_query($bigquery_client, 'get system pods by cluster path', array('clusterPath' => $clusterPath));
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("System Pods", false, true, $bigquery_query->parsed_query_string);

    }  else {
        print_title('All Kubernetes Pods per Project');

        $bigquery_query = new bigquery_query($bigquery_client, 'get pod summary by cluster');
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("Kubernetes Pods per Project", false, false, $bigquery_query->parsed_query_string);
    }

    require('footer.php'); 
?>
