<?php

$query = "SELECT * FROM " . TABLE_NAME . " ORDER BY onsale DESC";

$result = @mysql_query($query);

$ps = '<div class="display"><table class="display"><tr>';

//Number of products per row
define('PRODUCTS_PER_ROW', 4);

$columns = PRODUCTS_PER_ROW;

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



if (TABLE_NAME == 'dresses' || TABLE_NAME == 'tshirts' || TABLE_NAME == 'sweaters' || 
	TABLE_NAME == 'gloves' || TABLE_NAME == 'scarves' || TABLE_NAME == 'dreamcatchers') {

    $ps .= '<hr />';
    $ps .= '<table class="size_color">';
/*
 * sections that have size
 */
if (TABLE_NAME == 'dresses' || TABLE_NAME == 'tshirts' || 
	TABLE_NAME == 'sweaters' || TABLE_NAME == 'gloves') {

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
/*
 * sections that have color
 */
if (TABLE_NAME == 'tshirts' || TABLE_NAME == 'scarves' || TABLE_NAME == 'dreamcatchers') {

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
    $ps .= '</table>';  //close the table from size and color

}  //if there're sizes and colors



//add to cart button
    $ps .= '<div class="addtocart"><input type="button" value="Add To Cart" ';

    $table_name = TABLE_NAME;
    $image_name = $row['img'];
    $size_value = "document.getElementById('sz$image_name').value";
    $color_value= "document.getElementById('cl$image_name').value";

    $ps .= "onclick=\"sendToCart('$table_name@$image_name@";		//table, image name
    $ps .= $row['onsale'] ? $row['onsale']."@'+" : $row['price']."@'+" ;//price
    $ps .= $row['size'] ? $size_value."+'@'+" : "'@'+" ;		//size selected
    $ps .= $row['color'] ? $color_value : "''" ;			//color selected
    $ps .= ")\" /></div></td>";

    $columns--;
}

$ps .= '</tr></table>
<div id="copyright">Copyright <span onclick="javascript: location = \'php/control_panel.php\'">&copy</span> 2010 <b>Chayak Crafts</b>. All rights reserved</div>
</div class="display">';


echo $ps;

?>