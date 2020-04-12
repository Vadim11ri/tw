<?php 
    include_once('bot.php');
    include_once('botLimitType.php');
    include_once('botLimit.php');
    include_once('botLimitLog.php');
    
    /*
     * При получении бота, должен выбираться не занятый 
     * и не заблокированный бот с наименее израсходованным лимитом 
     * того типа действия, для которого бот будет использоваться. 
     * При прочих равных, выбирается бот, который не использовался дольше. 
     * 
     * Должны быть предусмотрены возможности: 
     * 
     * получения реквизитов бота (getCredentails*), 
     * получения состояния лимитов (getLimits), 
     * получения состояния (getLimit) и 
     * получения остатков конкретного лимита (getLimitLeft), 
     * изменение состояния конкретного лимита (incrLimit), 
     * установка (banFlagOn**) и 
     * снятие флага занятости бота (unban**), 
     * установка флага блокировки бота (ban*), 
     * изменение времени последнего использования (setLastUsed**), 
     * долговременное хранение промежуточных реквизитов (setIntermediateProps**)
     * (getIntermediateProps**), 
     * если таковое используется в соответствующем API.
     * 
     *  * - унаследовано
     *  ** - новое имя метода
     * 
     * Дополнительные таблицы:
     * 
     * #__bot_limit_type - фактически таблица из https://developer.twitter.com/en/docs/basics/rate-limits
     *      limit_type
     *      hours
     *      limit_value
     * 
     * #__bot_limit
     *      id
     *      limit_type
     *      bot_id
     *      available_limit
     *      props - сериализованный массив дополнительных параметров
     * 
     * #__bot_limit_log
     *      id
     *      limit_type
     *      bot_id
     *      utime - метка времени unix
     * 
     */
    
    class newBot extends parserBot {
        
        public function __construct($limit_type = null){
            parent::__construct();
	}
        
        protected function login($limit_type = null){
            if($limit_type === null){
                $query = 'SELECT a.`id`, a.`login`, a.`password`
                    FROM `#__parser_bot` a
                    WHERE a.`banned` = 0 AND a.`in_use` = 0 AND a.`network_id` = '.$this->network_id.'
                    ORDER BY a.`last_used` ASC
                    LIMIT 1
		';
            }else{
                $query = 'SELECT a.`id`, a.`login`, a.`password`
                    FROM `#__parser_bot` a 
                    INNER JOIN `#__bot_limit` b 
                    ON b.`limit_type` = '.$limit_type.' AND b.`bot_id` = a.`id` 
                    WHERE a.`banned` = 0 
                        AND a.`in_use` = 0 
                        AND a.`network_id` = '.$this->network_id.' 
                        AND b.`available_limit` > 0 
                    ORDER BY b.`available_limit` DESC, a.`last_used` ASC
                    LIMIT 1
                ';
            }
            $this->db->setQuery($query);
            $bot = $this->db->loadObject();
            if(!empty($bot)){
		$query = 'UPDATE `#__parser_bot` SET `in_use` = 1 WHERE `id` = '.$bot->id;
		$this->db->setQuery($query);
		$this->db->execute();
		$this->id = $bot->id;
		$this->login = $bot->login;
		$this->password = $bot->password;
            } else {
		sleep(60);
		return $this->login($for);
            }
            return true;
	}
        
        public function getLimits(){
            return botLimit::getListForBot($this->id);
        }
        
        public function getLimit(){
            return botLimit::getOne($this->id,$limit_type);
        }
        
        public function getLimitLeft(){
            $result = 0;
            $limit = botLimit::getOne($this->id,$limit_type);
            if($limit !== null){
                $result = $limit->available_limit;
            }
            return $result;
        }
        
        public function incrLimit($limit_type, $val = 1){
            for($i = 1; $i <= $val; $i++){
                botLimitLog::insert($this->id,$limit_type);
            }
            $type = botLimitType::getOne($limit_type);
            if($type === false){
                return;
            }
            botLimitLog::deleteOld($this->id,$limit_type,$type->hours);
            $count = botLimitLog::getCount($this->id,$limit_type);
            $limit_value = $type->limit_value - $count;
            if($limit_value < 0){
                $limit_value = 0;
            }
            botLimit::setAvailableLimit($this->id,$limit_type,$limit_value);
        }
        
        public function unban(){
            $query = 'UPDATE `#__parser_bot` SET `banned` = 0 WHERE `id` = '.$this->db->quote($this->id);
	    $this->db->setQuery($query);
	    $this->db->execute();
        }
        
        public function setLastUsed($last_used_datetime){
            $query = 'UPDATE `#__parser_bot` SET `last_used` = '.$last_used_datetime.', `in_use` = 0 WHERE `id` = '.$this->id;
	    $this->db->setQuery($query);
	    $this->db->execute();
        }
        
        public function setIntermediateProps($limit_type,$props_array){
            botLimit::setPropsArray($this->id,$limit_type,$props_array);
        }
        
        public function getIntermediateProps($limit_type){
            return botLimit::getPropsArray($this->id,$limit_type);
        }
    }
    