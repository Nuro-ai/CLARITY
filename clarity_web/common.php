<?php

function testprint($var, $title = false){
	$web = $_SERVER['HTTP_USER_AGENT'] ? true : false;

	if($web){
		print "<div style='border: 1px dotted red; padding: 5px;'>";
		if($title){
			print "<b>Debug: $title</b><br>";
		}       
		print "<pre>";
	} else if($title){
		print "Debug: $title\n";
	}

	print_r($var);

	if($web){
		print "</pre>";
		print "</div>";
	} 
} # end function testprint


function set_default($var, $default, $include_zero = false){
	if($include_zero){
		return ($var || $var == '0') ? $var : $default; 
	}
	return $var ? $var : $default;
} # end function set_default


function print_html($string, $tabs = 0){
	print str_repeat("\t", $tabs).$string."\n";
}

function show_title($title, $subtitle = false){
        print "<div class='page-header'>\n";
        print "  <h1>$title";
	if($subtitle){
		print "<br><small>$subtitle</small>";
	}
        print "</h1>\n</div>\n";
}

function filter($input = false){
	if(preg_match("/^[a-zA-Z0-9@:\/\-_\.\\\]{0,256}$/", $input)){
		return $input;
	} else {
		print "Invalid input<br>";
		exit;
	}
}

function update_title($pretty_name){
	if($pretty_name){
		print "<script type='text/javascript'>document.title='CLARITY - {$pretty_name}'</script>";
	}
}

function print_title($title, $subtitle = false){

	$title = substr(htmlentities($title),0, 128);
	$subtitle = substr(htmlentities($subtitle),0, 128);

    print "<div class='bg-light p-3 rounded' style='border-left: 5px solid #00A86B;'><h2>\n";
    if($title){
		print "{$title}";
    } 
    if($subtitle){
        print " <font class='text-muted'>{$subtitle}</font>\n";
    }
    print "</h2></div>\n";
}

function str_truncate($str, $max_length = 10000){
	if(strlen($str) > $max_length){
		return substr($str, 0, $max_length)." TRUNCATED ";
	} else {
		return $str;
	}
}

function mycount($var = false){
        if(isset($var) && is_countable($var)){
                return count($var);
        }
        return 0;
}

function safe_in_array($needle, $haystack){
	if(is_array($haystack)){
		return in_array($needle, $haystack);
	}
	return false;
}
