<?php

/*
 * This file is part of Pug
 */

use \Huxtable\Format;
use \Huxtable\Output;

$commands = [];

// --
// list
// --
$list = new Huxtable\Command('list', 'List all tracked projects', function()
{
	$output = new Output();
	$pug = new Pug\Pug();
	$projects = $pug->getProjects ($this->getOptionValue('t'));

	if (count ($projects) < 1)
	{
		$output->line ('pug: Not tracking any projects. See \'pug help\'');
	}
	else
	{
		$output->string ( listProjects ( $pug->getProjects() ) );
	}

	return $output->flush();
});

$list->addAlias('ls');
$list->setUsage("[list|ls] [-t]");

$list->registerOption('t', 'Sort by time modified (most recently modified first) before sorting projects by name');

$commands['list'] = $list;

// --
// track
// --
$commands['track'] = new Huxtable\Command('track', 'Track a project at <path>', function($path, $name='')
{
	$file = new SplFileInfo($path);

	// Resolve $path
	if(!file_exists($file->getRealPath()))
	{
		throw new \Huxtable\Command\CommandInvokedException("Couldn't track project, path '{$path}' not found", 1);
	}
	if(!$file->isDir())
	{
		throw new \Huxtable\Command\CommandInvokedException("Couldn't track project, path '{$path}' not a directory", 1);
	}

	if($name == '')
	{
		$name = basename($file->getRealPath());
	}

	$pug = new Pug\Pug();
	$pug->addProject (new Pug\Project ($name, $file->getRealPath(), $file->getCTime()));

	return listProjects($pug->getProjects());
});

// --
// untrack
// --
$commands['untrack'] = new Huxtable\Command('untrack', 'Stop tracking the project <name>.', function($name)
{
	$pug = new Pug\Pug();
	$pug->removeProject($name);

	return listProjects ($pug->getProjects());
});

// --
// update
// --
$update = new Huxtable\Command('update', 'Fetch project updates', function()
{
	$pug = new Pug\Pug();
	$sources = func_get_args();

	if (count ($sources) == 0)
	{
		$sources[] = '.';
	}

	for ($i=0; $i < count ($sources); $i++)
	{
		$pug->update ($sources[$i]);
	}
});

$update->addAlias('up');
$update->setUsage("update [all|<path>|<project>...]");

$commands['update'] = $update;

/**
 * @param	array	$projects
 */
function listProjects(array $projects)
{
	if (count ($projects) < 1)
	{
		return;
	}

	$output = new Output;
	$output->line ('total ' . count ($projects));

	foreach($projects as $project)
	{
		$updated = is_null ($project->getUpdated()) ? '-' : Format::date ($project->getUpdated());
		$path = str_replace (getenv('HOME'), '~', $project->getPath());

		$output->line (sprintf
		(
			'%-12s %s -> %s'
			, $updated
			, Output::colorize ($project->getName(), 'purple')
			, $path
		));
	}

	return $output->flush();
}

?>
