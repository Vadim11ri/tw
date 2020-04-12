<?php 

    /*
     * insert
     * deleteOld
     * getCount
     * 
     */
    
    class botLimitLog {
        
        public $id;
        public $limit_type;
        public $bot_id;
        public $utime;
        
        public static function insert($bot_id,$limit_type){
            $db = Factory::getDBO();
            $insert_time = time();
            $query = 'INSERT INTO `#__bot_limit_log` 
                SET `bot_id` = '.$bot_id.', 
                `limit_type` = "'.$limit_type.'", 
                `utime` = '.$insert_time;
	    $db->setQuery($query);
	    $db->execute();
        }
        
        public static function deleteOld($bot_id,$limit_type,$hours){
            $db = Factory::getDBO();
            $delete_time = time() - $hours * 3600;
            $query = 'DELETE FROM `#__bot_limit_log` 
                WHERE `bot_id` = '.$bot_id.' 
                AND `limit_type` = "'.$limit_type.'" 
                AND `utime` > '.$delete_time;
	    $db->setQuery($query);
	    $db->execute();
        }
        
        public static function getCount($bot_id,$limit_type){
            $result = 0;
            $db = Factory::getDBO();
            $query = 'SELECT count(*)   
                FROM `#__bot_limit_log` 
                WHERE `limit_type` = "'.$limit_type.'" 
                AND `bot_id` = '.$bot_id;
            $db->setQuery($query);
            $logDb = $db->loadResult();
            if(!empty($logDb)){
                $result = $logDb;
            }
            return $result;
        }
    }



