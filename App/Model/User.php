<?php

namespace App\Model;

	class User 
	{	
		protected $_user_id;
		protected $_login;
		protected $_password;
		protected $_email;
		protected $_firstname;
		protected $_lastname;
		protected $_phone;
		protected $_address_line_1;
		protected $_address_line_2;
		protected $_town_city;
		protected $_county;
		protected $_country;
		protected $_created_at;
		protected $_updated_at;
		protected $_last_login;
		protected $_admin;
	
		public function __construct( User $user ) 
		{											 
			$this->_user_id = $user->user_id;
			$this->_login = $user->login;
			$this->_password = $user->password;
			$this->_email = $user->email;
			$this->_firstname = $user->firstname;
			$this->_lastname = $user->lastname;		
			$this->_phone = $user->phone;
			$this->_address_line_1 = $user->address_line_1;
			$this->_address_line_2 = $user->address_line_2;
			$this->_town_city = $user->town_city;
			$this->_county = $user->county;
			$this->_country = $user->country;
			$this->_created_at = $user->created_at;
			$this->_updated_at = $user->updated_at;
			$this->_last_login = $user->last_login;
			$this->_admin = $user->admin;
	}

}
