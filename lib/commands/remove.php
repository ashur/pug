<?php

/*
 * This file is part of Pug
 */
namespace Pug;

use Huxtable\CLI;
use Huxtable\CLI\Command;
use Huxtable\CLI\Input;

/**
 * @command		rm
 * @desc		Stop tracking projects
 * @usage		rm [<name>|<namespace>|all]
 * @alias		remove,untrack
 */
$commandRemove = new CLI\Command('rm', 'Stop tracking projects', function( $query )
{
	$pug = new Pug();
	$query = strtolower( $query );

	try
	{
		if( $query == 'all' )
		{
			$didConfirm = strtolower( Input::prompt( 'Are you sure you want to remove all projects from Pug? (y/n)' ) );
			if( $didConfirm == 'y' )
			{
				$pug->removeAllProjects();
			}
		}
		// Is this a namespace?
		elseif( $pug->namespaceExists( $query ) )
		{
			$didConfirm = strtolower( Input::prompt( "Are you sure you want to remove all '{$query}' projects? (y/n)" ) );
			if( $didConfirm == 'y' )
			{
				$pug->removeProjectsInNamespace( $query );
			}
		}
		// ...or is this a project?
		else
		{
			$pug->removeProject( $query );
		}
	}
	catch( \Exception $e )
	{
		throw new Command\CommandInvokedException( "No projects or namespaces match '{$query}'.", 1 );
	}

	return listProjects ($pug->getProjects());
});

$commandRemove->addAlias( 'remove' );
$commandRemove->addAlias( 'untrack' );

$commandRemove->setUsage( 'remove [<name>|<namespace>|all]' );

return $commandRemove;
