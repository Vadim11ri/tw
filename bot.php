<?php
/*
* Родительский класс для получения реквизитов авторизации бота соц-сети с учетом лимитов
* Реквизиты доступа обозваны $login и $password, но с таким же успехом это могут быть, 
* на пример, токен и ключ.
*/
	abstract class parserBot {
		
		/*
		* @var	int	Идентификатор бота в базе данных
		*/
		protected $id;
		
		/*
		* @var	string	Логин бота
		*/
		protected $login;
		
		/*
		* @var	string	Логин пароль бота
		*/
		protected $password;
		
		/*
		* @var	int	Идентификатор соц-сети
		*/
		protected $network_id = 1;
		
		/*
		* @var	array	Массив лимитов
		*/
		protected $limits = array();
		
		/*
		* @var	DBO	Объект базы данных
		*/
		protected $db;
		
		/*
		* Конструктор
		*
		* Получение объекта базы данных
		*
		* @param	string	$limit_type	тип лимита, который будет использовать бот. 
		*								Может быть пустым, в этом случае, реквизиты отдаются 
		*								без учета лимитов.
		*/
		public function __construct($limit_type = null){
			$this->db = Factory::getDBO();
			$this->login($limit_type);
		}
		
		/*
		* Получение авторизационных реквизитов авторизации бота с учетом лимитов
		* установка флага занятости. В случае отсутствия свободных ботов - рекурсивно с паузой в минуту.
		* @param	string	$limit_type	тип лимита, который будет использовать бот.
		*/
		protected function login($limit_type = null){
			$query = 'SELECT `id`, `login`, `password`
					  FROM `#__parser_bot`
					  WHERE `banned` = 0 AND `in_use` = 0 AND `network_id` = '.$this->network_id.'
					  ORDER BY a.`last_used` ASC
					  LIMIT 1
					  ';
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
		
		/*
		* Получение текущего состояния лимитов
		*/
		abstract protected function getLimits();
		
		/*
		* Получение текущего состояния (количества выполненных за время лимитирования действий) конкретного лимита
		*
		* @param	string	$limit_type	тип лимита.
		*/
		abstract public function getLimit($limit_type);
		
		/*
		* Получение текущего остатка конкретного лимита
		*
		* @param	string	$limit_type	тип лимита.
		*/
		abstract public function getLimitLeft($limit_type);
		
		/*
		* Увеличение количества выполненных за время лимитирования действий
		*
		* @param	string	$limit_type	Тип лимита.
		* @param	int		$val		Количество действий.
		*/
		abstract public function incrLimit($limit_type, $val = 1);
		
		/*
		* Получение логина текущего бота
		*/
		public function getLogin(){
			return $this->login;
		}
		
		/*
		* Получение пароля текущего бота
		*/
		public function getPassword(){
			return $this->password;
		}
		
		/*
		* Получение строки авторизации текущего бота
		*/
		public function getCredentails(){
			return $this->login.':'.$this->password;
		}
		
		/*
		* Снятие флага занятости текущего бота, обнуление реквизитов.
		*/
		public function logout(){
			$now = Factory::getDate()->toSQL();
			$now = $this->db->quote($now);
			$query = 'UPDATE `#__parser_bot` SET `last_used` = '.$now.', `in_use` = 0 WHERE `id` = '.$this->id;
			$this->db->setQuery($query);
			$this->db->execute();
			$this->id = '';
			$this->login = '';
			$this->password = '';
		}
		
		/*
		* Установка флага блокировки текущего бота.
		*/
		public function ban(){
			$query = 'UPDATE `#__parser_bot` SET `banned` = 1 WHERE `id` = '.$this->db->quote($this->id);
			$this->db->setQuery($query);
			$this->db->execute();
		}
		
		/*
		* Смена текущего бота.
		* @param	string	$limit_type	тип лимита, который будет использовать бот.
		*/
		public function relogin($limit_type = false){
			$this->logout();
			$this->login($limit_type);
		}
		
		/*
		* Деструктор. Снятие флага занятости текущего бота.
		*/
		public function __destruct(){
			$this->logout();
		}
	}