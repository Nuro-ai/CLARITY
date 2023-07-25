<?php 

  require('config.php'); 
  require('common.php');

?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CLARITY</title>

    <script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.3/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.3/js/dataTables.bootstrap5.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.3.5/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.3.5/js/buttons.bootstrap5.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.3.5/js/buttons.html5.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.3.5/js/buttons.print.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.3.5/js/buttons.colVis.min.js"></script>

    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.2.0/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.3/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.3.5/css/buttons.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <script type="text/javascript">
      $(function () {
        $('[data-toggle="popover"]').popover()
      });
    </script>
  </head>
  
  <?php
          $current_page_ar = preg_split("/\//", filter($_SERVER['PHP_SELF']));
          $current_page = array_pop($current_page_ar);
          if(is_array($current_page_ar)){
              $site_root = implode('/', $current_page_ar).'/';
          } else {
               $site_root = "/";
          }
  ?>

  <body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark px-3">
      <a href="<?php print $site_root ?>"></a>
      <a class="navbar-brand" href="<?php print $site_root ?>" title="Cloud Asset Repository and Inventory Tool for You" >CLARITY</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
  <?php
          foreach($pages as $page_title => $page_file){
            # Don't show certain page links (index, search, etc)
            if($page_title == 'hidden'){
              continue;
            }  

            print "<li class='nav-item'>";
            if($page_file == $current_page){
              print "<a class='nav-link active' aria-current='page' href='{$page_file}'>{$page_title}</a>";
            }else if(is_array($page_file)){
              print "<li class='nav-item dropdown'>";
              $active_class = safe_in_array($current_page, array_values($page_file)) ? 'active' : '';

              print "<a class='nav-link dropdown-toggle {$active_class}' href='#' id='navbarDropdown' role='button' data-bs-toggle='dropdown' aria-expanded='false'>$page_title</a>";
              print "<ul class='dropdown-menu dropdown-menu-dark' aria-labelledby='navbarDropdown'>\n";

              foreach($page_file as $subpage_title => $subpage_file){
                $active_class = ($current_page == $subpage_file) ? 'link-light' : '';

                print "<li><a class='nav-link dropdown-item {$active_class}' href='{$subpage_file}'>{$subpage_title}</a></li>\n";
              }
              print "</ul>\n";
              print "</li>\n";
            } else {
                print "<a class='nav-link' href='{$page_file}'>{$page_title}</a>";
            }
            print "</li>";
          }

          $query = @$_GET['q'];
?>
        </ul>
        <form class="d-flex" method='GET' action='search.php'>
            <input class="form-control me-2" type="search" placeholder="Regex" name='q' aria-label="Search" value='<?php print htmlentities($query)?>'>
            <button class="btn btn-outline-success" type="submit">Search</button>
        </form>
      </div>
    </nav>
    <div class="container-fluid p-5">

      <?php
        require('datatable.class.php');
        require('bigquery.class.php');

        # Set up BigQuery Client
        require 'vendor/autoload.php';
        use Google\Cloud\BigQuery\BigQueryClient;

        $bigquery_client = new BigQueryClient();
        $dataset = $bigquery_client->dataset('gcp_enrichments');
        $table = $dataset->table('resource');

      ?>
