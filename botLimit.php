<?php 

    /*
     * getListForBot
     * getOne
     * setAvailableLimit
     * setPropsArray
     * getPropsArray
     * 
     */
    
    class botLimit {
        
        public $id;
        public $limit_type;
        public $bot_id;
        public $available_limit;
        public $props;
        
        public static function getOne($bot_id,$limit_type){
            $result = false;
            $db = Factory::getDBO();
            $query = 'SELECT *  
                FROM `#__bot_limit` 
                WHERE `limit_type` = "'.$limit_type.'" 
                AND `bot_id` = '.$bot_id;
            $db->setQuery($query);
            $botLimitDb = $db->loadObject();
            if(!empty($botLimitDb)){
                $result = new botLimit();
                $result->id = $botLimitDb->id;
                $result->limit_type = $botLimitDb->limit_type;
                $result->bot_id = $botLimitDb->bot_id;
                $result->available_limit = $botLimitDb->available_limit;
                $result->props = $botLimitDb->props;
            }
            return $result;
        }
        
        public static function getListForBot($bot_id){
            $resultList = array();
            $db = Factory::getDBO();
            $query = 'SELECT *  
                FROM `#__bot_limit` 
                WHERE `bot_id` = "'.$bot_id.'"';
            $db->setQuery($query);
            $botLimitDbList = $db->loadObjectList();
            foreach($botLimitDbList as $botLimitDb){
                $result = new botLimit();
                $result->id = $botLimitDb->id;
                $result->limit_type = $botLimitDb->limit_type;
                $result->bot_id = $botLimitDb->bot_id;
                $result->available_limit = $botLimitDb->available_limit;
                $result->props = $botLimitDb->props;
                $resultList[] = $result;
            }
            return $resultList;
        }
        
        public static function setAvailableLimit($bot_id,$limit_type,$limit_value){
            $db = Factory::getDBO();
            $query = 'UPDATE `#__bot_limit` 
                SET `available_limit` = '.$limit_value.' 
                WHERE `bot_id` = '.$bot_id.' 
                AND `limit_type` = "'.$limit_type.'"';
	    $db->setQuery($query);
	    $db->execute();
        }
        
        public static function setPropsArray($id,$limit_type,$props_array){
            $db = Factory::getDBO();
            $query = 'UPDATE `#__bot_limit` 
                SET `props` = "'. addslashes(serialize($props_array)).'" 
                WHERE `bot_id` = '.$bot_id.' 
                AND `limit_type` = "'.$limit_type.'"';
	    $db->setQuery($query);
	    $db->execute();
        }
        
        public static function getPropsArray($id,$limit_type){
            $result = array();
            $db = Factory::getDBO();
            $query = 'SELECT `props`  
                FROM `#__bot_limit` 
                WHERE `limit_type` = "'.$limit_type.'" 
                AND `bot_id` = '.$bot_id;
            $db->setQuery($query);
            $botPropsDb = $db->loadObject();
            if(!empty($botPropsDb)){
                $result = unserialize($botPropsDb->props);
            }
            return $result;
        }
    }



