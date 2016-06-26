<?php

/*
 * This file is part of Pug
 */
namespace Pug;

use Huxtable\CLI;

/**
 * @command		rm
 * @desc		Stop tracking a project
 * @usage		rm <name>
 * @alias		remove,untrack
 */
$commandRemove = new CLI\Command('rm', 'Stop tracking a project', function( $name )
{
	$pug = new Pug();
	$pug->removeProject($name);

	return listProjects ($pug->getProjects());
});

$commandRemove->addAlias( 'remove' );
$commandRemove->addAlias( 'untrack' );

return $commandRemove;
