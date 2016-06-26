<?php

/*
 * This file is part of Pug
 */
namespace Pug;

use Huxtable\CLI;

// Helpers
/**
 * @param	array	$projects
 * @param	string	$name
 */
function listProjects( array $projects, $name='' )
{
	if (count ($projects) < 1)
	{
		return;
	}

	$output = new CLI\Output;

	$iconEnabled = new CLI\Format\String( '*' );
	$iconEnabled->foregroundColor( 'green' );

	// List all projects
	if( strlen( $name ) == 0 )
	{
		foreach($projects as $project)
		{
			$output->line (sprintf
			(
				'%s %s'
				, $project->isEnabled() ? $iconEnabled : ' '
				, $project->getName()
			));
		}
	}
	else
	{
		$listed = false;

		foreach($projects as $project)
		{
			if( $project->getName() == $name )
			{
				$updated = is_null ($project->getUpdated()) ? '-' : CLI\Format::date ($project->getUpdated());
				$path = str_replace (getenv('HOME'), '~', $project->getPath());

				$output->line (sprintf
				(
					'%s %-12s  %s'
					, $project->isEnabled() ? $iconEnabled : ' '
					, $updated
					, $path
				));


				$listed = true;
			}
		}

		if( !$listed )
		{
			throw new CLI\Command\CommandInvokedException( "Project '{$name}' not found.", 1 );
		}
	}

	return $output->flush();
}
