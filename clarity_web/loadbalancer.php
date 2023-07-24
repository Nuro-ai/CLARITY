<?php

    require('header.php');

    $loadbalancerPath = @filter($_GET['loadbalancerPath']);
    $urlMapPath = @filter($_GET['urlMapPath']);
    $name = @filter($_GET['name']);
    $projectName = @filter($_GET['projectName']);

    if(isset($urlMapPath)){
        $pretty_name = isset($name) ? $name : $urlMapPath;
        update_title($pretty_name);
        print_title('Load Balancer Details');

        $bigquery_query = new bigquery_query($bigquery_client, 'get url map by path', array('urlMapPath' => $urlMapPath));
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("Load Balancer: {$pretty_name}", false, true, $bigquery_query->parsed_query_string);

        $bigquery_query = new bigquery_query($bigquery_client, 'get forwarding rules by url map path', array('urlMapPath' => $urlMapPath));
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("Forwarding Rules", false, true, $bigquery_query->parsed_query_string);

        $bigquery_query = new bigquery_query($bigquery_client, 'get backend services by url map path', array('urlMapPath' => $urlMapPath));
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("Backend Services", false, true, $bigquery_query->parsed_query_string);

        $bigquery_query = new bigquery_query($bigquery_client, 'get instance groups by url map path', array('urlMapPath' => $urlMapPath));
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("Instance Groups", false, true, $bigquery_query->parsed_query_string);

    } else if(isset($loadbalancerPath)){
        $pretty_name = isset($name) ? $name : $projectName;
        update_title($pretty_name);
        print_title('Cluster Instances in Project:', $pretty_name);

        $bigquery_query = new bigquery_query($bigquery_client, 'get clusters by project', array('projectName' => $projectName));
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("Clusters", false, true, $bigquery_query->parsed_query_string);

        $bigquery_query = new bigquery_query($bigquery_client, 'get nodepools by project', array('projectName' => $projectName));
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("Nodepools", false, true, $bigquery_query->parsed_query_string);

    }  else {
        print_title('All Load Balancers');
        update_title('Load Balancers');

        $bigquery_query = new bigquery_query($bigquery_client, 'get all loadbalancers');
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("Load Balancers", false, false, $bigquery_query->parsed_query_string);
    }

    require('footer.php'); 
?>
