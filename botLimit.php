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
    }



