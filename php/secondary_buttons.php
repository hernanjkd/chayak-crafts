<?php

$apparel_list = array('tshirts','sweaters','dresses');
$apparel_width = array('padding' => '150','tshirts' => '100','sweaters' => '130','dresses' => '100');

$accessories_list = array('hats','gloves','scarves','blankets','jewelry');
$accessories_width = array('padding' => '150','hats' => '70','gloves' => '100','scarves' => '100','blankets' => '110','jewelry' => '100');

$craft_list = array('paintings','dolls','mandalas','dreamcatchers');
$craft_width = array('padding' => '40','paintings' => '130','dolls' => '270','mandalas' => '130','dreamcatchers' => '100');

if (PAGE_IMAGE == 'apparel' || PAGE_IMAGE == 'accessories' || PAGE_IMAGE == 'craft') {

    if (PAGE_IMAGE == 'apparel') {
		$list = $apparel_list;
		$width = $apparel_width;
    }
    if (PAGE_IMAGE == 'accessories') {
		$list = $accessories_list;
		$width = $accessories_width;
    }
    if (PAGE_IMAGE == 'craft') {
		$list = $craft_list;
		$width = $craft_width;
    }

    $b2 = '<table id="sub2" style="padding-left:'. $width['padding'] .'px"><tr>';

    foreach($list as $item) {

		$off = false;
		if ($item == $filesec)
			$off = true;

		$b2 .= '
<td width="'. $width[$item] .'px" style="vertical-align: middle">
<img class="sub2" src="images/build/'. $item .'.png" ';

		if (!$off)
			$b2 .= '
	onmouseover="show(\''. $item .'2\')"
	onmouseout="hide(\''. $item .'2\')" ';
	
		$b2 .= '/>
<img id="'. $item .'2" class="sub2hidden" src="images/build/'. $item .'line.png" ';

		if (!$off)
			$b2 .= '
	onmouseover="show(\''. $item .'2\')"
	onmouseout="hide(\''. $item .'2\')"
	onclick="javascript: location = \''. $item .'.php\'" ';
		else
			$b2 .= 'style="visibility: visible;cursor: default" ';
	
	
		$b2 .= '/>
</td>';

		if ($item != 'dresses' && $item != 'jewelry' && $item != 'dreamcatchers')
	    	$b2 .= '
<td width="20px">
<img src="images/build/line.png" />
</td>';
    }//end of foreach

    $b2 .= '</tr></table>
    
    		';

    echo $b2;

}//end of if

?>