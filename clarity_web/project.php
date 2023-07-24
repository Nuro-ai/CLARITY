<?php

    require('header.php');

    $projectName = @filter($_GET['projectName']);

    if(isset($projectName)){
        update_title($projectName);

        print_title('Project:', $projectName);

        $bigquery_query = new bigquery_query($bigquery_client, 'get project by name', array('projectName' => $projectName));
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("Project Details", false, true, $bigquery_query->parsed_query_string);

        if(isset($bigquery_query->results[0]['projectParent'])){
            $projectPath = $bigquery_query->results[0]['projectParent'];
        }

        $bigquery_query = new bigquery_query($bigquery_client, 'get top project costs', array('projectName' => $projectName));
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("Top Costs Last Month", false, true, $bigquery_query->parsed_query_string);

        $bigquery_query = new bigquery_query($bigquery_client, 'get vpcs by project name', array('projectName' => $projectName));
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("Project VPCs", false, false, $bigquery_query->parsed_query_string);

        $bigquery_query = new bigquery_query($bigquery_client, 'get subnets by project name', array('projectName' => $projectName));
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("Project Subnets", false, false, $bigquery_query->parsed_query_string);

        $bigquery_query = new bigquery_query($bigquery_client, 'get instances by project', array('instanceProject' => $projectName));
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("Compute Instances", false, false, $bigquery_query->parsed_query_string);

        $bigquery_query = new bigquery_query($bigquery_client, 'get clusters by project', array('projectName' => $projectName));
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("K8s Clusters", false, false, $bigquery_query->parsed_query_string);

        $bigquery_query = new bigquery_query($bigquery_client, 'get external ips by project', array('projectName' => $projectName));
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("External IP Addresses", false, false, $bigquery_query->parsed_query_string);

        $bigquery_query = new bigquery_query($bigquery_client, 'get cloudsql by project', array('projectName' => $projectName));
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("CloudSQL Instances", false, false, $bigquery_query->parsed_query_string);

        $bigquery_query = new bigquery_query($bigquery_client, 'get buckets by projectName', array('projectName' => $projectName));
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("GCS Buckets", false, false, $bigquery_query->parsed_query_string);

        if($projectPath){
            $bigquery_query = new bigquery_query($bigquery_client, 'get iam by resource path', array('resourcePath' => $projectPath));
            $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
            $datatable->print_table("IAM Policies", false, true, $bigquery_query->parsed_query_string);
        }
    } else {
        print_title('All GCP Projects');

        $bigquery_query = new bigquery_query($bigquery_client, 'get all projects');
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("Project Summary", false, false, $bigquery_query->parsed_query_string);
    }
?>

<?php require('footer.php'); ?>