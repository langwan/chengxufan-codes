<?php

/**
 * author: langwan@chengxufan.com
 * version: 2013.09.20.15.12
 *
 * resize image
 *
 * url src is http://www.chengxufan.com/img/abc
 * resize url is http://www.chengxufan.com/img/abc/200/200
 *
 * example:
 *
 * $thumb = new Class_Thumb();
 * $thumb->path($path)->resize($width, $height)->execute();
 * if($thumb->isError()) {
 *     die("resize img error.");
 * }
 * header(sprintf("Content-Type: %s", $thumb->header()));
 * echo $thumb->body();
 *
 *
 * note:
 * the Class_Tag_Store is demo, so you need to implement new one.
 * method:
 * get - get document tags.
 * remove - remove document tags and update count.
 * add - add document tags and update count.
 * make - make a new tag.
 *
 */

class Class_Thumb {
	
	private $force = false;
	private $src;
	private $info;
	private $im;
	private $size;
	private $thumbSize;
	private $error = false;

	public function force($b) {
		$this->force = $b;
		return $this;
	}

	public function resize($width, $height) {
		$this->limitWidth = $width;
		$this->limitHeight = $height;
		return $this;
	}

	public function path($path) {
		$this->path = $path;
		return $this;
	}

	public function execute() {
		
		if($this->limitWidth != 0 || $this->limitHeight != 0)
				$this->realPath = sprintf("%s_%s_%s", $this->path, $this->limitWidth, $this->limitHeight);
		else {
			$this->realPath = $this->path;
		}

		if($this->force != true) {
			if(file_exists($this->realPath)) {
				$info = getimagesize($this->realPath);
				if($info === false)
					$this->error = true;
				return;
			}
		}


		if(!$this->getSrcInfo()) {
			$this->error = true;
			return;
		}

		$this->init();
		$this->resizeImg();
	}

	public function isError() {
		return $this->error == true ? true : false;
	}

	public function resizeImg() {
	
		$this->thumbSize = $this->getThumbSize(array($this->limitWidth, $this->limitHeight));
		$new = imagecreatetruecolor($this->thumbSize[0], $this->thumbSize[1]);
	
		imagecopyresampled($new, $this->im, 0, 0, 0, 0, $this->thumbSize[0], $this->thumbSize[1], $this->info[0], $this->info[1]);
		$this->save($new);		
	}

	public function header() {
		return $this->info['mime'];
	}

	public function body() {
		return file_get_contents($this->realPath);
	}


	public function getSrcInfo() {

		$this->info = getimagesize($this->path);		
		return true;
	}

	public function init() {
		$mime = $this->info['mime'];
		if($mime == 'image/png') {
			$this->im = imagecreatefrompng($this->path);
		} else if($mime == 'image/jpeg') {
			$this->im = imagecreatefromjpeg($this->path);
		} else if($mime == 'image/gif') {
			$this->im = imagecreatefromgif($this->path);
		}
	}



	public function save($image) {
		$mime = $this->info['mime'];
		if($mime == 'image/png') {
			imagepng($image, $this->realPath);
		} else if($mime == 'image/jpeg') {
			imagejpeg($image, $this->realPath);
		} else if($mime == 'image/gif') {
			imagegif($image, $this->realPath);
		}
		ImageDestroy($image);
	}

	public function getThumbSize($size) {

		$width = $this->info[0];
		$height = $this->info[1];

		if($width <= $size[0] && $height <= $size[1]) {
			return array($width, $height);
		}

		$rw = $width / $size[0];
		$rh = $height / $size[1];
		$sw = true;

		if($rw < $rh) {
			$sw = false;
		}

		if($sw == true) {
			$w = $size[0];
			$h = floor($height / $rw);
		} else {
			$h = $size[1];
			$w = floor($width / $rh);
		}

		return array($w, $h);
	}

}