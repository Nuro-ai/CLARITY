<?php

    require('header.php');

    $bucketName = @filter($_GET['bucketName']);
    $logBucketPath = @filter($_GET['logBucketPath']);
    $parentId = @filter($_GET['parentId']);

if(isset($logBucketPath)){
        $pretty_name = isset($name) ? $name : $logBucketPath;
        update_title($pretty_name);
        print_title('Log Bucket:', $pretty_name);

        $bigquery_query = new bigquery_query($bigquery_client, 'get log bucket by path', array('logBucketPath' => $logBucketPath));
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("GCS Buckets", false, true, $bigquery_query->parsed_query_string);

        $bigquery_query = new bigquery_query($bigquery_client, 'get iam by resource path', array('resourcePath' => $logBucketPath));
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
        print_title('All Log Buckets');

        $bigquery_query = new bigquery_query($bigquery_client, 'get all log buckets');
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, false, $linkable_columns, $pivotable_columns);
        $datatable->print_table("Log Buckets", false, false, $bigquery_query->parsed_query_string);
    }

    require('footer.php'); 
?>
