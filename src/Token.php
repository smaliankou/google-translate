<?php

namespace GoogleTranslate;

class Token
{
    private static $tkkDefault = "412320.3361919554";

    public static function generate($text)
    {
        // STEP 1: spread the the query char codes on a byte-array, 1-3 bytes per char
        $bytesArray = mb_str_to_array($text);

        // STEP 2: starting with TKK index, add the array from last step one-by-one, and do 2 rounds of shift+add/xor
        $d = explode('.', self::$tkkDefault);

        $tkkIndex = intval($d[0]);
        $tkkIndex = $tkkIndex ? $tkkIndex : 0;

        $tkkKey = intval($d[1]);
        $tkkKey = $tkkKey ? $tkkKey : 0;

        $encondingRound1 = array_reduce($bytesArray, function ($acc, $current) {
            $acc += $current;
            return self::shiftLeftOrRightThenSumOrXor($acc, "+-a^+6");
        }, $tkkIndex);
        $encondingRound2 = self::shiftLeftOrRightThenSumOrXor($encondingRound1, "+-3^+b+-f");
        $encondingRound2 = intval_32($encondingRound2 ^ (int)$d[1]);
        0 > $encondingRound2 && ($encondingRound2 = ($encondingRound2 & 2147483647) + 2147483648);
        $encondingRound2 %= 1E6;
        return $encondingRound2 . '.' . ($encondingRound2 ^ $tkkIndex);
    }

    private static function shiftLeftOrRightThenSumOrXor($a, $b)
    {
        for ($c = 0; $c < mb_strlen($b) - 2; $c += 3) {
            $d = $b[$c + 2];
            $d = "a" <= $d ? ord($d[0]) - 87 : (int)$d;
            $d = "+" == $b[$c + 1] ? unsigned_right_shift($a, $d) : left_shift_32($a, $d);
            $a = "+" == $b[$c] ? intval_32($a + $d & 0xffffffff) : intval_32($a ^ $d);
        }
        return $a;
    }


}