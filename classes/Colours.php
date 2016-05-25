<?php
/**
 * @author Petri Haikonen <sharkmachine@ecxol.net>
 * @package MyLittleWallpaper
 * @subpackage Classes
 */

// Check that correct entry point was used
if (!defined('INDEX'))
	exit();

/**
 * Class for calculating common image colours.
 */
class GetCommonColours {
	private $PREVIEW_WIDTH = 768;
	private $PREVIEW_HEIGHT = 768;

	private $colours = [
		// Group #1 (black - dark gray)
		'000000' => 1,
		'333333' => 1,

		// Group #2 (light gray - white)
		'666666' => 2,
		'cccccc' => 2,
		'ffffff' => 2,

		// Group #3 (dark red)
		'330000' => 3,
		'550000' => 3,
		'990000' => 3,
		'552b2b' => 3,

		// Group #4 (light red)
		'ff0000' => 4,
		'ff6666' => 4,
		'ffaaaa' => 4,
		'aa5555' => 4,
		'c89797' => 4,

		// Group #5 (dark brown)
		'331c12' => 5,
		'542e1d' => 5,
		'7f462c' => 5,
		'55402b' => 5,

		// Group #6 (light brown)
		'b3623e' => 6,
		'ff8d58' => 6,
		'ffbc9e' => 6,
		'aa8056' => 6,
		'c8b098' => 6,

		// Group #7 (dark orange)
		'341a00' => 7,
		'562a00' => 7,
		'9a4c00' => 7,

		// Group #8 (light orange)
		'ff7f01' => 8,
		'feb268' => 8,
		'ffd5ab' => 8,

		// Group #9 (dark yellow)
		'333300' => 9,
		'555500' => 9,
		'aaaa00' => 9,
		'555533' => 9,

		// Group #10 (light yellow)
		'ffff00' => 10,
		'ffff99' => 10,
		'aaaa55' => 10,
		'dbdc99' => 10,

		// Group #11 (dark green-yellow)
		'183400' => 11,
		'285600' => 11,
		'489a00' => 11,
		'3f552b' => 11,

		// Group #12 (light green-yellow)
		'79ff01' => 12,
		'affe68' => 12,
		'd3ffab' => 12,
		'7eaa56' => 12,
		'afc898' => 12,

		// Group #13 (dark green)
		'003300' => 13,
		'005500' => 13,
		'00aa00' => 13,
		'2b552b' => 13,

		// Group #14 (light green)
		'00ff00' => 14,
		'99ff99' => 14,
		'55aa55' => 14,
		'97c897' => 14,

		// Group #15 (dark pine green)
		'00341a' => 15,
		'00562a' => 15,
		'009a4c' => 15,
		'2b5540' => 15,

		// Group #16 (light pine green)
		'01ff7f' => 16,
		'68feb2' => 16,
		'abffd5' => 16,
		'56aa80' => 16,
		'98c8b0' => 16,

		// Group #17 (light pine green)
		'003433' => 17,
		'005655' => 17,
		'009a98' => 17,
		'2b5555' => 17,

		// Group #18 (light pine green)
		'01fffc' => 18,
		'68fefc' => 18,
		'abfffe' => 18,
		'56aaa9' => 18,
		'98c8c7' => 18,

		// Group #19 (dark denim)
		'001b34' => 19,
		'002d56' => 19,
		'00509a' => 19,
		'2b4055' => 19,

		// Group #20 (light denim)
		'0184ff' => 20,
		'68b6fe' => 20,
		'abd6ff' => 20,
		'5681aa' => 20,
		'98b1c8' => 20,

		// Group #21 (dark blue)
		'000044' => 21,
		'000088' => 21,
		'2b2b55' => 21,
		'0000ff' => 21,

		// Group #22 (light blue)
		'6666ff' => 22,
		'aaaaff' => 22,
		'5555aa' => 22,
		'9797c8' => 22,

		// Group #23 (dark purple)
		'180034' => 23,
		'280056' => 23,
		'48009a' => 23,
		'402b55' => 23,

		// Group #24 (light purple)
		'7901ff' => 24,
		'af68fe' => 24,
		'd3abff' => 24,
		'7e56aa' => 24,
		'af98c8' => 24,

		// Group #25 (dark red-violet)
		'320034' => 25,
		'530056' => 25,
		'95009a' => 25,
		'552b55' => 25,

		// Group #26 (light red-violet)
		'f601ff' => 26,
		'f868fe' => 26,
		'fcabff' => 26,
		'a756aa' => 26,
		'c698c8' => 26,

		// Group #27 (dark rose)
		'34001c' => 27,
		'56002f' => 27,
		'9a0053' => 27,
		'552b40' => 27,

		// Group #28 (light rose)
		'ff018a' => 28,
		'fe68b9' => 28,
		'ffabd8' => 28,
		'aa5683' => 28,
		'c898b2' => 28,
	];

	/**
	 * Not used?
	 * @var string
	 */
	public $error;

	/**
	 * Returns the colors of the image in an array, ordered in descending order, where the keys are the colors, and the values are the count of the color.
	 * @param string $img image path
	 * @return array
	 */
	public function getColours($img) {
		$tempName = ROOT_DIR . 'temp/' . uniqid() . '.gif';
		exec("convert " . escapeshellarg($img) . " -resize " . $this->PREVIEW_WIDTH . "x" . $this->PREVIEW_HEIGHT . "\> -dither None -remap " . ROOT_DIR . "classes/palette.gif " . $tempName);
		$size = getimagesize($tempName);
		if ($size[2] == 1)
			$im = imagecreatefromgif($tempName);
		elseif ($size[2] == 2)
			$im = imagecreatefromjpeg($tempName);
		elseif ($size[2] == 3)
			$im = imagecreatefrompng($tempName);

		if (!empty($im)) {
			$imgWidth = imagesx($im);
			$imgHeight = imagesy($im);
			$total_pixel_count = 0;
			$colourList = [];
			$tempGroups = [];
			for ($y = 0; $y < $imgHeight; $y++) {
				for ($x = 0; $x < $imgWidth; $x++) {
					$total_pixel_count++;
					$index = imagecolorat($im, $x, $y);
					$colors = imagecolorsforindex($im, $index);
					$hexCode = strtolower(str_pad(dechex($colors['red']), 2, '0', STR_PAD_LEFT) . str_pad(dechex($colors['green']), 2, '0', STR_PAD_LEFT) . str_pad(dechex($colors['blue']), 2, '0', STR_PAD_LEFT));
					if (!isset($colourList[$hexCode])) {
						$colourList[$hexCode] = 1;
					} else {
						$colourList[$hexCode]++;
					}
					if (isset($this->colours[$hexCode])) {
						if (!isset($tempGroups[$this->colours[$hexCode]])) {
							$tempGroups[$this->colours[$hexCode]] = 1;
						} else {
							$tempGroups[$this->colours[$hexCode]]++;
						}
					}
				}
			}
			imagedestroy($im);
			natcasesort($colourList);
			$colourList = array_reverse($colourList, true);
			natcasesort($tempGroups);
			$tempGroups = array_reverse($tempGroups, true);
			$groups = [];
			foreach ($tempGroups as $key => $val) {
				if ($val / $total_pixel_count >= 0.0075) {
					$groups[$key] = ['percent' => round($val / $total_pixel_count * 100, 2), 'pixels' => $val, 'colours' => []];
				}
			}
			foreach ($colourList as $colour => $amount) {
				if (isset($this->colours[$colour])) {
					if (isset($groups[$this->colours[$colour]])) {
						$groups[$this->colours[$colour]]['colours'][$colour] = $amount;
					}
				}
			}
			foreach ($groups as $gk => $gv) {
				natcasesort($groups[$gk]['colours']);
				$groups[$gk]['colours'] = array_reverse($groups[$gk]['colours'], true);
			}
			unlink($tempName);
			return $groups;
		} else trigger_error('No image!');
		unlink($tempName);
		return false;
	}
}