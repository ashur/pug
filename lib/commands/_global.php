<?php

/*
 * This file is part of Pug
 */
namespace Pug;

use Huxtable\CLI;

// Helpers
/**
 * @param	array	$projects
 * @param	boolean	$showGit
 * @param	boolean	$showPath
 * @param	boolean	$useColor
 */
function listProjects( array $projects, $showGit=false, $showPath=false, $useColor=true )
{
	$output = new CLI\Output;

	if (count ($projects) < 1)
	{
		return $output;
	}

	if( $useColor )
	{
		$iconEnabled = new CLI\FormattedString( '*' );
		$iconEnabled->foregroundColor( 'green' );
	}
	else
	{
		$iconEnabled = '*';
	}

	$maxLengthName = 0;
	$maxLengthBranch = 0;

	/* Get project values */
	$metadata = [];
	foreach( $projects as $project )
	{
		$projectMetadata['branch'] = $project->getActiveBranch();
		$projectMetadata['commit'] = $project->getCommitHash();
		$projectMetadata['icon'] = $project->isEnabled() ? $iconEnabled : ' ';
		$projectMetadata['name'] = $project->getName();
		$projectMetadata['path'] = str_replace( getenv( 'HOME' ), '~', $project->getPath() );

		if( strlen( $projectMetadata['name'] ) > $maxLengthName )
		{
			$maxLengthName = strlen( $projectMetadata['name'] );
		}
		if( strlen( $projectMetadata['branch'] ) > $maxLengthBranch )
		{
			$maxLengthBranch = strlen( $projectMetadata['branch'] );
		}

		$metadata[] = $projectMetadata;
	}

	/* Generate output */
	foreach( $metadata as $project )
	{
		$line = sprintf(
			"%s %-{$maxLengthName}s",
			$project['icon'],
			$project['name']
		);

		if( $showGit )
		{
			$line .= sprintf( "  %-{$maxLengthBranch}s  %-7s",
				$project['branch'],
				$project['commit']
			);
		}

		if( $showPath )
		{
			if( $useColor )
			{
				$projectPath = new CLI\FormattedString( $project['path'] );
				$projectPath->foregroundColor( 'cyan' );
			}
			else
			{
				$projectPath = $project['path'];
			}

			$line .= "  {$projectPath}";
		}

		$output->line( $line );
	}

	return $output;
}
