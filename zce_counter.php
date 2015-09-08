<?php
/**
 * User: grahamcrocker
 * Date: 08/09/15
 */

const ZCE_XML_FNAME = 'zced-form-country.xml';
const ZCE_SER_FNAME = 'zendcountryids.txt';
const VERBOSE   = true; //Shows Country Information per iteration

echo 'Total number of ZCEs worldwide is '.get_zce_world_count().PHP_EOL;


/**
 * If VERBOSE is true it will also print Country ID, Name, ZCE Count and Partial Total
 * @return int the total count of ZCE in the world
 */
function get_zce_world_count()
{
    $total_zce =0;
    //File was generated using function generate_countryinfo_from_xml()
    $country_ids = unserialize(file_get_contents(ZCE_SER_FNAME));
    foreach($country_ids as $cid => $country_name)
    {
        $zce_count_by_country = count(get_zce_by_country($cid));
        $total_zce += $zce_count_by_country;
        if(VERBOSE)
        {
            echo "Country ID: ".$cid.PHP_EOL.
                 "Country Name: ".$country_name.PHP_EOL.
                 "Country Count: ".$zce_count_by_country.PHP_EOL.
                 "Total Count: ".$total_zce.PHP_EOL.PHP_EOL;
        }
    }
    return $total_zce;
}

/**
 * @param $cid zend country id
 * @return array returns array of zce information, else error code
 */
function get_zce_by_country($cid)
{
    $request_url  = "http://www.zend.com/en/services/certification/zend-certified-engineer-directory/ajax/search-candidates?cid=${cid}&certtype_php=on&certtype=PHP&firstname=&lastname=&company=&ClientCandidateID=";
    $response     = file_get_contents($request_url);
    $arr_response = json_decode($response, true);
    if(is_array($arr_response))
    {
        if($arr_response["status"]==="OK")
        {
            return $arr_response["result"];
        }else
        {
            echo 'Error #02: Valid Response, Status not OK - check Response'.PHP_EOL;
            echo 'Country ID: '.$cid.PHP_EOL;
            var_dump($arr_response);
            return -2;
        }
    }else
    {
        echo 'Error #01: Invalid Response - check URI'.PHP_EOL;
        return -1;
    }
}

/*
 * Converts the Zend Country Id and associated Name from XML to a Serialized object
 * @return int|bool the number of bytes that were written to the file, or FALSE on failure.
 */
function generate_countryinfo_from_xml()
{
    $country_array = get_country_names_from_cids();
    return file_put_contents(ZCE_SER_FNAME, serialize($country_array));
}

/**
 * @return string name of country
 */
function get_country_names_from_cids()
{
    $dom = new DOMDocument();
    $dom->loadHTMLFile(ZCE_XML_FNAME);
    $options = $dom->getElementsByTagName("option");
    foreach($options as $node)
    {
        $index[$node->getAttribute("value")] = $node->textContent;
    }
    ksort($index);
    return $index;
}
