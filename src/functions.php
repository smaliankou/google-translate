<?php
namespace GoogleTranslate;

function mb_str_to_array($a)
{
    for ($d = [], $e = 0, $f = 0; $f < mb_strlen($a, 'UTF-8'); $f++) {
        $g = char_code_at($a, $f);
        if (128 > $g) {
            $d[$e++] = $g;
        } else {
            if (2048 > $g) {
                $d[$e++] = $g >> 6 | 192;
            } else {
                if (55296 == ($g & 64512) && $f + 1 < mb_strlen($a, 'UTF-8') && 56320 == (char_code_at($a,
                            $f + 1) & 64512)
                ) {
                    $g = 65536 + (($g & 1023) << 10) + (char_code_at($a, ++$f) & 1023);
                    $d[$e++] = $g >> 18 | 240;
                    $d[$e++] = $g >> 12 & 63 | 128;
                } else {
                    $d[$e++] = $g >> 12 | 224;
                    $d[$e++] = $g >> 6 & 63 | 128;
                }
            }
            $d[$e++] = $g & 63 | 128;
        }
    }
    return $d;
}

function char_code_at($str, $index)
{
    $char = mb_substr($str, $index, 1, 'UTF-8');
    if (mb_check_encoding($char, 'UTF-8')) {
        $ret = mb_convert_encoding($char, 'UTF-32BE', 'UTF-8');
        $result = hexdec(bin2hex($ret));
        return $result;
    }
    return false;
}

function unsigned_right_shift($a, $b)
{
    if ($a >= 0) {
        return $a >> $b;
    }
    if ($b == 0) {
        return (($a >> 1) & 0x7fffffff) * 2 + (($a >> $b) & 1);
    }
    return ((~$a) >> $b) ^ (0x7fffffff >> ($b - 1));
}

function intval_32($value)
{
    $value = ($value & 0xFFFFFFFF);

    if ($value & 0x80000000) {
        $value = -((~$value & 0xFFFFFFFF) + 1);
    }

    return $value;
}

function left_shift_32 ($number, $steps)
{
    if (PHP_INT_MAX == 2147483647) {
        return $number << $steps;
    }
    $binary = decbin($number) . str_repeat('0', $steps);
    $binary = str_pad($binary, 32, '0', STR_PAD_LEFT);
    $binary = substr($binary, strlen($binary) - 32);
    return $binary{0} == '1' ? -(pow(2, 31) - bindec(substr($binary, 1))) : bindec($binary);
}