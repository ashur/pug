# ![Quickly update Git and Subversion projects and their dependencies with a single command.](http://pug.sh.s3.amazonaws.com/pug3.png)

One command is all you need to update local repositories and their submodules. If a project is using [CocoaPods](https://cocoapods.org) or [Composer](https://getcomposer.org) to manage its dependencies, Pug will automatically update those, too.


## Installation

```bash
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

```bash
$ ln -s ~/tools/pug/bin/pug /usr/local/bin/pug
```

This lets you run `pug` commands from anywhere on the command line, not just from inside the Pug repository folder.

Alternatively, you can add the repository's `bin` folder to your environment `$PATH`. For example:

```bash
export PATH=$PATH:$HOME/tools/pug/bin
```


## 🐶 Basic Usage

### Update

Pull changes _and_ update submodules with a single command:

```bash
$ pug update ~/Developer/access
Updating '/Users/Ashur/Developer/access'...

 • Pulling...
   > Already up-to-date.

 • Updating submodules...
   > Submodule path 'vendor/huxtable': checked out '0cccd17fe78fdd9a778f5025b244eafc68553764'

```

#### Submodule State Restoration

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

Pug will hold on to the project definition, but skip it when you `update all`:

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


## ⚙ Configuration

Using a custom section of Git's own `config` command, Pug supports a few configuration options. They can be set either globally (using the `--global` flag) or on a per-project basis.

For example, we can change the global behavior to always automatically stash changes:

```bash
$ git config --global pug.update.stash true
```

and still keep the default no-stash behavior for a subset of projects:

```
$ cd ~/Developer/tapas
$ git config pug.update.stash false
```

### Options

_pug.update.**rebase**_

> _boolean_ — When **true**, `pug update` will perform `git fetch` and `git rebase` instead of `git pull`.
>
> Default value is **false**

> 🎉 **New in v0.5**

_pug.update.**stash**_

> _boolean_ — When **true**, automatically `stash` any changes in the active project before `pull`-ing, then pop the stack afterward
>
> Default value is **false**

_pug.update.**submodules**_

> _boolean_ — Whether to update submodules during `pug update`
>
> Default value is **true**


## 🛠 Under the hood

It's important for you to know what Pug is doing on your behalf during `pug update`. In order of operations:

```bash
git pull
git submodule update --init --recursive
```

If the `pug.update.rebase` configuration option is set to `true`, Pug will instead run:

```
git fetch
git rebase
git submodule update --init --recursive
```

### Dependency Managers

If Pug detects CocoaPods, it will try to determine if an update is necessary. If so:

```bash
pod install
```

If Pug detects Composer and determines an update is necessary:

```bash
composer update
```

To force dependency updates:

```bash
$ pug update --force
```


## 💡 Tips

### Save the "date"

Save yourself a few keystrokes every `update`:

```bash
$ pug up
```


## 🚩 Help

Command-specific help is always available on the command line:

```bash
$ pug help
usage: pug [--version] <command> [<args>]

Commands are:
   add        Start tracking a new project
   disable    Exclude project from 'all' updates
   enable     Include project in 'all' updates
   rm         Stop tracking a project
   show       Show tracked projects
   update     Fetch project updates
   upgrade    Fetch the newest version of Pug

See 'pug help <command>' to read about a specific command
```
