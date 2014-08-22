<?php

/*
 * This file is part of Pug
 */

$commands = [];

// Project
$project = new Huxtable\Application\Command('project', 'List, create or delete projects', function()
{
	$pug = new Pug\Pug();

	foreach($pug->getProjects() as $project)
	{
		$pattern = " * %-16s%-20s%s\n";
		printf($pattern, $project->getName(), $project->getUpdated(), $project->getPath());
	}
});

$projectAdd = new Huxtable\Application\Command('add', 'Add a project named <name> which lives at <path>', function($name, $path)
{
	$pug = new Pug\Pug();

	$pug->addProject(new Pug\Project($name, $path));

	foreach($pug->getProjects() as $project)
	{
		$pattern = " * %-16s%-20s%s\n";
		printf($pattern, $project->getName(), $project->getUpdated(), $project->getPath());
	}
});

$projectRemove = new Huxtable\Application\Command('remove', 'Remove the project named <name>.', function($name)
{
	$pug = new Pug\Pug();
	$pug->removeProject($name);
});

$projectSetPath = new Huxtable\Application\Command('set-path', 'Changes the path for the named project.', function($name, $path)
{
	$pug = new Pug\Pug();
	$pug->setPathForProject(new Pug\Project($name, $path));
});

$project->addSubcommand($projectAdd);
$project->addSubcommand($projectRemove);
$project->addSubcommand($projectSetPath);

$commands[] = $project;

// Update
$update = new Huxtable\Application\Command('update', 'Fetch project updates', function($app)
{
	$pug = new Pug\Pug();
	$pug->update($app);
});

$update->setUsage("update [<app>|all]");

$commands[] = $update;

?>
