<?php

class datatable{

	var $headers;
	var $headers_inv;
	var $rows;
	var $rowcount;
	var $table_id;
	var $table_class;
	var $hidden_columns;
	var $linkable_columns;
	var $pivotable_columns;

	function __construct($rows, $id, $table_class = false, $hidden_columns = false, $linkable_columns = false, $pivotable_columns = false){
		$this->rowcount = mycount($rows);

		# Set table defaults
		$this->table_id = $id ? $id : "generic_datatable";
		$this->table_class = $table_class ? $table_class : "table table-striped table-bordered table-hover";

		if($this->rowcount > 0){
			foreach($rows as $row){
				if(!$this->headers){
					$this->headers = array_keys($row);
					$this->headers_inv = array_flip($this->headers);
				}
				$this->rows[] = array_values($row);
			}
		} else {
			return false;
		}

		if(is_array($pivotable_columns)){
			foreach($pivotable_columns as $header => $name){
				if(safe_in_array($header, $this->headers)){
					$this->pivotable_columns[$header] = $name;
				}
			}
		}

		if(is_array($hidden_columns)){
			foreach($this->headers as $index => $header){
				if(safe_in_array($header, $hidden_columns) || (preg_match("/Path$/", $header) && count($hidden_columns) > 0)){
					$this->hidden_columns[$index] = $header;
				} 
			}
		}

		if(is_array($linkable_columns)){
			foreach($this->headers as $index => $header){
				if(isset($linkable_columns[$header])){
					$this->linkable_columns[$header] = $linkable_columns[$header];
				}
			}
		} 
	} # end constructor

	function print_table($title = false, $subtitle = false, $print_vertical = false, $query_string = false, $highlight = false){

		$pretty_headers = array(
			'vpcName' => 'VPC',
			'projectName' => 'Project',
			'vpcCount' => 'VPCCount'
		);

		if($title){
			$anchor = preg_replace("/\s+/", "", $title);
			print "<a id='{$anchor}'></a>";
			print "<h3 class='pt-4'>{$title}\n";

			if($query_string){
				print "<button type='button' title='Show SQL Query' class='btn btn-link btn-lg text-muted px-1 mb-2' data-bs-toggle='collapse' data-bs-target='#query_detail_{$this->table_id}' aria-expanded='false' aria-controls='collapseExample'><i class='m-0 fa fa-terminal fa-2xl' aria-hidden='true'></i></button>";
			} 				
			print "</h3>";
		} 
		
		if($query_string){
			print "<div class='collapse pt-1 pb-3' id='query_detail_{$this->table_id}' >
			<div class='card card-body bg-dark' style='border-left: 5px solid #00A86B;'>
			  <code class='text-white'>{$query_string}</code>
			</div>
		  </div>";
		}

		if($subtitle){
			print "<p>$subtitle</p>";
		}

		if($this->rowcount == 0){
			print "<div class='alert alert-secondary'>There were no results</div>";
			return false;
		}

		# Don't show large query results by default.  Give the user the option to save as CSV or show in browser.
		if($this->rowcount > 5000 && @!$_GET['show_all']){

			if($_SERVER['QUERY_STRING']){
				$query_params = $_SERVER['QUERY_STRING']."&show_all=1";
			} else {
				$query_params = "show_all=1";
			}
			$show_all_url = $_SERVER['SCRIPT_NAME'].'?'.$query_params;

			# Needed when system is behind a load balancer since the full path isn't present to PHP
			if(preg_match("/^\/\w+\.php/", $show_all_url)){
				$show_all_url = filter("/clarity".$show_all_url);
			}

			print "<div class='alert alert-danger'>
				<h4 class='alert-heading'>Warning:</h4>
				<p>This query resulted in a large number of results (".number_format($this->rowcount)." rows). Viewing in your browser may result in unresponsive and unhappy browser.</p>
				<hr>
				<div>
				  <!--<a href='?saveCSV={$this->table_id}' class='btn btn-primary'>Save as CSV <i class='icon-list'></i></a>-->
				  <a href='$show_all_url' class='btn btn-danger'>View in Browser <i class='icon-time icon-white'></i></a> 
				</div>
			</div>";
			return true;
		}

		# Display in vertical format if there's only one result
		if($this->rowcount == 1 && $print_vertical){
			print "<table class='table t-5'>\n";

			# Create an associative array that can be referenced by header name
			$r = array();
			foreach($this->headers as $i => $header){
				$r[$header] = $this->rows[0][$i];
			}

			# Get external pivots for the row
			$pivot = $this->get_pivots($r);

			if($pivot){
				print "  <tr>\n";
				print "    <th class='col-sm-1 px-4 align-middle'>Pivot</th>\n";
				print "    <td>{$pivot}</td>\n";
				print "  </tr>\n";
			}

			foreach($this->headers as $index => $header){
				if(@safe_in_array($header, $this->hidden_columns)){
					continue;
				}

				if(@$pretty_headers[$header]){
					$pretty_header = $pretty_headers[$header];

				} else {
					$pretty_header = preg_replace("/\_/", ' ', $header);
				}

				print "  <tr><th class='col-sm-1 px-4'>$pretty_header</th>\n";

				$cell = $this->rows[0][$index];
				if(is_array($cell)){
					if(mycount($cell) == 0){
						$cell = "";
					} else if(mycount($cell) == 1){
						# If a single array value, change to a string for easier viewing
						$first_element = array_shift($cell);
						if(!is_array($first_element)){
							$cell = $first_element;
						} else {
							$cell = "<pre>".str_truncate(htmlentities(print_r($cell, true)))."</pre>";
						}
					} else if(mycount($cell) == 0){
						$cell = "null";
					} else {
						$cell = "<pre>".str_truncate(htmlentities(print_r($cell, true)))."</pre>";
					}
				}

				# Highlight text when requested, like with search results
				if($highlight){
					$cell = preg_replace("/($highlight)/", "<span class='bg-warning'>\$1</span>", $cell);
				}

				# Format currency
				if(stristr($header, 'cost')){
					if(preg_match("/^(\d{4,}(\.\d{1,2})?)/", $cell, $m)){
						$cell = '$'.number_format(floatval($m[1]), 2, ".", ",");
					} else if(!$cell){
						$cell = "$0.00";
					} else{
						$cell = '$'.$cell;
					}
				}

				# Add commas for easier reading of large numbers
				if(preg_match("/^\d{4,10}$/", $cell)){
					$cell = number_format($cell);
				}

				if(@$this->linkable_columns[$header]){
					if(preg_match("/^(.+)__(.+)__$/", $this->linkable_columns[$header], $matches)){
						$link = $matches[1];
						$link_header = $matches[2];
						$link_var = $this->rows[0][array_search($link_header, $this->headers)];
						$link_var .= "&name={$cell}";

					} else {
						$link = $this->linkable_columns[$header];
						$link_var = $cell;
					}
					print "        <td><a href='{$link}{$link_var}'>$cell</a></td>\n";
				} else {
					print "  <td>$cell &nbsp</th>\n";
				}
				print "</tr>\n";

			}
			print "</table>\n";
		} else {

		print "
			<script type='text/javascript' charset='utf-8' language='javascript'>

			\$(document).ready(function() {
				var table = \$('#{$this->table_id}').DataTable( {
					lengthChange: false,
					pageLength: 50,

					buttons: [             
						{ extend: 'copy', className: 'btn btn-light'},
						{ extend: 'csv', className: 'btn btn-light'},
						{ extend: 'colvis', className: 'btn btn-light', text: 'Columns'},
						{ extend: 'pageLength', className: 'btn btn-light'	},
					]
				} );
			
				table.buttons().container()
					.appendTo( '#{$this->table_id}_wrapper .col-md-6:eq(0)' );
			} );
			</script>";

		print "<table class='{$this->table_class}' id='{$this->table_id}'>\n";

		# Print table headers
		$column_num = 0;
		if(mycount($this->headers) > 0){
			print "  <thead>\n";
			print "    <tr>\n";

			if(isset($this->pivotable_columns)){
				print "<th>Pivot</th>\n";
			}
		
			foreach($this->headers as $header){
				$column_num++;

				if(@safe_in_array($header, $this->hidden_columns)){
					continue;
				}

				if(@$pretty_headers[$header]){
					$pretty_header = $pretty_headers[$header];
				} else {
					$pretty_header = ucwords(preg_replace("/\_/", ' ', $header));
				}

				print "      <th>$pretty_header</th>\n";
				$column_num++;

			}
			print "    </tr>\n";
			print "  </thead>\n";
		}

		# Print rows
		if(mycount($this->rows) > 0){
			print "  <tbody>\n";
			foreach($this->rows as $row => $cells){
				print "    <tr>\n";
				
				if(isset($this->pivotable_columns)){
					# Create an associative array that can be referenced by header name
					$r = array();
					foreach($this->headers as $i => $header){
						$r[$header] = $cells[$i];
					}

					$pivot = $this->get_pivots($r);
					print "<td class='text-nowrap align-middle'>{$pivot}</td>";
				}

				foreach($cells as $index => $cell){
					$header = @$this->headers[$index];
				
					$tooltip = '';

					if(is_array($cell)){
					
						if(mycount($cell) == 0){
							$cell = "";
						} else if(mycount($cell) == 1){
							# If a single array value, change to a string for easier viewing
							$first_element = array_shift($cell);
							if(!is_array($first_element)){
								$cell = $first_element;
							} else if(mycount($cell) == 0){
								$cell = "null";
							} else {
								$cell = "<pre>".str_truncate(htmlentities(print_r($cell, true)))."</pre>";
							}
						} else {
							$cell = "<pre>".str_truncate(htmlentities(print_r($cell, true)))."</pre>";
						}
					}

					if($header == 'detailsJSON'){
						$cell = "<pre>".str_truncate($cell)."</pre>";
					}

					if($header == 'logSinkFilter'){
						$cell = "<code>$cell</code>";
					}

					if(isset($this->hidden_columns[$index])){
						continue;
					}

					# Highlight text when requested, like with search results
					if($highlight){
						$cell = preg_replace("/($highlight)/", "<span class='bg-warning'>\$1</span>", $cell);
					}

					# Format currency
					if(stristr($header, 'cost')){
						if(preg_match("/^(\d{4,}(\.\d{1,2})?)/", $cell, $m)){
							$cell = '$'.number_format(floatval($m[1]), 2, ".", ",");
						} else if(!$cell){
							$cell = "$0.00";
						} else {
							$cell = '$'.$cell;
						}
					}

					# Add commas for easier reading of large numbers
					if(preg_match("/^\d{4,10}$/", $cell)){
						$cell = number_format($cell);
					}


					if(@$this->linkable_columns[$header]){
						if(preg_match("/^(.+)__(.+)__(#.+)?$/", $this->linkable_columns[$header], $matches)){
							$link = $matches[1];
							$link_header = $matches[2];
							$anchor = @$matches[3];
							$link_var = $cells[array_search($link_header, $this->headers)];
							$link_var .= "&name={$cell}$anchor";

						} else {
							$link = $this->linkable_columns[$header];
							$link_var = $cell;
						}
						print "        <td class='align-middle text-nowrap'><a href='{$link}{$link_var}' $tooltip>$cell</a></td>\n";
					} else {
						print "        <td class='align-middle text-nowrap'>$cell</td>\n";
					}
				}	
				print "    </tr>\n";
			}
			print "  </tbody>\n";
		}

		print "</table>\n";
		}
	} # end method print()

	function get_pivots($r){
		if(!isset($this->pivotable_columns)){
			return false;
		}

		foreach($this->pivotable_columns as $header => $name){
			switch($header){
				case 'resourceProjectName':
				case 'projectName':
				$pivots['GCP Project Dashboard'] = "https://console.cloud.google.com/home/dashboard?project={$r['projectName']}";
				$pivots['GCP Log Explorer'] = "https://console.cloud.google.com/logs/query?project={$r['projectName']}";
				if(preg_match("/^(.+)\-\d{2,}$/", $r['projectName'], $matches)){
					$github_projectName = $matches[1];
				} else {
					$github_projectName = $r['projectName'];
				}
				if(constant('gcp_billing_account') && constant('gcp_org_id')){
					$pivots['GCP Project Billing'] = "https://console.cloud.google.com/billing/".constant('gcp_billing_account')."/reports;chartType=STACKED_BAR;timeRange=LAST_30_DAYS;grouping=GROUP_BY_SERVICE;projects={$r['projectName']};credits=SUSTAINED_USAGE_DISCOUNT,SPENDING_BASED_DISCOUNT,COMMITTED_USAGE_DISCOUNT,FREE_TIER,COMMITTED_USAGE_DISCOUNT_DOLLAR_BASE;negotiatedSavings=false?organizationId=".constant('gcp_org_id')."&pli=1";
				}
				break;

				case 'vpcName':
				$pivots['GCP VPC'] = "https://console.cloud.google.com/networking/networks/details/{$r['vpcName']}?project={$r['projectName']}&pageTab=SUBNETS";
				break;

				case 'subnetName':
				$pivots['GCP Subnet'] = "https://console.cloud.google.com/networking/subnetworks/details/{$r['subnetLocation']}/{$r['subnetName']}?project={$r['projectName']}";
				break;

				case 'sqlName':
				$pivots['GCP CloudSQL'] = "https://console.cloud.google.com/sql/instances/{$r['sqlName']}/overview?project={$r['projectName']}";
				$pivots['GCP CloudSQL Logs'] = "https://console.cloud.google.com/logs/query;query=resource.type%3D%22cloudsql_database%22%0Aresource.labels.database_id%3D%22{$r['projectName']}:{$r['sqlName']}%22?project={$r['projectName']}";
				break;

				case 'instanceName':
				$pivots['GCP Instance'] = "https://console.cloud.google.com/compute/instancesDetail/zones/{$r['instanceLocation']}/instances/{$r['instanceName']}?project={$r['projectName']}";
				break;

				case 'bucketPath':
				$pivots['GCP Bucket'] = "https://console.cloud.google.com/storage/browser/{$r['bucketName']};tab=objects?forceOnBucketsSortingFiltering=false&project={$r['projectName']}&prefix=&forceOnObjectsSortingFiltering=false";
				break;

				case 'clusterName':
				$pivots['GCP Cluster'] = "https://console.cloud.google.com/kubernetes/clusters/details/{$r['clusterLocation']}/{$r['clusterName']}/details?project={$r['projectName']}";
				break;

				case 'clusterEndpoint':
				$pivots['Censys'] = "https://search.censys.io/hosts/{$r['clusterEndpoint']}";
				break;

				case 'nodepoolName':
				$pivots['GCP Nodepool'] = "https://console.cloud.google.com/kubernetes/nodepool/{$r['nodepoolLocation']}/{$r['clusterName']}/{$r['nodepoolName']}?project={$r['projectName']}";
				break;

				case 'externalIP':
				$pivots['Censys'] = "https://search.censys.io/hosts/{$r['externalIP']}";
				break;

				case 'logBucketProject':
				$pivots['GCP Log Bucket'] = "https://console.cloud.google.com/logs/storage?project={$r['logBucketProject']}";
				break;

				case 'pubsubTopicName':
					if(preg_match("/^projects\/(.+)\/topics\/(.+)$/", $r['pubsubTopicName'], $matches)){
						$project = $matches[1];
						$topic = $matches[2];
						$pivots['GCP PubSub Topic'] = "https://console.cloud.google.com/cloudpubsub/topic/detail/{$topic}?project={$project}";
					}
				break;

				case 'pubsubSubscriptionName':
					if(preg_match("/^projects\/(.+)\/subscriptions\/(.+)$/", $r['pubsubSubscriptionName'], $matches)){
						$project = $matches[1];
						$subscription = $matches[2];
						$pivots['GCP PubSub Subscription'] = "https://console.cloud.google.com/cloudpubsub/subscription/detail/{$subscription}?project={$project}";
					}
				break;

				case 'logSinkName':
				$pivots['GCP Log Sink'] = "https://console.cloud.google.com/logs/router/sink/projects%2F{$r['logSinkProjectName']}%2Fsinks%2F{$r['logSinkName']}?project={$r['logSinkProjectName']}";
				break;

				case 'bqTableName':
				$pivots['GCP BigQuery Table'] = "https://console.cloud.google.com/bigquery?project={$r['projectName']}&ws=!1m5!1m4!4m3!1s{$r['projectName']}!2s{$r['bqDatasetName']}!3s{$r['bqTableName']}";
				break;

				case 'bqDatasetName':
				$pivots['GCP BigQuery Dataset'] = "https://console.cloud.google.com/bigquery?project={$r['projectName']}&ws=!1m4!1m3!3m2!1s{$r['projectName']}!2s{$r['bqDatasetName']}";
				break;

				case 'urlMapName':
				$pivots['GCP Load Balancer'] = "https://console.cloud.google.com/net-services/loadbalancing/details/http/{$r['urlMapName']}?project={$r['projectName']}";
				break;

				case 'redisName':
				$pivots['GCP Redis'] = "https://console.cloud.google.com/memorystore/redis/locations/{$r['redisLocation']}/instances/{$r['redisName']}/details/overview?project={$r['projectName']}";
				break;

				case 'podImage':
				if(preg_match("/us-docker.pkg.dev/", $r['podImage'])){
					$pivots['Docker Image'] = "https://{$r['podImage']}";
				}
				break;

				case 'serviceId':
				if(constant('gcp_billing_account') && constant('gcp_org_id')){
					$pivots['GCP Resource Billing'] = "https://console.cloud.google.com/billing/".constant('gcp_billing_account')."/reports;chartType=STACKED_BAR;timeRange=LAST_MONTH;grouping=GROUP_BY_SKU;projects={$r['projectName']};products=services%2F{$r['serviceId']};credits=SUSTAINED_USAGE_DISCOUNT,SPENDING_BASED_DISCOUNT,COMMITTED_USAGE_DISCOUNT,FREE_TIER,COMMITTED_USAGE_DISCOUNT_DOLLAR_BASE;negotiatedSavings=false?organizationId=".constant('gcp_org_id')."&pli=1";
				}
				break;

				case 'iamRole':
				if(constant('gcp_billing_account')){
					$role = urlencode($r['iamRole']);
					$pivots['GCP IAM Role'] = "https://console.cloud.google.com/iam-admin/roles/details/{$role}?organizationId=".constant('gcp_billing_account');
				}
				break;

				case 'account':
				if(constant('gcp_billing_account')){
					$pivots['GCP IAM Policy Analyzer'] = "https://console.cloud.google.com/iam-admin/analyzer/query;identity={$r['account']};scopeResource=".constant('gcp_billing_account').";scopeResourceType=2/report";
				}
				break;

				case 'sslAltName':
				$pivots['crt.sh SSL Cert Lookup'] = "https://crt.sh/?q={$r['sslAltName']}";
				break;

				default:
				$pivots["Unknown Pivot - $header"] = "";
				break;
			}
		}

		if(isset($pivots)){
			$pivot ="<a tabindex='0' class='btn btn-sm' role='button' data-html='true' data-bs-content-id='popover-content' data-toggle='popover' data-bs-trigger='focus' data-trigger='focus' data-bs-html='true' title='Pivot Destinations' data-bs-content=\"<ul class='list-group list-group-flush'>";
			foreach($pivots as $title => $href){
				$pivot .= "<li class='list-group-item'><i class='fa fa-external-link'></i> <a href='$href' target='_blank'>$title</a></li>";
			}

			$pivot .= "</ul>\"><button type='button' class='btn btn-outline-secondary btn-sm'>Pivot</button></a>";
			return $pivot;
		} else {
			return false;
		}
	} # End function get_pivots()

} # End class datatable

