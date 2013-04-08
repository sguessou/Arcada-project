<?php

namespace App\Controller;

use App\FrontController;
use App\Model\ShoppingCart;
use App\Model\ShoppingCartMapper;

/**
	* Cart Class
	*	Cart Controller 
	*/
class Cart extends AbstractController {
	
	protected $_data = array();
	
	public function addcart()
	{
		$cart = new ShoppingCartMapper();
		//$this->_data['cart_id'] = $cart->getCartId();
		//$this->_data['pid'] = (int) $_GET['pid'];
		$SCart = new ShoppingCart( 0, $cart->getCartId(), (int) $_GET['pid'], 0, 1, 0, 0 );
		$cart->addProduct( $SCart );
		
		$this->viewVars->flag = FALSE;
		if(isset($_GET['front']))
		{
			$this->viewVars->flag = TRUE;
			unset($_GET['front']);
		}
		
	}
	
	public function checkout()
	{
		
	}

}
