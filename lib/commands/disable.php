<?php

/*
 * This file is part of Pug
 */
namespace Pug;

use Huxtable\CLI;
use Huxtable\CLI\Command;

/**
 * @command		disable
 * @desc		Exclude projects from 'all' updates
 * @usage		disable [<name>|<namespace>|all]
 */
$commandDisable = new CLI\Command( 'disable', 'Exclude projects from \'all\' updates', function( $query )
{
	$pug = new Pug();
	$query = strtolower( $query );

	try
	{
		if( $query == 'all' )
		{
			$pug->disableAllProjects();
		}
		// Is this a namespace?
		elseif( $pug->namespaceExists( $query ) )
		{
			$pug->disableProjectsInNamespace( $query );
		}
		// ...or is this a project?
		else
		{
			$pug->disableProject( $query );
		}
	}
	catch( \Exception $e )
	{
		throw new Command\CommandInvokedException( "No projects or namespaces match '{$query}'.", 1 );
	}

	return listProjects( $pug->getProjects() );
});

$commandDisable->setUsage( 'disable [<name>|<namespace>|all]' );

return $commandDisable;
