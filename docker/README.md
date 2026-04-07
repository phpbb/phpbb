## Using Docker with phpBB

phpBB includes support for Docker development environment.
This allows developers and contributors to run and modify phpBB on a consistent environment through a GitHub account without the need to set up a local web server.

Features include:

* Automatic phpBB installation
* [Xdebug](https://github.com/xdebug/vscode-php-debug) pre-configured to step through the phpBB code and add breakpoints
* Full LAMP stack with database access

## How it works


### Build docker image

* Clone the `phpbb/phpbb` repository
* In the terminal, navigate to the phpBB repository location
* Navigate to `docker` folder
* Run `docker image build -t phpbbdev .` to build docker image

### Create phpBB development container

* Build docker image (see previous step)
* Run `docker run -d --name phpbb -p 80:80 -p 8080:8080 -p 3306:3306 -v /path-to/phpBB/:/workspaces/phpbb -v /path-to/phpBB/docker/development-team:/var/phpbb docker.io/library/phpbbdev:latest`

In the above command please replace `/path-to/phpBB` with the location where you cloned the phpBB repository.

You can also replace the `development-team` with `customisations-team` or a different folder that defines how your local phpBB should be configured, installed and kept synchronized.


### How to use it

After creating the container, you can access your local phpBB by navigating to http://localhost in your browser.

Your local phpBB board will be installed using the `phpbb-config.yml` config file located inside the `phpbb/docker/development-team` folder inside your phpBB repository.

### How to develop with it

Container monitors any changes made to the `phpBB` folder inside your local Git repository and automatically copies them to the running container.

Container will keep in sync with your local copy automatically without the need to restart the container or rebuild the image.

Files and folders listed inside `phpbb/docker/development-team/rsync-exclude.txt` will be ignored during synchronization. Default: `install`, `cache`, `config`



