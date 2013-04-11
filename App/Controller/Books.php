<?php

namespace App\Controller;

use App\FrontController;
use App\Model\ProductMapper;
use App\Model\ProductTypesMapper;
use App\Model\ShoppingCartMapper;
use App\Util\Auth;
use App\Util\Session;

class Books extends AbstractController {

		public function index()
		{
			$session = new Session();
			$this->viewVars->path = $session->get('path');
			
			$PMapper = new ProductMapper();
			$this->viewVars->Products = $PMapper->getAllProducts( null,"Book" );
			
			$SCart = new ShoppingCartMapper();
			$SCart->setCartId();	
			$this->viewVars->qty = $SCart->getQuantity();	
			$this->viewVars->CartProducts = $SCart->getCartProducts();
			
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
		}	
		
		public function showproduct()
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
		}//End showProduct	
	
	}
