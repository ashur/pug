<?php

use Huxtable\CLI\Command;
use Huxtable\Core\File;

/**
 * @name			install
 * @description		Symlink 'pug' to a convenient path
 * @usage			install <dir>
 */
$command = new Command( 'install', 'Symlink \'pug\' to a convenient path', function( $dir )
{
	$pathSource = dirname( dirname( __DIR__ ) ) . '/bin/pug';

	try
	{
		$destinationDirectory = new File\Directory( $dir );

		if( !$destinationDirectory->exists() )
		{
			throw new \Exception( "Invalid location: '{$dir}' does not exist" );
		}
	}
	catch( \Exception $e )
	{
		throw new Command\CommandInvokedException( $e->getMessage(), 1 );
	}

	if( !$destinationDirectory->isWritable() )
	{
		throw new Command\CommandInvokedException( "Invalid location: You do not have permission to write to '{$destinationDirectory}'" );
	}

	$target = $destinationDirectory->child( 'pug' );
	$source = new File\File( $pathSource );

	if( !$source->exists() )
	{
		throw new Command\CommandInvokedException( "Invalid source: '{$source}' not found", 1 );
	}

	if( $target->exists() || is_link( $target->getPathname() ) )
	{
		throw new Command\CommandInvokedException( "Invalid target: '{$target}' already exists", 1 );
	}

	symlink( $source, $target );
	echo "Linked to '{$target}'" . PHP_EOL;
});

return $command;
