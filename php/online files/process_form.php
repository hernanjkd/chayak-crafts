<?php

$report_error = array();


$img_name = $_REQUEST['previous_img_name'];



// Check for the image
if ($_FILES['img']['type'] == '' && $img_name == '')
    array_push($report_error, '&nbsp;&nbsp;&nbsp; - imagen');


// Check for the price
if ($_REQUEST['price'] == '')
    array_push($report_error, '&nbsp;&nbsp;&nbsp; - precio');


// Check for the section
if ($_REQUEST['list'] == '')
    array_push($report_error, '&nbsp;&nbsp;&nbsp; - seccion');


/*
 * Display errors if there're any
 */
if (count($report_error) > 0) {

    $t = '<font color="red" size="30px">ERROR</font><br />La foto, el precio y la seccion deben estar en todos los productos. En este caso falto:<br />';

    foreach($report_error as $e)
	$t .=  $e . '<br />';

    echo $t;
}

/*
 * If no errors, save all the info
 */
else {

    include('../cgi-bin/mysql_connect.php');




    $options = explode(',' , $_REQUEST['list']);

    $table = array_shift($options);



    /*
     * Deal with the image
     */
    if ($_FILES['img']['type'] != '') {

    	//delete the img from tmp if there's another to upload
    	if ($img_name != '') {
	    unlink('../images/tmp/' . $img_name);
	    unlink('../images/tmp/thumbs/'. $img_name);
	}


    	list( , $ext) = explode('/' , $_FILES['img']['type']);

     	$img_name = date('ymdHis') . '.' . $ext;


    	$target_path = '/home/users/web/b695/moo.jakchamalku/images/products/' . $img_name;

    	move_uploaded_file($_FILES['img']['tmp_name'] , $target_path);

	if ($ext == 'jpg' || $ext == 'jpeg')
	    $oimg = imagecreatefromjpeg('../images/products/' . $img_name);
	if ($ext == 'png')
	    $oimg = imagecreatefrompng('../images/products/' . $img_name);
	if ($ext == 'gif')
	    $oimg = imagecreatefromgif('../images/products/' . $img_name);

	$width = imagesx($oimg);
	$height = imagesy($oimg);

    	// this variable is used to calculate the thumbnail width and height
    	define('WH_RATIO', 170/200);

	if (WH_RATIO > $width / $height) {
	    $nheight = 200;
	    $nwidth = floor($width * $nheight / $height);
	}
	else {
	    $nwidth = 170;
	    $nheight = floor($height * $nwidth / $width);
	}
	
	$tmp_img = imagecreatetruecolor($nwidth, $nheight);

	imagecopyresized($tmp_img, $oimg, 0, 0, 0, 0, $nwidth, $nheight, $width, $height);

	if ($ext == 'jpg' || $ext == 'jpeg')
	    imagejpeg($tmp_img, '../images/products/thumbs/' . $img_name);
	if ($ext == 'png')
	    imagepng($tmp_img, '../images/products/thumbs/' . $img_name);
	if ($ext == 'gif')
	    imagegif($tmp_img, '../images/products/thumbs/' . $img_name);


	// save image name in database
	$query = "INSERT INTO $table (img) VALUES ('$img_name')";
	@mysql_query($query);
    }

    // There's an image in the tmp folder
    else {

	// for the large (regular) images
    	$current_path = '/home/users/web/b695/moo.jakchamalku/images/tmp/'      . $img_name;
  	$new_path     = '/home/users/web/b695/moo.jakchamalku/images/products/' . $img_name;

	rename($current_path, $new_path);


	// for the thumbsnails
	$current_path = '/home/users/web/b695/moo.jakchamalku/images/tmp/thumbs/'      . $img_name;
	$new_path     = '/home/users/web/b695/moo.jakchamalku/images/products/thumbs/' . $img_name;

	rename($current_path, $new_path);

	// save previous image name in database
	$query = "INSERT INTO $table (img) VALUES ('$img_name')";
	@mysql_query($query);
    }




    /*
     * Deal with the section list
     */

    if ($options[0]) {

	// color and/or size attributes have been selected, or subsections
	$c = array();
	$s = array();
	$sec = array();

	foreach($options as $p) {
	    if (substr($p, -1) == 'c')
	        $c[] = substr($p, 0, -1);
	    // all the sections end in 's' as well
	    elseif (substr($p, -1) == 's' && $table != 'hats' && $table != 'jewelry')
	        $s[] = substr($p, 0, -1);
	    else
	        $sec[] = $p;
	}




	//go through the list of sections and save them in database
	foreach($sec as $z) {
	    $query = "UPDATE `chayak`.`$table` SET `$z` = '1' WHERE `$table`.`img` = '$img_name'";
	    @mysql_query($query);
	}


	//sort the lists, return as a string to put in database
	$colors = sortStringc($c);
	$sizes = sortStrings($s);

	// save to database only the selected
	if ($colors != '') {
	    $query = "UPDATE `chayak`.`$table` SET `color` = '$colors' WHERE `$table`.`img` = '$img_name'";
	    @mysql_query($query);
	}
	if ($sizes != '') {
	    $query = "UPDATE `chayak`.`$table` SET `size` = '$sizes' WHERE `$table`.`img` = '$img_name'";
	    @mysql_query($query);
	}

    } // $options[0]



    /*
     * price and sale
     */
    $query = "UPDATE `chayak`.`$table` SET `price` = '" . $_REQUEST['price'] . "' WHERE `$table`.`img` = '$img_name'";
    @mysql_query($query);

    if (isset($_REQUEST['sale'])) {
	$query = "UPDATE `chayak`.`$table` SET `onsale` = '" . $_REQUEST['newprice'] . "' WHERE `$table`.`img` = '$img_name'";
	@mysql_query($query);
    }


    /*
     * width and height
     */
    if (isset($_REQUEST['width'])) {
	$query = "UPDATE `chayak`.`$table` SET `width` = '" . $_REQUEST['width'] . "' WHERE `$table`.`img` = '$img_name'";
	@mysql_query($query);
    }
    if (isset($_REQUEST['height'])) {
	$query = "UPDATE `chayak`.`$table` SET `height` = '" . $_REQUEST['height'] . "' WHERE `$table`.`img` = '$img_name'";
	@mysql_query($query);
    }


    /*
     * home page
     */
    if (isset($_POST['home'])) {
	$query = "UPDATE `chayak`.`$table` SET `home_page` = TRUE WHERE `$table`.`img` = '$img_name'";
	@mysql_query($query);
    }


    mysql_close();


//Send this cookie to control_panel.php for the message that the product has been saved successfully
setcookie('save_info', 'success', time()+3600, '/');

header('Location: control_panel.php');


} //else save info






function sortStringc($list) {
    if (!$list[0] || !$list[1])
	return $list[0];
    sort($list);
    $s = '';
    foreach($list as $p)
	$s .= "$p,";
    $s = substr($s, 0, -1);
    return $s;
}


function sortStrings($list) {
    if (!$list[0] || !$list[1])
	return $list[0];
    $a = array();
    $x3;
    $x2;
    $x;
    $l;
    $m;
    $s;
    for ($i=0 ; $i < count($list) ; $i++) {
	$item = $list[$i];
	if ($item == '3XL')  $x3 = $item;
	if ($item == '2XL')  $x2 = $item;
	if ($item == 'XL')   $x  = $item;
	if ($item == 'L')    $l  = $item;
	if ($item == 'M')    $m  = $item;
	if ($item == 'S')    $s  = $item;
    }
    if ($s)  array_push($a, $s);
    if ($m)  array_push($a, $m);
    if ($l)  array_push($a, $l);
    if ($x)  array_push($a, $x);
    if ($x2) array_push($a, $x2);
    if ($x3) array_push($a, $x3);
    $s = '';
    foreach($a as $p)
	$s .= "$p,";
    $s = substr($s, 0, -1);
    return $s;
}



?>