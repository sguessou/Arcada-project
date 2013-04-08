<?php

namespace App\Model;


// Base custom exception class for the SignUp class.
class SignUpException extends \Exception
{
  public function __construct($message = null, $code = 0) 
  {
    parent::__construct($message, $code);
    error_log('Error in '.$this->getFile().
      ' Line: '.$this->getLine().
      ' Error: '.$this->getMessage()
    );
  }
}

// Indicates database exception in signup process.
class SignUpDatabaseException extends SignUpException {}

// Indicates user already exists in signup process.
class SignUpNotUniqueException extends SignUpException {}

// Indicates email problem in signup confirmation process.
class SignUpEmailException extends SignUpException {}

// Indicates confirmation problem in signup process.
class SignUpConfirmationException extends SignUpException {}

class SignUpMapper extends AbstractMapper
{
	  protected $_db; // Database connection
	  protected $_from; // The name / address the signup email should be sent from
	  protected $_to; //The name / address the signup email should be sent to
	  protected $_subject; // The subject of the confirmation email
	  protected $_message; // Text of message to send with confirmation email
	  protected $_listener; // Url to use for confirmation
	  protected $_confirmCode; // Confirmation code to append to $this->listener
	  
	  
	  public function __construct( $listener, $frmName, $frmAddress, $subj, $msg )
  	{
     	$this->_db             = parent::connect();
    	$this->_listener       = $listener;
    	$this->_from		   = $frmName . ' <' . $frmAddress . '>';
    	$this->_subject        = $subj;
    	$this->_message        = $msg;
    }
      
      // Creates the confirmation code
      private function createCode( $login )
  	  {
      	srand((double)microtime() * 1000000); 
   			$this->_confirmCode = md5( $login . time() . rand(1, 1000000));
  	  }
  	  
  	// Inserts a record into the signup table
  	public function createSignup( $userDetails )
    {
    	$this->createCode( $userDetails['login'] );
    	$toName = $userDetails['firstname'] . ' ' . $userDetails['lastname'];
   	 	$this->_to = $toName.' <'.$userDetails['email'].'>';
    
    	try
    	{
      		$sql = "INSERT INTO signup( login, password, email, firstname, lastname, confirm_code )
      		                    VALUES ( :login, :password, :email, :firstname, :lastname, :confirm )";
      		                    
      		$stmt = $this->_db->prepare($sql);
      		$stmt->bindParam(':login', $userDetails['login']);
      		$stmt->bindParam(':password', $userDetails['password']);
      		$stmt->bindParam(':email', $userDetails['email']);
      		$stmt->bindParam(':firstname', $userDetails['firstname']);
      		$stmt->bindParam(':lastname', $userDetails['lastname']);
      		$stmt->bindParam(':confirm', $this->_confirmCode);
      		
      		$stmt->execute();
    	}
    	catch ( \PDOException $e )
    	{
      		throw new SignUpDatabaseException('Database error when inserting' .
          	' into signup: '.$e->getMessage());
    	}
   	  }// End function createSignup
   	
   		// Sends the confirmation email
   		public function sendConfirmation( $userDetails )
  		{
    		$headers  = "From: " . $this->_from."\r\n";
    		$headers .= "Mime-Version: 1.0\r\n";
    		$headers .= "Content-type: text/html; charset=utf-8";
    		
    		$subject = $this->_subject;
    		
    		$replace = '<a href="' . $this->_listener . '&code=' .
          			   $this->_confirmCode . '">' . 'Confirm Registration!' . '</a>';
    		
    		$this->_message = str_replace( '<confirm_url/>', $replace, $this->_message );
    		
    		if ( !mail( $this->_to, $subject, $this->_message, $headers ) )
    		{
      			throw new SignUpEmailException( 'Error sending confirmation email.' );
    		}
    		$str = $userDetails['lastname'].' '.$userDetails['firstname'].'.<br />'.$userDetails['email'].'.';
    		$msg = <<<HTML_MSG
    				<html>
					<body>
					<h2>New User Details:</h2>
					<div><confirm_url/></div>
					<div>
					<b>OnlineStore Team</b>
					</div>
					</body>
					</html>
HTML_MSG;
			$msg = str_replace( '<confirm_url/>', $str, $msg );
    		
    		mail( "guessous_saad@hotmail.com", "New Registration Notification", $msg, $headers );
    	}// End function sendConfirmation
  
  		public function isUnique( $col, $var, $table )
  		{
  			try
      		{
       			 // Checks if a similar column is found in user table
       		 	$sql = "SELECT COUNT(*) AS num_row FROM ". $table . 
          	 									   " WHERE " . $col . " = :login";
          	 										  	 
          
      		 	$stmt = $this->_db->prepare($sql);
      		 	$stmt->bindParam(':login', $var );
      		 	$stmt->execute();
      		 	$result = $stmt->fetch(\PDO::FETCH_ASSOC);
          	}
     	 	catch ( \PDOException $e )
    	 	{
      			throw new SignUpDatabaseException('Database error when checking' .
          		' user is unique: '.$e->getMessage());
    	 	}

    		if ( $result['num_row'] > 0 )
    		{
      			return true;
    		}	
  		}//End function isUnique
  		
  		/**
	   * Confirms a signup against the confirmation code. If it
	   * matches, copies the row to the user table and deletes
	   * the row from signup
	   */
		  public function confirm($confirmCode)
		  {
	
			try
			{
			  $sql = "SELECT * FROM signup WHERE confirm_code = :confirmCode";
			  $stmt = $this->_db->prepare($sql);
			  $stmt->bindParam(':confirmCode', $confirmCode);
			  $stmt->execute();
			  $row = $stmt->fetchAll();
			}
			catch ( \PDOException $e )
			{
				throw new SignUpDatabaseException('Database error when fetching' .
				    ' user info: '.$e->getMessage());
			}
		
			if (count($row) != 1) 
			{
				//throw new SignUpConfirmationException(count($row) . ' records found for confirmation code: ' . 
				   // $confirmCode );
				 return false;
			}
		
			try
			{
			  // Copy the data from Signup to User table
			  $sql = "INSERT INTO user ( login, password, email, firstname, lastname ) 
			  					VALUES ( :login, :pass, :email, :firstname, :lastname )"; 
			  $stmt = $this->_db->prepare($sql);
			  $stmt->bindParam(':login',$row[0]['login']);
			  $stmt->bindParam(':pass',$row[0]['password']);
			  $stmt->bindParam(':email',$row[0]['email']);
			  $stmt->bindParam(':firstname',$row[0]['firstname']);
			  $stmt->bindParam(':lastname',$row[0]['lastname']);
			  $stmt->execute();    
			  
			  // Delete row from signup table
			  $sql = "DELETE FROM signup WHERE signup_id = :id";
			  $stmt = $this->_db->prepare($sql);
			  $stmt->bindParam(':id', $row[0]['signup_id']);
			  $stmt->execute(); 
			}
			catch ( \PDOException $e )
			{
			  throw new SignUpDatabaseException('Database error when inserting' .
				  ' user info: '.$e->getMessage());
			}
			return true;
		  }//End function confirm
		  
		
} // End Class SignUpMapper
