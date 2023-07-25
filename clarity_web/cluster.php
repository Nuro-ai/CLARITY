<?php

    require('header.php');

    $clusterName = @filter($_GET['clusterName']);
    $clusterPath = @filter($_GET['clusterPath']);
    $nodepoolName = @filter($_GET['nodepoolName']);
    $nodepoolPath = @filter($_GET['nodepoolPath']);
    $name = @filter($_GET['name']);
    $projectName = @filter($_GET['projectName']);

    if(isset($clusterPath)){
        $pretty_name = isset($name) ? $name : $clusterPath;
        update_title($pretty_name);
        print_title('Cluster Instance:', $pretty_name);

        $bigquery_query = new bigquery_query($bigquery_client, 'get cluster by path', array('clusterPath' => $clusterPath));
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("Cluster", false, true, $bigquery_query->parsed_query_string);

        $bigquery_query = new bigquery_query($bigquery_client, 'get nodepools by cluster path', array('clusterPath' => $clusterPath));
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("Nodepools", false, true, $bigquery_query->parsed_query_string);

        $bigquery_query = new bigquery_query($bigquery_client, 'get nodes by cluster path', array('clusterPath' => $clusterPath));
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("Nodes", false, true, $bigquery_query->parsed_query_string);

        $bigquery_query = new bigquery_query($bigquery_client, 'get project pods by cluster path', array('clusterPath' => $clusterPath));
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("Project Pods", false, true, $bigquery_query->parsed_query_string);

        $bigquery_query = new bigquery_query($bigquery_client, 'get system pods by cluster path', array('clusterPath' => $clusterPath));
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("System Pods", false, true, $bigquery_query->parsed_query_string);

    } else if(isset($projectName)){
        $pretty_name = isset($name) ? $name : $projectName;
        update_title($pretty_name);
        print_title('Cluster Instances in Project:', $pretty_name);

        $bigquery_query = new bigquery_query($bigquery_client, 'get clusters by project', array('projectName' => $projectName));
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("Clusters", false, true, $bigquery_query->parsed_query_string);

        $bigquery_query = new bigquery_query($bigquery_client, 'get nodepools by project', array('projectName' => $projectName));
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("Nodepools", false, true, $bigquery_query->parsed_query_string);

        $bigquery_query = new bigquery_query($bigquery_client, 'get nodes by project', array('projectName' => $projectName));
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("Nodes", false, true, $bigquery_query->parsed_query_string);

        $bigquery_query = new bigquery_query($bigquery_client, 'get pods by project', array('projectName' => $projectName));
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("Pods", false, true, $bigquery_query->parsed_query_string);

    }  else {
        print_title('All Clusters');

        $bigquery_query = new bigquery_query($bigquery_client, 'get all clusters');
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("Clusters", false, false, $bigquery_query->parsed_query_string);
    }

    require('footer.php'); 
?>
