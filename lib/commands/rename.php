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

	$useColor = $this->getOptionValue( 'no-color' ) == null;
	$output = listProjects( $pug->getProjects(), false, false, $useColor );

	return $output->flush();
});

/* Options */
$commandRename->registerOption( 'no-color' );

return $commandRename;
