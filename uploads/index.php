<?php
set_time_limit(0);
include_once '..' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . "config.php";
if (! (preg_match_all('/uploads\/(.*)/', $_SERVER['REQUEST_URI'], $match_all) && sizeof($match_all) >= 2)) {
    header("HTTP/1.0 404 Not Found");
    exit();
}
$file = $match_all[1][0];
if (! (preg_match_all('/(.*)_(\d+)x(\d+).(.*)/', $file, $match_all) && sizeof($match_all) >= 5)) {
    header("HTTP/1.0 404 Not Found");
    exit();
}
$ext = $match_all[4][0];
$srcFile = $match_all[1][0] . '.' . $ext;
$width = $match_all[2][0];
$height = $match_all[3][0];
if (! in_array(strtolower($ext), explode(',', UPLOAD_IMAGE_EXT_NAMES))) {
    header("HTTP/1.0 404 Not Found");
    exit();
}
if (! resize_image($srcFile, $file, $width, $height)) {
    header("HTTP/1.0 404 Not Found");
    exit();
}

header("Location: {$_SERVER['REQUEST_URI']}");

function resize_image($img_src, $new_img_path, $new_width, $new_height)
{
    $img_info = @getimagesize($img_src);
    if (! $img_info || $img_info[0] < 0 || $img_info[1] < 0 || empty($new_img_path)) {
        return false;
    }
    $size_src = $img_info;
    $w = $size_src['0'];
    $h = $size_src['1'];
    if (strpos($img_info['mime'], 'jpeg') !== false) {
        $pic_obj = imagecreatefromjpeg($img_src);
    } else 
        if (strpos($img_info['mime'], 'gif') !== false) {
            $pic_obj = imagecreatefromgif($img_src);
        } else 
            if (strpos($img_info['mime'], 'png') !== false) {
                $pic_obj = imagecreatefrompng($img_src);
            } else {
                return false;
            }
    
    if ($new_width > $img_info['0'] || $new_height > $img_info['1']) {} else 
        if ($new_width == 0 || $new_height == 0) {
            if ($new_width == 0 && $new_height > 0) {
                $max = $new_height;
                $h = $max;
                $w = $w * ($max / $size_src['1']);
            } else 
                if ($new_height == 0 && $new_width > 0) {
                    $max = $new_width;
                    $w = $max;
                    $h = $h * ($max / $size_src['0']);
                } else {
                    return false;
                }
        } else {
            $w = $new_width;
            $h = $new_height;
        }
    
    $image = imagecreatetruecolor($w, $h);
    imagecopyresampled($image, $pic_obj, 0, 0, 0, 0, $w, $h, $size_src['0'], $size_src['1']);
    $jpgQuality = 75;
    $pngQuality = 5;
    if (preg_match('~.([^.]+)$~', $new_img_path, $match)) {
        $new_type = strtolower($match[1]);
        switch ($new_type) {
            case 'jpg':
                imagejpeg($image, $new_img_path, $jpgQuality);
                break;
            case 'gif':
                imagegif($image, $new_img_path);
                break;
            case 'png':
                $pimage = $pic_obj;
                imagesavealpha($pimage, true);
                $thumb = imagecreatetruecolor($w, $h);
                imagealphablending($thumb, false);
                imagesavealpha($thumb, true); 
                imagecopyresampled($thumb,$pimage,0,0,0,0,$w,$h,$size_src['0'],$size_src['1']);
                imagepng($thumb, $new_img_path,$pngQuality);
                imagedestroy($thumb);
                break;
            default:
                imagejpeg($image, $new_img_path, $jpgQuality);
        }
    } else {
        imagejpeg($image, $new_img_path, $jpgQuality);
    }
    imagedestroy($image);
    imagedestroy($pic_obj);
    return true;
}

?>