<?php

/*
 * This file is part of Pug
 */
namespace Pug;

use Huxtable\CLI;
use Huxtable\CLI\Command;

/**
 * @command		enable
 * @desc		Include projects in 'all' updates
 * @usage		enable [<name>|<namespace>|all]
 */
$commandEnable = new CLI\Command( 'enable', 'Include projects in \'all\' updates', function( $query )
{
	$pug = new Pug();
	$query = strtolower( $query );

	try
	{
		if( $query == 'all' )
		{
			$pug->enableAllProjects();
		}
		// Is this a namespace?
		elseif( $pug->namespaceExists( $query ) )
		{
			$pug->enableProjectsInNamespace( $query );
		}
		// ...or is this a project?
		else
		{
			$pug->enableProject( $query );
		}
	}
	catch( \Exception $e )
	{
		throw new Command\CommandInvokedException( "No projects or namespaces match '{$query}'.", 1 );
	}

	return listProjects( $pug->getProjects() );
});

$commandEnable->setUsage( 'enable [<name>|<namespace>|all]' );

return $commandEnable;
