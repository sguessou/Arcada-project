<?php

namespace App\Controller;

use App\FrontController;
use App\Model\ShoppingCartMapper;
use App\Model\ProductMapper;
use App\Model\ProductTypesMapper;
use App\Util\Auth;
use App\Util\Session;

Class Dvd extends AbstractController {
		public function index(){
		
			$session = new Session();
			$this->viewVars->path = $session->get('path');
			
			$SCart = new ShoppingCartMapper();
			$SCart->setCartId();	
			$this->viewVars->qty = $SCart->getQuantity();	
			$this->viewVars->CartProducts = $SCart->getCartProducts();	
			
			//Are we logged in?
			$auth = new Auth();	
			$auth->register( 'DVD->index', $_SERVER['REMOTE_ADDR'], gethostbyaddr( $_SERVER['REMOTE_ADDR'] ) );
			
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
			
			$PrdMapper = new ProductMapper();

			if(isset($_GET['flag']) && $_GET['flag'] == 'begin')
			{
				$this->viewVars->first = 1;
		    	$this->viewVars->start = 0;
		    	$this->viewVars->PAGE_SIZE = 10;		
				list( $this->viewVars->Dvds, $this->viewVars->totalRows ) = $PrdMapper->fetchProducts(2, 'product_id', $this->viewVars->start, 10);	
				unset($_GET['flag']);
			}				
			
			if( isset( $_GET["start"] ) )
			{
				$this->viewVars->PAGE_SIZE = 10;
				$this->viewVars->start  = (int)$_GET["start"];
				$this->viewVars->first = (int)$_GET["first"];
			    list( $this->viewVars->Dvds, $this->viewVars->totalRows ) = $PrdMapper->fetchProducts(2, 'product_id', $this->viewVars->start, 10);

			} 			
		}// End index
		
		public function showDvd()
		{
			$session = new Session();
			$this->viewVars->path = $session->get('path');
			
			$PMapper = new ProductMapper();
			$PTMapper = new ProductTypesMapper();
			
			$SCart = new ShoppingCartMapper();
			$SCart->setCartId();	
			$this->viewVars->qty = $SCart->getQuantity();
			$this->viewVars->CartProducts = $SCart->getCartProducts();							
		
			$pid = (int) $_GET["pid"];
		
			$this->viewVars->Product = $PMapper->getProduct( $pid );	
			$this->viewVars->ProductTypeName = $PTMapper->fetchTypeName( $this->viewVars->Product->ptype_id );
			
			//Are we logged in?
			$auth = new Auth();	
			$auth->register( 'Books->index', $_SERVER['REMOTE_ADDR'], gethostbyaddr( $_SERVER['REMOTE_ADDR'] ) );
			
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
		}//End showDvd	
	
	}
