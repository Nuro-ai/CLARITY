<?php

    require('header.php');

    $bucketName = @filter($_GET['bucketName']);
    $bucketPath = @filter($_GET['bucketPath']);
    $parentId = @filter($_GET['parentId']);

    if(isset($bucketName)){
        $pretty_name = isset($name) ? $name : $bucketName;
        update_title($pretty_name);
        print_title('Storage Bucket:', $pretty_name);

        $bigquery_query = new bigquery_query($bigquery_client, 'get bucket by name', array('bucketName' => $bucketName));
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("GCS Buckets", false, true, $bigquery_query->parsed_query_string);
        
        if(isset($bigquery_query->results[0]['bucketPath'])){
            $bucketPath = $bigquery_query->results[0]['bucketPath'];
            $bigquery_query = new bigquery_query($bigquery_client, 'get iam by resource path', array('resourcePath' => $bucketPath));
            $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
            $datatable->print_table("IAM Policies", false, true, $bigquery_query->parsed_query_string);
        }
    }  else  if(isset($bucketPath)){
        $pretty_name = isset($name) ? $name : $bucketPath;
        update_title($pretty_name);
        print_title('Storage Bucket:', $pretty_name);

        $bigquery_query = new bigquery_query($bigquery_client, 'get bucket by path', array('bucketPath' => $bucketPath));
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("GCS Buckets", false, true, $bigquery_query->parsed_query_string);

        $bigquery_query = new bigquery_query($bigquery_client, 'get iam by resource path', array('resourcePath' => $bucketPath));
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("IAM Policies", false, true, $bigquery_query->parsed_query_string);
    } 
    
    else if(isset($parentId)){
            $pretty_name = isset($name) ? $name : $parentId;
            update_title($pretty_name);
            print_title('Storage Buckets in Project:', $pretty_name);
    
            $bigquery_query = new bigquery_query($bigquery_client, 'get buckets by parentId', array('parentId' => $parentId));
            $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
            $datatable->print_table("GCS Buckets", false, true, $bigquery_query->parsed_query_string);
    }  else {
        print_title('All Storage Buckets');

        $bigquery_query = new bigquery_query($bigquery_client, 'get all buckets');
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("GCS Buckets", false, false, $bigquery_query->parsed_query_string);
    }

    require('footer.php'); 
?>
