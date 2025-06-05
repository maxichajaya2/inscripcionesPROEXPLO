<?php

namespace App\Helpers;

use NumberFormatter;

class CurrencyHelper
{
    public static function formatCurrency($amount, $currencyCode, $locale = 'en-US')
    {
        $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
        $formatter->setTextAttribute(NumberFormatter::CURRENCY_CODE, $currencyCode);
        return  $formatter->formatCurrency($amount, $currencyCode);
    }

    public static function formatTextCurrency($amount, $currencyCode, $locale = 1)
    {
        if($locale == 1){
            $texto_moneda = $currencyCode == 'USD' ? "Dólares Americanos" : "Nuevos Soles";
            $text = " y 00/100 ". $texto_moneda;
            $code = "es-ES";
        }else{
            $text = $currencyCode == 'USD' ? "U.S. Dollars" : "Peruvian New Soles";
            $code = "en-US";
        }
        $formatter = new NumberFormatter($code , NumberFormatter::SPELLOUT);
        $literal_name = $formatter->formatCurrency( $amount , $currencyCode );
        $literal_name = ucfirst($literal_name). $text;
        return  $literal_name;
    }

}
