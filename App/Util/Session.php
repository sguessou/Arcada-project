<?php

namespace App\Util;

	class Session 
	{
		public function __construct()
		{
			$this->_openSession();
		}
		
		protected function _openSession()
		{
			//$this->destroy();
			if( !isset( $_SESSION['active'] ) || !( $_SESSION['active'] == TRUE ) )
			{
				session_start();
				 $_SESSION['active'] = TRUE;
			}
			else
			{
				session_regenerate_id(true);
			}
		}
		
		public function set( $name, $value )
		{
			$_SESSION[$name] = $value;
		}
		
		public function get( $name )
		{
			if( isset( $_SESSION[$name]) )
			{
				return $_SESSION[$name];
			}
			else
			{
				return false;
			} 
		}
		
		public function del( $name )
		{
			unset( $_SESSION[$name] );
		}
		
		public function session_reg()
		{
			
		}	
		
		public function destroy()
		{
			$_SESSION = array();
			session_destroy();
			session_regenerate_id();
		}
	}
