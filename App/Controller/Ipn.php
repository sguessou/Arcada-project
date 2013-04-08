<?php

namespace App\Controller;

use App\Util\Session;
use App\Model\ShoppingCartMapper;
use App\Util\Auth;
use App\Util\IpnListener;
use App\Model\AbstractMapper;

class Ipn extends AbstractController
{

	protected $_db;
			
	public function index()
	{
			$session = new Session();
			$this->viewVars->path = $session->get('path');
			
			$SCart = new ShoppingCartMapper();
			$SCart->setCartId();	
			$this->viewVars->qty = $SCart->getQuantity();
			$this->viewVars->CartProducts = $SCart->getCartProducts();
			
			$auth = new Auth();
			$auth->register( 'Ipn->index', $_SERVER['REMOTE_ADDR'], gethostbyaddr( $_SERVER['REMOTE_ADDR'] ) );
			
			$this->viewVars->loggedIn = NULL;
			$this->viewVars->admin = NULL;
			if( $auth->isLogged() )
			{
				$this->viewVars->loggedIn = TRUE;
			
				//Checks for admin credentials
				if( $auth->isAdmin( $auth->getLogin() ) )
				{
					$this->viewVars->admin = TRUE;
				}
			}		
			
			// tell PHP to log errors to ipn_errors.log in this directory
			ini_set('log_errors', true);
			ini_set('error_log', dirname(__FILE__).'/log/ipn_errors.log');
			
			// intantiate the IPN listener
			//include('ipnlistener.php');
			$listener = new IpnListener();

			// tell the IPN listener to use the PayPal test sandbox
			$listener->use_sandbox = true;
			
			// try to process the IPN POST
			try 
			{
    		$listener->requirePostMethod();
    		$verified = $listener->processIpn();
			} 
			catch (\Exception $e) 
			{
    		error_log($e->getMessage());
    		exit(0);
			}	

			if ($verified) 
			{
    		$errmsg = '';   // stores errors from fraud checks
    
    		// 1. Make sure the payment status is "Completed" 
    		if ($_POST['payment_status'] != 'Completed') 
    		{ 
        	// simply ignore any IPN that is not completed
        	exit(0); 
    		}

    		// 2. Make sure seller email matches your primary account email.
    		if ($_POST['receiver_email'] != 'YOUR PRIMARY PAYPAL EMAIL') 
    		{
        	$errmsg .= "'receiver_email' does not match: ";
        	$errmsg .= $_POST['receiver_email']."\n";
    		}
    
    		// 3. Make sure the amount(s) paid match
    		if ($_POST['mc_gross'] != '9.99') 
    		{
        	$errmsg .= "'mc_gross' does not match: ";
        	$errmsg .= $_POST['mc_gross']."\n";
    		}
    
    		// 4. Make sure the currency code matches
    		if ($_POST['mc_currency'] != 'USD') 
    		{
        	$errmsg .= "'mc_currency' does not match: ";
        	$errmsg .= $_POST['mc_currency']."\n";
    		}

    		// 5. Ensure the transaction is not a duplicate.
    		//$this->_db = parent::connect();
    		
    		//mysql_connect('localhost', 'DB_USER', 'DB_PW') or exit(0);
    		//mysql_select_db('DB_NAME') or exit(0);

    		$txn_id = mysql_real_escape_string($_POST['txn_id']);
    		
    		$DBSettings = parse_ini_file( '/project2/App/Model/db_settings.ini', TRUE );
				$conn = new \PDO( $DBSettings['db_settings']['DB_DSN'], $DBSettings['db_settings']['DB_USERNAME'], 
		    $DBSettings['db_settings']['DB_PASSWORD'] );
				$conn->setAttribute( \PDO::ATTR_PERSISTENT, true );		
				$conn->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
				$this->_db = $conn;
				
    		$sql = "SELECT COUNT(*) FROM paypal_ipn WHERE txn_id = '$txn_id'";
    		$stmt = $this->_db->query($sql);
    		//$r = mysql_query($sql);
    
    		if (!$stmt->execute()) 
    		{
        	error_log(mysql_error());
        	exit(0);
    		}
    
    		$exists = $stmt->fetch();
    		//mysql_result($r, 0);
    		//mysql_free_result($r);
    
    		if ($exists > 0) 
    		{
        	$errmsg .= "'txn_id' has already been processed: ".$_POST['txn_id']."\n";
    		}
    
    		if (!empty($errmsg)) 
    		{
        	// manually investigate errors from the fraud checking
        	$body = "IPN failed fraud checks: \n$errmsg\n\n";
       	 	$body .= $listener->getTextReport();
        	mail('guessous_saad@hotmail.com', 'IPN Fraud Warning', $body);
    		}	 
    		else 
    		{
        	// add this order to a table of completed orders
        	$payer_email = mysql_real_escape_string($_POST['payer_email']);
        	$mc_gross = mysql_real_escape_string($_POST['mc_gross']);
        	$sql = "INSERT INTO paypal_ipn VALUES 
                (NULL, '$txn_id', '$payer_email', $mc_gross)";
          $stmt = $this->_db->prepare($sql);
        
        		if (!$stmt->execute()) 
        		{
            	error_log(mysql_error());
            	exit(0);
        		}
        
        		// send user an email with a link to their digital download
        		$to = filter_var($_POST['payer_email'], FILTER_SANITIZE_EMAIL);
        		$subject = "Your digital download is ready";
        		mail($to, "Thank you for your order", "Download URL: ...");
    		}	
			}//End outer if 
			else 
			{
    // manually investigate the invalid IPN
    mail('guessous_saad@hotmail.com', 'Invalid IPN', $listener->getTextReport());
		  }

	}//End function index	
				
}//End class Ipn

