<?php

namespace App\Model;

class MergeCartProduct 
{

	public $product_id;
	public $product_name; 
	public $product_price;
	public $item_id;
	public $quantity;

	
	public function __construct( $product_id, $product_name, $product_price, $item_id, $quantity ) 
	{ 
				$this->product_id = $product_id;
				$this->product_name = $product_name;
				$this->product_price = $product_price;			
				$this->item_id = $item_id;
				$this->quantity = $quantity;
	}

}
