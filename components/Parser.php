<?php

namespace app\components;


class Parser
{
    private static $CurlInstance;

    //Preparing a request
    public function CurlInstance($url){
        if(is_null(self::$CurlInstance)){
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_USERAGENT, "google");
            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_VERBOSE, true);
            self::$CurlInstance = $ch;
        }
        curl_setopt(self::$CurlInstance, CURLOPT_URL, $url);
        return self::$CurlInstance;
    }

    /**
     * @param SimpleXMLElement $xml
     * @return array
     */
    public function xmlToArray(\SimpleXMLElement $xml)
    {
        $parser = function (\SimpleXMLElement $xml, array $collection = []) use (&$parser) {
            $nodes = $xml->children();
            $attributes = $xml->attributes();

            if (0 !== count($attributes)) {
                foreach ($attributes as $attrName => $attrValue) {
                    $collection['attributes'][$attrName] = strval($attrValue);
                }
            }

            if (0 === $nodes->count()) {
                $collection['value'] = strval($xml);
                return $collection;
            }

            foreach ($nodes as $nodeName => $nodeValue) {
                if (count($nodeValue->xpath('../' . $nodeName)) < 2) {
                    $collection[$nodeName] = $parser($nodeValue);
                    continue;
                }

                $collection[$nodeName][] = $parser($nodeValue);
            }

            return $collection;
        };

        return [
            $xml->getName() => $parser($xml)
        ];
    }


    function preview_text($value, $limit = 300)
    {
        $value = stripslashes($value);
        $value = htmlspecialchars_decode($value, ENT_QUOTES);
        $value = str_ireplace(array('<br>', '<br />', '<br/>'), ' ', $value);
        $value = strip_tags($value);
        $value = trim($value);

        if (mb_strlen($value) < $limit) {
            return $value;
        } else {
            $value   = mb_substr($value, 0, $limit);
            $length  = mb_strripos($value, ' ');
            $end     = mb_substr($value, $length - 1, 1);

            if (empty($length)) {
                return $value;
            } elseif (in_array($end, array('.', '!', '?'))) {
                return mb_substr($value, 0, $length);
            } elseif (in_array($end, array(',', ':', ';', '«', '»', '…', '(', ')', '—', '–', '-'))) {
                return trim(mb_substr($value, 0, $length - 1)) . '...';
            } else {
                return trim(mb_substr($value, 0, $length)) . '...';
            }

            return trim();
        }
    }

    //Collect News
    public function CollectList(){
        //Collect rss RBC
        $rss = curl_exec (self::CurlInstance('http://static.feed.rbc.ru/rbc/internal/rss.rbc.ru/rbc.ru/news.rss'));

        //Decode string to xml
        $rss = simplexml_load_string($rss);

        //Decode xml to array
        $decodexml = self::xmlToArray($rss);

        $i = 0;
        $Items = [];

        //data for decode
        $classname="article__text";
        $dom = new \DOMDocument;
        $finder = new \DomXPath($dom);

        foreach ($decodexml["rss"]["channel"]["item"] as $item) {

            //Collect details
            $details = curl_exec(self::CurlInstance($item["link"]["value"]));

            //Decode details
            $description = "";
            $dom->loadHTML($details);
            $finder->__construct($dom);
            $spaner = $finder->query("//*[contains(@class, '$classname')]");
            foreach ($spaner as $element) {
                $nodes = $element->childNodes;
                foreach ($nodes as $node) {
                    if($node->nodeName == "p")
                    $description .= $node->nodeValue;
                }
            }
            $description = str_replace("\n\n","\n",$description);
            $description = trim(preg_replace('/[\s]{2,}/', ' ', $description));

            $announcement = self::preview_text($description,197);

            //Collect data to array
            $Items[$i] = [
                "GUID" =>  $item["guid"]["value"],
                "TITLE" => $item["title"]["value"],
                "ANNOUNCEMENT" => $announcement,
                "DATETIME" => date("Ymd",strtotime($item["pubDate"]["value"])),
                "PICTURE" => $item["enclosure"]["attributes"]["url"],
                "DESCRIPTION" => $description
            ];

            if(($i++) == 14){
                break;
            }
        }
        return $Items;
    }
}