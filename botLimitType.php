<?php 
    
    class botLimitType {
        
        public $limit_type;
        public $hours;
        public $limit_value;
        
        public static function getOne($limit_type){
            $result = false;
            $db = Factory::getDBO();
            $query = 'SELECT *  
                FROM `#__bot_limit_type` 
                WHERE `limit_type` = "'.$limit_type.'"';
            $db->setQuery($query);
            $botLimitTypeDb = $db->loadObject();
            if(!empty($botLimitTypeDb)){
                $result = new botLimitType();
                $result->limit_type = $botLimitTypeDb->limit_type;
                $result->hours = $botLimitTypeDb->hours;
                $result->limit_value = $botLimitTypeDb->limit_value;
            }
            return $result;
        }
    }



