<?php

session_name('shoppingCart');
ini_set('session.save_path','cgi-bin/tmp');

session_start();

$shop_cookie = $_COOKIE['cppepeeshopcart'];

//add to cart
if ($shop_cookie) {

    setcookie('cppepeeshopcart', '', time()-3600, '/');

    //table, image, price, size, color
    list( , , $sc_price, , ) = explode('@', $shop_cookie);

    if (!isset($_SESSION['items']))
	$_SESSION['items'] = 0;

    if (!isset($_SESSION['total_price']))
	$_SESSION['total_price'] = 0;

    if (!isset($_SESSION['item_list']))
	$_SESSION['item_list'] = array();


    $_SESSION['items']++;
    $_SESSION['total_price'] += ($sc_price * 1);
    $_SESSION['item_list'][] = $shop_cookie;
}

//delete from cart
$shop_cookie = $_COOKIE['cpsepeeshopcart'];

if ($shop_cookie) {

    setcookie('cpsepeeshopcart', '', time()-3600, '/');

    //table, image, price, size, color
    list( , , $sc_price, , ) = explode('@', $shop_cookie);

    $_SESSION['items']--;
    $_SESSION['total_price'] -= ($sc_price * 1);
    array_splice($_SESSION['item_list'], array_search($shop_cookie, $_SESSION['item_list']), 1);
}



if ($_SESSION['items'] == 0) {
    $show_items = 'empty';
    $show_price = '';
}
else if ($_SESSION['items'] == 1) {
    $show_items = $_SESSION['items'] .' item';
    $show_price = '$' . $_SESSION['total_price'];
}
else if ($_SESSION['items'] > 1) {
    $show_items = $_SESSION['items'] . ' items';
    $show_price = '$' . $_SESSION['total_price'];
}

$cart_div = '<div id="cart_info">'. $show_items .'<br />'. $show_price .'</div>';

?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">



<head>

<title>Chayak</title>

<link type="text/css" rel="stylesheet" href="css/general.css" />
<link type="text/css" rel="stylesheet" href="css/buttons.css" />
<link type="text/css" rel="stylesheet" href="css/secondary_buttons.css" />
<link type="text/css" rel="stylesheet" href="css/products.css" />

<script language="javascript" type="text/javascript" src="javascript/functions.js"></script>

</head>




<body>

<!--
LOAD THE SUBMENU IMAGE SO THEY SHOW UP QUICK
-->
<img style="position:absolute" src="images/build/sub.png" width="0px" height="0px" />


<div id="bg">

<img id="bgimg" src="../images/build/background.png" />

