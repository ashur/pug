<?php

/*
 * This file is part of Pug
 */
namespace Pug;

use Huxtable\CLI;
use Huxtable\CLI\Command;
use Huxtable\CLI\Output;

/**
 * @command		show
 * @desc		Show tracked projects
 * @usage		show <name> <path>
 * @alias		track
 */
$commandShow = new Command( 'show', 'Show tracked projects', function( $name=null )
{
	$pug = new Pug();

	/* Show Git metadata */
	$showGit = $this->getOptionValue( 'A' ) == true;
	$showGit = $showGit || $this->getOptionValue( 'g' ) == true;
	$showGit = $showGit || $this->getOptionValue( 'git' ) == true;

	/* Show project path */
	$showPath = $this->getOptionValue( 'A' ) == true;
	$showPath = $showPath || $this->getOptionValue( 'p' ) == true;
	$showPath = $showPath || $this->getOptionValue( 'path' ) == true;

	/* Use color */
	$useColor = $this->getOptionValue( 'no-color' ) == null;

	if( !is_null( $name ) )
	{
		try
		{
			$project = $pug->getProject( $name );
		}
		catch( \Exception $e )
		{
			throw new Command\CommandInvokedException( $e->getMessage(), 1 );
		}

		$projects = [ $project ];
		$showGit = true;
		$showPath = true;
	}
	else
	{
		$projects = $pug->getProjects( $this->getOptionValue( 't' ) );
	}

	if( count( $projects ) < 1 )
	{
		$output = new Output();
		$output->line( 'pug: Not tracking any projects. See \'pug help\'' );
	}
	else
	{
		$output = listProjects( $projects, $showGit, $showPath, $useColor );
	}

	return $output->flush();
});

// Options
$commandShow->registerOption( 'A' );
$commandShow->registerOption( 'g' );
$commandShow->registerOption( 'git' );
$commandShow->registerOption( 'no-color' );
$commandShow->registerOption( 'p' );
$commandShow->registerOption( 'path' );
$commandShow->registerOption( 't', 'Sort by time modified (most recently modified first) before sorting projects by name' );

// Aliases
$commandShow->addAlias( 'list' );
$commandShow->addAlias( 'ls' );

// Usage
$commandShowUsage = <<<USAGE
show [options] [<name>]

OPTIONS
    -A, --all
	    Include all project metadata in listing.

    -g, --git
        Include current branch and HEAD in listing.

    -p, --path
        Include project path in listing.

    -t
        Sort projects by time updated, most recent first.

USAGE;

$commandShow->setUsage( $commandShowUsage );

return $commandShow;
