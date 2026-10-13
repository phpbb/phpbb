## Using Docker with phpBB

phpBB includes support for Docker development environment.
This allows developers and contributors to run and modify phpBB on a consistent local environment using a Docker container.

Features include:

* Automatic phpBB installation
* [Xdebug](https://github.com/xdebug/vscode-php-debug) pre-configured to step through the phpBB code and add breakpoints
* Full LAMP stack with database access

## How it works


### Build docker image

* Clone the `phpbb/phpbb` repository
* In the terminal, navigate to the `docker` folder inside the phpBB repository (ie `cd /path-to/phpbb/docker`)
* Run `docker image build -t phpbbdev .` to build docker image

### Create phpBB development container

* Build `phpbbdev` docker image (see previous step)
* Run `docker run -d --name phpbb -p 80:80 -p 8080:8080 -p 3306:3306 -v /path-to/phpbb/:/workspaces/phpbb -v /path-to/phpbb/docker/development-team:/var/phpbb docker.io/library/phpbbdev:latest`

> [!NOTE]
> In the above command you have to replace `/path-to/phpbb` with the location where you cloned the phpBB repository.
>
> You can also replace the `development-team` with `customisations-team` or a different folder that defines how your local phpBB should be configured, installed and kept synchronized.


### How to use it

After creating the container, now you can access your local phpBB by navigating to http://localhost in your browser.

Your local phpBB board will be installed according to the rules defined inside your `phpbb-config.yml` config file (default: located inside `phpbb/docker/development-team` folder inside your phpBB repository).

### How to develop with it

On the very first run, container will install and configure Apache, MySQL and phpBB.

Container monitors any changes made to the `phpBB` folder inside your local `/path-to/phpbb/phpBB` directory and automatically copies them to the running container.

Container will keep in sync with your local copy automatically without the need to restart the container or rebuild the image.

> [!NOTE]
> Files and folders listed inside `phpbb/docker/development-team/rsync-exclude.txt` will be ignored during synchronization. Default: `install`, `cache`, `config`
