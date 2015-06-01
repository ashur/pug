<?php

/*
 * This file is part of Pug
 */
namespace Pug;

interface DependencyManager
{
	public function __construct( \SplFileInfo $dir );
	public function update();
}

?>
