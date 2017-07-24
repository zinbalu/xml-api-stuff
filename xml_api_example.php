<?php

class EcontXMLClient {
    public static function request($url, $params = array(),$timeout = 10) {
        $request = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><request></request>');
        self::array2XMLNode($params,$request);
        $ch = curl_init($url);
        curl_setopt_array($ch,array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_POSTFIELDS => array(
                'xml' => $request->asXML()
            )
        ));
        $r = curl_exec($ch);
        if($r === false) throw new Exception("Connection error.");
        //if user or password are incorrect, the response is HTML!!
        return json_decode(json_encode(new \SimpleXMLElement($r)),true);//poor man's XML2Array
    }
    public static function array2XMLNode($array, \SimpleXMLElement $parentNode) {
        if(!is_array($array)) return;
        foreach($array as $k => $v) {
            if(is_array($v)) {
                if(!array_key_exists(0,$v)) $vv = array($v);
                else $vv = $v;
                foreach ($vv as $vvv) {
                    self::array2XMLNode($vvv,$parentNode->addChild($k));
                }
            } else {
                $parentNode->addChild($k,$v);
            }
        }
    }
}

$params = array(
    'system' => array(
        'validate' => 1,//1 - only validates the data, 0 - creates a shipment
        'only_calculate' => 0,//1 - only calculates the price for delivery, 0 - creates a shipment
        'response_type' => 'XML',
        //'email_errors_to' => 'tihomir@tivart.com',// request will be async, and sends mail with errors (for big requests with multiple shipments)
    ),
    'client' => array(
        'username' => 'iasp-dev',
        'password' => 'iasp-dev',
    ),
    'loadings' => array(
        'row' => array(
            'sender' => array(
                'name' => "client name",
                'name_person' => "name of the employee if client is a company",
                'email' => 'client email',
                'phone_num' => 'client phone',
                'email_on_delivery' => 'email for notification when the shipment is delivered',
                'city'=>'name of the settlement',
                'post_code' => 'settlement post code',
                'office_code' => 'code of the office',//if the delivery is from office
                'quarter' => 'quarter name',
                'street' => 'street name',
                'street_num' => 'street number',
                'street_bl' => 'block',
                'street_vh' => 'entrance',
                'street_et' => 'floor',
                'street_ap' => 'apartment',
                'street_other' => 'additional information on the address'
            ),
            'receiver' => array(
                'name' => "client name",
                'name_person' => "name of the employee if client is a company",
                'email' => 'client email',
                'phone_num' => 'client phone',
                'sms_no' => 'phone for notification when the shipment will be delivered',
                'city'=>'name of the settlement',
                'post_code' => 'settlement post code',
                'office_code' => 'code of the office',//if the delivery is to office
                'quarter' => 'quarter name',
                'street' => 'street name',
                'street_num' => 'street number',
                'street_bl' => 'block',
                'street_vh' => 'entrance',
                'street_et' => 'floor',
                'street_ap' => 'apartment',
                'street_other' => 'additional information on the address'
            ),
            'payment' => array(
                'side' => 'SENDER',
                'method' => 'CASH',
                'key_word' => ''
            ),
            'shipment' => array(
                'shipment_type' => 'PACK',
                'description' => 'shipment description',
                'pack_count' => 1,//pieces count
                'weight' => 1,
                'tariff_sub_code' => 'DOOR_DOOR',//delivery type from/to DOOR/OFFICE - DOOR_DOOR, DOOR_OFFICE, OFFICE_DOOR, OFFICE_OFFICE
                'pay_after_accept' => 1,//receiver may review the contents before paying the COD
                'pay_after_test' => 1,//receiver may test or try out the contents before paying the COD
            ),
            'services' => array(
                'oc' => 18.42,//decalred value (insurance)
                'oc_currency' => 'BGN',//currency of the declared value
                'cd' => 18.42,//COD
                'cd_currency' => 'BGN',//currency of the COD
                'cd_agreement_num' => '',//agreement number for paying COD (ex. if cod amount will be paid via bank)
            ),
        )
    )
);

var_dump(EcontXMLClient::request('http://www.econt.com/e-econt/xml_parcel_import2.php',$params));
