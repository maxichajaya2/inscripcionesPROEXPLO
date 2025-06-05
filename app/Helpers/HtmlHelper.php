<?php

namespace App\Helpers;

class HtmlHelper
{
    public static function caracteresHTML($str){
        $caracteres = array("á", "é", "í", "ó","ú","Á","É","Í","Ó","Ú","ñ","Ñ","°","°"," ","á", "é", "í", "ó","ú","Á","É","Í","Ó","Ú","ñ","Ñ","°","°"," "); //caracteres a reemplazar
        $caracteres_html = array("&aacute;", "&eacute;","&iacute;","&oacute;","&uacute;","&Aacute;","&Eacute;","&Iacute;","&Oacute;","&Uacute;", "&ntilde;", "&Ntilde;","&deg;","&ordm;","&nbsp;","&aacute", "&eacute","&iacute","&oacute","&uacute","&Aacute","&Eacute","&Iacute","&Oacute","&Uacute", "&ntilde", "&Ntilde","&deg","&ordm","&nbsp"); //valores a imprimir
        return str_replace($caracteres_html, $caracteres, $str);
    }

    public static function get_client_ip() {
	    $ipaddress = '';
	    if (isset($_SERVER['HTTP_CLIENT_IP']))
	        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
	    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
	        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
	    else if(isset($_SERVER['HTTP_X_FORWARDED']))
	        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
	    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
	        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
	    else if(isset($_SERVER['HTTP_FORWARDED']))
	        $ipaddress = $_SERVER['HTTP_FORWARDED'];
	    else if(isset($_SERVER['REMOTE_ADDR']))
	        $ipaddress = $_SERVER['REMOTE_ADDR'];
	    else
	        $ipaddress = 'UNKNOWN';
	    return $ipaddress;
	}
}
