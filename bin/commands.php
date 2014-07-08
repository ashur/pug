<?php

/*
 * This file is part of Pug
 */

$commands = [];

// Project
$project = new Huxtable\Application\Command('project', 'List, create or delete projects', function()
{
	$config = Pug\Config::open();

	foreach($config->getProjects() as $project)
	{
		$pattern = "   %-11s%s\n";
		printf($pattern, $project->getName(), $project->getPath());
	}
});

$projectAdd = new Huxtable\Application\Command('add', 'Add a project named <name> which lives at <path>', function($name, $path)
{
	$config  = Pug\Config::open();

	$config->addProject(new Pug\Project($name, $path));

	foreach($config->getProjects() as $project)
	{
		$pattern = "   %-11s%s\n";
		printf($pattern, $project->getName(), $project->getPath());
	}
});

$projectRemove = new Huxtable\Application\Command('remove', 'Remove the project named <name>.', function($name)
{
	$config  = Pug\Config::open();
	$config->removeProject($name);
});

$projectSetPath = new Huxtable\Application\Command('set-path', 'Changes the path for the named project.', function($name, $path)
{
	echo "@todo update entry for '{$name}:{$path}'".PHP_EOL;
});


$project->addSubcommand($projectAdd);
$project->addSubcommand($projectRemove);
$project->addSubcommand($projectSetPath);

$commands[] = $project;

// Update
$commands[] = new Huxtable\Application\Command('update', 'Fetch project updates', function($app=null)
{
	$config = Pug\Config::open();

	if(is_null($app))
	{
		foreach($config->getProjects() as $project)
		{
			$project->update();
		}
	}
	else
	{
		$project = $config->getProject($app)->update();
	}
});

?>
