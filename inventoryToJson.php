<?php

namespace Shable\Converter;

class InventoryToJson
{
    /**
     * @var string
     */
    private $shableInventory;

    public function __construct(string $inventory)
    {
        $this->shableInventory = $inventory;
    }

    public function convert(): string
    {
        $settingsPattern = '#^_(.*)#im';
        $sectionPattern = '#^\[(.*?)\]#im';

        $matches = [];
        preg_match_all($sectionPattern,$this->shableInventory,$matches,PREG_OFFSET_CAPTURE);
        $start = 0;
        $sections = [];

        for ($i=0;$i<count($matches[0]);$i++){
            $sectionMatch = $matches[0][$i];
            $sectionStart = $sectionMatch[1];
            $length = $sectionStart-$start;
            $sections[]= substr($this->shableInventory,$start,$length);
            $start = $sectionStart;
        }

        //last section
        $sectionLengthSoFar = 0;
        foreach($sections as $section){
            $sectionLengthSoFar += strlen($section);
        }

        $length = strlen($this->shableInventory)-$sectionLengthSoFar;
        $sections[]= substr($this->shableInventory,$start,$length);

        //configSection
        $sectionIterator = 0;
        $inventory['configuration'] = [];
        foreach($sections as $section){
            $sectionName ='';
            foreach(explode("\n",$section) as $lineNumber => $line) {
                if ($sectionIterator == 0) {
                    if (empty(trim($line))) {
                        continue;
                    }
                    $inventory['configuration'][] = $line;
                } else {

                    if($lineNumber ==0){
                        $sectionName = str_replace(['[',']'],'',$line);
                    } else {
                        if (empty(trim($line))) {
                            continue;
                        }

                        if(substr($line,0,1) == '#'){
                            //ignore comments
                            continue;
                        }

                        /* here we have one host per line */
                        list($hostName) = explode(' ',$line);
                        $restOfConfig = substr($line,strlen($hostName)+1);

                        $configItems = preg_split("/([a-zA-z]+\=)/i",trim($restOfConfig),-1,PREG_SPLIT_DELIM_CAPTURE);
                        $foundValues = [];
                        foreach($configItems as $foundValue){

                            if(stristr($foundValue,'=')){
                                $key = str_replace('=','',$foundValue);
                            } else {
                                if(strlen($foundValue)===0){
                                    continue;
                                }
                                $value = $foundValue;
                            }
                            if(!empty($key) && !empty($value)){

                                if(isset($inventory[$sectionName][$hostName][$key])){
                                    die($hostName.' key: '. $key.' seems to be have two value for '.$key);
                                }

                                if(substr_count($value,"'") == 1){
                                    die('Wrong quote count in '.$sectionName.' -> '.$hostName.' -> '.$key);
                                }

                                if(empty($value)){
                                    die($hostName.' key: '. $key.' seems to be wrong');
                                }

                                $value = trim($value);
                                $value = str_replace("'","",$value);
                                $foundValues[$key] = $value;
                                $inventory[$sectionName][$hostName][$key] = trim($value);
                                unset($key);
                                unset($value);

                            }
                        }
                    }
                }

            }
            $sectionIterator++;
        }

        return json_encode($inventory);
    }

}