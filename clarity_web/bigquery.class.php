<?php

class bigquery_query{
    var $query_string;
    var $parsed_query_string;
    var $query_name;
    var $query_params;
    var $freeform_query;
    var $results;
    var $errors;

    function __construct($bigquery_client, $query_name, $query_params = false, $freeform_query = false){
        $this->query_name = $query_name;
        $this->query_params = $query_params;
        $this->query_string = $this->get_query();

        if($freeform_query){
            $this->query_string = $freeform_query;
        }

        if(!$this->query_string){
            $this->errors[] = "No query string for query name: '{$this->query_name}'";
        }

        if(!$this->errors){
            $queryJobConfig = $bigquery_client->query($this->query_string);
            $this->parsed_query_string = $this->query_string;

            if($this->query_params){
               $queryJobConfig->parameters($this->query_params);

               foreach($this->query_params as $param => $value){
                    $this->parsed_query_string = preg_replace("/@$param/", "\"$value\"", $this->parsed_query_string);
               }
            }

            $cached_results = false;
            
            if(constant('redis_server') && preg_match("/^\d{1,5}$/", constant('redis_port'))){
                $query_key = hash('sha256', $this->parsed_query_string);
                $redis = new Redis(); 
                try{
                    $redis->connect(constant('redis_server'), constant('redis_port'), 2);

                    # "Unadvertised" function to flush redis cache just in case it's needed
                    if(isset($_GET['flushredis'])){
                        $redis->flushAll();
                        print "<div class='alert alert-warning my-3' role='alert'>Redis cache flushed</div>";
                    } 

                    $cached_results = $redis->get($query_key);
                } catch(Exception $e){
                    print "<div class='alert alert-warning my-3' role='alert'><strong>Warning:</strong> Could not connect to Redis cache server</div>";
                }
            }
            

            if(is_string($cached_results)){
                #print "Using cache<br>";
                $raw_results = unserialize($cached_results);
                $this->results = $raw_results;
            } else {
                if(!(constant('bigquery_project') && constant('bigquery_dataset'))){
                    print "<div class='card mt-3'>
                                <h5 class='card-header bg-warning'>Error - BigQuery project/dataset not defined</h5>
                                <div class='card-body'>
                                    Modify the <code>bigquery_project</code> and <code>bigquery_dataset</code> constant varibles in <code>config.php</code> to match the location of your Cloud Asset Inventory BigQuery dataset.
                                    <br><br>
                                    Example <code>config.php</code>:
                                    <br>
                                    <code>
    # BigQuery project and dataset for CAI data<Br>
    define('bigquery_project', 'my-inventory-project');<br>
    define('bigquery_dataset', 'my-cai-bigquery-export-dataset');<br>
                                    </code>
                                </div>
                            </div>\n";
                            exit;
                    return false;
                }

                try{
                    $raw_results = $bigquery_client->runQuery($queryJobConfig);
                } catch(Exception $e){
                    print "<div class='card'>
                                <h5 class='card-header bg-warning'>Query Error - '{$this->query_name}'</h5>
                                <div class='card-body''>
                                    <p><pre>{$e->getMessage()}\n\n{$this->parsed_query_string}</pre></p>
                                </div>
                            </div>\n";
                    return false;
                }

                if(isset($raw_results)){
                    $this->parse_results($raw_results);
                    try{
                        # Add to cache, set to expire in 1 hour
                        #print "Setting cache<br>";
                        if(constant('redis_server') && preg_match("/^\d{1,5}$/", constant('redis_port'))){
                            $redis->set($query_key, serialize($this->results), ['nx', 'ex'=>3600]);
                        } 
                    } catch(Exception $e){     
                        print "<div class='alert alert-warning my-3' role='alert'><strong>Warning:</strong> Could not set cache in Redis</div>";
                    }
                }
  
            }
        }

        if($this->errors){
            print "<h2>Errors:</h2><ul>";
            foreach($this->errors as $error){
                print "<li>$error</li>";
            }
            print "</ul>";
        }

        return $this;
    } # end constructor

    function parse_results($raw_results){
        foreach ($raw_results as $row) {
            $this->results[] = $row;
        }
    } # end method parse_results

	function get_query(){
        $query_string = false;
        $project = constant('bigquery_project');
        $dataset = constant('bigquery_dataset');

		switch($this->query_name){
			case 'get sample data':
			$query_string = "SELECT * FROM `{$project}.{$dataset}.instance_view` LIMIT 10";
			break;

            case 'get all projects':
            $query_string = "SELECT * FROM `{$project}.{$dataset}.snapshot_project_summary`
            ORDER BY projectName";
            break;

            case 'get project logging summaries':
            $query_string = "SELECT * FROM `{$project}.{$dataset}.snapshot_project_logging_summary` ORDER BY projectName";
            break;

            case 'get project by name':
            $query_string = "SELECT * FROM {$project}.{$dataset}.snapshot_project WHERE projectName = @projectName";
            break;

            case 'get vpcs by project name':
            $query_string = "SELECT projectName, networkName, networkDescription, networkPath, COUNT(DISTINCT subnetSelfLink) AS subnetCount, COUNT(subnetVPCFlowLogEnable) AS SubnetsWithVPCFlowLogs, COUNT(firewallDefaultDenyLogging) AS SubnetsWithFirewallLogging, MAX(vpcInstanceCount) as instanceCount
            FROM `{$project}.{$dataset}.snapshot_network` 
            WHERE projectName = @projectName
            GROUP by projectName, networkName, networkPath, networkDescription";
            break;

            case 'get all vpcs':
            $query_string = "SELECT projectName, networkName, networkDescription, networkPath, COUNT(DISTINCT subnetSelfLink) AS subnetCount, COUNT(subnetVPCFlowLogEnable) AS SubnetsWithVPCFlowLogs, COUNT(firewallDefaultDenyLogging) AS SubnetsWithFirewallLogging, MAX(vpcInstanceCount) as instanceCount
            FROM `{$project}.{$dataset}.snapshot_network` 
            GROUP by projectName, networkName, networkPath, networkDescription";
            break;

            case 'get subnets by networkPath':
            $query_string = "SELECT projectName, networkPath, SPLIT(networkPath, '/')[OFFSET(7)] as networkName, networkDescription, subnetName, subnetIPCidrRange, subnetLocation, subnetVPCFlowLogEnable, subnetSelfLink, firewallDefaultDenyLogging, subnetId, subnetInstanceCount as instanceCount
            FROM `{$project}.{$dataset}.snapshot_network` 
            WHERE networkPath = @networkPath";
            break;

            case 'get subnets by project name':
            $query_string = "SELECT projectName, networkPath, SPLIT(networkPath, '/')[OFFSET(7)] as networkName, networkDescription, subnetName, subnetIPCidrRange, subnetLocation, subnetVPCFlowLogEnable, subnetSelfLink, firewallDefaultDenyLogging, subnetId, subnetInstanceCount as instanceCount
            FROM `{$project}.{$dataset}.snapshot_network` 
            WHERE projectName = @projectName";
            break;

            case 'get subnets by networkSelfLink':
            $query_string = "SELECT projectName, networkPath, SPLIT(networkPath, '/')[OFFSET(7)] as networkName, networkDescription, subnetName, subnetIPCidrRange, subnetLocation, subnetVPCFlowLogEnable, firewallDefaultDenyLogging, subnetId, subnetInstanceCount as instanceCount
            FROM `{$project}.{$dataset}.snapshot_network` 
            WHERE networkSelfLink = @networkSelfLink";
            break;

            case 'get subnet by ID':
            $query_string = "SELECT projectName, networkPath, SPLIT(networkPath, '/')[OFFSET(7)] as networkName, networkDescription, subnetName, subnetIPCidrRange, subnetLocation, subnetVPCFlowLogEnable, firewallDefaultDenyLogging, subnetId, subnetInstanceCount as instanceCount
            FROM `{$project}.{$dataset}.snapshot_network` 
            WHERE subnetId = @subnetId";
            break;

            case 'get subnet by subnetSelfLink':
            $query_string = "SELECT projectName, networkPath, SPLIT(networkPath, '/')[OFFSET(7)] as networkName, networkDescription, subnetName, subnetIPCidrRange, subnetLocation, subnetVPCFlowLogEnable, firewallDefaultDenyLogging, subnetId, subnetInstanceCount as instanceCount
            FROM `{$project}.{$dataset}.snapshot_network` 
            WHERE subnetSelfLink = @subnetSelfLink";
            break;

            case 'get all subnets':
            $query_string = "SELECT projectName, networkPath, SPLIT(networkPath, '/')[OFFSET(7)] as networkName, networkDescription, subnetName, subnetIPCidrRange, subnetLocation, subnetVPCFlowLogEnable, firewallDefaultDenyLogging, subnetId, subnetInstanceCount as instanceCount
            FROM `{$project}.{$dataset}.snapshot_network`";
            break;

            case 'get cloudsql by name':
            $query_string = "SELECT * 
            FROM `{$project}.{$dataset}.snapshot_cloudsql` 
            WHERE sqlName = @cloudSQLName";
            break;

            case 'get cloudsql by project':
            $query_string = "SELECT * 
            FROM `{$project}.{$dataset}.snapshot_cloudsql` 
            WHERE projectName = @projectName";
            break;

            case 'get all cloudsql':
            $query_string = "SELECT *
            FROM `{$project}.{$dataset}.snapshot_cloudsql`";
            break;

            case 'get bucket by name':
            $query_string = "SELECT * 
            FROM `{$project}.{$dataset}.snapshot_bucket` 
            WHERE bucketName = @bucketName";
            break;

            case 'get bucket by path':
            $query_string = "SELECT * 
            FROM `{$project}.{$dataset}.snapshot_bucket` 
            WHERE bucketPath = @bucketPath";
            break;

            case 'get buckets by parentId':
            $query_string = "SELECT * 
            FROM `{$project}.{$dataset}.snapshot_bucket` 
            WHERE bucketParent = @parentId";
            break;

            case 'get buckets by parentId':
            $query_string = "SELECT * 
            FROM `{$project}.{$dataset}.snapshot_bucket` 
            WHERE bucketParent = @parentId";
            break;

            case 'get buckets by projectName':
            $query_string = "SELECT * 
            FROM `{$project}.{$dataset}.snapshot_bucket` 
            WHERE projectName = @projectName";
            break;
    
            case 'get all buckets':
            $query_string = "SELECT *
            FROM `{$project}.{$dataset}.snapshot_bucket`";
            break;

            case 'get instance by path':
            $query_string = "SELECT * 
            FROM `{$project}.{$dataset}.snapshot_instance` 
            WHERE instancePath = @instancePath";
            break;

            case 'get instances by project':
            $query_string = "SELECT * 
            FROM `{$project}.{$dataset}.snapshot_instance` instance
#            JOIN `{$project}.{$dataset}.snapshot_project` project ON instance.projectName = project.projectName
            WHERE instance.projectName = @instanceProject";
            break;

            case 'get instances by subnetSelfLink':
            $query_string = "SELECT * 
            FROM `{$project}.{$dataset}.snapshot_instance` instance
            WHERE instance.subnetSelfLink = @subnetSelfLink";
            break;

            case 'get instances by networkSelfLink':
            $query_string = "SELECT * 
            FROM `{$project}.{$dataset}.snapshot_instance` instance
            WHERE instance.networkSelfLink = @networkSelfLink";
            break;

            case 'get instances by subnetSelfLink':
            $query_string = "SELECT * 
            FROM `{$project}.{$dataset}.snapshot_instance` 
            WHERE subnetSelfLink = @subnetSelfLink";
            break;

            case 'get all instances':
            $query_string = "SELECT *
            FROM `{$project}.{$dataset}.snapshot_instance`";
            break;

            case 'search by query':
            $query_string = "SELECT name, asset_type, resource 
            FROM `{$project}.{$dataset}.resource` 
            WHERE DATE(readTime) = DATE_SUB(CURRENT_DATE(), INTERVAL 1 DAY)
            AND (CONTAINS_SUBSTR(name, @query) OR CONTAINS_SUBSTR(resource.data, @query))
            LIMIT 1000";
            break;

            case 'get resource summary':
            $query_string = "SELECT projectName, assetType, resourceCount
            FROM `{$project}.{$dataset}.snapshot_resource`
            ORDER BY resourceCount DESC";
            break;

            case 'get all clusters':
            $query_string = "SELECT *
            FROM `{$project}.{$dataset}.snapshot_cluster`";
            break;

            case 'get cluster by path':
            $query_string = "SELECT *
            FROM `{$project}.{$dataset}.snapshot_cluster`
            WHERE clusterPath = @clusterPath";
            break;

            case 'get clusters by project':
            $query_string = "SELECT *
            FROM `{$project}.{$dataset}.snapshot_cluster`
            WHERE projectName = @projectName";
            break;

            case 'get all nodepools':
            $query_string = "SELECT *
            FROM `{$project}.{$dataset}.snapshot_nodepool`";
            break;

            case 'get nodepools by cluster path':
            $query_string = "SELECT *
            FROM `{$project}.{$dataset}.snapshot_nodepool`
            WHERE clusterPath = @clusterPath";
            break;

            case 'get nodepools by project':
            $query_string = "SELECT *
            FROM `{$project}.{$dataset}.snapshot_nodepool`
            WHERE projectName = @projectName";
            break;

            case 'get pod summary by cluster':
            $query_string = "SELECT cluster_name as clusterName, clusterPath, project_name AS projectName, clusterLocation, COUNTIF(namespace != 'kube-system') as projectPodCount, COUNTIF(namespace = 'kube-system') as systemPodCount
            FROM `{$project}.{$dataset}.snapshot_k8s_pod`
            GROUP BY cluster_name, clusterLocation, clusterPath, project_name
            ORDER BY projectPodCount DESC";
            break;

            case 'get pods by cluster path':
            $query_string = "SELECT pod_name as podName, cluster_name as clusterName, node_name as nodeName, pod_image as podImage, project_name as projectName, pod_path as podPath,  * 
            EXCEPT(project_name, pod_name, cluster_name, pod_image, node_name, pod_path),
            FROM `{$project}.{$dataset}.snapshot_k8s_pod`
            WHERE clusterPath = @clusterPath
            AND namespace != \"kube-system\"";
            break;

            case 'get project pods by cluster path':
            $query_string = "SELECT pod_name as podName, cluster_name as clusterName, node_name as nodeName, pod_image as podImage, project_name as projectName, pod_path as podPath,  * 
            EXCEPT(project_name, pod_name, cluster_name, pod_image, node_name, pod_path),
            FROM `{$project}.{$dataset}.snapshot_k8s_pod`
            WHERE clusterPath = @clusterPath 
            AND namespace != \"kube-system\"";
            break;
    
            case 'get system pods by cluster path':
            $query_string = "SELECT pod_name as podName, cluster_name as clusterName, node_name as nodeName, pod_image as podImage, project_name as projectName, pod_path as podPath,  * 
            EXCEPT(project_name, pod_name, cluster_name, pod_image, node_name, pod_path),
            FROM `{$project}.{$dataset}.snapshot_k8s_pod`
            WHERE clusterPath = @clusterPath 
            AND namespace = \"kube-system\"";
            break;

            case 'get pod by path':
            $query_string = "SELECT pod_name as podName, cluster_name as clusterName, node_name as nodeName, pod_image as podImage, project_name as projectName, pod_path as podPath,  * 
            EXCEPT(project_name, pod_name, cluster_name, pod_image, node_name, pod_path),
            FROM `{$project}.{$dataset}.snapshot_k8s_pod`
            WHERE pod_path = @podPath";
            break;

            case 'get pod by by project':
            $query_string = "SELECT *
            FROM `{$project}.{$dataset}.snapshot_k8s_pod`
            WHERE project_name = @projectName";
            break;

            case 'get external ips by project':
            $query_string = "SELECT *  
            FROM `{$project}.{$dataset}.snapshot_external_ip` 
            WHERE projectName = @projectName";
            break;

            case 'get all log sinks':
            $query_string = "SELECT * FROM {$project}.{$dataset}.snapshot_logsink";
            break;
 
            case 'get log sink by path':
            $query_string = "SELECT * 
            FROM {$project}.{$dataset}.snapshot_logsink
            WHERE snapshot_logsink.logSinkPath = @logSinkPath";
            break;
                
            case 'get exclusion filters by log sink path':
            $query_string = "SELECT logSinkExclusions.name, logSinkExclusions.description, logSinkExclusions.filter, logSinkExclusions.disabled
            FROM {$project}.{$dataset}.resource_logging_googleapis_com_LogSink logSink,
            UNNEST(logSink.resource.data.exclusions) AS logSinkExclusions
            WHERE logSink.name = @logSinkPath
            GROUP BY logSinkExclusions.name, logSinkExclusions.description, logSinkExclusions.filter, logSinkExclusions.disabled";
            break;

            case 'get all log sinks to pubsub':
            $query_string = "SELECT logSinkName, logSinkPath, logSinkDestination, logSinkFilter, logSinkWriterIdentity, logSinkProjectName, pubsubTopicName, pubsubSubscriptionName, pubsubSubscriptionPushEndpoint FROM {$project}.{$dataset}.snapshot_logsink_pubsubs
            WHERE pubsubTopicName IS NOT NULL";
            break;

            case 'get all log sinks to log buckets':
            $query_string = "SELECT logSinkName, logSinkPath, logSinkDestination, logSinkFilter, logSinkWriterIdentity, logSinkProjectName, logBucketLink, logBucketDescription, logBucketRetentionDays, logBucketProject FROM {$project}.{$dataset}.snapshot_logsink_logbuckets
            WHERE logBucketLink IS NOT NULL";            
            break;

            case 'get all bigquery datasets':
            $query_string = "SELECT * FROM {$project}.{$dataset}.snapshot_bqdataset bqdataset";
            break;

            case 'get bigquery by dataset path':
            $query_string = "SELECT * FROM {$project}.{$dataset}.snapshot_bqdataset bqdataset
            WHERE bqDatasetPath = @bqDatasetPath";
            break;

            case 'get bigquery tables by dataset path':
            $query_string = "SELECT * FROM {$project}.{$dataset}.snapshot_bqtable 
            WHERE bqDatasetPath = @bqDatasetPath";
            break;

            case 'get bigquery datasets by project name':
            $query_string = "SELECT * FROM {$project}.{$dataset}.snapshot_bqdataset 
            WHERE projectName = @projectName";
            break;

            case 'get tables from schema':
            $query_string = "SELECT table_name FROM {$project}.{$dataset}.INFORMATION_SCHEMA.TABLES";
            break;

            case 'get sample data by tablename':
            $query_string = "SELECT * FROM @tableName LIMIT 10";
            break;

            case 'get all loadbalancers':
            $query_string = "SELECT *
            FROM `{$project}.{$dataset}.snapshot_loadbalancer`";
            break;

            case 'get url map by path':
            $query_string = "SELECT fwdRuleIP, urlMapName, projectName, urlMapDescription, urlMapHost, 
            FROM `{$project}.{$dataset}.snapshot_loadbalancer`
            CROSS JOIN UNNEST(urlMapHost) as urlMapHost
            WHERE urlMapPath = @urlMapPath
            GROUP BY urlMapHost, fwdRuleIP, urlMapName, projectName, urlMapDescription";
            break;

            case 'get forwarding rules by url map path':
            $query_string = "SELECT urlMapHost, urlRulePath, backendServiceName, backendServicePath
            FROM `{$project}.{$dataset}.snapshot_loadbalancer`
            WHERE urlMapPath = @urlMapPath";
            break;

            case 'get backend services by url map path':
            $query_string = "SELECT backendServiceName, backendServicePath, backendServicePort, backendLogging, instanceGroupName, instanceGroupPath, instanceGroupDescription
            FROM `{$project}.{$dataset}.snapshot_loadbalancer`
            WHERE urlMapPath = @urlMapPath";
            break;

            case 'get instance groups by url map path':
            $query_string = "SELECT instanceGroupName, instanceGroupPath, instanceGroupDescription, instanceGroupNetwork, instanceGroupSubnet
            FROM `{$project}.{$dataset}.snapshot_loadbalancer`
            WHERE urlMapPath = @urlMapPath";
            break;

            case 'get all redis':
            $query_string = "SELECT * FROM `{$project}.{$dataset}.snapshot_redis`";
            break;

            case 'get redis by path':
            $query_string = "SELECT * FROM `{$project}.{$dataset}.snapshot_redis`
            WHERE redisPath = @redisPath";
            break;

            case 'get redis by project':
            $query_string = "SELECT * FROM `{$project}.{$dataset}.snapshot_redis`
            WHERE projectName = @projectName";
            break;

            case 'get nodes by cluster path':
            $query_string = "SELECT * FROM `{$project}.{$dataset}.snapshot_node`
            WHERE clusterPath = @clusterPath";
            break;

            case 'get nodes by project':
            $query_string = "SELECT * FROM `{$project}.{$dataset}.snapshot_node`
            WHERE projectName = @projectName";
            break;

            case 'get pods by cluster path':
            $query_string = "SELECT * FROM `{$project}.{$dataset}.snapshot_pod`
            WHERE clusterPath = @clusterPath";
            break;

            case 'get pods by node path':
            $query_string = "SELECT * FROM `{$project}.{$dataset}.snapshot_pod`
            WHERE nodePath = @nodePath";
            break;

            case 'get pods by project':
            $query_string = "SELECT * FROM `{$project}.{$dataset}.snapshot_pod`
            WHERE projectName = @projectName";
            break;

            case 'get iam summary':
            $query_string = "SELECT account, count(*) AS policy_count FROM `{$project}.{$dataset}.snapshot_iam_policy` GROUP BY account ORDER BY policy_count DESC LIMIT 5000";
            break;

            case 'get iam by service account':
            $query_string = "SELECT * FROM `{$project}.{$dataset}.snapshot_iam_policy` WHERE CONTAINS_SUBSTR(account, @serviceAccount)";
            break;

            case 'get iam by account':
            $query_string = "SELECT * FROM `{$project}.{$dataset}.snapshot_iam_policy` WHERE account = @account";
            break;

            case 'get iam by resource path':
            $query_string = "SELECT * FROM `{$project}.{$dataset}.snapshot_iam_policy` WHERE iamResourcePath = @resourcePath ORDER BY account";
            break;

            case 'get all log buckets':
            $query_string = "SELECT logBucketLink as logBucketPath, logBucketDescription as description, logBucketRetentionDays as retentionDays, logBucketProject as project FROM `{$project}.{$dataset}.snapshot_logbucket`";
            break;

            case 'get log bucket by path':
            $query_string = "SELECT logBucketLink as logBucketPath, logBucketDescription as description, logBucketRetentionDays as retentionDays, logBucketProject as project FROM `{$project}.{$dataset}.snapshot_logbucket` WHERE logBucketLink = @logBucketPath";
            break;

            case 'get all ssl certs':
            $query_string = "SELECT * FROM `{$project}.{$dataset}.snapshot_ssl_certs`";
            break;

            case 'get all external ips':
            $query_string = "SELECT * FROM `{$project}.{$dataset}.snapshot_external_ip`";
            break;

            case 'freeform query':
            $query_string = "";
            break;

		}
		return $query_string;
	} # end method get_query

} # end bigquery_query class

?>
