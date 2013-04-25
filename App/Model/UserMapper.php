<?php

namespace App\Model;


/**
	* UserMapper Class
	*	Provides functionality for user table
	*/
class UserMapper extends AbstractMapper 
{
	/**
   * Database connection
   * @access protected
   * @var PDO object
   */
	protected $_db; 
	
	/**
   * UserMapper constructor
   * Establish database connectivity
   * @param none 
   * @access public
   */
	public function __construct()
	{
		$this->_db = parent::connect();
	}	

	/**
	* Gets a single row from user table 
	*	@param string containing login 
	* @return single row of data on success
	*/	
	public function fetchUser( $login )
	{
		$sql = "SELECT * FROM user WHERE login = :login";
	  $stmt = $this->_db->prepare($sql);
		$stmt->bindParam(':login', $login);
		
		if( $stmt->execute() )
		{
			return $row = $stmt->fetch(\PDO::FETCH_ASSOC);
		}
		else
		{
			print_r( $stmt->errorInfo() );
			return;
		}
	}
	
	/**
	* Updates user table column last_log to current time 
	*	@param string contains login 
	* @return none
	*/	
	public function lastLog( $login )
	{
		$sql = "UPDATE user SET last_log = DATE_FORMAT( NOW(), '%W %M %d %H:%i:%s %Y') WHERE login = :login";
	  $stmt = $this->_db->prepare($sql);
		$stmt->bindParam(':login', $login);
		
		if( $stmt->execute() )
		{
			return; 
		}
		else
		{
			print_r( $stmt->errorInfo() );
			return;
		}
	}	
	
	/**
	* Returns value of a given column based on user id 
	*	@param string contains column name, string contains login 
	* @return associative array
	*/
	public function returnClmn( $clmn, $login )
	{
		$sql = "SELECT $clmn FROM user WHERE login = :login";
	  $stmt = $this->_db->prepare($sql);
		$stmt->bindParam(':login', $login, \PDO::PARAM_STR );
		
		if( $stmt->execute() )
		{
			return $row = $stmt->fetch(\PDO::FETCH_ASSOC);
		}
		else
		{
			print_r( $stmt->errorInfo() );
			return;
		}
	}
	
	/**
	* Returns email, firstname & lastname of a given user based on login 
	* @param string contains login 
	* @return associative array
	*/
	public function retUserInfo( $login )
	{
		$sql = "SELECT email, firstname, lastname FROM user WHERE login = :login";
	  	$stmt = $this->_db->prepare($sql);
		$stmt->bindParam(':login', $login, \PDO::PARAM_STR );
		
		if( $stmt->execute() )
		{
			return $row = $stmt->fetch(\PDO::FETCH_ASSOC);
		}
		else
		{
			print_r( $stmt->errorInfo() );
			return;
		}
	}
	
	/**
	* Updates table user, set the address and address related columns
	* @param array $_POST 
	* @return none
	*/
	public function updateUser( $data )
	{
		$sql = "UPDATE user SET address_line_1 = :address1,
														address_line_2 = :address2,
														town_city = :town,
														county = :county,
														country = :country,
														updated_at = :updated
														WHERE login = :login";
														
	  $stmt = $this->_db->prepare($sql);
	  $stmt->bindParam(':address1', $data['address_line_1'], \PDO::PARAM_STR );
	  $stmt->bindParam(':address2', $data['address_line_2'], \PDO::PARAM_STR );
	  $stmt->bindParam(':town', $data['city'], \PDO::PARAM_STR );
	  $stmt->bindParam(':county', $data['county'], \PDO::PARAM_STR );
	  $stmt->bindParam(':country', $data['country'], \PDO::PARAM_STR );
	  $date = date("Y-m-d H:i:s");
		$stmt->bindParam(':updated', $date, \PDO::PARAM_STR );  
		$stmt->bindParam(':login', $data['login'], \PDO::PARAM_STR );
		
		if( $stmt->execute() )
		{
			return;
		}
		else
		{
			print_r( $stmt->errorInfo() );
			return;
		}
	}//End method updateUser
	
	/**
	* Updates table user, set the personal data and it's related columns
	* @param array $_POST 
	* @return none
	*/
	public function updatePersonal( $data )
	{
		$sql = "UPDATE user SET firstname = :firstname,
								lastname = :lastname,
								email = :email,
								updated_at = :updated
								WHERE login = :login 
									  AND user_id = :user_id";
														
	  $stmt = $this->_db->prepare($sql);
	  $stmt->bindParam(':firstname', $data['firstname'], \PDO::PARAM_STR );
	  $stmt->bindParam(':lastname', $data['lastname'], \PDO::PARAM_STR );
	  $stmt->bindParam(':email', $data['email'], \PDO::PARAM_STR );
	  $date = date("Y-m-d H:i:s");
	  $stmt->bindParam(':updated', $date, \PDO::PARAM_STR );  
	  $stmt->bindParam(':login', $data['username'], \PDO::PARAM_STR );
	  $stmt->bindParam(':user_id', $data['user_id'], \PDO::PARAM_INT );
		
		if( $stmt->execute() )
		{
			return TRUE;
		}
		else
		{
			print_r( $stmt->errorInfo() );
			return FALSE;
		}
	}//End method updatePersonal
	
	/**
	* Updates table user, set the password column
	* @param array $_POST 
	* @return none
	*/
	public function updatePasswd( $data )
	{
		$sql = "UPDATE user SET password = MD5(:password),
								updated_at = :updated
								WHERE login = :login 
									  AND user_id = :user_id";
														
	  $stmt = $this->_db->prepare($sql);
	  $stmt->bindParam(':password', $data['new_passwd'], \PDO::PARAM_STR );
	  $date = date("Y-m-d H:i:s");
	  $stmt->bindParam(':updated', $date, \PDO::PARAM_STR );  
	  $stmt->bindParam(':login', $data['username'], \PDO::PARAM_STR );
	  $stmt->bindParam(':user_id', $data['user_id'], \PDO::PARAM_INT );
		
		if( $stmt->execute() )
		{
			return TRUE;
		}
		else
		{
			print_r( $stmt->errorInfo() );
			return FALSE;
		}
	}//End method updatePersonal
	

}//End Class UserMapper
