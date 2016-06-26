<?php

/*
 * This file is part of Pug
 */
namespace Pug;

use Huxtable\CLI;

/**
 * @command		disable
 * @desc		Exclude project from 'all' updates
 * @usage		disable <name>
 */
$commandDisable = new CLI\Command( 'disable', 'Exclude project from \'all\' updates', function( $name )
{
	$pug = new Pug();
	$pug->disableProject( $name );

	return listProjects( $pug->getProjects() );
});

return $commandDisable;
