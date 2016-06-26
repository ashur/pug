<?php

/*
 * This file is part of Pug
 */
namespace Pug;

use Huxtable\CLI;

/**
 * @command		update
 * @desc		Fetch project updates
 * @usage		update [<name>]
 * @alias		remove,untrack
 */
$commandUpdate = new CLI\Command('update', 'Fetch project updates', function()
{
	$pug = new Pug();
	$sources = func_get_args();

	$options = $this->getOptionsWithValues();
	$forceDependencyUpdate = isset( $options['f'] ) || isset( $options['force'] );

	if( count( $sources ) == 0 )
	{
		$sources[] = '.';
	}
	if( $sources[0] == 'all' )
	{
		$sources = $pug->getEnabledProjects();
	}

	for( $i=0; $i < count ($sources); $i++ )
	{
		$target = $sources[$i];

		try
		{
			$pug->updateProject( $target, $forceDependencyUpdate );
		}
		catch( \Exception $e )
		{
			// Standard single-line failure with exit code
			if( count( $sources ) == 1 && $target != 'all' )
			{
				throw new CLI\Command\CommandInvokedException( $e->getMessage(), 1 );
			}

			$name = $target instanceof Project ? $target->getName() : $target;

			$stringHalted = new CLI\Format\String( "Updating '{$name}'... halted:" );
			$stringHalted->backgroundColor( 'red' );

			$stringMessage = new CLI\Format\String( " • {$e->getMessage()}" );
			$stringMessage->foregroundColor( 'red' );

			echo $stringHalted . PHP_EOL . PHP_EOL;
			echo $stringMessage . PHP_EOL . PHP_EOL;
		}
	}
});

$commandUpdate->addAlias('up');
$commandUpdate->registerOption( 'f', 'Force dependency managers to update' );
$commandUpdate->registerOption( 'force', 'Force dependency managers to update' );

$updateUsage = <<<USAGE
update [options] [all|<path>|<project>...]

OPTIONS
     -f, --force
         force dependency managers (ex., CocoaPods) to update


USAGE;

$commandUpdate->setUsage( $updateUsage );

return $commandUpdate;
