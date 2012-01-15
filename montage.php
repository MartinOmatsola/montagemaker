<?php
/**
* Author: Martin Okorodudu
*/
require_once("utils.php");

class Montage {
	
	var $src_img;
	var $cell_img_url_array;
	var $cell_w;
	var $cell_h;
	var $montage_w;
	var $montage_h;
	var $percent; //reduction factor used by processImages()

	function Montage($src_img, $cell_img_url_array, $cell_w, $cell_h, $montage_w, $montage_h, $percent) {
		if (!is_numeric($cell_w) || !is_numeric($cell_h) || !is_numeric($montage_w) || !is_numeric($montage_h) || !is_numeric($percent)) {
			die("Error: non numeric input to montage constructor");
		}
		$this->src_img = $src_img;
		$this->cell_img_url_array = $cell_img_url_array;
		$this->cell_w = $cell_w;
		$this->cell_h = $cell_h;
		$this->montage_w = $montage_w;
		$this->montage_h = $montage_h;
		$this->percent = $percent;
	}
		
	function processImages() {
		$len = sizeof($this->cell_img_url_array);
		$result = array();
		
		//compute avg value of all images
		for ($i = 0; $i < $len; $i++) {
			$cell_imgurl = $this->cell_img_url_array[$i];
			$cell_img = myImageCreate($cell_imgurl);
			if (!$cell_img) {
				continue;
			}
	
			$w = imagesx($cell_img);
			$h = imagesy($cell_img);
			
			$new_w = $w * $this->percent;
			$new_h = $h * $this->percent;
	
			$tmp_img = imagecreatetruecolor($new_w, $new_h);
	
			//resize image to reduce processing time
			imagecopyresampled($tmp_img, $cell_img, 0, 0, 0, 0, $new_w, $new_h, $w, $h);
			
			$avg = round(computeAvgGrayscale($tmp_img));
			
			//store resized image
			$img = imagecreatetruecolor($this->cell_w, $this->cell_h);
			imagecopyresampled($img, $cell_img, 0, 0, 0, 0, $this->cell_w, $this->cell_h, $w, $h);	
			
			$result[$avg] = $img;
			imagedestroy($cell_img);
			imagedestroy($tmp_img);
		}
		return $result;	
	}

	function makeMontage($cell_img_array) {
		
		//make sure source image height and width are multiples of cell height and width
		$new_src_w = ($new_src_w % $cell_w ) + $new_src_w;
		$new_src_h = ($new_src_w % $cell_h ) + $new_src_h;
		
		$montage = imagecreatetruecolor($this->montage_w, $this->montage_h);
		$w = imagesx($this->src_img);
		$h = imagesy($this->src_img); 
		imagecopyresampled($montage, $this->src_img, 0, 0, 0, 0, $this->montage_w, $this->montage_h, $w, $h);
		
		//no longer useful
		imagedestroy($this->src_img);
		
		//stores templates from src image
		$tmp = imagecreatetruecolor($this->cell_w, $this->cell_h);
	
		//sort keys (avg values) for use in main loop
		$keys = array_keys($cell_img_array);
		sort($keys);
		
		//process cells
		for ($i = 0; $i < $this->montage_w; $i = $i + $this->cell_w) {
			for ($j = 0; $j < $this->montage_h; $j = $j + $this->cell_h) {
				//compute avg value of template
				imagecopy($tmp, $montage, 0, 0, $i, $j, $this->cell_w, $this->cell_h);
				$avg = round(computeAvgGrayscale($tmp));		
				
				//did n't get a hit
				if (empty($cell_img_array[$avg])) {
					
					//find closest
					if ($avg < $keys[0]) {
						$avg = $keys[0];
					} elseif ($avg > $keys[sizeof($keys) - 1]) {
						$avg = $keys[sizeof($keys) - 1];
					} else {
						for ($x = 1; $x < sizeof($keys); $x++) {
							$down = $keys[$x - 1];
							$up = $keys[$x]; 
							if ($down < $avg and $up > $avg) {
								//either round down or up, i choose up
								$avg = $up;
								break;
							}
						}
					}
				} 
				imagecopy($montage, $cell_img_array[$avg], $i, $j, 0, 0, $this->cell_w, $this->cell_h);
			}
		}
		imagedestroy($tmp);
		return $montage;
	}

	function showMontage() {
		$img_array = $this->processImages();
		$montage = $this->makeMontage($img_array);
		unset($img_array);
		imagejpeg($montage);
		imagedestroy($montage);
	}

	function getMontage() {
		$img_array = $this->processImages();
		$montage = $this->makeMontage($img_array);
		unset($img_array);
		return $montage;
	}
}
?>
