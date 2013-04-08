<?php

namespace App\Model;

class CountriesMapper extends AbstractMapper {
	
	protected $_countries;
	
	protected $_conn;

	public function __construct() {
			$this->_conn = parent::connect(); 
			$this->_countries = array();
			}
			
	public function getCountries() 
	{ 
		$sql = "SELECT name, printable_name FROM country ORDER BY printable_name ASC";
		$stmt = $this->_conn->query($sql);
		
		if($stmt->execute())
		{
			while ( $row = $stmt->fetch(\PDO::FETCH_ASSOC) )
			{
								$this->_countries[] = $row;
			}
		  return $this->_countries;
		}
		else
		{
			print_r( $stmt->errorInfo() );
			return;
		} 				
	}//End method getCountries

}//End Class CountriesMapper
