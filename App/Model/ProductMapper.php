<?php

namespace App\Model;

class ProductMapper extends AbstractMapper {

	protected $_productTypes = array();
	protected $_products = array();

	protected $_conn;

	public function __construct() {
			$this->_conn = parent::connect(); 
			}
/*
This function inserts a product into the database, the image related to the product is named as product_id.jpg.
The file product_id.jpg is moved to the products_images folder.
*/			
	public function insertProduct( Product $Product ) {
	  
	   $stmt = $this->_conn->prepare("INSERT INTO products( ptype_id, product_name, product_price, product_language,
	   																			product_description, product_author, product_isbn10 ) 	
	   															 VALUES ( :ptype_id, :product_name, :product_price, :product_language,
	   																		  :product_description, :product_author, :product_isbn10 ) " );
		
		$stmt->bindParam( ":ptype_id", $Product->ptype_id );	
		$stmt->bindParam( ":product_name", $Product->product_name );
		$stmt->bindParam( ":product_price", $Product->product_price );
		$stmt->bindParam( ":product_language", $Product->product_language );
		$stmt->bindParam( ":product_description", $Product->product_description );
		$stmt->bindParam( ":product_author", $Product->product_author );
		$stmt->bindParam( ":product_isbn10", $Product->product_isbn10 );
		
		if( !$stmt->execute() ) {
			print_r( $stmt->errorInfo() );
			return null;
		}
		
		$pid = $this->_conn->lastInsertId();
		$path = realpath(APPLICATION_PATH);
		$imageTmp = $path.'/images/tmp_images/';
		$imagePath = $path.'/images/products_images/';
		$imageName = $pid.".jpg";
		
		if( !move_uploaded_file( $_FILES["cover"]["tmp_name"], $imagePath.$imageName ) ) {
			echo "<p>Sorry, there was a problem uploading that photo.</p>"	;	
		}
	}
/*
This function returns the products type id (ptype_id), it's used in the next function to get a list of products 
based on their type name.
*/
	public function getPTypeId( $PName ) {
		$stmt = $this->_conn->prepare( "SELECT ptype_id FROM product_types WHERE type_name LIKE :type_name" );
		$stmt->bindParam( ":type_name", $PName );
		
		if ( $stmt->execute() ) {
				return $row = $stmt->fetch();		
		} else {
				print_r( $stmt->errorInfo() );		
		}
	}
/*
This function returns a list of products according to the parameters $limit and $PName.
*/
	public function getAllProducts( $limit = null, $PName ) {
		
		$ptype_id = $this->getPTypeId( $PName );
		
		if( $limit != null ) {
				$stmt = $this->_conn->prepare("SELECT * FROM products WHERE ptype_id = :ptype_id ORDER BY product_id DESC LIMIT :limit");
				$stmt->bindParam( ":limit", $limit, \PDO::PARAM_INT );
				$stmt->bindParam( ":ptype_id", $ptype_id["ptype_id"], \PDO::PARAM_INT );
		
		} else {
				$stmt = $this->_conn->prepare( "SELECT * FROM products WHERE ptype_id = :ptype_id" );
				$stmt->bindParam( ":ptype_id", $ptype_id["ptype_id"], \PDO::PARAM_INT );
			}
			
	    $this->_products = array();

		
		if ( $stmt->execute() ) {
				while( $row = $stmt->fetch() ) {
						$this->_products[] = new Product( $row["product_id"], $row["ptype_id"], $row["product_name"],
																	$row["product_price"], $row["product_language"], $row["product_description"],
																	$row["product_author"], $row["product_isbn10"]);				
											}
											
						
		} else {
				print_r( $stmt->errorInfo() );		
				}
		return $this->_products;
			
  }
  
  public function getProduct( $pid ) {
  		$stmt = $this->_conn->prepare( "SELECT * FROM products WHERE product_id = :product_id" );
  		$stmt->bindParam( ":product_id", $pid );
  		
  		if ( $stmt->execute() ) {
				      $row = $stmt->fetch();
						$this->_products = new Product( $row["product_id"], $row["ptype_id"], $row["product_name"],
														 $row["product_price"], $row["product_language"], $row["product_description"],
														 $row["product_author"], $row["product_isbn10"] );				
		} else {
				print_r( $stmt->errorInfo() );	
				return FALSE;	
				}
				
		return $this->_products;
  
  
  }
  
  public function fetchProducts($PrdTypeId, $order, $startRow, $numRows)
  {
  	$stmt = $this->_conn->prepare( "SELECT SQL_CALC_FOUND_ROWS * FROM products WHERE ptype_id = :ptype_id ORDER BY $order DESC LIMIT :startRow, :numRows" );
  	$stmt->bindParam( ":ptype_id", $PrdTypeId, \PDO::PARAM_INT );
  	$stmt->bindValue( ":startRow", $startRow, \PDO::PARAM_INT );
    $stmt->bindValue( ":numRows", $numRows, \PDO::PARAM_INT );
    
    $this->_products = array();
    
    if($stmt->execute())
	{
		foreach($stmt->fetchAll() as $row)
		{
        	$this->_products[] = $row;
      	}
	}
	else
	{
		print_r($stmt->errorInfo());
		return;
	}
    
    $stmt = $this->_conn->query("SELECT found_rows() AS totalRows");
	$row = $stmt->fetch();
    
    return array( $this->_products, $row['totalRows'] );
    
  }//End fetchProducts
  
  /*
    *	This function updates table products in db. 
    *	@param: Object $Product
    *	@return: boolean false if unsuccessfull.
    */
  public function update_product(Product $Product)
  {
	  $stmt = $this->_conn->prepare("UPDATE products SET ptype_id = :ptype_id,
														 product_name = :product_name,
														 product_price = :product_price,
														 product_language = :product_language,
														 product_description = :product_description,
														 product_author = :product_author,
														 product_isbn10 = :product_isbn10 
									WHERE product_id = :product_id");
		
		$stmt->bindParam( ":ptype_id", $Product->ptype_id );	
		$stmt->bindParam( ":product_name", $Product->product_name );
		$stmt->bindParam( ":product_price", $Product->product_price );
		$stmt->bindParam( ":product_language", $Product->product_language );
		$stmt->bindParam( ":product_description", $Product->product_description );
		$stmt->bindParam( ":product_author", $Product->product_author );
		$stmt->bindParam( ":product_isbn10", $Product->product_isbn10 );
		$stmt->bindParam( ":product_id", $Product->product_id );

		
		if( !$stmt->execute() ) {
			print_r( $stmt->errorInfo() );
			return FALSE;
		}
		
		if(isset($_FILES['cover']) && $_FILES['cover']['error'] == UPLOAD_ERR_OK)
		{
			$path = realpath(APPLICATION_PATH);
			$imagePath = $path.'/images/products_images/';
			$imageName = $Product->product_id.".jpg";
		
			if(!move_uploaded_file($_FILES["cover"]["tmp_name"], $imagePath.$imageName)) 
			{
				echo "<p>Sorry, there was a problem uploading that photo.</p>";
				return FALSE;	
			}
			//unset($_POST['cover']);
			return TRUE;
		}
		
		return TRUE;			
  }//End method update_product


}

?>
