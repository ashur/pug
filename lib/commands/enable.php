<?php

/*
 * This file is part of Pug
 */
namespace Pug;

use Huxtable\CLI;

/**
 * @command		enable
 * @desc		Include project in 'all' updates
 * @usage		enable <name>
 */
$commandEnable = new CLI\Command( 'enable', 'Include project in \'all\' updates', function( $name )
{
	$pug = new Pug();
	$pug->enableProject( $name );

	return listProjects( $pug->getProjects() );
});

return $commandEnable;
