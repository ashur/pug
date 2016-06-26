<?php

/*
 * This file is part of Pug
 */
namespace Pug\DependencyManager;

interface IDependencyManager
{
	public function __construct( \SplFileInfo $dir );
	public function update( $force=false );
}
