<?php
namespace App\Controller;


use App\Model\ProductTypesMapper;
use App\Model\ProductMapper;
use App\Model\ProductTypes;
use App\Model\Product;
use App\Util\Auth;
use App\Util\Session;
use App\Model\UserMapper;
use App\Model\AccessLogMapper;

class Admin extends AbstractController {

	public function index() 
	{
		$user = new UserMapper();
		$this->viewVars->user = NULL;
		
		$auth = new Auth();		
		$session = new Session();
		$this->viewVars->path = $session->get('path');	

		$this->viewVars->loggedIn = NULL;
		$this->viewVars->admin = NULL;
	
		if( $auth->isLogged() )
		{
			$this->viewVars->loggedIn = TRUE;
			$this->viewVars->msg = "Welcome Back";
			
				//Checks for admin credentials
				if( $auth->isAdmin( $auth->getLogin() ) )
				{
					$this->viewVars->admin = TRUE;
					$this->viewVars->user = $user->fetchUser( $auth->getLogin() );
				}
		}
		else { $auth->logout(); }
													
	}//End function index
			
		
	public function addproduct() 
	{
		$PTMapper = new ProductTypesMapper();
		$this->viewVars->MPTypes = $PTMapper->fetchMPTypes();	
		
		$auth = new Auth();		
		$session = new Session();
		$this->viewVars->path = $session->get('path');	

		$this->viewVars->loggedIn = NULL;
		$this->viewVars->admin = NULL;
	
		if( $auth->isLogged() )
		{
			$this->viewVars->loggedIn = TRUE;
			//$this->viewVars->msg = "Welcome Back !";
			
			//Checks for admin credentials
			if( $auth->isAdmin( $auth->getLogin() ) )
			{
				$this->viewVars->admin = TRUE;
			}
		}
		else { $auth->logout(); }										
	}
			
	public function saveproduct() 
	{
		$auth = new Auth();		
		$session = new Session();
		$this->viewVars->path = $session->get('path');	

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
		 else { $auth->logout(); }							
		
			$PMapper = new ProductMapper();	
			$PMapper->insertProduct( new Product( 0, $_POST["ptype_id"], $_POST["title"], $_POST["price"], $_POST["language"],
																	 $_POST["description"], $_POST["author"], $_POST["isbn10"] ) );
			
	}	

	public function ptypes() 
	{
		$PTMapper = new ProductTypesMapper();
		
		$auth = new Auth();		
		$session = new Session();
		$this->viewVars->path = $session->get('path');	

		$this->viewVars->loggedIn = NULL;
		$this->viewVars->admin = NULL;
	
		if( $auth->isLogged() )
		{
			$this->viewVars->loggedIn = TRUE;
			//$this->viewVars->msg = "Welcome Back !";
			
				//Checks for admin credentials
				if( $auth->isAdmin( $auth->getLogin() ) )
				{
					$this->viewVars->admin = TRUE;
				}
		}						
		else { $auth->logout(); }
			
		if( isset( $_POST['type_name'] ) && $_POST['type_name'] )
		{
			$PTMapper->savePType( new ProductTypes( null, $_POST["parent_ptype_id"], $_POST["type_name"] ) );
			unset( $_POST['type_name'] );
		}	
		
		$this->viewVars->PTypes = $PTMapper->fetchPTypes();
	}	
	
		/**
   * var $logs is assigned array returned by class AccessLogMapper method getAll
   * @param none
   * @access public
   */	
		public function viewLog()
		{
			$auth = new Auth();		
			$session = new Session();
			$this->viewVars->path = $session->get('path');
			
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
			else { $auth->logout(); }
			
			$accessLogs = new AccessLogMapper();
			if( isset( $_POST['date'] ) )
			{
				$accessLogs->deleteLogs( $_POST['date'] );
			}
		
			if( isset( $_GET["start"] ) )
			{
				$this->viewVars->start  = (int)$_GET["start"];
				$this->viewVars->first = (int)$_GET["first"];
			}
			else
			{
				//initialize var first
				$this->viewVars->first = 1;
				$this->viewVars->start = 0;
			} 
			
			$this->viewVars->PAGE_SIZE = $this->_pageSize;
			list( $this->viewVars->logs, $this->viewVars->totalRows ) = $accessLogs->getAll( 'lastAccess', $this->viewVars->start, $this->_pageSize );
		
			
		}//End method viewLog	
		
		public function viewProducts()
		{
			$auth = new Auth();		
			$session = new Session();
			$this->viewVars->path = $session->get('path');
			
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
			else { $auth->logout(); }
			
			$PrdTypMapper = new ProductTypesMapper();
			$this->viewVars->PrdTypes = $PrdTypMapper->fetchPTypes();
			$this->viewVars->products = NULL;
			
			$PrdMapper = new ProductMapper();
			$PSize = 10;
			$this->viewVars->PAGE_SIZE = $PSize;
			
			if(isset($_POST['PType']))
			{		
				//initialize var first
				$this->viewVars->first = 1;
				$this->viewVars->start = 0;
				$this->viewVars->pt = $_POST['PType'];
				list( $this->viewVars->products, $this->viewVars->totalRows ) = $PrdMapper->fetchProducts((int)$_POST['PType'], 'product_id', $this->viewVars->start, $PSize);
				//unset($_POST);
				//return;
			}
			
			if( isset( $_GET["start"] ) )
			{
				$this->viewVars->start  = (int)$_GET["start"];
				$this->viewVars->first = (int)$_GET["first"];
				$this->viewVars->pt = $_GET['ptid'];
			    list( $this->viewVars->products, $this->viewVars->totalRows ) = $PrdMapper->fetchProducts((int)$_GET['ptid'], 'product_id', $this->viewVars->start, $PSize);
				//return;
			} 

		}				
	
 }//End Class Admin
