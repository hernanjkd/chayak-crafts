<?php


$useb = array('home','apparel','accessories','craft','music','cart');

$dark_image = '<img src="../images/build/'. PAGE_IMAGE .'darkbtn.png" />';

function light_image ($useb) {

    if ($useb == 'apparel' || $useb == 'accessories' || $useb == 'craft')
		$good4sub = true;


    $li = '
	<img class="iregular"
	onmouseover="show(\'li'.$useb.'btn\')';
		if ($good4sub)
			$li .= ";show('sub$useb')";
    $li .= '"
	onmouseout="hide(\'li'.$useb.'btn\')';
		if ($good4sub)
			$li .= ";hide('sub$useb')";
    $li .= '"
	src="../images/build/'.$useb.'btn.png" />';


    if ($good4sub && PAGE_IMAGE != $useb)
		$li .= write_sub($useb);


    $li .= '
	<img class="ilight" id="li'.$useb.'btn"
	onmouseover="show(\'li'.$useb.'btn\')';
		if ($good4sub)
			$li .= ";show('sub$useb');show('isub$useb')";
    $li .= '"
	onmouseout="hide(\'li'.$useb.'btn\')';
		if ($good4sub)
			$li .= ";hide('sub$useb');hide('isub$useb')";
    $li .= '"
	onclick="javascript: location = \''.$useb.'.php\'"
	src="../images/build/'.$useb.'lightbtn.png" />';

    return $li;
}

function write_sub($useb) {

    if ($useb == 'apparel') {
	$div_style = 'style="padding-left: 5px"';
	$div_img_style = 'style="top: 30px"';
	$img_style = 'width="100px" height="108px" style="padding-left: 3px"';
	$submenu = '
	    <tr><td onclick="javascript: location = \'tshirts.php\'">T-SHIRTS</td></tr>
	    <tr><td onclick="javascript: location = \'sweaters.php\'">SWEATERS</td></tr>
	    <tr><td onclick="javascript: location = \'dresses.php\'">DRESSES</td></tr>';
    }

    if ($useb == 'accessories') {
	$div_style = 'style="padding-left: 20px"';
	$div_img_style = 'style="top: 30px"';
	$img_style = 'width="100px" height="175px" style="padding-left: 16px"';
	$submenu = '
	    <tr><td onclick="javascript: location = \'hats.php\'">HATS</td></tr>
	    <tr><td onclick="javascript: location = \'gloves.php\'">GLOVES</td></tr>
	    <tr><td onclick="javascript: location = \'scarves.php\'">SCARVES</td></tr>
	    <tr><td onclick="javascript: location = \'blankets.php\'">BLANKETS</td></tr>
	    <tr><td onclick="javascript: location = \'jewelry.php\'">JEWELRY</td></tr>';
    }

    if ($useb == 'craft') {
	$div_style = '';
	$div_img_style = 'style="top: 30px"';
	$img_style = 'width="210px" height="146px"';
	$submenu = '
	    <tr><td onclick="javascript: location = \'paintings.php\'">PAINTINGS</td></tr>
	    <tr><td onclick="javascript: location = \'dolls.php\'">NATIVE AMERICAN DOLLS</td></tr>
	    <tr><td onclick="javascript: location = \'mandalas.php\'">MANDALAS</td></tr>
	   <tr><td onclick="javascript: location = \'dreamcatchers.php\'">DREAM CATCHERS</td></tr>';
    }

    $li = '
	<div class="imgsub" id="isub'.$useb.'" '. $div_img_style .'>
	<img src="images/build/sub.png" '. $img_style .' />
	</div>
	<div class="submenu" id="sub'.$useb.'" '. $div_style .'>
	<table
	onmouseover="show(\'li'.$useb.'btn\');show(\'sub'.$useb.'\');show(\'isub'.$useb.'\')"
	onmouseout="hide(\'li'.$useb.'btn\');hide(\'sub'.$useb.'\');hide(\'isub'.$useb.'\')">
	'. $submenu .'
	</table>
	</div>';

    return $li;
}




$button_part = '

<table id="buttons"><tr>

<td width="100px">';
$button_part .= ($filesec == $useb[0]) ? $dark_image : light_image($useb[0]) ;
$button_part .= '
</td>

<td width="112px">';
$button_part .= ($filesec == $useb[1]) ? $dark_image : light_image($useb[1]) ;
$button_part .= '
</td>

<td width="178px">';
$button_part .= ($filesec == $useb[2]) ? $dark_image : light_image($useb[2]) ;
$button_part .= '
</td>

<td width="107px">';
$button_part .= ($filesec == $useb[3]) ? $dark_image : light_image($useb[3]) ;
$button_part .= '
</td>

<td width="160px">';
$button_part .= ($filesec == $useb[4]) ? $dark_image : light_image($useb[4]) ;
$button_part .= '
</td>

<td>';
$button_part .= (PAGE_IMAGE == $useb[5]) ? $dark_image : light_image($useb[5]) ;
$button_part .= '
</td>

<td>
'. $cart_div .'
</td>

</tr></table id="buttons">
';


echo $button_part;

?>