<?php 
namespace App\Validators;

use App\Model\SignUpMapper;

	class RegFormValidator extends AbstractValidator
	{
		public function __construct()
		{
		}
		
		// Process data form & checks for empty fields
		public function processForm( $formData )
		{
			$requiredFields = array( "login", "firstname", "lastname", "email", "email2", "password", "password2" );
			$missingFields = array();
			
			foreach ( $requiredFields as $requiredField )
			{
				if ( !$formData[$requiredField] )
				{
					$missingFields[] = $requiredField;
					$this->messages[] = "The $requiredField field cannot be empty!";
				}
			} 
			return $missingFields;
		}// End function processForm
		
		public function validateFields( $formData )
		{
			$missingFields = array();
			
			//login field should be between 6 and 12 characters long
			if( !$this->strLength( $formData['login'], 6, 12 ) )
			{
				$missingFields[] = 'login'; 
			}
			
			
			
		/*	//Check for uniqueness of login
			$formMapper = new SignUpMapper();
			if( $formMapper->isUnique( 'login', $submitVars['login'] ) )
            {
                $missingFields[] = 'login';
                $this->messages[] = "Entered username is already in use, choose another one!";
            } */
			
			if( $missingFields ) return $missingFields;
			
			//Check that both emails are not beyond 50 chars long
			if( !$this->strLength( $formData['email'], 5, 50 ) )
			{
				$missingFields[] = 'email'; 
			}
			
			if( !$this->strLength( $formData['email2'], 5, 50 ) )
			{
				$missingFields[] = 'email2'; 
			}
			
			//Validate email field
			if( !$this->email( $formData['email'] ) )
			{
				$missingFields[] = 'email';
			}
			
			//Validate email2 field
			if( !$this->email( $formData['email2'] ) )
			{
				$missingFields[] = 'email2'; 
			}
		
			//Check to see that both emails are identical
			$var = strcmp( $formData['email'], $formData['email2'] );
			
			if( $var!=0 )
			{
				$this->messages[] = "The entered emails are not identical. Retype them correctly!";
				$missingFields = array( 'email', 'email2' );
			}
			
			if( $missingFields ) return $missingFields;
			
			//password & password2 should be between 6 and 12 characters long
			if( !$this->strLength( $formData['password'], 6, 12 ) )
			{
				$this->messages = array();
				$this->messages[] = "The entered password should be between 6 and 12 characters long!";
				$missingFields[] = 'password'; 
			}
			
			if( !$this->strLength( $formData['password2'], 6, 12 ) )
			{
				$this->messages = array();
				$this->messages[] = "The entered password should be between 6 and 12 characters long!";
				$missingFields[] = 'password2'; 
			}
			
			if( $missingFields ) return $missingFields;
			
			//Check to see that first password contains 1 number at least
			if( preg_match_all( '/[0-9]/' ,$formData['password'], $out ) < 1 )
			{
				$this->messages = array();
				$this->messages[] = "The password should contain at least one number!";
				$missingFields[] = 'password';
			}
			
			//Check to see that both passwords are identical
			$var = strcmp( $formData['password'], $formData['password2'] );
			
			if( $var!=0 )
			{  
			    $this->messages = array();
				$this->messages[] = "The entered passwords are not identical. Retype them correctly!";
				$missingFields = array( 'password', 'password2' );
			}
			
			return $missingFields;
			
		}//End function validateFields
		
		protected function strLength( $value, $minLen=0, $maxLen=0 )
		{	
			if( strlen($value) < $minLen )
			{
				$this->messages[] = "$value is less than ".$minLen." characters long";
            	return false;
			}
			
			if( strlen($value) > $maxLen )
			{
				$this->messages[] = "$value is more than ".$maxLen." characters long";
            	return false;
			}
			
			return true;
		}
		
		protected function email( $value )
		{
			if(!filter_var($value, FILTER_VALIDATE_EMAIL))
			{
            	$this->messages[] = "The email address does not appear to be valid";
            	return false;
        	}
        	
        	return true;
		}
		
		protected function valid_pass($candidate) 
		{
   			$r1='/[A-Z]/';  //Uppercase
   			$r2='/[a-z]/';  //lowercase
   			$r3='/[!@#$%^&*()\-_=+{};:,<.>]/';  // special char!
   			$r4='/[0-9]/';  //numbers

   			if(preg_match_all($r1,$candidate, $o)<2) return FALSE;

   			if(preg_match_all($r2,$candidate, $o)<2) return FALSE;

   			if(preg_match_all($r3,$candidate, $o)<2) return FALSE;

   			if(preg_match_all($r4,$candidate, $o)<2) return FALSE;

   			if(strlen($candidate)<8) return FALSE;

   			return TRUE;
		}
		
 		public function shorten_string($string, $wordsreturned)
	  /*  Returns the first $wordsreturned out of $string.  If string
		contains fewer words than $wordsreturned, the entire string
		is returned.
		*/
		{
		  $retval = $string;      //  Just in case of a problem
		 
		  $array = explode(" ", $string);
		  if (count($array)<=$wordsreturned)
		/*  Already short enough, return the whole thing
		*/
		  {
		   $retval = $string;
		  }
		  else
		/*  Need to chop of some words
		*/
		  {
		   array_splice($array, $wordsreturned);
		   $retval = implode(" ", $array)." ...";
		  }
		  return $retval;
		}
		
	} // End Class RegFormValidator

