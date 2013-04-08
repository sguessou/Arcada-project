<?php

namespace App\Model;

class Product {

	public $product_id;
	public $ptype_id;
	public $product_name; 
	public $product_price;
	public $product_language;
	public $product_description;
	public $product_author;
	public $product_isbn10; 
	
	public function __construct( $product_id, $ptype_id, $product_name, $product_price, $product_language,
											 $product_description, $product_author, $product_isbn10 ) {
											 
				$this->product_id = $product_id;
				$this->ptype_id = $ptype_id;
				$this->product_name = $product_name;
				$this->product_price = $product_price;
				$this->product_language = $product_language;
				$this->product_description = $product_description;
				$this->product_author = $product_author;
				$this->product_isbn10 = $product_isbn10;
				
	}
}

?>
