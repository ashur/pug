<?php

/*
 * This file is part of Pug
 */
namespace Pug;

use Huxtable\CLI;
use Huxtable\CLI\Output;

/**
 * @command		show
 * @desc		Show tracked projects
 * @usage		show <name> <path>
 * @alias		track
 */
$commandShow = new CLI\Command( 'show', 'Show tracked projects', function( $name='' )
{
	$output = new Output();

	$pug = new Pug();
	$projects = $pug->getProjects( $this->getOptionValue('t') );

	if( count( $projects ) < 1 )
	{
		$output->line( 'pug: Not tracking any projects. See \'pug help\'' );
	}
	else
	{
		$output->string( listProjects( $pug->getProjects(), $name ) );
	}

	return $output->flush();
});

// Options
$commandShow->registerOption( 't', 'Sort by time modified (most recently modified first) before sorting projects by name' );

// Aliases
$commandShow->addAlias( 'list' );
$commandShow->addAlias( 'ls' );

// Usage
$commandShowUsage = <<<USAGE
show [options] [<name>]

OPTIONS
     -t  sort by time updated, recently updated first

USAGE;

$commandShow->setUsage( $commandShowUsage );

return $commandShow;
