<?php

namespace App\Controller;

use App\FrontController;
use App\Model\ShoppingCartMapper;
use App\Model\ProductMapper;
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
			
			$PMapper = new ProductMapper();
			$this->viewVars->Dvds = $PMapper->getAllProducts( NULL,"Dvd");
					
		}// End index	
	
	}
