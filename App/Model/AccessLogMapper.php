<?php

namespace App\Model;

	/**
	* AccessLogMapper Class
	*	Provides functionality for accessLog table
	*/
	class AccessLogMapper extends AbstractMapper
	{
		/**
   * Database connection
   * @access protected
   * @var PDO object
   */
		protected $_db;
		/**
   * Logs array
   * @access protected
   * @var array
   */
		protected $_logs;
		
		/**
   * AccessLogMapper constructor
   * Establish database connectivity
   * @param none 
   * @access public
   */
		public function __construct()
		{
			$this->_db = parent::connect();
		}
		
		/**
   * Fetches all records from accessLog table
   * @param none
   * @return array of rows on success
   * @return print error message and return void on failure
   * @access public
   */
		public function getAll( $order, $startRow, $numRows )
		{
			$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM accessLog ORDER BY $order DESC LIMIT :startRow, :numRows";
			$stmt = $this->_db->prepare($sql);
			$stmt->bindValue( ":startRow", $startRow, \PDO::PARAM_INT );
      $stmt->bindValue( ":numRows", $numRows, \PDO::PARAM_INT );
      $this->_logs = array();
		
			if( $stmt->execute() )
			{
				foreach ( $stmt->fetchAll() as $row )
				{
        	$this->_logs[] = $row;
      	}
			}
			else
			{
				print_r( $stmt->errorInfo() );
				return;
			}
			
			$stmt = $this->_db->query("SELECT found_rows() AS totalRows");
			$row = $stmt->fetch();
			
			return array( $this->_logs, $row['totalRows'] );
		}//End function getAll
		
		/**
   * If called with parameter, delete all rows that are older than param
   * If called without param, truncate table
   * @param string
   * @return none
   * @return print error message and return void on failure
   * @access public
   */
		public function deleteLogs( $date = NULL )
		{
			if( $date )
			{
					$sql = "DELETE FROM accessLog WHERE DATE(lastAccess) <= :date";
					$stmt = $this->_db->prepare($sql);
					$stmt->bindValue( ":date", $date );
					
					if( !$stmt->execute() )
					{
						print_r( $stmt->errorInfo() );
						return;
					}
					
					return;
			}
		}
		
	}//End Class AccessLogMapper

?>
