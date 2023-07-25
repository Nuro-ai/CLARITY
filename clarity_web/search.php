<?php

    require('header.php');

    $query = trim($_GET['q']);

    if(isset($query)){
        update_title('Search Results');
        print_title('Search Results:', $query);

        $bigquery_query = new bigquery_query($bigquery_client, 'search by query', array('query' => $query, 'query' => $query));
        
        $search_result_count = 0;
        if(isset($bigquery_query->results)){
            foreach($bigquery_query->results as $row_num => $row){
                if(isset($row['resource']['data'])){
                    $json_object = json_decode($row['resource']['data'], false);
                    $data = objectToArray($json_object);

                    $resource_type = $row['asset_type'];
    
                    $search_results[$resource_type][] = $data;
                    $search_result_count++;
                }
            }
            #testprint($search_results);

            if($search_result_count > 0){
                # Need to normalize columns where they are missing in the data set to have complete tables
                $category_headers = array();
                foreach($search_results as $category => $category_data){
                    foreach($category_data as $row){
                        foreach(array_keys($row) as $header){
                            $category_headers[$category][$header] = 1;
                        }
                    }
                }
                
                foreach($search_results as $category => $category_data){
                    foreach($category_data as $result_num => $row){
                        $normalized_result = array();
                        foreach(array_keys($category_headers[$category]) as $header){
                            if(!isset($search_results[$category][$result_num][$header])){
                                $normalized_result[$header] = 'null';
                            } else {
                                # Query match highlighting
                                $normalized_result[$header] = $search_results[$category][$result_num][$header];
                            }   
                        }

                        $search_results[$category][$result_num] = $normalized_result;
                    }
                }

                print "<ul class='list-group w-50 py-2'>\n";
                print "<li class='list-group-item d-flex justify-content-between align-items-start list-group-item-success'><div class='fw-bold'>Result Summary</div><div>{$search_result_count} results found</font></li>\n";
                foreach($search_results as $category => $category_data){
                    $result_count = count($category_data);
                    $result = $result_count > 1 ? 'results' : 'result';
                    print "<a href='#{$category}' class='link-dark text-decoration-none'><li class='list-group-item d-flex justify-content-between align-items-start list-group-item-light list-group-item-action'>
                    <span class='badge bg-primary' style='width: 100px;'>{$result_count} {$result}</span>
                            <div class='ms-2 me-auto'><div class=''>{$category}</div></div></li></a>\n";
                }
                print "</ul>";

                foreach($search_results as $resource_type => $rows){
                    $datatable = new datatable($rows, "datatable-".@++$i, false, $hidden_columns, $linkable_columns, $pivotable_columns);
                    $datatable->print_table("$resource_type", false, true, $bigquery_query->parsed_query_string, $query);
                }
            }


        } else {
            print "<div class='alert alert-secondary'>There were no results</div>";
        }
    } else {
?>
<div class="jumbotron">
  <h1 class="display-4">Search</h1>
  <p class="lead">Search across the entire set of GCP resource metadata.<br>
  <small class'text-muted'>Examples: IP addresses, instance names, labels, settings.</small></p>

    <div class="input-group form-inline">
        <div class="form-outline col-xs-4">

    <form method='get' action='search.php' class='form-group form-inline'>
        <input name='q' type="search" class="form-control p-3" placeholder="Search" length='30' aria-label="Search" aria-describedby="search-addon" />
    </div>
        <button type="button" class="p-3 btn btn-primary"><i class="fa fa-search"></i></button>
    </form>
    </div>
 </div>
    
<?php
    }

    require('footer.php'); 

    function objectToArray($object){
        if(!is_object($object) && !is_array($object)) {
            return $object;
        }
        return array_map('objectToArray', (array) $object);
    }
?>
