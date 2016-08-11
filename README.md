# ![Quickly update Git and Subversion projects and their dependencies with a single command.](http://pug.sh.s3.amazonaws.com/pug3.png)

One command is all you need to update local repositories and their submodules. If a project is using [CocoaPods](https://cocoapods.org) or [Composer](https://getcomposer.org) to manage its dependencies, Pug will automatically update those, too.

## Contents

1. [Installation](#installation)
1. [Basics](#basics)
1. [Update](#update)
1. [Configuration](#configuration)
1. [Tips](#tips)
1. [Help](#help)

## Installation

```
$ git clone --recursive https://github.com/ashur/pug.git
```

### Requirements

Pug requires PHP 5.4 or greater

### Upgrading

> 🎉 **New in v0.5**

If you're upgrading from **v0.5** or later, use the built-in command to fetch the latest version:

```
$ pug upgrade
```

If you're upgrading from **v0.4** or earlier, the best way to handle all the submodules and fiddly bits is with Pug itself:

```
$ cd ~/tools/pug
$ pug up
```

> 💡 **Tip** — Not sure which version you're running?
>
> ```
> $ pug --version
> pug version 0.5.0
> ```
>
> Nice. 😁

### Extra Credit

Let's say you cloned Pug to a local directory `~/tools`:

```
$ cd ~/tools
$ git clone --recursive https://github.com/ashur/pug.git
```

You'll probably want to symlink the Pug executable to a directory already on your `$PATH`:

```
$ ln -s ~/tools/pug/bin/pug /usr/local/bin/pug
```

This lets you run `pug` commands from anywhere on the command line, not just from inside the Pug repository folder.

Alternatively, you can add the repository's `bin` folder to your environment `$PATH`. For example:

```
export PATH=$PATH:$HOME/tools/pug/bin
```


## Basics

After you've installed Pug, try updating a local Git repository at any path:

```
$ pug update ~/Developer/tapas
Updating '/Users/ashur/Developer/tapas'...

 • Pulling...
   > Already up-to-date.

 • Updating submodules... done.
```

By default, `pug update` performs two operations on the repository:

* `git pull`
* `git submodule update`

> 🔬 [Learn more](#update) about the specifics of what Pug does during an update. You can re-configure the default behavior on a global or per-repository basis. See [Configuration](#configuration) for more information.

Using Pug to update repositories at an arbitrary path is nice, but projects make things even easier.


### Projects

First, let's add a repository to our list of tracked projects:

```
$ pug add plank ~/Developer/plank
* plank
```

Now we can grab updates using the project name instead of the full path:

```
$ pug update plank
```

That's nicer! Let's add a few more projects:

```
$ pug show
* plank
* prompt
* transmit
```

With a single command, we can update multiple projects _and_ their submodules.

```
$ pug update all
```

### Enable/Disable

Need to focus on a subset of your projects for a while? Disable anything you don't need:

```
$ pug disable prompt
* plank
  prompt
* transmit
```

Pug will hold on to the project definition, but skip it when you `update all`:

```
$ pug update all
Updating 'plank'...

 • Pulling...
   > Already up-to-date.

Updating 'transmit'...

 • Pulling...
   > Already up-to-date.

   • Updating submodules... done.

```


### Groups

> 🎉 **New in v0.6**

As the list of tracked projects grows, it can get harder to stay organized:

```
$ pug show
* ansible
* cios
* dotfiles
* mlib
  plank
  prompt
* tapas
* tios
* transmit
* zoo
```

Groups help keep things nice and tidy. To add a new project to a group, use the `<group>/<project>` naming pattern:

```
$ pug add mac/coda ~/Developer/Coda
```

To move an existing project into a group, just rename it:

```
$ pug rename ansible sysops/ansible
```

Much better:

```
$ pug show
* bots/mlib
* bots/zoo
* dotfiles
* ios/coda
  ios/prompt
* ios/transmit
* mac/coda
* mac/transmit
* sysops/ansible
  web/plank
* web/tapas
```

In addition to keeping projects organized, we can also perform operations on groups just like individual projects. Done with `ios` for a while? Disable all the projects in that group:

```
$ pug disable ios
```

Want to update just the projects in your `bots` group? Simple!

```
$ pug update bots
```

> 💡 **Tip** — Add `--all` to update disabled projects in the group as well


## Update

It's important for you to know what Pug is doing on your behalf during `pug update`. In order of operations:

```
git pull
git submodule update --init --recursive
```

If the `pug.update.rebase` configuration option is set to `true`, Pug will instead run:

```
git fetch
git rebase
git submodule update --init --recursive
```

> 🔬 [Learn more](#configuration) about how to configure `pug update` to suit your needs

### Dependency Managers

If Pug detects CocoaPods, it will try to determine if an update is necessary. If so:

```
pod install
```

If Pug detects Composer and determines an update is necessary:

```
composer update
```

To force dependency updates:

```
$ pug update <target> --force
```

### Submodule State Restoration

> 🎉 **New in v0.5**

In previous versions, submodules were always left checked out on a detached HEAD after `pug update`. If you were doing development on a submodule, checking the submodule back out to its original branch and pulling down changes manually was a hassle.

With state restoration, if a submodule is checked out to a branch, Pug now returns it to its previous state and automatically `pull`s down any changes from the submodule's remote as well:

```
$ pug update tapas                     
Updating 'tapas'...

 • Pulling...
   > From github.com:ashur/corpora
   >    7b4a17c..e1caf08  master     -> origin/master
   > Updating 339cea5..57c314f
   > Fast-forward
   >  corpora | 2 +-
   >  1 file changed, 1 insertion(+), 1 deletion(-)

 • Updating submodules...
   > Submodule path 'corpora': checked out 'e1caf08eac44a149b14e4f2bbc4eb12ba6a4e6e4'
   % Submodule path 'corpora': checked out 'master'
   % Submodule path 'corpora': pulling 'master'... done.
```

> **Note** — If a submodule is checked out on a detached HEAD prior to the update, `pug update` leaves it that way.


## Configuration

Pug supports a few configuration options by piggybacking Git's own `config` command. They can be set either globally (using the `--global` flag) or on a per-project basis.

For example, we can change the global `pug update` behavior to always automatically stash changes:

```
$ git config --global pug.update.stash true
```

and still keep the default no-stash behavior where we need to:

```
$ cd ~/Developer/tapas
$ git config pug.update.stash false
```

### Options

⚙ pug.update.**rebase**

> _boolean_ — When **true**, `pug update` will perform `git fetch` and `git rebase` instead of `git pull`.
>
> Default value is **false**

> 🎉 **New in v0.5**

⚙ pug.update.**stash**

> _boolean_ — When **true**, automatically `stash` any changes in the active project before `pull`-ing, then pop the stack afterward
>
> Default value is **false**

⚙ pug.update.**submodules**

> _boolean_ — Whether to update submodules during `pug update`
>
> Default value is **true**



## 💡 Tips

### Save the "date"

Save yourself a few keystrokes every `update`:

```
$ pug up
```


## Help

Command-specific help is always available on the command line:

```
$ pug help
usage: pug [--version] <command> [<args>]

Commands are:
   add        Start tracking a new project
   disable    Exclude projects from 'all' updates
   enable     Include projects in 'all' updates
   rename     Rename an existing project
   rm         Stop tracking projects
   show       Show tracked projects
   update     Fetch project updates
   upgrade    Fetch the newest version of Pug

See 'pug help <command>' to read about a specific command
```
