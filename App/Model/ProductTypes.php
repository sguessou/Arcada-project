<?php
namespace App\Model;

class ProductTypes {
	
	public $ptype_id;
	public $parent_ptype_id;
	public $type_name;
	
	public function __construct( $ptype_id, $parent_ptype_id, $type_name ) {
			$this->ptype_id = $ptype_id;
			$this->parent_ptype_id = $parent_ptype_id;
			$this->type_name = $type_name;
		}
										
	
	
	}