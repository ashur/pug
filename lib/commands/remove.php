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

	/* Assume "yes" */
	$assumeYes = $this->getOptionValue( 'y' ) == true;
	$assumeYes = $assumeYes || $this->getOptionValue( 'yes' ) == true;
	$assumeYes = $assumeYes || $this->getOptionValue( 'assume-yes' ) == true;

	try
	{
		if( $query == 'all' )
		{
			if( !$assumeYes )
			{
				$didConfirm = strtolower( Input::prompt( 'Are you sure you want to remove all projects from Pug? (y/n)' ) );
			}
			if( $assumeYes || $didConfirm == 'y' )
			{
				$pug->removeAllProjects();
			}
		}
		// Is this a namespace?
		elseif( $pug->namespaceExists( $query ) )
		{
			$namespace = Project::getNormalizedNamespaceString( $query );

			if( !$assumeYes )
			{
				$didConfirm = strtolower( Input::prompt( "Are you sure you want to remove all projects in the '{$namespace}' group? (y/n)" ) );
			}
			if( $assumeYes || $didConfirm == 'y' )
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
$commandRemove->registerOption( 'y' );
$commandRemove->registerOption( 'yes' );
$commandRemove->registerOption( 'assume-yes' );

$commandRemove->addAlias( 'remove' );
$commandRemove->addAlias( 'untrack' );

/* Usage */
$commandRemoveUsage = <<<USAGE
rm [all|<group>[/<project>]|<project>] [-y|--yes|--assume-yes]

OPTIONS
    -y, --yes, --assume-yes
        Automatic yes to prompts. Assume "yes" as answer to all prompts and run
        non-interactively.

USAGE;

$commandRemove->setUsage( $commandRemoveUsage );

return $commandRemove;
