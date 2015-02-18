# Pug

<div style="clear: left;"></div>

Quickly update local projects and their dependencies with a single command. Currently supports Subversion and Git, and [CocoaPods](http://cocoapods.org/) and [Composer](https://getcomposer.org).


## Installation

```bash
$ git clone --recursive https://github.com/ashur/pug.git
```

You'll probably want to add the resulting folder to your environment `$PATH`. For example, if you're running **bash**, you'd add the following to `~/.bashrc`:

```bash
export PATH=$PATH:/path/to/pug/folder
```

This lets you run `pug` commands from anywhere on the command line, not just from inside the Pug repository folder.

### Requirements

Pug requires PHP 5.4 or greater

## Usage

Pug can fetch updates for projects that live at arbitrary paths. Some thrilling examples:

```bash
$ pug update ../plank
$ pug update ~/Developer/plank
```

Update the project at your current working directory:

```bash
$ pug update
$ pug update ./
```

Admittedly, the convenience here is small for simple projects, but Pug gives you a tiny leg up as things get more complicated. A single command is all you need to update Git repositories _and_ their submodules _and_ dependencies managed by tools like CocoaPods.

### Tracking

If you juggle multiple projects that need to stay up-to-date, Pug really starts to shine with tracking:

```bash
$ pug track ~/Developer/plank
total 2
Feb 18 08:46 plank -> /Users/ashur/Developer/plank
Feb 18 08:35 pug -> /Users/ashur/Developer/pug
```

Need to get up to speed across multiple projects every morning? It's a breeze, Louise:

```bash
$ pug update all
```

Want to stop tracking a project? We've got that too:

```bash
pug untrack plank
```

### Shortcuts

You look like a busy person. Pug has short command aliases for listing tracked apps:

```
$ pug list|ls
```

and for updating:

```
$ pug update|up
```

Just for you, Ace.

## pug help

Command-specific help is available on the command line:

```bash
$ pug help
usage: pug <command> [<args>]

Commands are:
   help       Display help information about pug
   list       List all tracked projects
   track      Track a project at <path>
   untrack    Stop tracking the project <name>.
   update     Fetch project updates

See 'pug help <command>' to read about a specific command
```
