<?php

    # BigQuery project and dataset for CAI data
    define('bigquery_project', FALSE);
    define('bigquery_dataset', FALSE);

    # Optional to enable Billing pivots
    # Provide your GCP billing account nubmer and GCP org id to enable billing pivots
    # Account number is in the format ######-######-######
    define('gcp_billing_account', FALSE);
    define('gcp_org_id', FALSE);

    # Optional
    # Connection settings for local Redis cache for faster subsequent queries
    define('redis_server', FALSE);
    define('redis_port', FALSE);

    $pages = array(
        'hidden' => array(
            'index.php', 
            'search.php'
        ),
        'Projects' => 'project.php',
        'Resources' => 'resource.php',
        'IAM' => 'iam.php',
        'Compute' => array(
            'Instances' => 'instance.php',
            'Clusters' => 'cluster.php',
            'Nodes' => 'node.php',
            'Pods' => 'pod.php'
        ),
        'Networking' => array(
            'VPCs' => 'vpc.php',
            'Subnets' => 'subnet.php',
            'Load Balancers' => 'loadbalancer.php',
            'External IPs' => 'ip.php'
        ),
        'Data' => array(
            'BigQuery' => 'bigquery.php',
            'CloudSQL' => 'cloudsql.php',
            'Google Cloud Storage' => 'bucket.php',
            'Memorystore Redis' => 'redis.php',
            'SSL Certificates' => 'sslcert.php'
        ),
        'Logging' => array(
            'GCP Logging Summary' => 'logsummary.php',
            'All Log Sinks' => 'logging.php',
            'Log Buckets' => 'logbucket.php'
        )
    );

    # Links to pages within the app
    $GLOBALS['linkable_columns'] = array(
        "projectName" => "project.php?projectName=",
        'instances' => 'project.php?projectName=__projectName__#ComputeInstances',
        "networkPath" => "vpc.php?networkPath=",
        "networkName" => "vpc.php?networkPath=__networkPath__",
        "subnetSelfLink" => "subnet.php?subnetSelfLink=",
        "networkSelfLink" => "vpc.php?networkSelfLink=",
        "vpcName" => "vpc.php?networkPath=__networkPath__",
        'subnetName' => 'subnet.php?subnetSelfLink=__subnetSelfLink__',
        'subnets' => 'project.php?projectName=__projectName__#ProjectSubnets',
        'vpcs' => 'project.php?projectName=__projectName__#ProjectVPCs',
        'sqlName' => 'cloudsql.php?sqlName=',
        'instanceName' => 'instance.php?instancePath=__instancePath__',
        'cloudSQLs' => 'cloudsql.php?projectName=__projectName__',
        'GCSBuckets' => 'bucket.php?parentId=__projectParentId__',
        'bucketName' => 'bucket.php?bucketName=',
        'resourceProjectName' => 'search.php?q=',
        'clusterName' => 'cluster.php?clusterPath=__clusterPath__',
        'clusters' => 'cluster.php?projectName=__projectName__',
        'externalIPs' => 'project.php?projectName=__projectName__#ExternalIPAddresses',
        'addressUser' => 'search.php?q=',
        'externalIP' => 'search.php?q=',
        'logSinkName' => 'logging.php?logSinkPath=__logSinkPath__',
        'bqDatasetName' => 'bigquery.php?bqDatasetPath=__bgDatasetPath__',
        'bqTableCount' => 'bigquery.php?bqDatasetPath=__bgDatasetPath__',
        'BigQueryTables' => 'bigquery.php?projectName=__projectName__',
        'urlMapName' => 'loadbalancer.php?urlMapPath=__urlMapPath__',
        'redisName' => 'redis.php?redisPath=__redisPath__',
        'redis' => 'redis.php?projectName=__projectName__',
        'projectPodCount' => 'pod.php?projectPods=true&clusterPath=__clusterPath__',
        'systemPodCount' => 'pod.php?systemPods=true&clusterPath=__clusterPath__#SystemPods',
        'podCount' => 'pod.php?clusterPath=__clusterPath__',
        'podName' => 'pod.php?podPath=__podPath__',
        'instanceServiceAccount' => 'iam.php?serviceAccount=',
        'account' => 'iam.php?account=',
        'role' => 'iam.php?role=',
        'iamResourcePath' => 'iam.php?resourcePath=',
        'logBucketPath' => 'logbucket.php?logBucketPath=',
        'table_name' => 'customquery.php?tablename='
    );
    
    $GLOBALS['hidden_columns'] = array(
        'subnetId',
        'instancePath',
        'projectParentId',
        'ancestors',
        'bucketParent',
        'logSinkPath',
        'bqDatasetPath',
        'bqTablePath',
        'nodepoolPath', 
        'nodepoolParent',
        'clusterPath',
        'urlMapPath',
        'redisPath',
        'nodePath',
        'podPath',
        'networkPath',
        'subnetPath',
        'podPath',
        'serviceId',
        'skuId'
    );
    
    # Links outside of the app that show up under the 'Pivot' button
    $GLOBALS['pivotable_columns'] = array(
        'projectName' => 'GCP Project', 
        'resourceProjectName' => 'GCP Project',
        'vpcName' => 'GCP VPC',
        'subnetName' => 'GCP Subnet',
        'projectName' => 'Terraform',
        'sqlName' => 'GCP CloudSQL',
        'instanceName' => 'GCP Instance',
        'instanceName' => 'FleetDM',
        'bucketPath' => 'GCP Bucket',
        'clusterName' => 'GCP Cluster',
        'nodepoolName' => 'GCP Node',
        'externalIP' =>  'Censys',
        'clusterEndpoint' =>  'Censys',
        'logBucketProject' => 'GCP Log Sink',
        'pubsubTopicName' => 'GCP PubSub Topic',
        'pubsubSubscriptionName' => 'GCP PubSub Subscription',
        'logSinkName' => 'GCP Log Sink',
        'bqDatasetName' => 'GCP BigQuery Dataset',
        'bqTableName' => 'GCP BigQuery Table',
        'urlMapName' => 'GCP Load Balancer',
        'redisName' => 'GCP Redis',
        'podImage' => 'Docker Image',
        'serviceId' => 'GCP Resource Billing',
        'iamRole' => 'GCP IAM Role',
        'account' => 'GCP IAM Policy Analyzer - Account',
        'sslAltName' => 'Crt.sh Cert Lookup',
    );


    $GLOBALS['pretty_headers'] = array(
        'vpcName' => 'VPC Name',
        'ipaddr' => 'IP Addr',
        'servicename' => 'Service Name',
        'productname' => 'Product Name',
        'vpcCount' => 'VPC Count'
    );
