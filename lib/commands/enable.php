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
 * @usage		enable [all|<group>|<project>]
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
		throw new Command\CommandInvokedException( "No groups or projects match '{$query}'.", 1 );
	}

	$useColor = $this->getOptionValue( 'no-color' ) == null;
	$output = listProjects( $pug->getProjects(), false, false, $useColor );

	return $output->flush();
});

/* Options */
$commandEnable->registerOption( 'no-color' );

$commandEnable->setUsage( 'enable [all|<group>|<project>]' );

return $commandEnable;
