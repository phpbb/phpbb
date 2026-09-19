# phpBB Docker

This project contains a bash script to spin up a development environment for phpBB 
in Docker. This script was only tested on Ubuntu and probably only works on Linux without
any modifications.

## Supported features

Currently the script supports the following environment.

### Supported databases

- MySQL

### Supported servers

- NGINX

### Supported PHP versions

- All of them which have a [Docker image](https://hub.docker.com/_/php/).

## Installation

First, you will need to have [Docker installed](https://docs.docker.com/install/) on your machine.

To be able to use the script you need to specify the `PHPBB_ROOT_PATH` environment 
variable which should contain the path to the root of your phpBB repository.

On most Linux systems you should be able to use the following command to accomplish 
this:
```bash
echo 'export PHPBB_ROOT_PATH=/path/to/the/repo' >> ~/.bashrc
```

In addition you could add the path of the script to your `PATH`, so you can simply 
execute the script by typing `phpbb-docker.bash`:
```bash
echo 'export PATH="$PATH:/path/to/this/repository"' >> ~/.bashrc
```

Finally, you should update your environment variables by running
```bash
bash
```

Finally, the server is configured in a way that expects the host name to be `phpbb.local`, for
this to work, you need to add a rule to your hosts file (e.g. `/etc/hosts`). For example, add
this line to it:
```bash
127.0.0.1	phpbb.local
```

And now you should be all set up.

## How does it work?

The script will spin up different containers for each branch you are working on.
This will make it possible to have your last development instance whenever
you decide to switch to a branch you worked on before.

This behaviour however will spin up a new container for each time you run
the script on a different branch. For that reason, you should regularly 
clear the containers you no longer need.

## Defaults and parameters

The default settings are as follows:
- MySQL database
- PHP 7
- NGINX webserver
- The database user, password and database name is `phpbb`.

The administrator username is `admin` and the password is `adminadmin`.

Your server should be available at `http://phpbb.local`.

There are command line arguments that can be set when running the script. The syntax is the
following:
```bash
phpbb-docker.bash -arg-name argvalue
```

or 

```bash
phpbb-docker.bash --arg-name argvalue
```

The supported arguments are:

```
-p or --php-version     For setting the PHP version. The argument should be a version number, 
                        which will be appended by '-fpm' for the PHP base image. E.g.: -p 7.2
                        Defaults to 7.
                        
-e or --environment     The phpBB environment name. Defaults to development.
```

## Debugging

The PHP image contains XDebug which will try to connect to the host machine on port 9000. The source folder 
`repo/path/phpBB` maps to `/var/www/phpBB` in the container.

## License

This script and the documentation is public domain, you can read the full license [here](LICENSE).
