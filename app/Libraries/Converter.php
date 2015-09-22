<?php
/**
 * Created by PhpStorm.
 * User: MohammadSadjad
 * Date: 2/24/2015
 * Time: 4:51 PM
 */

namespace App\Http\Libraries;

class Converter {

    public static function XML2Array($xml)
    {
        $arr = array();

        foreach ($xml as $element)
        {
            $tag = $element->getName();
            $e = get_object_vars($element);
            if (!empty($e))
            {
                $arr[$tag] = $element instanceof SimpleXMLElement ? xml2array($element) : $e;
            }
            else
            {
                $arr[$tag] = trim($element);
            }
        }
        return $arr;
    }

    public static function Array2XML( $data, &$xml_data ) {
        foreach( $data as $key => $value ) {
            if( is_array($value) ) {
                if( is_numeric($key) ){
                    $key = 'user'; //dealing with <0/>..<n/> issues
                }
                $subnode = $xml_data->addChild($key);
                Converter::Array2XML($value, $subnode);
            } else {
                $xml_data->addChild("$key",htmlspecialchars("$value"));
            }
        }
    }
}