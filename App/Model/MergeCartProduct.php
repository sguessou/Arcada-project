<?php

namespace App\Model;

class MergeCartProduct 
{

	public $product_id;
	public $product_name; 
	public $product_price;
        public $ptype_id;
	public $item_id;
	public $quantity;

	
	public function __construct( $product_id, $product_name, $product_price, $ptype_id, $item_id, $quantity ) 
	{ 
				$this->product_id = $product_id;
				$this->product_name = $product_name;
				$this->product_price = $product_price;	
                                $this->ptype_id = $ptype_id;
				$this->item_id = $item_id;
				$this->quantity = $quantity;
	}

}
