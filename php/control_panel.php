<?php

session_name('add_modify_products');
ini_set('session.save_path','../cgi-bin/tmp');

session_start();

$user = $_POST['user_name'];
$pwd = $_POST['password'];


if ($user && $pwd) {


    include('../cgi-bin/mysql_connect.php');

    $query = "SELECT * FROM login_info";
    $result = @mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);

    
    if (md5($user) === $row['user_name'] && md5($pwd) === $row['password']) {

	$_SESSION['user_name'] = $user;
	$_SESSION['password']  = $pwd;
    }

    else
	$incorrect = '<font color="red">Incorrect user name or password</font><br />';

    mysql_close();
}



$logout = $_COOKIE['slogoutotsp'];
if ($logout) {
    setcookie('slogoutotsp', '', time()-3600, '/');
    session_destroy();

    header('Location: ../index.php');
    exit;
}



if (isset($_SESSION['user_name']) && isset($_SESSION['password'])) {
    $page = '
<span style="position:absolute;color:blue;text-decoration:underline;cursor:pointer;left:5px;"
onclick="logout()" />Log out</span>

<div>
<span id="p1">P</span><span id="p2">a</span><span id="p3">n</span><span id="p4">e</span><span id="p5">l</span> 

<span id="p6">d</span><span id="p7">e</span> 

<span id="p8">C</span><span id="p9">o</span><span id="p10">n</span><span id="p11">t</span><span id="p12">r</span><span id="p13">o</span><span id="p14">l</span>
</div>

<table>
<tr>
<td onclick="javascript:location=\'form.php\'">Agregar Producto</td>
<td onclick="javascript:location=\'view_products.php\'">Modificar Productos</td>
</tr>
</table>
    ';
}

else
    $page = '
<span style="position:absolute;color:blue;text-decoration:underline;cursor:pointer;left:5px;"
onclick="javascript: location = \'../index.php\'" />Home</span>

<form action="control_panel.php" method="post">
' . $incorrect . '
User Name:<input type="text" name="user_name" /><br />
Password:<input type="password" name="password" /><br /><br />
<input type="submit" value="Submit" />
</form>
    ';






$message = '';

if ($_COOKIE['save_info']) {
    $message = '<h1><font color="green">El producto se a grabado!</font></h1><hr />';
    setcookie('save_info', '', time()-3600, '/');
}


?>

<html>

<head>
<title>Panel de Control</title>

<style type="text/css">

body
{
	text-align: center;
	background: url('../images/build/flagecuador.gif');
}

div
{
	background-color: black;
	border: ridge gray 4px;
	width: 700px;
	margin-left: auto;
	margin-right: auto;
	font-size: 60px;
	font-family: courier;
	color: white;
}

form
{
	position: relative;
	margin-right: auto;
	margin-left: auto;
	border: solid black 1px;
	background-color: white;
	width: 240px;
	top: 200px;
	text-align: right;
}

table
{
	margin-left: auto;
	margin-right: auto;
	position: relative;
	border-spacing: 150px;
}

td
{
	border: ridge gray 4px;
	background-color: lightgray;
	color: black;
	padding: 8px;
	font-size: 28px;
	font-family: monospace;
	cursor: pointer;
	text-align: center;
}

</style>

</head>


<body>


<?php

echo $message;

echo $page;

?>


</body>



<script type="text/javascript"><!--

var x = 1
var limit = 14

var t1
var t2
var t3
var t4

setInterval('createDisplay()',200)

function createDisplay() {

    if (x == 12) {
	t1 = 'p12'
	t2 = 'p13'
	t3 = 'p14'
	t4 = 'p1'
    }

    else if (x == 13) {
	t1 = 'p13'
	t2 = 'p14'
	t3 = 'p1'
	t4 = 'p2'
    }

    else if (x == 14) {
	t1 = 'p14'
	t2 = 'p1'
   	t3 = 'p2'
	t4 = 'p3'
	x = 0
    }

    else {
    	t1 = 'p' + x
    	t2 = 'p' + (x+1)
    	t3 = 'p' + (x+2)
    	t4 = 'p' + (x+3)
    }

    document.getElementById(t1).style.color = 'white'
    document.getElementById(t2).style.color = 'yellow'
    document.getElementById(t3).style.color = 'blue'
    document.getElementById(t4).style.color = 'red'
    
    x += 1
}

function logout() {
    var d = new Date()
    d.setTime(d.getTime() + (24*60*60*1000))  //expires tomorrow
    //simple log out of the secret pages
    document.cookie = 'slogoutotsp=out; expires=' + d.toGMTString() + '; path=/'
    location.reload()
}

--></script>



</html>