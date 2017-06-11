<?php

/*
 * This file is part of Pug
 */
namespace Pug;

use Huxtable\CLI;
use Huxtable\CLI\Command;

/**
 * @command		update
 * @desc		Fetch project updates
 * @usage		update [all|<group> [--all]|<project>|<path>]
 * @alias		remove,untrack
 */
$commandUpdate = new CLI\Command('update', 'Fetch project updates', function( $query='./' )
{
	$pug = new Pug();

	$options = $this->getOptionsWithValues();
	$forceDependencyUpdate = isset( $options['f'] ) || isset( $options['force'] );

	/*
	 * Determine which projects we're going to update...
	 *
	 *   The default behavior (with no arguments) is to attempt updating the working directory './'
	 *
	 *   Note: Given the increased flexibility with Namespace support, 'pug update' no longer
	 *   supports multiple arguments.
	 */
	$projects = [];

	try
	{
		if( $query == 'all' )
		{
			$projects = $pug->getEnabledProjects();
		}
		elseif( $pug->namespaceExists( $query ) )
		{
			$namespaceProjects = $pug->getProjectsInNamespace( $query );

			/* Add all, including disabled */
			if( $this->getOptionValue( 'all' ) )
			{
				$projects = $namespaceProjects;
			}
			/* Add all enabled */
			else
			{
				foreach( $namespaceProjects as $namespaceProject )
				{
					if( $namespaceProject->isEnabled() )
					{
						$projects[] = $namespaceProject;
					}
				}
			}
		}
		// ...or is this a project?
		else
		{
			$projects[] = $pug->getProject( $query );
		}
	}
	catch( \Exception $e )
	{
		throw new Command\CommandInvokedException( "No groups or projects match '{$query}'.", 1 );
	}

	/*
	 * Now update them
	 */
	for( $i=0; $i < count( $projects ); $i++ )
	{
		$target = $projects[$i];

		try
		{
			$pug->updateProject( $target, $forceDependencyUpdate );
		}
		catch( \Exception $e )
		{
			// Standard single-line failure with exit code
			if( count( $projects ) == 1 && $query != 'all' )
			{
				throw new CLI\Command\CommandInvokedException( $e->getMessage(), 1 );
			}

			$name = $target instanceof Project ? $target->getName() : $target;

			$stringHalted = new CLI\FormattedString( "Updating '{$name}'... halted:" );
			$stringHalted->backgroundColor( 'red' );

			$stringMessage = new CLI\FormattedString( " • {$e->getMessage()}" );
			$stringMessage->foregroundColor( 'red' );

			echo $stringHalted . PHP_EOL . PHP_EOL;
			echo $stringMessage . PHP_EOL . PHP_EOL;
		}
	}
});

$commandUpdate->addAlias( 'up' );
$commandUpdate->registerOption( 'all', 'Update all projects in the group, even if disabled' );
$commandUpdate->registerOption( 'f', 'Force dependency managers to update' );
$commandUpdate->registerOption( 'force', 'Force dependency managers to update' );

$updateUsage = <<<USAGE
update [-f|--force] [all|<group> [--all]|<project>|<path>]

OPTIONS
     --all
         update all projects in the group, even if disabled

     -f, --force
         force dependency managers (ex., CocoaPods) to update

USAGE;

$commandUpdate->setUsage( $updateUsage );

return $commandUpdate;
