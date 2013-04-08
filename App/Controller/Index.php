<?php
namespace App\Controller;

use App\FrontController;
use App\Model\ProductMapper;
use App\Model\ProductTypesMapper;
use App\Model\ShoppingCartMapper;
use App\Util\Auth;
use App\Util\Session;
use App\Validators\RegFormValidator;

class Index extends AbstractController {
		
		public function index(){
		
			$session = new Session();
			$this->viewVars->path = $session->get('path');
		
			// We get the most recent four books for display.
			$PMapper = new ProductMapper();
			$this->viewVars->Products = $PMapper->getAllProducts( 4,"Book" );
			$this->viewVars->Dvds = $PMapper->getAllProducts( 4,"Dvd" );
			
			//$wordCount = new RegFormValidator();
			
			//foreach($Products as $product)
				//	$Products->product_description = $wordCount->shorten_string($product->description_name, 10);
			
			
			//$PTypesMapper = new ProductTypesMapper();
			//$this->viewVars->PTypes = $PTypesMapper->fetchMPTypes(); 
			// This code gets the main product types. The navigation tabs are loaded based on these.
			
			$SCart = new ShoppingCartMapper();
			$SCart->setCartId();	
			$this->viewVars->qty = $SCart->getQuantity();	
			$this->viewVars->CartProducts = $SCart->getCartProducts();		
			
			//Are we logged in?
			$auth = new Auth();	
			$auth->register( 'Main->index', $_SERVER['REMOTE_ADDR'], gethostbyaddr( $_SERVER['REMOTE_ADDR'] ) );
			
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
		
		
		/*public function showproduct(){
		
			$PMapper = new ProductMapper();
			$PTMapper = new ProductTypesMapper();
			
			$SCart = new ShoppingCartMapper();
			$SCart->setCartId();	
			$this->viewVars->qty = $SCart->getQuantity();
		
			$pid = ( int ) $_GET["pid"];
		
			$this->viewVars->Product = $PMapper->getProduct( $pid );	
			$this->viewVars->ProductTypeName = $PTMapper->fetchTypeName( $this->viewVars->Product->ptype_id );
			
			//Are we logged in?
			$auth = new Auth();	
			
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
						
		} */	
		
		// Remove item from shopping cart @param $item_id
		public function removeCartItem( $item_id )
		{
			$auth = new Auth();
			$auth->register( 'Main->removeCartItem', $_SERVER['REMOTE_ADDR'], gethostbyaddr( $_SERVER['REMOTE_ADDR'] ) );

			$SCart = new ShoppingCartMapper();
			$SCart->setCartId();
			$SCart->removeItem( $item_id );
		}
	
	}
