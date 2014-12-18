<?php

/*
 * This file is part of Pug
 */

$commands = [];

// --
// list
// --
$list = new Huxtable\Command('list', 'List all tracked projects', function()
{
	$pug = new Pug\Pug();
	listProjects($pug->getProjects());
});

$list->addAlias('ls');
$list->setUsage("[list|ls]");

$commands[] = $list;

// --
// track
// --
$commands[] = new Huxtable\Command('track', 'Track a project at <path>', function($path, $name='')
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
	$pug->addProject(new Pug\Project($name, $file->getRealPath()));

	listProjects($pug->getProjects());
});

// --
// untrack
// --
$commands[] = new Huxtable\Command('untrack', 'Stop tracking the project <name>.', function($name)
{
	$pug = new Pug\Pug();
	$pug->removeProject($name);

	listProjects($pug->getProjects());
});

// --
// update
// --
$update = new Huxtable\Command('update', 'Fetch project updates', function($name='.')
{
	$pug = new Pug\Pug();
	$pug->update($name);
});

$update->addAlias('up');
$update->setUsage("[update|up] [<name>|<path>|all]");

$commands[] = $update;

/**
 * @param	array	$projects
 * @param	boolean	$showPath
 */
function listProjects(array $projects)
{
	foreach($projects as $project)
	{
		printf(" %-16s%-20s%s\n", $project->getName(), $project->getUpdated(), $project->getPath());
	}
}

?>
