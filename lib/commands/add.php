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
 * @usage		add [<group>/]<name> <path> [--url=<url>]
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

	/* Clone new project to track */
	$repoURL = $this->getOptionValue( 'url' );
	if( !is_null( $repoURL ) )
	{
		if( $dirProject->exists() )
		{
			if( count( $dirProject->children() ) > 0 )
			{
				throw new CLI\Command\CommandInvokedException( "Couldn't clone repository: '{$path}' already exists and is not an empty directory.", 1 );
			}
		}

		echo "Cloning into '{$dirProject}'... " ;
		$result = CLI\Shell::exec( "git clone --recursive {$repoURL} {$dirProject}", true, '  > ' );

		if( $result['exitCode'] === 0 )
		{
			echo 'done.' . PHP_EOL;
		}
		else
		{
			echo 'failed:' . PHP_EOL . PHP_EOL;
			echo $result['output']['formatted'] . PHP_EOL;

			exit( 1 );
		}
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

	return listProjects( $pug->getProjects() );
});

/* Options */
$commandAdd->registerOption( 'url' );

/* Aliases */
$commandAdd->addAlias( 'track' );

/* Usage */
$commandAddUsage = <<<USAGE
add [<group>/]<name> <path> [--url=<url>]

OPTIONS
    --url=<url>
        Clone the Git repository at <url> to <path>.

USAGE;

$commandAdd->setUsage( $commandAddUsage );

return $commandAdd;
