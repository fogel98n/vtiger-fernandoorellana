<?php

class PDFMaker_Utils_Helper
{
    /**
     * @param array $value
     * @return int
     */
    public static function count($value)
    {
        return !empty($value) && is_array($value) ? count($value) : 0;
    }
}