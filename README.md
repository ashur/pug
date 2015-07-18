# ![Quickly update Git and Subversion projects and their dependencies with a single command.](http://pug.sh.s3.amazonaws.com/pug.png)

One command is all you need to update local repositories and their submodules. If a project is using [CocoaPods](https://cocoapods.org) or [Composer](https://getcomposer.org) to manage its dependencies, Pug will automatically update those, too.


## Requirements

Pug requires PHP 5.4 or greater


## Installation

```bash
$ git clone --recursive https://github.com/ashur/pug.git
```

### Extra Credit

You'll probably also want to add the resulting folder to your environment `$PATH`. For example:

```bash
export PATH=$PATH:/path/to/pug/folder
```

Alternatively, you can symlink Pug to a directory already on your `$PATH`:

```bash
$ ln -s /path/to/pug/pug /usr/local/bin/pug
```

This lets you run `pug` commands from anywhere on the command line, not just from inside the Pug repository folder.


## Basic Usage

### Update

```bash
$ pug update ~/Developer/access
Updating '/Users/Ashur/Developer/access'... 

 • Pulling... 
   > Already up-to-date.

 • Updating submodules... 
   > Submodule path 'vendor/huxtable': checked out '0cccd17fe78fdd9a778f5025b244eafc68553764'

```


### Tracking

If you juggle multiple projects that need to stay up-to-date, Pug really shines with tracking:

```bash
$ pug add tapas ~/Developer/tapas
* dotfiles
* plank
* tapas
```

Updating a tracked project is easy:

```bash
$ pug update tapas
```

Updating all your projects at once is even easier:

```bash
$ pug update all
Updating 'dotfiles'... 

 • Pulling... 
   > Already up-to-date.
 • Updating submodules... done.

Updating 'plank'... 

 • Pulling... 
   > Already up-to-date.
 • Updating Composer... done.

Updating 'tapas'... 

 • Pulling... 
   > Already up-to-date.

```

### Enable/Disable
Need to focus on a subset of your projects for a while? Disable anything you don't need:

```bash
$ pug disable plank
* dotfiles
  plank
* tapas
```

Pug will hold onto the project definition, but skip it when you `update all`:

```bash
$ pug update all
Updating 'dotfiles'... 

 • Pulling... 
   > Already up-to-date.
 • Updating submodules... done.

Updating 'tapas'... 

 • Pulling... 
   > Already up-to-date.

```


## Configuration

Pug supports a few configuration options via Git's `config` command. They can be set either globally (using the `--global` flag) or on a per-project basis. For example:


```bash
$ git config [--global] pug.update.stash true
```

### Options

_pug.update.**stash**_

> _boolean_ — When **true**, automatically `stash` any changes in the active project before `pull`-ing, then pop the stack afterward
> 
> Default value is **false**

_pug.update.**submodules**_

> _boolean_ — When **false**, override default submodule update during `pug update`
> 
> Default value is **true**


## Under the hood

It's important to know what Pug is doing when it updates. In order of operations:

### SCM

If a project is using Git:

```bash
git pull
git submodule update --init --recursive
```

If it's using Subversion, it runs:

```bash
svn up
```

### Dependency Managers

If Pug detects CocoaPods, it will try to determine if an update is necessary:

* Is the `Pods` folder missing?
* Is the `Podfile.lock` file missing?
* Was the `Podfile` updated as part of the main repository update?

If any of above are true, Pug will then run:

```bash
pod install
```

If Pug detects Composer, it will try to determine if an update is necessary:

* Is the `composer.lock` file missing?
* Was `composer.json` updated as part of the main repository update?

If either of above are true, Pug will then run:

```bash
composer update
```


## Tips

### Save the 'date'

Drop the `date` and save yourself a few (thousand) keystrokes:

```bash
$ pug up
```


### pug up pug

It's easy to keep your copy of Pug up-to-date... using Pug! Add your local copy as a tracked project:

```bash
$ pug add pug [/path/to/pug]
```

Optionally, you can disable it so it's not jamming up your daily `update all` routine:

```bash
$ pug disable pug
```

Grab the latest version at any time:

```bash
$ pug up pug
```


## pug help

Command-specific help is available on the command line:

```bash
$ pug help
usage: pug [--version] <command> [<args>]

Commands are:
   add        Start tracking a new project
   disable    Exclude project from 'all' updates
   enable     Include project in 'all' updates
   help       Display help information about pug
   rm         Stop tracking a project
   show       Show tracked projects
   update     Fetch project updates

See 'pug help <command>' to read about a specific command
```
