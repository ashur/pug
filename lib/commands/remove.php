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
 * @usage		rm [all|<group>|<project>]
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
			$namespace = Project::getNormalizedNamespaceString( $query );

			$didConfirm = strtolower( Input::prompt( "Are you sure you want to remove all projects in the '{$namespace}' group? (y/n)" ) );
			if( $didConfirm == 'y' )
			{
				$pug->removeProjectsInNamespace( $namespace );
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
		throw new Command\CommandInvokedException( "No groups or projects match '{$query}'.", 1 );
	}

	$useColor = $this->getOptionValue( 'no-color' ) == null;
	$output = listProjects( $pug->getProjects(), false, false, $useColor );

	return $output->flush();
});

/* Options */
$commandRemove->registerOption( 'no-color' );

$commandRemove->addAlias( 'remove' );
$commandRemove->addAlias( 'untrack' );

$commandRemove->setUsage( 'rm [all|<group>|<project>]' );

return $commandRemove;
