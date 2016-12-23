<?php

/*
 * This file is part of Pug
 */
namespace Pug;

use Huxtable\CLI;
use Huxtable\Core\File;

/**
 * @command		add
 * @desc		Start tracking a new project
 * @usage		add [<group>/]<name> <path>
 * @alias		track
 */
$commandAdd = new CLI\Command( 'add', 'Start tracking a new project', function( $name, $path )
{
	try
	{
		$dirProject = new File\Directory( $path );
	}
	catch( \Exception $e )
	{
		throw new CLI\Command\CommandInvokedException( "Couldn't track project. {$e->getMessage()}", 1 );
	}

	if( !$dirProject->exists() )
	{
		throw new CLI\Command\CommandInvokedException( "Couldn't track project. Path '{$path}' not found.", 1 );
	}

	$pug = new Pug();
	$project = new Project( $name, $dirProject, true, $dirProject->getCTime() );

	try
	{
		$pug->addProject( $project );
	}
	catch( \Exception $e )
	{
		throw new CLI\Command\CommandInvokedException( "Couldn't track project. {$e->getMessage()}", 1 );
	}

	$output = listProjects( $pug->getProjects() );
	return $output->flush();
});

$commandAdd->addAlias( 'track' );
$commandAdd->setUsage( 'add [<group>/]<name> <path>' );

return $commandAdd;
