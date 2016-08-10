<?php

/*
 * This file is part of Pug
 */
namespace Pug;

use Huxtable\CLI;
use Huxtable\CLI\Command;

/**
 * @command		rename
 * @desc		Rename an existing project
 * @usage		rename <old> <new>
 */
$commandRename = new CLI\Command( 'rename', 'Rename an existing project', function( $old, $new )
{
	$pug = new Pug();

	try
	{
		$pug->renameProject( $old, $new );
	}
	catch( \Exception $e )
	{
		throw new Command\CommandInvokedException( $e->getMessage(), 1 );
	}

	return listProjects( $pug->getProjects() );
});

return $commandRename;
