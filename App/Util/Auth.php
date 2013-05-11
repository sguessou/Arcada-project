<?php

namespace App\Util;

use App\Model\AbstractMapper;
use App\Model\UserMapper;

class Auth extends AbstractMapper
{
	//Instance of database connection class
	protected $_db;
	
	//Instance of Session class
	protected $_session;

	//Url to re-direct to in case not authenticated
	protected $_redirect; 

	//String to use when making hash of username and password
	protected $_hashKey;
	
	//Var to hold username
	protected $_login;
	
	//Auth constructor
    //Checks for valid user automatically
	public function __construct()
	{
		$this->_db = parent::connect();
		$this->_session = new Session();
		$this->_hashKey = 'Saruman';
		$this->_redirect = $this->_session->get('path').'/index.php?controller=login';//'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/index.php?controller=login'; 
		//$this->_login = $_SERVER['login'];
	}
	
	public function login()
	{
		// See if we have values already stored in the session
		if ( $this->_session->get('hash') )
		{
		  if ( $this->confirmAuth() )
		  {
		  	return;
		  }
		}

		// If this is a fresh login, check $_POST variables
		if ( !isset( $_POST['login'] ) || !isset( $_POST['password'] ) )
		{
			$this->redirect();
		}
		
        //Encrypt password
		$password = md5( $_POST['password'] );
		
		try
		{
		  // Query to count number of users with this combination
		  $sql = "SELECT COUNT(*) AS num_users FROM user WHERE login =:login AND password = :pass";
		      
		  $stmt = $this->_db->prepare($sql);
		  // bind the user input
		  $stmt->bindParam(':login', $_POST['login']);
		  $stmt->bindParam(':pass', $password);
		  $stmt->execute();
		  $row = $stmt->fetch( \PDO::FETCH_ASSOC );
		}
		catch ( \PDOException $e )
		{
		  error_log('Error in '.$e->getFile().
		      ' Line: '.$e->getLine().
		      ' Error: '.$e->getMessage()
		  );
			$this->redirect();
		}
		// If there isn't exactly one entry, redirect
		if ( $row['num_users'] != 1 )
		{    
			$this->redirect('notOk');
		}
		// Else is a valid user; set the session variables
		else
		{
			$this->storeAuth( $_POST['login'], $password );
			
			//We set login flag to 1 
			$user_mapper = new UserMapper();
			$user_mapper->set_logging_flag($_POST['login'], 1);
		}
	  }//End function login

	//Sets the session variables after a successful login
	public function storeAuth( $login, $password )
  	{
		$this->_session->set( 'login', $login );
		
		// remember the $password var is a MD5 - never keep the plaintext password
		$this->_session->set( 'password', $password );

		// Create a session variable to use to confirm sessions
		$hashKey = md5( $this->_hashKey . $login . $password );
		$this->_session->set( 'hash', $hashKey );
  	}  

	//Confirms that an existing login is still valid
	public function confirmAuth()
  	{
		$login = $this->_session->get( 'login' );
		$this->_login = $login;
		$password = $this->_session->get( 'password' );
		$hashKey = $this->_session->get( 'hash' );
		
		if ( md5( $this->_hashKey . $login . $password ) != $hashKey )
		{
		  	$this->logout();
		}
		return true;
  	}
	
	//Logs the user out
	public function logout()
  {
	//We set user table login flag to 0 
		$user_mapper = new UserMapper();
		$user_mapper->set_logging_flag($this->_session->get('login'), 0);
		  
		$this->_session->del( 'login' );
		$this->_session->del( 'password' );
		$this->_session->del( 'hash' );
		// For security reasons I am choosing to destroy the session 
		// here to start a completely new one.  If however you need to keep 
		// any other data in the session other than Auth data - you 
		// may choose not to do this.
		// $this->session->destroy(); 
		//var_dump($_SESSION);
		$this->redirect();  
  }

	//Redirects browser and terminates script execution
	private function redirect($str = 'ok')
  {
		header( 'Location:' . $this->_redirect . '&flag=' . $str );		
		exit();
  }
  	
  //Checks if user is logged in
  public function isLogged()
  {		
		$login = $this->_session->get( 'login' );
		$this->_login = $login;
		$password = $this->_session->get( 'password' );
		$hashKey = $this->_session->get( 'hash' );
		
		if ( md5( $this->_hashKey . $login . $password ) != $hashKey )
		{
		  	return FALSE;
		}
		return TRUE;
  }
  	
  	//Is user admin?
	public function isAdmin( $login )
	{
		 $sql = "SELECT admin FROM user WHERE login = :login";
		 $stmt = $this->_db->prepare( $sql );
		 $stmt->bindParam( ':login', $login );
		 $stmt->execute();
		 $result = $stmt->fetch(\PDO::FETCH_ASSOC);
		 
		 if( $result['admin'] )
		 {
		 	return TRUE;
		 }
		 	
		return FALSE;
	}
	
	public function getLogin()
	{
		return $this->_login;
	}

	public function register( $pageUrl, $ip, $host )
	{
		$sql = "INSERT INTO accessLog( pageUrl, ip, host ) VALUES( :pageUrl, :ip, :host )";
		$stmt = $this->_db->prepare( $sql );
		$stmt->bindParam( ':pageUrl', $pageUrl );
		$stmt->bindParam( ':ip', $ip );
		$stmt->bindParam( ':host', $host );
		$stmt->execute();
		return;
	}
	
}// End Class Auth
