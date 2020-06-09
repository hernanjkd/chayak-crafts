<?php

if ($_SESSION['items'] == 0) {
    $message = '<div id="empty_cart">
		There are no items in the cart</div>';
    echo $message;
}

else {

    $ct = '<div id="display_cart">
    
    <div><span style="background-color:green">CHECK OUT</span></div>
    
    <table>';

    foreach($_SESSION['item_list'] as $thread) {
	
	list($table, $image, $price, $size, $color) = explode('@', $thread);

	$ct .= '
<tr>
<td valign="top">
<input onclick="takeFromCart(\''. $thread .'\')" type="button" value="x" />
</td><td>
<img src="images/products/thumbs/'. $image .'" />
</td><td>';

	if ($size)
	    $ct .= 'Size: '. $size .'<br />';

	if ($color)
	    $ct .= 'Color: '. $color;

	$ct .= '</td><td width="200px" align="right">
Price: $'. $price .'
</td></tr>
<tr><td></td><td></td><td></td><td><hr /></td></tr>
	';
    }

    $ct .= '<tr><td></td><td></td><td></td><td align="right" valign="top" height="35px">TAX &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 5%</td></tr>
<tr><td></td><td></td><td></td><td align="right">TOTAL &nbsp;&nbsp;&nbsp; $'. number_format($_SESSION['total_price'] * 1.05, 2) .'</td></table>

<div><span style="background-color:green">CHECK OUT</span></div>

</div>';

    echo $ct;
}

?>