<?php

namespace App\Model;

class ShoppingCart {

	public $item_id;
	public $cart_id;
	public $product_id;
	public $attributes;
	public $quantity;
	public $buy_now;
	public $added_on;
	
	public function __construct( $item_id, $cart_id, $product_id, $attributes, $quantity, $buy_now, $added_on )
	{
		$this->item_id = $item_id;
		$this->cart_id = $cart_id;
		$this->product_id = $product_id;
		$this->attributes = $attributes;
		$this->quantity = $quantity;
		$this->buy_now = $buy_now;
		$this->added_on = $added_on;
	}
}
