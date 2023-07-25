<?php

    require('header.php');

    $projectName = @filter($_GET['projectName']);
    $serviceAccount = @filter($_GET['serviceAccount']);
    $account = @filter($_GET['account']);
    $role = @filter($_GET['role']);
    $resourcePath = @filter($_GET['resourcePath']);
    $name = @filter($_GET['name']);

    $hidden_columns = array();

    if(isset($serviceAccount)){
        $pretty_name = isset($name) ? $name : $serviceAccount;
        update_title($pretty_name);
        print_title('Service Account Details:', $pretty_name);

        $bigquery_query = new bigquery_query($bigquery_client, 'get iam by service account', array('serviceAccount' => $serviceAccount));
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("IAM Polciies", false, true, $bigquery_query->parsed_query_string);
    } else if(isset($account)){
        $pretty_name = isset($name) ? $name : $account;
        update_title($pretty_name);
        print_title('Account Details:', $pretty_name);

        $bigquery_query = new bigquery_query($bigquery_client, 'get iam by account', array('account' => $account));
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("IAM Policies", false, true, $bigquery_query->parsed_query_string);
    } else if(isset($resourcePath)){
        $pretty_name = isset($name) ? $name : $resourcePath;
        update_title($pretty_name);
        print_title('Resource:', $pretty_name);

        $bigquery_query = new bigquery_query($bigquery_client, 'get iam by resource path', array('resourcePath' => $resourcePath));
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("IAM Policies", false, true, $bigquery_query->parsed_query_string);
    } else {
        print_title('IAM Summary by Account');

        $bigquery_query = new bigquery_query($bigquery_client, 'get iam summary');
        $datatable = new datatable($bigquery_query->results, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
        $datatable->print_table("Accounts", false, true, $bigquery_query->parsed_query_string);
    }

    require('footer.php'); 
?>
