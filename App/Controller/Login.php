<?php

namespace App\Controller;

use App\FrontController;
use App\Model\UserMapper;
use App\Model\ShoppingCartMapper;
use App\Model\SignUpMapper;
use App\Model\CountriesMapper;
use App\Validators\RegFormValidator;
use App\Util\Auth;
use App\Util\Session;

Class Login extends AbstractController {

	protected $_notifyUrl;
	protected $_cancelUrl;
	protected $_returnUrl;
	protected $_paypalEmail;
		
		public function index()
		{
			$session = new Session();
			$this->viewVars->path = $session->get('path');
			
			$SCart = new ShoppingCartMapper();
			$SCart->setCartId();	
			$this->viewVars->qty = $SCart->getQuantity();
			$this->viewVars->CartProducts = $SCart->getCartProducts();
			
			$auth = new Auth();
			$auth->register( 'Login->index', $_SERVER['REMOTE_ADDR'], gethostbyaddr( $_SERVER['REMOTE_ADDR'] ) );
			
			$this->viewVars->loggedIn = NULL;
			$this->viewVars->admin = NULL;
			/*if( $auth->isLogged() )
			{
				$this->viewVars->loggedIn = TRUE;
				$this->viewVars->msg = "Welcome Back !";
			
				//Checks for admin credentials
				if( $auth->isAdmin( $auth->getLogin() ) )
				{
					$this->viewVars->admin = TRUE;
				}
			}	*/
			
			$this->viewVars->warning = NULL;
			if( isset($_GET['checkout']) && ($_GET['checkout'] == 'checkout') && !$auth->isLogged())
			{
				$this->viewVars->warning = TRUE;
			}
			elseif(isset($_GET['checkout']) && ($_GET['checkout'] == 'checkout') && $auth->isLogged())
			{
				header('Location:'.$session->get('path').'/index.php?controller=login&action=login&checkout=checkout');
				exit();
			}
			
			
				
		}	
		
		public function register() 
		{	
			$session = new Session();
			$this->viewVars->path = $session->get('path');
			
			$SCart = new ShoppingCartMapper();
			$SCart->setCartId();	
			$this->viewVars->qty = $SCart->getQuantity();
			$this->viewVars->CartProducts = $SCart->getCartProducts();					
			
			//$countryMapper = new CountriesMapper();
			//$this->viewVars->Countries = $countryMapper->getCountries();
			$auth = new Auth();
			$auth->register( 'Login->register', $_SERVER['REMOTE_ADDR'], gethostbyaddr( $_SERVER['REMOTE_ADDR'] ) );
			$this->viewVars->loggedIn = NULL;
			$this->viewVars->admin = NULL;
			if( $auth->isLogged() )
			{
				$this->viewVars->loggedIn = TRUE;
				$this->viewVars->msg = "Welcome Back !";
			
				//Checks for admin credentials
				if( $auth->isAdmin( $auth->getLogin() ) )
				{
					$this->viewVars->admin = TRUE;
				}
			
			}
			
			
		}
	
	public function login()
	{
		$user = new UserMapper();
		$this->viewVars->user = NULL;
		
		$session = new Session();
		$this->viewVars->path = $session->get('path');
		
		$SCart = new ShoppingCartMapper();
		$SCart->setCartId();	
		$this->viewVars->qty = $SCart->getQuantity();
		$this->viewVars->CartProducts = $SCart->getCartProducts();		
	
		$auth = new Auth();
		$auth->register( 'Login->login', $_SERVER['REMOTE_ADDR'], gethostbyaddr( $_SERVER['REMOTE_ADDR'] ) );
		$auth->login();
		$this->viewVars->login = $auth->getLogin();
		
		if( isset( $_GET['logout'] ) && $_GET['logout'] == 'logout' )
		{
			$user->lastLog( $auth->getLogin() );
			$auth->logout();						
		}
		
		$this->viewVars->loggedIn = NULL;
		$this->viewVars->admin = NULL;
		if( $auth->confirmAuth() )
		{
			$this->viewVars->loggedIn = TRUE;
			$this->viewVars->msg = "Welcome back ";
			
            //Fetch user data from user table into user variable
			$this->viewVars->user = $user->fetchUser( $auth->getLogin() );
			
			//Checks for admin credentials
			if( $auth->isAdmin( $auth->getLogin() ) )
			{
				//$this->viewVars->admin = TRUE;
				header('Location:index.php?controller=admin');
			}
		}

		// If param checkout is true, it means that login has been called through the cart in order to checkout
		// We set another var checkout to true and we take the proper action in the view  
		if( isset($_GET['checkout']) && ($_GET['checkout'] == 'checkout')) 
		{
			$this->viewVars->checkout = TRUE;
			// We check to see if user address in user table is not null
			$address = $user->returnClmn('address_line_1', $auth->getLogin() );
			if($address['address_line_1']) 
			{
				$this->viewVars->address = TRUE;
			}
			else 
			{
				$this->viewVars->address = NULL;
				$countries = new CountriesMapper();
				$this->viewVars->countries = $countries->getCountries();
			}
		}
		else 
		{
			$this->viewVars->checkout = NULL;
		}
		
		
	}// End function login
		
		public function signup()
		{
			$session = new Session();
			$this->viewVars->path = $session->get('path');

			$auth = new Auth();
			$auth->register( 'Login->signup', $_SERVER['REMOTE_ADDR'], gethostbyaddr( $_SERVER['REMOTE_ADDR'] ) );
			
			$SCart = new ShoppingCartMapper();
			$SCart->setCartId();	
			$this->viewVars->qty = $SCart->getQuantity();
			$this->viewVars->CartProducts = $SCart->getCartProducts();	
			
			$urlPath = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
			
			$reg_messages = array( 'success' => array( 'title'   => 'Confirmation Successful!',
        												 'content' => '<p>Thank you! Your account has now been confirmed.' .
        															  'You can now <a href="<confirm_url/>">login</a></p>'
    													),
    								'confirm_error' => array( 'title'   => 'Confirmation Problem',
        													   'content' => '<p>There was a problem confirming your account.' .
        																	'<br />Please try again or contact the site ' .
        																	'administrators</p>'
   															 ),
    								'email_sent' => array( 'title'   => 'Check your email!',
        													'content' => '<p>Thank you! Please check your email to ' .
        																 'confirm your account.</p>'
    														),
    								'email_error' => array( 'title'   => 'Email Problem',
        													 'content' => '<p>Unable to send confirmation email.<br />' .
                                                                          'Please contact the site administrators.</p>'
    														),
    							    'signup_not_unique' => array( 'title'   => 'Registration Problem',
        														   'content' => '<p>There was an error creating your account.<br />' .
        																		'The desired username or email address has already been taken.</p>'
    															  ),
    								'signup_error' => array( 'title'   => 'Registration Problem',
        													  'content' => '<p>There was an error creating your account.<br />' .
        																   'Please contact the site administrators.</p>'
    														) );
    		// Settings for SignUp class
			$listener = $urlPath;
			$frmName = 'OnlineStore Admin';
			$frmAddress = 'noreply@onlinestore.com';
			$subj = 'Account Confirmation';
			$msg = <<<EOD
				<html>
				<body>
				<h2>Thank you for registering!</h2>
				<div>The final step is to confirm 
				your account by clicking on:</div>
				<div><confirm_url/></div>
				<div>
				<b>OnlineStore Team</b>
				</div>
				</body>
				</html>
EOD;
			
			try
			{
				// Instantiate the signup class
  				$signUp = new SignUpMapper( $listener, $frmName, $frmAddress, $subj, $msg );
  				
  				$this->viewVars->admin = NULL;
  				$this->viewVars->loggedIn = NULL;
  				
  				// Is this an account confirmation?
  				if ( isset( $_GET['code']) )
  				{
  					if( $signUp->confirm( $_GET['code'] ) )
  					{
	  					//Replace <confirm_url/> with real login url in message content
	  					$msg = $reg_messages['success']['content'];
	  					$msg = str_replace( '<confirm_url/>', 'http://'.$_SERVER['SERVER_NAME'].'/project/index.php?controller=login', $msg );
		  				$this->viewVars->display = '<div><h3 class="success">'.$reg_messages['success']['title']
		    					 				   .'</h3><h2 class="neutral">'.$msg
		    					 				   .'</h2></div>'; 
		  				$this->viewVars->missingFields = NULL;
      				}
      				else
      				{
      					$this->viewVars->display = '<div><h3 class="codeErr">Confirmation Code Error!'
		    					 				   .'</h3><h2 class="neutral">There was an error with your code: '.$_GET['code']
		    					 				   .' Sign up again!</h2></div>'; 
		  				$this->viewVars->missingFields = NULL;
      				}
  				}
  				// Has the form been submitted?
  				elseif ( isset( $_POST['username'] ) )
  				{
  					$submitVars = array( 'login' => $_POST['username'],
          								  'password' => $_POST['password'],
          								  'email' => $_POST['email'],
          							      'firstname' => $_POST['firstname'],
          								  'lastname' => $_POST['lastname'], 
          								  'email2'	=> $_POST['email2'],
          								  'password2' => $_POST['password2'] );
          								  
          		    $formValidator = new RegFormValidator();
          		    
          		    //Check for empty fields
          		    $missingFields = $formValidator->processForm( $submitVars );
                    
                    //If no empty field is found, validate login, emails and passwords
                    if(!$missingFields)
                    {
                    	$missingFields = $formValidator->validateFields( $submitVars );
                    }
                    
                    $this->viewVars->loginMsg = NULL;
                    $this->viewVars->emailMsg = NULL;
                    
                    //Finally check the uniqueness of login & email
                    if( $signUp->isUnique( 'login', $submitVars['login'], 'user' ) )
                    {
                    	$missingFields[] = 'login';
                    	$this->viewVars->loginMsg = 'Chosen username is already in use! Enter another one!';
                    	
                    }
                    
                    if( $signUp->isUnique( 'email', $submitVars['email'] , 'user' ) )
                    {
                    	$missingFields[] = 'email';
                    	$this->viewVars->emailMsg = 'Chosen email is already in use! Enter another one!';
                    	
                    }
                    //If error in submitted data, display it 
                    $this->viewVars->missingFields = NULL;
                    if( $missingFields )
                    {
                    	$this->viewVars->missingFields = $missingFields;
                    	$this->viewVars->messages = $formValidator->getMessages();
                    	$this->viewVars->vars = $submitVars;
                    }
                    else
                    {         		    
          		    
          				try
      					{
      						$submitVars['password'] = md5( $submitVars['password'] );
      						$submitVars['password2'] = $submitVars['password'];	
        				// Create signup 
        					$signUp->createSignup($submitVars);
        				// Send confirmation email
        					$signUp->sendConfirmation($submitVars);
        				
        					$this->viewVars->display = '<div><h3 class="signup">'.$reg_messages['email_sent']['title']
        					 						   .'</h3><h2 class="neutral">'.$reg_messages['email_sent']['content']
     												   .'</h2></div>';
        					//$this->viewVars->data = $submitVars;
      					}
      					catch (SignUpEmailException $e)
      					{
        					$this->viewVars->display = $reg_messages['email_error'];
      					}
     					catch (SignUpNotUniqueException $e)
      					{
        					$this->viewVars->display = $reg_messages['signup_not_unique'];
      					}
      					catch (SignUpException $e)
      					{
        					$this->viewVars->display = $reg_messages['signup_error'];
      					}
      				  }// End inner if
  					}// End outer if
  				
				} // End outer try
				catch (Exception $e)
				{
  					error_log( 'Error in '.$e->getFile().
      					   	   ' Line: '.$e->getLine().
      					   	   ' Error: '.$e->getMessage() );
  					$display = $reg_messages['signup_error'];
				}        
			
			
		} // End function signup
		
		/**
	* Saves user address in user table if $_POST['save'] is set and is equal to 'save'
	* Lists products to be purchased and enables user to proceed with order  
	* @param none
	* @return none
	*/
		public function proceed()
		{
			$session = new Session();
			$this->viewVars->path = $session->get('path');
			
			$SCart = new ShoppingCartMapper();
			$SCart->setCartId();	
			$this->viewVars->qty = $SCart->getQuantity();
			$this->viewVars->CartProducts = $SCart->getCartProducts();
			
			$auth = new Auth();
			$auth->register( 'Login->proceed', $_SERVER['REMOTE_ADDR'], gethostbyaddr( $_SERVER['REMOTE_ADDR'] ) );
			
			$this->viewVars->loggedIn = NULL;
			$this->viewVars->admin = NULL;
			if( $auth->isLogged() )
			{
			  $this->viewVars->loggedIn = TRUE;
			  $this->viewVars->login = $auth->getLogin();
				
				//Checks for admin credentials
				if( $auth->isAdmin( $auth->getLogin() ) )
				{
					$this->viewVars->admin = TRUE;
				}
			}	
			if( !$this->viewVars->loggedIn ) $auth->redirect();	
			
			$user = new UserMapper();
			$this->viewVars->userInfo = $user->retUserInfo($auth->getLogin());
			
			//Checks that user has a valid address			
			$address = $user->returnClmn('address_line_1', $auth->getLogin());
			if(!$address['address_line_1']){ header('Location:'.$session->get('path').'/index.php?controller=login&action=login&checkout=checkout'); } 
			
			//If method proceed is called within login to save user's address 
			if( isset($_GET['save']) && ($_GET['save'] == 'save') )
			{
				$user->updateUser($_POST);
			}
			
			if( isset($_GET['cart']) && ($_GET['cart'] == 'add') )
			{
				$SCart->addItem();
				header('Location:'.$session->get('path').'/index.php?controller=login&action=proceed');
			}
			
			if( isset($_GET['cart']) && ($_GET['cart'] == 'remove') )
			{
				$SCart->removeItem();
				header('Location:'.$session->get('path').'/index.php?controller=login&action=proceed');
			}
		}//End function proceed
		
		public function sendPayment()
		{
			$session = new Session();
			$this->viewVars->path = $session->get('path');
			
			$SCart = new ShoppingCartMapper();
			$SCart->setCartId();	
			$this->viewVars->qty = $SCart->getQuantity();
			$this->viewVars->CartProducts = $SCart->getCartProducts();
			
			$auth = new Auth();
			$auth->register( 'Login->proceed', $_SERVER['REMOTE_ADDR'], gethostbyaddr( $_SERVER['REMOTE_ADDR'] ) );
			
			$this->viewVars->loggedIn = NULL;
			$this->viewVars->admin = NULL;
			if( $auth->isLogged() )
			{
			  $this->viewVars->loggedIn = TRUE;
			  $this->viewVars->login = $auth->getLogin();
				
				//Checks for admin credentials
				if( $auth->isAdmin( $auth->getLogin() ) )
				{
					$this->viewVars->admin = TRUE;
				}
			}	
			if( !$this->viewVars->loggedIn ) $auth->redirect();	
			
			$user = new UserMapper();
			$this->viewVars->userInfo = $user->retUserInfo($auth->getLogin());
			
			$this->_paypalEmail = 'john@city.uk';
			
			$this->_notifyUrl = 'http://rascal.mooo.com/project2/index.php?controller=ipn';
			
			$msg = 'Your payment was cancelled!';
			$this->_cancelUrl = 'http://rascal.mooo.com/project2/index.php?controller=login&action=notify&msg='.$msg;
			
			$msg = 'Your payment was successfull!';
			$this->_returnUrl = 'http://rascal.mooo.com/project2/index.php?controller=login&action=notify&msg='.$msg;
			
			$queryString = '?business='.urlencode($this->_paypalEmail).'&';
			
			foreach($_POST as $key => $value)
			{
				$value = urlencode(stripslashes($value));
				$queryString .= "$key=$value&";
			}
			
			$queryString .= 'return='.urlencode(stripslashes($this->_returnUrl)).'&';
			$queryString .= 'cancel_return='.urlencode(stripslashes($this->_cancelUrl)).'&';
			$queryString .= 'notify_url='.urlencode(stripslashes($this->_notifyUrl));
			
			header('Location:https://www.sandbox.paypal.com/cgi-bin/webscr'.$queryString);
			
			exit();
			
			$this->viewVars->queryString = $queryString;
			
			
		}
		
		public function notify()
		{
			$session = new Session();
			$this->viewVars->path = $session->get('path');
			
			$SCart = new ShoppingCartMapper();
			$SCart->setCartId();	
			$this->viewVars->qty = $SCart->getQuantity();
			$this->viewVars->CartProducts = $SCart->getCartProducts();
			
			$auth = new Auth();
			$auth->register( 'Login->proceed', $_SERVER['REMOTE_ADDR'], gethostbyaddr( $_SERVER['REMOTE_ADDR'] ) );
			
			$this->viewVars->loggedIn = NULL;
			$this->viewVars->admin = NULL;
			if( $auth->isLogged() )
			{
			  $this->viewVars->loggedIn = TRUE;
			  $this->viewVars->login = $auth->getLogin();
				
				//Checks for admin credentials
				if( $auth->isAdmin( $auth->getLogin() ) )
				{
					$this->viewVars->admin = TRUE;
				}
			}	
			if( !$this->viewVars->loggedIn ) header('Location:index.php?controller=login&action=login');	
			
			$user = new UserMapper();
			$this->viewVars->userInfo = $user->retUserInfo($auth->getLogin());
			
			$this->viewVars->msg = $_GET['msg'];
		}
	
	}//End Class Login
