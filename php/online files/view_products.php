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




$logout = $_COOKIE['slogoutotsp'];
if ($logout) {
    setcookie('slogoutotsp', '', time()-3600, '/');
    session_destroy();

    header('Location: ../index.php');
    exit;
}



/*
 * They have submitted another image
 */
if ($_FILES['img_file']['type'] != '') {

    list($foto, $ext) = explode('/', $_FILES['img_file']['type']);

    if ($foto == 'image' && ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png' || $ext == 'gif')) {

	//delete old images
	unlink('../images/products/' . $_REQUEST['old_name']);
	unlink('../images/products/thumbs/' . $_REQUEST['old_name']);



	$img_name = date('ymdHis') . '.' . $ext;

	//fix up new image
  	$target_path = '/home/users/web/b695/moo.jakchamalku/images/products/' . $img_name;

	move_uploaded_file($_FILES['img_file']['tmp_name'] , $target_path);


	// load image
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
	    imagejpeg($tmp_img, '../images/products/thumbs/' . $img_name);
	if ($ext == 'png')
	    imagepng($tmp_img, '../images/products/thumbs/' . $img_name);
	if ($ext == 'gif')
	    imagegif($tmp_img, '../images/products/thumbs/' . $img_name);



	//update db
	$t = $_REQUEST['table'];
	$query = "UPDATE `chayak`.`$t` SET `img` = '$img_name' WHERE `$t`.`img` = '".$_REQUEST['old_name']."'";
	@mysql_query($query);
    }
}




/*
 * This cookie is used to update the db
 */
$data = $_COOKIE['einfoepupdate'];

if ($data) {

    //delete cookie
    setcookie('einfoepupdate', '', time()-3600, '/');

    //table, img, column, data
    list($t, $i, $c, $d) = explode('@' , $data);

    $query = "UPDATE `chayak`.`$t` SET `$c` = ";
    if ($d) 
	$query.= "'$d' WHERE `$t`.`img` = '$i'";
    else 
	$query .= "NULL WHERE `$t`.`img` = '$i'";
    @mysql_query($query);
}




/*
 * This cookie will delete the product
 */
$dproduct = $_COOKIE['tpoutofdb'];

if ($dproduct) {

    //delete cookie
    setcookie('tpoutofdb', '', time()-3600, '/');

    //image_name , table_name
    list($i, $t) = explode('@' , $dproduct);

    //delete images
    unlink('../images/products/' . $i);
    unlink('../images/products/thumbs/' . $i);

    //delete info from db
    $query = "DELETE FROM `chayak`.`$t` WHERE `$t`.`img` = '$i'";
    @mysql_query($query);
}



/*
 * Display the products of a table
 */
$table = $_COOKIE['tcttststp'];

if ($table) {

    //don't delete cookie so the page will stay the same when reloaded




    $query = "SELECT * FROM $table ORDER BY onsale DESC";

    $result = @mysql_query($query);

    $row = mysql_fetch_array($result, MYSQL_ASSOC);

    // if the section is emtpy, show a message, else display the table products
    if (!$row)
	$page = '<hr /><div id="empty">NO HAY NINGUN PRODUCTO EN ESTA SECCION</div>';
	
    else {

    	$page = '<hr /><table id="display">';


    	while ($row) {



		$page .= '
<tr><td style="border: solid black 1px">

<table id="inside"><tr>
<td>
    <form action="view_products.php" method="post" enctype="multipart/form-data">
	<div>
	<img id="'.$row['img'].'" src="../images/products/thumbs/'.$row['img'].'"
		onclick="showLarge(this)" style="cursor:pointer" /> <br />
	<input type="file" name="img_file" /> <br />
	<input type="hidden" name="old_name" value="' . $row['img'] . '" />
	<input type="hidden" name="table" value="' . $table . '" />
	<input type="submit" value="Cambiar" />
	</div>
    </form>
</td>

<td style="text-align: right">
    <div>
	Price: <font color="green">$' . $row['price'] . '</font><br /><br />
    	<input id="p' . $row['img'] . '" type="text" style="width:60px" />
	<br />
	<input type="button" value="Cambiar" 
onclick="updateDB(\''.$table.'@'.$row['img'].'@price@\'+document.getElementById(\'p'.$row['img'].'\').value)" />
    </div>
	<br /><br />
    <div>
	Sale Price: <font color="green">';  

	if ($row['onsale'])
	    $page .= '$' . $row['onsale'];
	else
	    $page .= '<font color="red">not on sale</font>';

		$page .= '</font><br /><br />
	<input id="s' . $row['img'] . '" type="text" style="width:60px" />
	<br />
	<input type="button" value="Cambiar" 
onclick="updateDB(\''.$table.'@'.$row['img'].'@onsale@\'+document.getElementById(\'s'.$row['img'].'\').value)" />
    </div>
</td>
		';


/*
 *	DIMENSIONS
 */
		if ($table == 'paintings' || $table == 'mandalas' || 
			$table == 'dreamcatchers' || $table == 'blankets') {

		    $page .= '
<td style="text-align:right">
    <div>
	Dimensions: <font color="green">';

	if ($row['width'] && $row['height'])
	    $page .= $row['height'] . '"x ' . $row['width'] . '"';
	else if ($row['width'])
	    $page .= $row['width'] . '"';
	else if ($row['height'])
	    $page .= $row['height'] . '"';
	else
	    $page .= '<font color="red">no width or height<br />have been selected</font>';

		    $page .= '</font><br /><br />
	Height: <input type="text" style="width:60px" id="hei'.$row['img'].'" value="'.$row['height'].'" /> <br />
	<input type="button" value="Cambiar"
onclick="updateDB(\''.$table.'@'.$row['img'].'@height@\'+document.getElementById(\'hei'.$row['img'].'\').value)" /><br />
	Width: <input type="text" style="width:60px" id="wid'.$row['img'].'" value="'.$row['width'].'" /> <br />
	<input type="button" value="Cambiar" 
onclick="updateDB(\''.$table.'@'.$row['img'].'@width@\'+document.getElementById(\'wid'.$row['img'].'\').value)" />
    </div>
</td>
		    ';
		}//if table width height


/*
 *	SIZE
 */
		if ($table == 'tshirts' || $table == 'sweaters' ||
			 $table == 'dresses' || $table == 'gloves') {

		    $l = explode(',' , $row['size']);
		    $sizelst = array();

		    foreach($l as $item)
			$sizelst[$item] = true;

		    $page .= '
<td>
    <div style="text-align:right">
	Sizes: <font color="green">';

if ($row['size']) {
    $n = 1;
    foreach($l as $item) {
	if ($n == 4) {
	    $page .= '<br />';
	    $n = 1;
	}
	$page .= $item . ' ';
	$n++;
    }
}
else
    $page .= '<font color="red">no sizes have<br />been selected</font>';

		    $page .= '</font><br /><br />
3XL<input id="3xl'.$row['img'].'" type="checkbox" value="3XL" ';
if ($sizelst['3XL']) $page .= 'checked="1"';
$page .= ' /><br />
2XL<input id="2xl'.$row['img'].'" type="checkbox" value="2XL" ';
if ($sizelst['2XL']) $page .= 'checked="1"';
$page .= ' /><br />
XL <input id="xl'.$row['img'].'" type="checkbox" value="XL" ';
if ($sizelst['XL']) $page .= 'checked="1"';
$page .= ' /><br />
L  <input id="la'.$row['img'].'" type="checkbox" value="L" ';
if ($sizelst['L']) $page .= 'checked="1"';
$page .= ' /><br />
M  <input id="me'.$row['img'].'" type="checkbox" value="M" ';
if ($sizelst['M']) $page .= 'checked="1"';
$page .= ' /><br />
S  <input id="sm'.$row['img'].'" type="checkbox" value="S" ';
if ($sizelst['S']) $page .= 'checked="1"';
$page .= ' /><br />
<input type="button" value="Cambiar" 
onclick="updateDBlst(\''.$table.'@'.$row['img'].'@size@\',\'' . $row['img'] . '\',\'size\')" />
    </div>
</td>
		    ';
		}//if tables that have size attribute


/*
 * 	COLOR
 */
		if ($table == 'tshirts' || $table == 'scarves' || $table == 'dreamcatchers') {

		    $l = explode(',' , $row['color']);
		    $colorlst = array();

		    foreach($l as $item)
			$colorlst[$item] = true;

		    $page .= '
<td>
    <div style="text-align:right">
	Colors: <font color="green">';

if ($row['color']) {
    $n = 1;
    foreach($l as $item) {
	if ($n == 4) {
	    $page .= '<br />';
	    $n = 1;
	}
	$page .= $item . ' ';
	$n++;
    }
}
else
    $page .= '<font color="red">no colors have<br />been selected</font>';

		    $page .= '</font><br /><br />
<table style="text-align:right"><tr><td style="vertical-align:top"">
Black<input id="black'.$row['img'].'" type="checkbox" value="black" ';
if ($colorlst['black']) $page .= 'checked="1"';
$page .= ' /><br />
Blue<input id="blue'.$row['img'].'" type="checkbox" value="blue" ';
if ($colorlst['blue']) $page .= 'checked="1"';
$page .= ' /><br />
Light Blue<input id="lightblue'.$row['img'].'" type="checkbox" value="lightblue" ';
if ($colorlst['lightblue']) $page .= 'checked="1"';
$page .= ' /><br />
Brown<input id="brown'.$row['img'].'" type="checkbox" value="brown" ';
if ($colorlst['brown']) $page .= 'checked="1"';
$page .= ' /><br />
Green<input id="green'.$row['img'].'" type="checkbox" value="green" ';
if ($colorlst['green']) $page .= 'checked="1"';
$page .= ' /><br />
Dark Green<input id="darkgreen'.$row['img'].'" type="checkbox" value="darkgreen" ';
if ($colorlst['darkgreen']) $page .= 'checked="1"';
$page .= ' /><br />
Maroon<input id="maroon'.$row['img'].'" type="checkbox" value="maroon" ';
if ($colorlst['maroon']) $page .= 'checked="1"';
$page .= ' /><br />
</td><td style="vertical-align:top">
Orange<input id="orange'.$row['img'].'" type="checkbox" value="orange" ';
if ($colorlst['orange']) $page .= 'checked="1"';
$page .= ' /><br />
Pink<input id="pink'.$row['img'].'" type="checkbox" value="pink" ';
if ($colorlst['pink']) $page .= 'checked="1"';
$page .= ' /><br />
Purple<input id="purple'.$row['img'].'" type="checkbox" value="purple" ';
if ($colorlst['purple']) $page .= 'checked="1"';
$page .= ' /><br />
Red<input id="red'.$row['img'].'" type="checkbox" value="red" ';
if ($colorlst['red']) $page .= 'checked="1"';
$page .= ' /><br />
White<input id="white'.$row['img'].'" type="checkbox" value="white" ';
if ($colorlst['white']) $page .= 'checked="1"';
$page .= ' /><br />
Yellow<input id="yellow'.$row['img'].'" type="checkbox" value="yellow" ';
if ($colorlst['yellow']) $page .= 'checked="1"';
$page .= ' /><br />
</td></tr></table>
<input type="button" value="Cambiar" 
onclick="updateDBlst(\''.$table.'@'.$row['img'].'@color@\',\'' . $row['img'] . '\',\'color\')" />
    </div>
</td>
		    ';
		}//if tables that have color attributes




		$page .= '

</td><td style="border:solid black 1px;border-top:none;border-left:none;vertical-align:bottom;text-align:right">Show in&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br />Home page <input type="checkbox" ';
if ($row['home_page'])
    $page .= 'checked="1"';

		$page .= '
onclick="updateDB(\''.$table.'@'.$row['img'].'@home_page@';
$page .= $row['home_page'] ? '0' : '1' ;
	$page .='\')" />

</td>
</tr></table id="inside">

<td style="vertical-align:top;border:solid black 1px;border-left:none;border-bottom:none">

    <div id="delete" style="cursor: pointer;" onclick="borrarProducto(\''.$row['img'].'@'.$table.'\')">
	BORRAR
    </div>

</td></tr>
        	';



	    $row = mysql_fetch_array($result, MYSQL_ASSOC);
        }

        $page .= '</table>';
    }
}

mysql_close();

?>


<html>

<style type="text/css">

#delete
{
	background-color: red;
}

#display
{
	position: relative;
	top: 30px;
	border-spacing: 30px;
}

#display td
{
	padding: 4px;
}

#empty
{
	position: absolute;
	width: 98%;
	text-align: center;
	font-family: verdana;
	font-style: italic;
	top: 150px;
}

#inside td
{
	vertical-align: top;
}

#inside td div
{
	border: none;
	border-right: solid black 1px;
	border-bottom: solid black 1px;
}

#sections td
{
	border-right: solid black 1px;
	padding: 5px;
	cursor: pointer;
}

a
{
	position: absolute;
	right: 10px;
}

div
{
	vertical-align: middle;
	border: solid black 1px;
}

hr
{
	position: relative;
	top: 20px;
}

span
{
 	color: blue;
	text-decoration: underline;
	cursor: pointer;
	position: absolute;
}

</style>

<span onclick="logout()" style="left: 5px">Log out</span>
<span onclick="back('control_panel.php')" style="right:10px">Panel de Control</span>
<span onclick="back('form.php')" style="top:30px;right:10px">Agregar Producto</span>

<table id="sections" style="margin-left:auto;margin-right:auto;">
<tr>
<td id="blankets" 	onmouseover="change(this)"
			onmouseout="changeBack(this)"
			onclick="send(this)">Blankets</td>
<td id="dreamcatchers" 	onmouseover="change(this)"
			onmouseout="changeBack(this)"
			onclick="send(this)">Dream Catchers</td>
<td id="dresses" 	onmouseover="change(this)"
			onmouseout="changeBack(this)"
			onclick="send(this)">Dresses</td>
<td id="gloves" 	onmouseover="change(this)"
			onmouseout="changeBack(this)"
			onclick="send(this)">Gloves</td>
<td id="hats" 		onmouseover="change(this)"
			onmouseout="changeBack(this)"
			onclick="send(this)">Hats</td>
<td id="jewelry" 	onmouseover="change(this)"
			onmouseout="changeBack(this)"
			onclick="send(this)">Jewelry</td>
<td id="mandalas" 	onmouseover="change(this)"
			onmouseout="changeBack(this)"
			onclick="send(this)">Mandalas</td>
<td id="music"		onmouseover="change(this)"
			onmouseout="changeBack(this)"
			onclick="send(this)">Music</td>
<td id="dolls" 		onmouseover="change(this)"
			onmouseout="changeBack(this)"
			onclick="send(this)">Native American Dolls</td>
<td id="paintings"	onmouseover="change(this)"
			onmouseout="changeBack(this)"
			onclick="send(this)">Paintings</td>
<td id="scarves"	onmouseover="change(this)"
			onmouseout="changeBack(this)"
			onclick="send(this)">Scarves</td>
<td id="sweaters"	onmouseover="change(this)"
			onmouseout="changeBack(this)"
			onclick="send(this)">Sweaters</td>
<td id="tshirts" style="border-right:none" onmouseover="change(this)"
			onmouseout="changeBack(this)"
			onclick="send(this)">T-shirts</td>
</tr>
</table>


<?php

echo $page;

?>


<script type="text/javascript"><!--

/*
 * Esto es lo que uso para update la db, mando un string en una cookie con toda la informacion
 * la exepcion es la imagen porque hay que mandar la nueva imagen. La data tiene 
 * 1.table_name
 * 2.img_name
 * 3.column_name (img, price, onsale, etc)
 * 4.new data
 */
function updateDB(data) {
    var d = new Date()
    d.setTime(d.getTime() + (24*60*60*1000))  //expires tomorrow
    //esta info es para update
    document.cookie = 'einfoepupdate=' + data + '; expires=' + d.toGMTString() + '; path=/'
    location.reload()
}

function borrarProducto(data) {
    var d = new Date()
    d.setTime(d.getTime() + (24*60*60*1000))  //expires tomorrow
    //this product out of database
    document.cookie = 'tpoutofdb=' + data + '; expires=' + d.toGMTString() + '; path=/'
    location.reload()
}

var size_lst = ['sm','me','la','xl','2xl','3xl']
var color_lst = ['black','blue','lightblue','brown','green','darkgreen','maroon','orange','pink', 'purple','red','white','yellow']
function updateDBlst(data, id, attribute) {
    var lst = ''
    if (attribute == 'size')
    	for(var i in size_lst) {
    	    var e = document.getElementById(size_lst[i]+id)
 	    if (e.checked)
	    	lst += e.value + ','
    }
    if (attribute == 'color')
    	for(var i in color_lst) {
    	    var e = document.getElementById(color_lst[i]+id)
 	    if (e.checked)
	    	lst += e.value + ','
    }
    lst = lst.substring(0, lst.length - 1)
    updateDB(data + lst)
}

function send(e) {
    var d = new Date()
    d.setTime(d.getTime() + (24*60*60*1000))  //expires tomorrow
    //this cookie tells the section to show the products
    document.cookie = 'tcttststp=' + e.id + '; expires=' + d.toGMTString() + '; path=/'
    location.reload()
}

function back(page) {
    //delete the cookie that shows the products from the table selected
    document.cookie = 'tcttststp=false; expires=Fri, 3 Aug 2001 20:47:11 UTC; path=/'
    location = page
}

function logout() {
    //delete the cookie that shows the products from the table selected
    document.cookie = 'tcttststp=false; expires=Fri, 3 Aug 2001 20:47:11 UTC; path=/'
    var d = new Date()
    d.setTime(d.getTime() + (24*60*60*1000))  //expires tomorrow
    //simple log out of the secret pages
    document.cookie = 'slogoutotsp=out; expires=' + d.toGMTString() + '; path=/'
    location.reload()
}

function change(e) {
    e.style.backgroundColor = 'darkgreen'
    e.style.color = 'white'
}

function changeBack(e) {
    e.style.backgroundColor = 'white'
    e.style.color = 'black'
}

function showLarge(e) {
    open('../images/products/' + e.id, 'large_image', 'toolbar=0, status=0, menubar=0, location=0, directories=0')
}

--></script>


</html>