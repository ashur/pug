<?php

/*
 * This file is part of Pug
 */
namespace Pug;

class Autoloader
{
	/**
	 *
	 */
	static public function register()
	{
		spl_autoload_register( __CLASS__ . "::autoload" );
	}

	/**
	 * @return	void
	 */
	static public function autoload( $class )
	{
		if( substr( $class, 0, 1 ) == '\\' )
		{
			$class = substr( $class, 1 );
		}

		if( strpos( $class, __NAMESPACE__ ) == 0 )
		{
			// Convert class into filename
			$basename = str_replace( __NAMESPACE__ . '\\', '', $class );
			$basename = substr( $basename, 0, 1 ) == '\\' ? substr( $basename, 1 ) : $basename;
			$basename = str_replace( '\\', DIRECTORY_SEPARATOR, $basename ) . '.php';

			$filename = __DIR__ . DIRECTORY_SEPARATOR . $basename;

			if( file_exists( $filename ) )
			{
				require_once( $filename );
			}
		}
	}
}
