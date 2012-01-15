<?php

require_once 'montage.php';

$cells = array();
	
$dh = opendir('cell-images/');
while (($file = readdir($dh)) !== false) {
	if (!is_dir($file)) {
		$cells[] = 'cell-images/' . $file;
	}
}
closedir($dh);

$img = myImageCreate('lara.jpg');

$cell_w = 10;
$cell_h = 10;
$montage_w = 1000;
$montage_h = 750;

$montage = new Montage($img, $cells, $cell_w, $cell_h, $montage_w, $montage_h, 0.1);
$img = $montage->getMontage();

header('Content-type: image/png');

imagepng($img);

imagedestroy($img);
unset($cells);
?>
