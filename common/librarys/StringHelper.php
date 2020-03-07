<?php
namespace common\librarys;


class StringHelper
{
    public static function matchContentImage($content){
        $images = [];
        preg_match_all('/<img .*?src=["|\'](.*?)["|\'].*?>/', $content, $matchs);
        if ($matchs) {
            $images = $matchs[1];
        }
        return $images;
    }
    
    public static function generateContentDesc($content,$max){
        return mb_substr(strip_tags(htmlspecialchars_decode($content)), 0, $max, ENV_CHARSET);
    }
    
    public static function getChineseWeek()
    {
        $week = array(
            "星期日",
            "星期一",
            "星期二",
            "星期三",
            "星期四",
            "星期五",
            "星期六"
        );
        return $week[date("w")];
    }

    public static function getDate($time)
    {
        if (! $time) {
            return '';
        }
        if (Date('Y-m-d', $time) == Date('Y-m-d')) {
            return Date('H:i', $time);
        } else 
            if (Date('Y', $time) == Date('Y')) {
                return Date('m-d H:i', $time);
            }
        return Date('Y-m-d H:i', $time);
    }
    
    public static function money($money){
        return floatval($money);
    }

    public static function numberUnit($number)
    {
        if ($number < 10000) {
            return $number;
        } else 
            if ($number < 100000000) {
                $unit = "万";
                $opUnit = 10000;
            } else {
                $unit = "亿";
                $opUnit = 100000000;
            }
        
        $number = sprintf("%.1f", $number / $opUnit);
        if (ceil($number) == $number) {
            $number = (int) $number;
        }
        return $number . $unit;
    }
    
    public static function omit($value, $end, $text = "..", $code = 'utf-8')
    {
        $end += 2;
        return mb_strimwidth($value, 0, $end, $text, $code);
    }

    public static function omitArticl($content, $maxTextLength = 300, $maxLine = 6)
    {
        $text = "";
        while (true) {
            $lineIndex = strpos($content, '<br/>');
            if ($lineIndex != null) {
                $lineIndex += 5;
            }
            $text .= $lineIndex ? substr($content, 0, $lineIndex) : substr($content, 0);
            $textLength = mb_strlen(strip_tags($text), ENV_CHARSET);
            if ($textLength < $maxTextLength) {
                if (substr_count($text, '<br/>') >= $maxLine) {
                    break;
                }
                $content = $lineIndex ? substr($content, $lineIndex) : "";
                if ($content == "") {
                    break;
                }
            } else {
                break;
            }
        }
        return $text;
    }
    
    public static function getContentImage($content)
    {
        $images = [];
        preg_match_all('/<img .*?src=["|\'](.*?)["|\'].*?>/', $content, $matchs);
        if ($matchs) {
            $images = $matchs[1];
        }
        return $images;
    }
    
    public static function getFirstChar($s0)
    {
        $firstchar_ord = ord(strtoupper($s0{0}));
        if (($firstchar_ord >= 65 and $firstchar_ord <= 91) or ($firstchar_ord >= 48 and $firstchar_ord <= 57))
            return $s0{0};
        $s = iconv("UTF-8", "gb2312", $s0);
        $asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
        if ($asc >= - 20319 and $asc <= - 20284)
            return "A";
        if ($asc >= - 20283 and $asc <= - 19776)
            return "B";
        if ($asc >= - 19775 and $asc <= - 19219)
            return "C";
        if ($asc >= - 19218 and $asc <= - 18711)
            return "D";
        if ($asc >= - 18710 and $asc <= - 18527)
            return "E";
        if ($asc >= - 18526 and $asc <= - 18240)
            return "F";
        if ($asc >= - 18239 and $asc <= - 17923)
            return "G";
        if ($asc >= - 17922 and $asc <= - 17418)
            return "H";
        if ($asc >= - 17417 and $asc <= - 16475)
            return "J";
        if ($asc >= - 16474 and $asc <= - 16213)
            return "K";
        if ($asc >= - 16212 and $asc <= - 15641)
            return "L";
        if ($asc >= - 15640 and $asc <= - 15166)
            return "M";
        if ($asc >= - 15165 and $asc <= - 14923)
            return "N";
        if ($asc >= - 14922 and $asc <= - 14915)
            return "O";
        if ($asc >= - 14914 and $asc <= - 14631)
            return "P";
        if ($asc >= - 14630 and $asc <= - 14150)
            return "Q";
        if ($asc >= - 14149 and $asc <= - 14091)
            return "R";
        if ($asc >= - 14090 and $asc <= - 13319)
            return "S";
        if ($asc >= - 13318 and $asc <= - 12839)
            return "T";
        if ($asc >= - 12838 and $asc <= - 12557)
            return "W";
        if ($asc >= - 12556 and $asc <= - 11848)
            return "X";
        if ($asc >= - 11847 and $asc <= - 11056)
            return "Y";
        if ($asc >= - 11055 and $asc <= - 10247)
            return "Z";
        return null;
    }
    
}

?>
