<?


	/******************************************************************************************************
	*** Package: AVA-Panel Version 3.0
	*** Copyright (c) 2006, Anton A. Rassypaeff. All rights reserved
	*** License: GNU General Public License v3
	*** Author: Anton A. Rassypaeff | Рассыпаев Антон Александрович
	*** Contacts:
	***   Site: http://ava-panel.ru
	***   E-mail: manage@ava-panel.ru
	******************************************************************************************************/


class Image extends objectInterface{
	/*
		Работа с изображениями
	*/

	private $image;
	private $colors = array();
	private $ext;

	public function __ava__createImage($imagePath){
		/*
			Считывает изображение
		*/

		if(!file_exists($imagePath)) throw new AVA_Files_Exception('{Call:Lang:core:core:fajlnenajden:'.Library::serialize(array($imagePath)).'}');
		$this->ext = regExp::lower(Files::getExtension($imagePath));

		switch($this->ext){
			case '.gif':
				$func = 'imagecreatefromgif';
				break;

			case '.jpg':
			case '.jpeg':
				$func = 'imagecreatefromjpeg';
				break;

			case '.png':
				$func = 'imagecreatefrompng';
				break;

			case '.bmp':
				$func = 'imagecreatefromwbmp';
				break;

			default:
				throw new AVA_Files_Exception('{Call:Lang:core:core:rabotasizobr:'.Library::serialize(array($this->ext)).'}');
		}

		if(!function_exists($func)) throw new AVA_Files_Exception('{Call:Lang:core:core:rabotasizobr:'.Library::serialize(array($this->ext)).'}');
		$this->image = $func($imagePath);
	}

	public function __ava__createEmptyImage($x, $y, $col = false){
		/*
			Создает пустое изображение
		*/

		$this->image = imagecreatetruecolor($x, $y);
		$this->x = $x;
		$this->y = $y;

		if($col){
			imagefill($this->image, 0, 0, $this->getColor($col));
		}
	}

	public function __ava__write($str, Font $font, $x = 0, $y = 0, $h = 'l', $v = 't', $deformation = 0){
		/*
			Пишет строку на картинке
		*/

		if($h == 'r') $x = $this->getX() - $x;
		if($v == 'b') $y = $this->getY() - $y;

		switch($font->getType()){
			case 'ttf':
				$transpStep = round((100 - $font->getTransparency()) / ($deformation + 1));

				for($i = $deformation; $i > 0; $i --){
					imagettftext(
						$this->image,
						$font->getSize() + $i,
						$font->getAngle(),
						$x - round($i / 2),
						$y + round($i / 2),
						$this->getColor($font->getColor(), $font->getTransparency() + ($transpStep * ($i))),
						$font->getFont(),
						$str
					);
				}

				return imagettftext($this->image, $font->getSize(), $font->getAngle(), $x, $y, $this->getColor($font->getColor(), $font->getTransparency()), $font->getFont(), $str);

			case 'default': imagestring($this->image, $font->getSize(), $x, $y, $str, $this->getColor($font->getColor(), $font->getTransparency()));
			default: throw new AVA_Files_Exception('{Call:Lang:core:core:rabotasoshri:'.Library::serialize(array($font->getType())).'}');
		}
	}

	public function __ava__getBox($str, $font){
		/*
			Возвращает ширину и высоту прорисовываемой строки
		*/

		$return = array();

		switch($font->getType()){
			case 'ttf':
				$p = imageTTFBBox($font->getSize(), $font->getAngle(), $font->getFont(), $str);

				$l = ($p[0] < $p[6]) ? $p[0] : $p[6];
				$r = ($p[2] > $p[4]) ? $p[2] : $p[4];
				$t = ($p[5] < $p[7]) ? $p[5] : $p[7];
				$b = ($p[1] > $p[3]) ? $p[1] : $p[3];

				$return = array($r - $l, $b - $t);
				break;

			case 'default': return '';
			default: throw new AVA_Files_Exception('{Call:Lang:core:core:rabotasoshri:'.Library::serialize(array($font->getType())).'}');
		}

		return $return;
	}

	public function __ava__rotate($angle, $color = '000', $transparent = true){
		/*
			Поворачивает изображение
			Если цвет - false, незакрытые области закрываются transparent цветомъ
		*/

		$this->image = imagerotate($this->image, $angle, $this->getColor($color));
		if($transparent) $this->transparentColor($color);
	}

	public function __ava__resizeImageWhichMore($wh, $ht, $l = 0, $t = 0, $enlarge = true){
		/*
			Производит ресайз изображения смотря что больше подходит - высота или ширина
		*/

		$x = $this->getX() - $l;
		$y = $this->getY() - $t;
		$pwh = $x / $wh;
		$pht = $y / $ht;

		if($pwh > $pht) $ht = (int)($y / $pwh);
		else $wh = (int)($x / $pht);

		return $this->resizeImage($wh, $ht, $l, $t, $enlarge);
	}

	public function __ava__resizeImageWidth($wh, $l = 0, $t = 0, $enlarge = true){
		/*
			Ресайз с ориентацией по ширине
		*/

		$x = $this->getX() - $l;
		$y = $this->getY() - $t;
		$pwh = $x / $wh;
		return $this->resizeImage($wh, (int)($y / $pwh), $l, $t, $enlarge);
	}

	public function __ava__resizeImageHeight($ht, $l = 0, $t = 0, $enlarge = true){
		/*
			Ресайз с ориентацией по высоте
		*/

		$x = $this->getX() - $l;
		$y = $this->getY() - $t;
		$pht = $y / $ht;
		return $this->resizeImage((int)($x / $pht), $ht, $l, $t, $enlarge);
	}

	public function __ava__resizeImage($wh, $ht, $l = 0, $t = 0, $enlarge = true){
		/*
			Уменьшает (увеличивет) изображение
		*/

		if(!$enlarge && $this->getX() < $wh && $this->getY() < $ht) return true;
		$dst = imagecreatetruecolor($wh, $ht);
		if($return = imagecopyresampled($dst, $this->image, 0, 0, $l, $t, $wh, $ht, $this->getX(), $this->getY())) $this->image = $dst;
		return $return;
	}

	public function __ava__cutImage($x, $y, $wh, $ht){
		/*
			Вырезает прямоугольник из изображения и создает новое
		*/

		$dst = imagecreatetruecolor($wh, $ht);
		if($return = imagecopy($dst, $this->image, 0, 0, $x, $y, $wh, $ht)) $this->image = $dst;
		return $return;
	}

	public function __ava__resizeAndSliceImage($wh, $ht, $h = 'l', $v = 't', $enlarge = true){
		/*
			Уменьшает изображение, затем производит его вырезку
		*/

		$wh2 = $wh;
		$ht2 = $ht;

		$x = $this->getX();
		$y = $this->getY();
		$pwh = $x / $wh2;
		$pht = $y / $ht2;

		if($pwh < $pht) $ht2 = (int)($y / $pwh);
		else $wh2 = (int)($x / $pht);
		$this->resizeImage($wh2, $ht2, 0, 0, $enlarge);
		$dst = imagecreatetruecolor($wh, $ht);

		if($h == 'l') $l = 0;
		elseif($h == 'c') $l = $wh2 / 2 - $wh / 2;
		elseif($h == 'r') $l = $wh2 - $wh;

		if($v == 't') $t = 0;
		elseif($v == 'c') $t = $ht2 / 2 - $ht / 2;
		elseif($v == 'b') $t = $ht2 - $ht;

		imagecopymerge($dst, $this->image, 0, 0, $l, $t, $wh, $ht, 100);
		$this->image = $dst;
		return true;
	}

	public function __ava__drawPolygon($corners, $col1, $col2 = false){
		/*
			Рисует многоугольник с рамкой цвета col1 и заливкой col2
		*/

		$points = array();
		foreach($corners as $i => $e){
			$points[] = $e['x'];
			$points[] = $e['y'];
		}

		imagefilledpolygon($this->image, $points, count($corners), $this->getColor($col1));
	}

	public function __ava__innerImageByFile($file, $x = 0, $y = 0, $h = 'l', $v = 't', $transparency = 0, $angle = 0){
		/*
			Накладывает изображение взятое из файла
		*/

		$img = new Image;
		$img->createImage($file);
		return $this->innerImage($img, $x, $y, $h, $v, $transparency, $angle);
	}

	public function __ava__innerImage(Image $img, $x = 0, $y = 0, $h = 'l', $v = 't', $transparency = 0, $angle = 0){
		/*
			Накладывает изображение
		*/

		if($h == 'r') $x = $this->getX() - $x;
		if($v == 'b') $y = $this->getY() - $y;
		if($angle) $img->rotate($angle);
		imagecopymerge($this->image, $img->getImageInstance(), $x, $y, 0, 0, $img->getX(), $img->getY(), 100 - $transparency);
	}

	public function __ava__getColor($color, $transparent = 0){
		/*
			Создает цвет по его HTML-индексу
		*/

		$color = strtolower($color);
		$transparent = round($transparent * 1.27);

		if(!isset($this->colors[$color][$transparent])){
			$r = 0;
			$g = 0;
			$b = 0;

			switch($color){
				case 'red':
					$r = 221;
					$g = 0;
					$b = 0;
					break;

				case 'green':
					$r = 0;
					$g = 221;
					$b = 0;
					break;

				case 'blue':
					$r = 0;
					$g = 0;
					$b = 221;
					break;

				case 'white':
					$r = 255;
					$g = 255;
					$b = 255;
					break;

				case 'black':
					$r = 0;
					$g = 0;
					$b = 0;
					break;

				case 'yellow':
					$r = 255;
					$g = 255;
					$b = 0;
					break;

				default:
					if(regExp::Len($color) == '3'){
						$r = hexdec($color['0'].$color['0']);
						$g = hexdec($color['1'].$color['1']);
						$b = hexdec($color['2'].$color['2']);
					}
					elseif(regExp::Len($color) == '6'){
						$r = hexdec($color['0'].$color['1']);
						$g = hexdec($color['2'].$color['3']);
						$b = hexdec($color['4'].$color['5']);
					}
					else{
						throw new AVA_Exception('{Call:Lang:core:core:nekorrektnyj2:'.Library::serialize(array($color)).'}');
					}
			}

			$this->colors[$color][$transparent] = imagecolorallocatealpha($this->image, $r, $g, $b, $transparent);
		}

		return $this->colors[$color][$transparent];
	}

	public function __ava__transparentColor($color){
		imagecolortransparent($this->image, $this->getColor($color));
	}

	public function getX(){
		return imagesx($this->image);
	}

	public function getY(){
		return imagesy($this->image);
	}

	public function getImageInstance(){
		return $this->image;
	}

	public function getCT(){
		/*
			Возвращает Content-type для этого расширения
		*/

		return Files::getCT($this->ext);
	}

	public function __ava__flushImage($file = '', $quality = 100, $ext = false){
		/*
			Отправляет изображение на вывод
		*/

		if(!$ext) $ext = $this->ext;
		switch($ext){
			case '.gif':
				$func = 'imagegif';
				break;

			case '.jpg':
			case '.jpeg':
				$func = 'imagejpeg';
				break;

			case '.png':
				$func = 'imagepng';
				break;

			case '.bmp':
				$func = 'imagewbmp';
				break;

			default:
				throw new AVA_Files_Exception('{Call:Lang:core:core:rabotasizobr:'.Library::serialize(array($this->ext)).'}');
		}

		if(!function_exists($func)) throw new AVA_Files_Exception('{Call:Lang:core:core:rabotasizobr:'.Library::serialize(array($this->ext)).'}');
		if($file){
			if(Files::isWritable($file)) return $func($this->image, $file, $quality);
			else{
				$tmpFile = TMP.Library::inventStr(16).Files::getExtension($file);
				$func($this->image, $tmpFile, $quality);
				return $GLOBALS['Core']->ftpCopy($tmpFile, $file);
			}
		}
		else $func($this->image, $file, $quality);
	}
}

?>