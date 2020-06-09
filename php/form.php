<?php

session_name('add_modify_products');
ini_set('session.save_path','../cgi-bin/tmp');

session_start();

include('../cgi-bin/mysql_connect.php');

$query = "SELECT * FROM login_info";
$result = @mysql_query($query);
$row = mysql_fetch_array($result, MYSQL_ASSOC);

/*
 *
 *	MAKE SURE THE USER IS LOGGED IN FOR THIS SITE TO DISPLAY
 *
 */
if (md5($_SESSION['user_name']) === $row['user_name'] && 
	md5($_SESSION['password']) === $row['password'])
    $loggedin = true;
else
    header('Location: control_panel.php');




// this cookie is used to log out and delete the images in the tmp
$logout_cookie = $_COOKIE['slogoutotsp'];

// this is the cookie that takes you back to the control panel main page
$back_cookie = $_COOKIE['backfosho'];

// if this cookie exists is because the page should reset
$reset_cookie = $_COOKIE['resetfosho'];



if ($logout_cookie) {

    setcookie('slogoutotsp', '', time()-3600, '/');

    session_destroy();

    //delete the image if it exists
    if (file_exists('../images/tmp/' . $logout_cookie)) {
	unlink('../images/tmp/' . $logout_cookie);
	unlink('../images/tmp/thumbs/' . $logout_cookie);
    }

    header('Location: ../index.php');
    exit;
}


else if ($back_cookie) {

    list($i , $p) = explode('@', $back_cookie);

    //delete the image if it exists
    if (file_exists('../images/tmp/' . $i)) {
	unlink('../images/tmp/' . $i);
	unlink('../images/tmp/thumbs/' . $i);
    }

    //delete the cookie
    setcookie('backfosho', '', time()-3600, '/');

    header("Location: $p");
    exit;
}


else if ($reset_cookie) {

    //delete the image if it exists
    if (file_exists('../images/tmp/' . $reset_cookie)) {
	unlink('../images/tmp/' . $reset_cookie);
	unlink('../images/tmp/thumbs/' . $reset_cookie);
    }

    //delete the cookie
    setcookie('resetfosho', '', time()-3600, '/');
}


else {

/*
 * This produces the preview of the info that was selected. These variables are also used to create 
 * the sticky form.
 */
if (isset($_REQUEST['list'])) {

    $img_name = solveImage();
    $price = getPrice();
    $sale = getSale();
    $dimensions = getDimensions();

    // this is a list  [0] = section, color, size    [1] = HTML to write down the list in [0]
    $listHTML = getListHTML();

    $preview = '<hr /><div id="preview">';

    if ($img_name)
	$preview .= '<img src="../images/tmp/thumbs/' . $img_name . '" id="' . $img_name . '" onclick="showLarge(this)" /><br />';

    if ($sale) {
	if ($price)
	    $preview .= '<br />Price: <s>$' . $price . '</s>';
	$preview .= '<br /><font color="red">ON SALE  $' . $sale . '</font><br />';
    }
    else if ($price)
	$preview .= '<br />Price: $' . $price . '<br />';

    if ($dimensions[0] && $dimensions[1])
	$preview .= '<br />Dimensions: ' . $dimensions[0] . '"x ' . $dimensions[1] . '"<br />';
    else if ($dimensions[0])
	$preview .= '<br />Dimensions: ' . $dimensions[0] . '"<br />';
    else if ($dimensions[1])
	$preview .= '<br />Dimensions: ' . $dimensions[1] . '"<br />';
	
	

    if ($listHTML)
	$preview .= $listHTML[1];

    $preview .= '</div>';

}

} //else of the all cookies, so it doesn't put the images again

/*
 * Deals with all the procedures of the image. Puts it in the tmp folder
 */
function solveImage() {

    // this variable is used to calculate the thumbnail width and height
    define('WH_RATIO', 170/200);

    $previous_img = $_REQUEST['previous_img_name'];

    if ($_FILES['img']['type'] != '') {


	// delete previous uploaded image
	if ($previous_img != '') {
	    unlink('../images/tmp/' . $previous_img);
	    unlink('../images/tmp/thumbs/' . $previous_img);
	}

    	list( , $ext) = explode('/' , $_FILES['img']['type']);

	
	// delete because thumbnail won't be able to be created
	if ($ext != 'jpg' && $ext != 'jpeg' && $ext != 'png' && $ext != 'gif')
	    return NULL;


   	$img_name = date('ymdHis') . '.' . $ext;

/*
 *
 *	CHANGE THIS WHEN UPLOADING IT ONLINE
 *
 */
  	$target_path = '/Applications/MAMP/htdocs/images/tmp/' . $img_name;

	move_uploaded_file($_FILES['img']['tmp_name'] , $target_path);


	// load image
	if ($ext == 'jpg' || $ext == 'jpeg')
	    $oimg = imagecreatefromjpeg('../images/tmp/' . $img_name);
	if ($ext == 'png')
	    $oimg = imagecreatefrompng('../images/tmp/' . $img_name);
	if ($ext == 'gif')
	    $oimg = imagecreatefromgif('../images/tmp/' . $img_name);


	$width = imagesx($oimg);
	$height = imagesy($oimg);


	// get dimensions for thumb
	if (WH_RATIO > $width / $height) {
	    $nheight = 200;
	    $nwidth = floor($width * $nheight / $height);
	}
	else {
	    $nwidth = 170;
	    $nheight = floor($height * $nwidth / $width);
	}

	
	// create a new blank image with the new dimensions
	$tmp_img = imagecreatetruecolor($nwidth, $nheight);

	// copy and resize the old image into the new blank image
	imagecopyresized($tmp_img, $oimg, 0, 0, 0, 0, $nwidth, $nheight, $width, $height);

	// save thumbnail into a file
	if ($ext == 'jpg' || $ext == 'jpeg')
	    imagejpeg($tmp_img, '../images/tmp/thumbs/' . $img_name);
	if ($ext == 'png')
	    imagepng($tmp_img, '../images/tmp/thumbs/' . $img_name);
	if ($ext == 'gif')
	    imagegif($tmp_img, '../images/tmp/thumbs/' . $img_name);


	return $img_name;
    }

    else {

	//check if there's an image already uploaded
	if (file_exists('../images/tmp/' . $previous_img))
	    return $previous_img;

	return NULL;
    }
}


/*
 * returns a list, where [0] is a list of the options selected, and [1] is the HTML that will print them
 */
function getListHTML() {

    if ($_REQUEST['list'] != '') {

	$info = explode("," , $_REQUEST['list']);

	// pass the list to 
	$listHTML[0] = $info;
	
	$t = '<br />Seccion: <b>' . strtoupper($info[0]) . '</b>';

	// delete the first item on the list, catch it on $sec
	$sec = array_shift($info);

	// display them in the section part
	if ($sec == 'hats' || $sec == 'jewelry')
	    foreach ($info as $piece)
		$t .= '&nbsp&nbsp&nbsp<b>' . strtoupper($piece) . '</b>';

	// display them next to options
	else {

	    $t .= '<br />';

	    $c = array();
	    $s = array();

	    if ($info[0]) {

		// check the last letter to see if it's size or color, and put it in the corresponding list
		foreach ($info as $piece) {
		    if (substr($piece, -1) == 'c')
			$c[] = substr($piece, 0, -1);
		    else
			$s[] = substr($piece, 0, -1);
		}

		if ($c) {

		    sort($c);

		    $t .= '<br />Color: <select>';

	    	    foreach ($c as $color)
	    	  	$t .= '<option>' . $color . '</option>';

		    $t .= '</select>';
		}

		if ($s) {

		    $ns = sortSize($s);

		    $t .= '<br />Size: <select>';

		    foreach ($ns as $size)
			$t .= '<option>' . $size . '</option>';

		    $t .= '</select>';
		}
	    } //if options
	} //else display options

	$listHTML[1] = $t;

	return $listHTML;
    }

    else
	return NULL;
}

function sortSize($list) {
    $alter = array();
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
    if ($s)  array_push($alter, $s);
    if ($m)  array_push($alter, $m);
    if ($l)  array_push($alter, $l);
    if ($x)  array_push($alter, $x);
    if ($x2) array_push($alter, $x2);
    if ($x3) array_push($alter, $x3);

    return $alter;
}

function getDimensions() {

    if ($_REQUEST['height'] != '' || $_REQUEST['width'] != '') {

	$list[0] = $_REQUEST['height'];
	$list[1] = $_REQUEST['width'];

	return $list;
    }

    else
	return NULL;
}

function getSale() {
    return isset($_REQUEST['sale']) ? $_REQUEST['newprice'] : NULL ;
}

function getPrice() {
    return isset($_REQUEST['price']) ? $_REQUEST['price'] : NULL ;
}





if ($loggedin) {

    $page = '

<form name="f1" action="process_form.php" method="post" enctype="multipart/form-data">


<div class="buttons" style="left: 5px" onclick="logout()" />Log out</div>
<div class="buttons" style="top: 8px;right: 10px;" onclick="back(\'control_panel.php\')">
Panel de Control</div>
<div class="buttons" style="right: 10px;top: 30px;" onclick="back(\'view_products.php\')">
Modificar Producto</div>



<fieldset id="imagen">
<legend class="leg">Imagen</legend>
<input type="file" name="img" />
</fieldset>



<fieldset id="seccion">
<legend class="leg">Seccion</legend>
Sweaters<input type="checkbox" name="section" id="sweaters" value="sweaters"
 	onclick="display(this);record(this)" />	<br />
T-Shirts<input type="checkbox" name="section" id="tshirts" value="tshirts"
	onclick="display(this);record(this)" /> <br />
Dresses<input type="checkbox" name="section" id="dresses" value="dresses"
	onclick="display(this);record(this)" /> <br />
Gloves<input type="checkbox" name="section" id="gloves" value="gloves"
	onclick="display(this);record(this)" /> <br />
Scarves<input type="checkbox" name="section" id="scarves" value="scarves"
	onclick="display(this);record(this)" /> <br />
Hats<input type="checkbox" name="section" id="hats" value="hats"
	onclick="display(this);record(this)" /> <br />
Paintings<input type="checkbox" name="section" id="paintings" value="paintings"
 	onclick="display(this);record(this)" /> <br />
Jewelry<input type="checkbox" name="section" id="jewelry" value="jewelry"
	onclick="display(this);record(this)" /> <br />
Mandalas<input type="checkbox" name="section" id="mandalas" value="mandalas"
	onclick="display(this);record(this)" /> <br />
Dream Catchers<input type="checkbox" name="section" id="dreamcatchers" value="dreamcatchers"
	onclick="display(this);record(this)" /> <br />
Native American Dolls<input type="checkbox" name="section" id="dolls" value="dolls"
	onclick="display(this);record(this)" /> <br />
Blankets<input type="checkbox" name="section" id="blankets" value="blankets"
	onclick="display(this);record(this)" /> <br />
Music<input type="checkbox" name="section" id="music" value="music"
	onclick="display(this);record(this)" /> <br />
</fieldset>



<fieldset id="precio">
<legend class="leg">Precio</legend>
$<input type="text" size="10" name="price" id="price" value="';

$page .= $price ? $price : '0.00' ;

$page .= '" /></fieldset>



<fieldset id="descuento">
<legend class="leg">Descuento</legend>
ON SALE<input type="checkbox" name="sale" id="sale" value="sale"'; 

if ($sale) 
    $page .= ' checked="1"'; 

$page .= ' onclick="newPrice(this)" />

<span id="newpricehere"></span>
</fieldset>



<fieldset id="home_page">
<legend class="leg">Home Page</legend>
<input type="checkbox" name="home" value="show_home_page"';

if (isset($_POST['home']))
    $page .= ' checked="1"';

$page .= ' />
</fieldset>



<span id="additional"></span>
<span id="hidden"></span>



<input type="hidden" id="previous_img_name" name="previous_img_name" value="' . $img_name . '" />




<input id="breset" type="button" value="Reset" onclick="resetForm()" />
<input id="bpreview" type="button" value="See Preview" onclick="createPreview()" />
<input id="bsubmit" type="button" value="Submit" onclick="submitForm()" />



</form>


' . $preview

    ; //end of $page
}



?>

<html>




<head>
<title>Formulario</title>












<style type="text/css">

#bpreview
{
	position: relative;
	left: 44%;
	top: 60px;
}

#breset
{
	position: relative;
	top: 60px;
	left: 36%;
}

#bsubmit
{
	position: relative;
	left: 45%;
	top: 60px;
}

#color
{
	position: absolute;
	left: 60%;
	top: 30px;
	text-align: right;
}

#descuento
{
	position: absolute;
	left: 10%;
	top: 190px;
	width: 110px;
	text-align: right;
}

#dimensiones
{
	position: absolute;
	left: 45%;
	top: 30px;
	text-align: right;
}

#home_page
{
	position: absolute;
	left: 3%;
	top: 270px;
	text-align: center;
}

#imagen
{
	position: absolute;
	left: 3%;
	top: 30px;
}

#precio
{
	position: absolute;
	left: 10%;
	top: 110px;
	width: 110px;
	text-align: right;
}

#preview
{
	position: relative;
	margin-left: auto;
	margin-right: auto;
	top: 80px;
	text-align: center;
}

#preview img
{
	cursor: pointer;
}

#seccion
{
	position: relative;
	left: 28%;
	top: 20px;
	width: 170px;
	text-align: right;
}

#seccion_adicional
{
	position: absolute;
	left: 50%;
	top: 100px;
	text-align: right;
}

#talle
{
	position: absolute;
	left: 49%;
	top: 30px;
	text-align: right;
}

div.buttons
{
	text-decoration: underline;
	color: blue;
	position: absolute;
	cursor: pointer;
}

img
{
	border: solid black 1px;
}

hr
{
	position: relative;
	top: 60px;
}

legend.leg
{
	text-align: center;
}

</style>













</head>






<body>


<?php

echo $page;

?>


</body>















<script type="text/javascript"><!--



function submitForm() {
    var t = '<input type="hidden" name="list" value="' + list + '" />'
    document.getElementById('hidden').innerHTML = t
    document.f1.submit()
}



function resetForm() {
    var d = new Date()
    d.setTime(d.getTime() + (24*60*60*1000))  //expires tomorrow
    var n = document.getElementById('previous_img_name').value
    if (!n) n = 'false'
    var cookie = 'resetfosho=' + n + '; expires=' + d.toGMTString() + '; path=/'
    document.cookie = cookie
    location.reload()
}



function back(page) {
    var d = new Date()
    d.setTime(d.getTime() + (24*60*60*1000))  //expires tomorrow
    var n = document.getElementById('previous_img_name').value
    n += '@' + page
    var cookie = 'backfosho=' + n + '; expires=' + d.toGMTString() + '; path=/'
    document.cookie = cookie
    location.reload()
}



function createPreview() {
    var t = '<input type="hidden" name="list" value="' + list + '" />'
    document.getElementById('hidden').innerHTML = t
    document.f1.action = 'form.php'
    document.f1.submit()
}


/*
 * Keeps track of the options checked so that I can pass them to form_check.php and not go through 
 * all the options to see which ones where checked
 */
var list = []
function record(e) {

    if (e.name == 'section') {
	list.length = 0
	list[0] = e.value
    }
    else {
	//make sure that the onclick was to check it and not uncheck it
	if (record_check(e.id))
	    list.push(e.value)
	else
	    //delete the unchecked value
	    list.splice(list.indexOf(e.value), 1)
    }
}

function record_check(id) {
    return document.getElementById(id).checked
}






var previousID
function display(e) {
    //keeps only one checkbox checked by deleting the previous one
    if (previousID != undefined) 
	document.getElementById(previousID).checked = false
    previousID = e.id
    generateTable(e.id)
}

function generateTable(id) {
    var text = ''
    if (id == 'sweaters') 
	text += getSize()
    if (id == 'tshirts')
	text += getColors() + getSize()
    if (id == 'dresses')
	text += getSize()
    if (id == 'gloves')
	text += getSize()
    if (id == 'scarves')
	text += getColors()
    if (id == 'hats')
	text += getSections('h')
    if (id == 'paintings')
	text += getDimensions()
    if (id == 'jewelry')
	text += getSections('j')
    if (id == 'mandalas')
	text += getDimensions()
    if (id == 'dreamcatchers')
	text += getDimensions() + getColors()
    if (id == 'dolls')
	text += getDimensions()
    if (id == 'blankets')
	text += getDimensions()

    document.getElementById('additional').innerHTML = text
}

function getColors() {
    var t = ''+
'<fieldset id="color">'+
'<legend class="leg">Color</legend>'+
'Black<input type="checkbox" value="blackc" id="blackc" name="color" onclick="record(this)" /><br />'+
'Blue<input type="checkbox" value="bluec" id="bluec" name="color" onclick="record(this)" /><br />'+
'Light Blue<input type="checkbox" value="lightbluec" id="lightbluec" name="color" onclick="record(this)" /><br />'+
'Brown<input type="checkbox" value="brownc" id="brownc" name="color" onclick="record(this)" /><br />'+
'Green<input type="checkbox" value="greenc" id="greenc" name="color" onclick="record(this)" /><br />'+
'Dark Green<input type="checkbox" value="darkgreenc" id="darkgreenc" name="color" onclick="record(this)" /><br />'+
'Maroon<input type="checkbox" value="maroonc" id="maroonc" name="color" onclick="record(this)" /><br />'+
'Orange<input type="checkbox" value="orangec" id="orangec" name="color" onclick="record(this)" /><br />'+
'Pink<input type="checkbox" value="pinkc" id="pinkc" name="color" onclick="record(this)" /><br />'+
'Purple<input type="checkbox" value="purplec" id="purplec" name="color" onclick="record(this)" /><br />'+
'Red<input type="checkbox" value="redc" id="redc" name="color" onclick="record(this)" /><br />'+
'White<input type="checkbox" value="whitec" id="whitec" name="color" onclick="record(this)" /><br />'+
'Yellow<input type="checkbox" value="yellowc" id="yellowc" name="color" onclick="record(this)" />'+
'</fieldset>'

    return t
}

function getSize() {
    var t = ''+
'<fieldset id="talle">'+
'<legend class="leg">Talle</legend>'+
'3XL<input type="checkbox" name="size" onclick="record(this)" id="3XLs" value="3XLs" /><br />'+
'2XL<input type="checkbox" name="size" onclick="record(this)" id="2XLs" value="2XLs" /><br />'+
'XL<input type="checkbox" name="size" onclick="record(this)" id="XLs" value="XLs" /><br />'+
'L<input type="checkbox" name="size" onclick="record(this)" id="Ls" value="Ls" /><br />'+
'M<input type="checkbox" name="size" onclick="record(this)" id="Ms" value="Ms" /><br />'+
'S<input type="checkbox" name="size" onclick="record(this)" id="Ss" value="Ss" />'+
'</fieldset>'

    return t
}

function getDimensions() {
    var t = ''+
'<fieldset id="dimensiones">'+
'<legend class="leg">Dimensiones</legend>'+
'Height:<input type="text" size="10" name="height" <?php if ($dimensions) 
							echo "value=\"$dimensions[0]\""; ?> /> in<br />'+
'Width:<input type="text" size="10" name="width" <?php if ($dimensions) 
							echo "value=\"$dimensions[1]\""; ?> /> in'+
'</fieldset>'

    return t
}

function getSections(id) {
    var t = ''

    if (id == 'j') {
	t = '<fieldset id="seccion_adicional">'+
'<legend class="leg">Subseccion</legend>'+
'Bracelets<input type="checkbox" name="jewelry" id="bracelets" value="bracelets" onclick="record(this)" /><br />'+
'Earrings<input type="checkbox" name="jewelry" id="earrings" value="earrings" onclick="record(this)" /><br />'+
'Necklaces<input type="checkbox" name="jewelry" id="necklaces" value="necklaces" onclick="record(this)" /><br />'+
'Rings<input type="checkbox" name="jewelry" id="rings" value="rings" onclick="record(this)" />'+
'</fieldset>'
    }

    if (id == 'h') {
	t = '<fieldset id="seccion_adicional">'+
'<legend class="leg">Subseccion</legend>'+
'Bomber Hats<input type="checkbox" name="hats" id="bomberhats" value="bomberhats" onclick="record(this)" /><br />'+
'Wool Hats<input type="checkbox" name="hats" id="woolhats" value="woolhats" onclick="record(this)" /><br />'+
'</fieldset>'
    }

    return t
}


/*
 * If the sale is checked but it wasn't clicked (preview) then find out.
 */
var salelement = document.getElementById('sale')
if (salelement.checked)
    newPrice(salelement)

function newPrice(e) {
    var newprice = document.getElementById('newpricehere')
    if (e.checked == true) {
    	var t = '<br />$<input type="text" name="newprice" id="newprice" size="10" <?php if ($_REQUEST['newprice']) echo 'value="'.$_REQUEST['newprice'].'"'; else echo 'value="0.00"'; ?> />'
    	newprice.innerHTML = t
    }
    else
	newprice.innerHTML = ''
}

function logout() {
    var d = new Date()
    d.setTime(d.getTime() + (24*60*60*1000))  //expires tomorrow
    var n = document.getElementById('previous_img_name').value
    if (!n)
	n = 'out'
    //simple log out of the secret pages
    document.cookie = 'slogoutotsp=' + n + '; expires=' + d.toGMTString() + '; path=/'
    location.reload()
}



function showLarge(e) {
    open('../images/tmp/' + e.id, 'large_image', 'toolbar=0, status=0, menubar=0, location=0, directories=0')
}





<?php

/*
 * Create the sticky form, so I will use the function generateTable() since it creates the additional sections
 * then push() the info in the list, so the info is stored and so can be preiewed
 */
if ($listHTML) {

    $sid = $listHTML[0][0];

    $t = "generateTable('$sid') \n";
    $t .= "previousID = '$sid' \n";

    foreach ($listHTML[0] as $id) {
	$t .= "document.getElementById('$id').checked = true \n";
	$t .= "list.push('$id') \n";
    }

    echo $t;
}

?>





--></script>







</html>