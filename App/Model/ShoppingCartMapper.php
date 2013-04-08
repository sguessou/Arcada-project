<?php


namespace App\Model;



class ShoppingCartMapper extends AbstractMapper {
	
  protected $_cartId;
  protected $_conn;
  protected $_data = array();
  protected $_ProductData = array();
  protected $_CartData = array();
  
  public function __construct() {
  		$this->_conn = parent::connect();
    
     }
  
  public function setCartId() 
  {
  // If Cart ID hasn't been set yet...
  	if( $this->_cartId == '' ) 
  	{
  	   // If ID is in the session, get it from there 
  		if( isset( $_SESSION['cart_id'] ) ) 
  		{
  			$this->_cartId = $_SESSION['cart_id'];
  	     }
  		// If not, check wether the ID was saved as a cookie
  	    elseif( isset( $_COOKIE['cart_id'] ) )
  		{
  			$this->_cartId = $_COOKIE['cart_id'];
  			$_SESSION['cart_id'] = $this->_cartId;
  			
  			// Regenerate cookie to be valid for 7 days (604800 seconds)
  			setcookie( 'cart_id', $this->_cartId, time() + 604800 );
  		}
  		else 
  		{
  			// Generate cart id and save it to $_cartId, the session and a cookie
  			$this->_cartId = md5( uniqid( rand(), true ) );
  			$_SESSION['cart_id'] = 	$this->_cartId;
  			setcookie( 'cart_id', $this->_cartId, time() + 604800 );
  		}
      }
	}
	
	public function getCartId()
	{
		// Ensure we have a cart id for the current visitor
		if( !isset( $this->_cartId ) )
			   $this->setCartId();
			
		return $this->_cartId;
	}
	
	public function addProduct( ShoppingCart $SCart )
	{
		$SCart->quantity = (int) $this->getQuantity( $SCart->product_id );
		
	    //var_dump( $SCart->quantity  );
		
		if( empty( $SCart->quantity ) )
		{
			$SCart->quantity = 1;
			
			$stmt = $this->_conn->prepare("INSERT INTO shopping_cart( cart_id, product_id, quantity )
												   VALUES ( :cart_id, :product_id, :quantity )" ); 
												   
			$stmt->bindParam( ":cart_id", $SCart->cart_id, \PDO::PARAM_STR );
			$stmt->bindParam( ":product_id", $SCart->product_id, \PDO::PARAM_INT );
			$stmt->bindParam( ":quantity", $SCart->quantity, \PDO::PARAM_INT );
		
			if( !$stmt->execute() ) 
    		{
				print_r( $stmt->errorInfo() );
			}	
		}
		else
		{
			//var_dump($SCart->quantity);
			$SCart->quantity += 1;
			$SCart->added_on = date('Y-m-d G:i:s');
			//var_dump($SCart->quantity);
			
			$stmt = $this->_conn->prepare("UPDATE	shopping_cart 
										   SET		quantity = :quantity,
										   			added_on = :added_on
										   WHERE	cart_id = :cart_id
										   			AND product_id = :product_id");
										   			
			$stmt->bindParam( ":quantity", $SCart->quantity, \PDO::PARAM_INT );		
			$stmt->bindParam( ":added_on", $SCart->added_on, \PDO::PARAM_STR );					   			 
			$stmt->bindParam( ":cart_id", $SCart->cart_id, \PDO::PARAM_STR );
			$stmt->bindParam( ":product_id", $SCart->product_id, \PDO::PARAM_INT );
			
			if( !$stmt->execute() ) 
    		{
			print_r( $stmt->errorInfo() );
			}	
					
		}
	}    
	 
     
    public function getQuantity( $productId = NULL )
    {
    	if( !empty( $productId) )
    	{
    		$stmt = $this->_conn->prepare( "SELECT	quantity 
    									 	 FROM	shopping_cart 
    									 	 WHERE 	cart_id = :cart_id
    												AND product_id = :product_id" );
    	
    		$stmt->bindParam( ":cart_id", $this->_cartId, \PDO::PARAM_STR );
    		$stmt->bindParam( ":product_id", $productId, \PDO::PARAM_INT );
    	
    		if( $stmt->execute() ) 
    		{
				$this->_data = $stmt->fetch();
				return $this->_data[0];
			}
			else
			{
			print_r( $stmt->errorInfo() );
			return null;
			}
		}
		else
		{
			$stmt = $this->_conn->prepare( "SELECT	SUM(quantity)
											 FROM	shopping_cart
											 WHERE	cart_id = :cart_id" );
			$stmt->bindParam( ":cart_id", $this->_cartId, \PDO::PARAM_STR );
			
			if( $stmt->execute() ) 
    		{
				$this->_data = $stmt->fetch();
				return $this->_data[0];
			}
			else
			{
			print_r( $stmt->errorInfo() );
			return null;
			}
		}
    }
    
    public function getCartProducts()
    {
    	$stmt = $this->_conn->prepare("SELECT 	products.product_id, products.product_name, products.product_price,
    											shopping_cart.item_id, shopping_cart.quantity	  
    									FROM 	products, shopping_cart 
    									WHERE 	products.product_id IN (
    																	SELECT	product_id
    																	FROM	shopping_cart
    																	WHERE	cart_id = :cart_id )
    																	
    											AND products.product_id = shopping_cart.product_id
    											AND	shopping_cart.cart_id = :cart_id");
    									
    	$stmt->bindParam( ":cart_id", $this->_cartId,\PDO::PARAM_STR );
    	
    	if( $stmt->execute() ) 
    	{
				$this->_ProductData = array();
				
				while( $row = $stmt->fetch() )
				{
				
					$this->_ProductData[] = new MergeCartProduct( $row["product_id"], $row["product_name"],
											   				   $row["product_price"], $row["item_id"], 
											   				   $row["quantity"] );
			 	}// End outer while
			}
    	else
    	{
    		die( print_r($stmt->errorInfo()) );		
    	}	
    	
    	return $this->_ProductData;
    }  
    
    /*
    *	This function removes item from shopping cart. 
    *	If quantity > 1, the record is updated: quantity is reduced by 1. 
    *	If quantity == 1, the record is deleted alltogether.
    *	
    *	@param: int $item_id
    *	@return: none
    */
    public function removeItem()
    {
    	$item_id = (int) $_GET['item_id'];
    	$stmt = $this->_conn->prepare("SELECT	 quantity
    									FROM	shopping_cart
    									WHERE	cart_id = :cart_id
    											AND item_id = :item_id");
    											
    	$stmt->bindParam( ":cart_id", $this->_cartId, \PDO::PARAM_STR );
    	$stmt->bindParam( ":item_id", $item_id, \PDO::PARAM_INT );
    	
    	if( !$stmt->execute() ) { die( print_r( $stmt->errorInfo() ) ); }	
    	
    	$row = $stmt->fetch();
    	
    	if( $row['quantity'] > 1 )
    	{
    		$quantity = (int)$row['quantity'] - 1;
    		
    		$stmt = $this->_conn->prepare("UPDATE	 shopping_cart
    										SET		quantity = :quantity			
    										WHERE	cart_id = :cart_id
    												AND item_id = :item_id");
    											
    		$stmt->bindParam( ":quantity", $quantity, \PDO::PARAM_INT );									
    		$stmt->bindParam( ":cart_id", $this->_cartId, \PDO::PARAM_STR );
    		$stmt->bindParam( ":item_id", $item_id, \PDO::PARAM_INT );
    		
    		if( !$stmt->execute() ) { die( print_r( $stmt->errorInfo() ) ); }	
    	}
    	else
    	{
    		$stmt = $this->_conn->prepare("DELETE FROM	shopping_cart WHERE	cart_id = :cart_id
    																		AND item_id = :item_id");
    		$stmt->bindParam( ":cart_id", $this->_cartId, \PDO::PARAM_STR );
    		$stmt->bindParam( ":item_id", $item_id, \PDO::PARAM_INT );
    		
    		if( !$stmt->execute() ) { die( print_r( $stmt->errorInfo() ) ); }	
    																		
    	}
    	
    }
    
     /*
    *	This function increments quantity of existing item by 1. 
    *	@param: int $item_id
    *	@return: none
    */
    public function addItem()
    {
    	$item_id = (int) $_GET['item_id'];
    	$stmt = $this->_conn->prepare("UPDATE		shopping_cart
    																		 SET	quantity = quantity + 1			
    																					WHERE	cart_id = :cart_id
    																					AND item_id = :item_id");
    																			
    		$stmt->bindParam( ":cart_id", $this->_cartId, \PDO::PARAM_STR );
    		$stmt->bindParam( ":item_id", $item_id, \PDO::PARAM_INT );
    		
    		if( !$stmt->execute() ) 
    		{ 
    			print_r($stmt->errorInfo()); 
    			return;
    		}	
    		
    		return;
    	}
     
 }//End Class ShoppingCartMapper
