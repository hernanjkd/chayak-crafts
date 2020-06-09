<?php

$ps = '<div class="display"><table class="display"><tr>';

//Number of products per row
define('PRODUCTS_PER_ROW', 4);

$columns = PRODUCTS_PER_ROW;


$table_lst[] = $filesec;

//Check to see what file it is and then add the table names that must be displayed
if ($filesec == 'apparel')
	$table_lst = array('tshirts','dresses','sweaters');

if ($filesec == 'accessories')
	$table_lst = array('hats','gloves','scarves','jewelry','blankets');

if ($filesec == 'craft')
	$table_lst = array('dreamcatchers','paintings','dolls','mandalas');

if ($filesec == 'index' || $filesec == 'home')
	$table_lst = array('tshirts','hats','jewelry','dresses','dreamcatchers','music',
						'paintings','sweaters','scarves','mandalas','dolls','gloves',
						'blankets');


//First round do the sales, then take care of the rest
for($i=0 ; $i<2 ; $i++) {

$condition = $i ? 'onsale IS NULL' : 'onsale != 0';

if ($filesec == 'index' || $filesec == 'home')
	$condition .= ' AND home_page != 0';


foreach($table_lst as $table_name) {

$query = "SELECT * FROM $table_name WHERE $condition";

$result = @mysql_query($query);

while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

    if ($columns < 1) {
    	$ps .= '</tr><tr>';
    	$columns = PRODUCTS_PER_ROW;
    }



    $ps .= '
<td class="product">';

if ($row['onsale']) 
    $ps .= '<div class="onsale">ON &nbsp;SALE</div>';


//image

    $ps .= '<table><tr><td class="image"><div class="image">
    <img class="product" id="'.$row['img'].'" src="images/products/thumbs/'.$row['img'].'" 
	onclick="showLarge(this)" />
    </div></td></tr></table>';


//dimensions

if ($row['width'] && $row['height'])
    $ps .= '<div class="dimensions">' . $row['height'] . '"x ' . $row['width'] . '"</div>';
else if ($row['width'])
    $ps .= '<div class="dimensions">' . $row['width'] . '" wide</div>';
else if ($row['height'])
    $ps .= '<div class="dimensions">' . $row['height'] . '" tall</div>';


//price

    $ps .= '<div class="price">Price: ';
    $show_price = '$' . $row['price'] . '<br /><font color="white">&nbsp</font></div>';
    $show_sale_price = '<s>$' . $row['price'] . '</s><br /><font class="onsale" color="#3fcec8">$'
			. $row['onsale'] . '</font></div>';
    $ps .= $row['onsale'] ? $show_sale_price : $show_price ;



if ($table_name == 'dresses' || $table_name == 'tshirts' || $table_name == 'sweaters' || 
	$table_name == 'gloves' || $table_name == 'scarves' || $table_name == 'dreamcatchers') {

    $ps .= '<hr />';
    $ps .= '<table class="size_color">';
/*
 * sections that have size
 */
if ($table_name == 'dresses' || $table_name == 'tshirts' || 
	$table_name == 'sweaters' || $table_name == 'gloves') {

    $ps .= '<tr><td align="right">Size: </td><td align="left">';

    if ($row['size']) {
	$size_list = explode(',' , $row['size']);
	$ps .= '<select id="sz' . $row['img'] . '">';
    	foreach ($size_list as $item)
	    $ps .= "<option>$item</option>";
    	$ps .= '</select>';
    }
    else
	$ps .= ' - - - -';

    $ps .= '</td></tr>';
}

// this is done so the "add to cart" button is even in all the products since they have different heights
else if ($filesec == 'index' || $filesec == 'home')
	$ps .= '<td style="height:8px"></td>';

/*
 * sections that have color
 */
if ($table_name == 'tshirts' || $table_name == 'scarves' || $table_name == 'dreamcatchers'){

    $ps .= '<tr><td align="right">Color: </td><td align="left">';

    if ($row['color']) {
	$color_list = explode(',' , $row['color']);
	$ps .= '<select id="cl' . $row['img'] . '">';
    	foreach ($color_list as $item)
	    $ps .= "<option>$item</option>";
    	$ps .= '</select>';
    }
    else
	$ps .= ' - - - -';

    $ps .= '</td></tr>';
}

// this is done so the "add to cart" button is even in all the products since they have different heights
else if ($filesec == 'index' || $filesec == 'home')
	$ps .= '<td style="height:8px"></td>';

    $ps .= '</table>';  //close the table from size and color

}  //if there're sizes and colors

// this is done so the "add to cart" button is even in all the products since they have different heights
else if ($filesec == 'index' || $filesec == 'home')
	$ps .= '<div style="height:48px"></div>';


//add to cart button
    $ps .= '<div class="addtocart"><input type="button" value="Add To Cart" ';

    $image_name = $row['img'];
    $size_value = "document.getElementById('sz$image_name').value";
    $color_value= "document.getElementById('cl$image_name').value";

    $ps .= "onclick=\"sendToCart('$table_name@$image_name@";		//table, image name
    $ps .= $row['onsale'] ? $row['onsale']."@'+" : $row['price']."@'+" ;//price
    $ps .= $row['size'] ? $size_value."+'@'+" : "'@'+" ;		//size selected
    $ps .= $row['color'] ? $color_value : "''" ;			//color selected
    $ps .= ")\" /></div></td>";

    $columns--;
}// while $row
}// foreach, go through the list
}// for, first time get the on sales

$ps .= '</tr></table>

<div id="copyright">Copyright <span onclick="javascript: location = \'php/control_panel.php\'">&copy</span> 2010 <b>Chayak Crafts</b>. All rights reserved</div>
</div class="display">';


echo $ps;

?>