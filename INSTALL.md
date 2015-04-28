# Installation

Run the following at a command line:

```bash
$ git clone --recursive https://github.com/ashur/pug.git
```

## Extra Credit

You'll probably also want to add the resulting folder to your environment `$PATH`. For example, if you're running **bash**, you'd add the following to `~/.bashrc`:

```bash
export PATH=$PATH:/path/to/pug/folder
```

Alternatively, you can symlink Pug to a directory already on your `$PATH`:

```bash
$ ln -s /path/to/pug/pug /usr/local/bin/pug
```

This lets you run `pug` commands from anywhere on the command line, not just from inside the Pug repository folder.

## Requirements

Pug requires PHP 5.4 or greater
