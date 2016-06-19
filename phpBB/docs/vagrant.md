## Using Vagrant with phpBB

phpBB includes support for Vagrant. This allows developers and contributors to run phpBB without the need to set up their own local web server with traditional WAMP/MAMP stacks. It also provides a consistent environment between developers for writing and debugging code changes more productively.

phpBB uses the [Laravel/Homestead](https://laravel.com/docs/5.1/homestead) Vagrant box. It runs a Linux server with Ubuntu 14.04, PHP 5.6, Nginx, SQLite3, MySQL, and a whole lot more (complete specs below).

## Get Started

* Download and Install [Vagrant](https://www.vagrantup.com/downloads.html)
* Download and Install [VirtualBox](https://www.virtualbox.org/wiki/Downloads)
* Run `vagrant up` from the root of your cloned fork of the phpBB Git repository

```sh
$ vagrant up
```

* Access phpBB at `http://192.168.10.10/`
* Username: **admin**
* Password: **adminadmin**

## Additional commands:
* Access your Linux server from the command line:

```sh
$ vagrant ssh
```

* Pause your server:

```sh
$ vagrant suspend
```

* Shut down your server:

```sh
$ vagrant halt
```

* Delete and remove your server:

```sh
$ vagrant destroy
```

> Note: destroying the vagrant server will remove all traces of the VM from your computer, reclaiming any disk space used by it. However, it also means the next time you vagrant up, you will be creating a brand new VM with a fresh install of phpBB and a new database.

## Customising the phpBB configuration

By default, phpBB is pre-configured to install with a MySQL database. You can, however, switch to PostegreSQL or SQLite3 by editing the `phpbb-install-config.yml` file in the vagrant directory. The next time you run `vagrant up` (or `vagrant provision`) it will be installed under the new configuration.

If you prefer to access phpBB from the more friendly URL `http://phpbb.app` then you must update your computer's hosts file. This file is typically located at `/etc/hosts` for Mac/Linux or `C:\Windows\System32\drivers\etc\hosts` for Windows. Open this file and add the following line to it, at the very bottom, and save.

```
192.168.10.10  phpbb.app
```

## How it all works

When you vagrant up, the Laravel/Homestead box is transparently loaded as a Virtual Machine on your computer (this may take several minutes the very first time while it downloads the VM image to your computer). Your local phpBB repository clone is mirrored/shared with the VM, so you can work on the phpBB code on your computer, and see the changes immediately when you browse to phpBB at the URL provided by the VM.

This is very similar to traditional methods of working with a local WAMP/MAMP stack, except the webserver is now being provided by a VM of a Linux server. The advantages here are the exact same Linux server environment is being used by everybody who uses Vagrant with phpBB, so there will be consist behaviour unlike when everybody is developing on different versions of PHP, server configurations, etc.

The environment is also "sandboxed" from your system. This means you don't need to worry about adjusting your own computer's internal PHP settings, setting up databases, or doing damage to your system or to phpBB. Other than the phpBB codebase, which lives on your computer, all execution is taking place within the VM and you can at any time, halt or destroy the VM and start a brand new one.

There are some caveats, however. You can only run one vagrant VM for the phpBB repository. And of course, the database will be destroyed when you vagrant destroy. If the database is important, you should SSH into your vagrant VM and export/import the DB as needed using SSH commands.

For example, to export/import a MySQL database (using phpBB's `store` directory):

SSH into the VM

```sh
$ vagrant ssh
```

Export MySQL:

```sh
$ mysqldump -uhomestead -psecret phpbb > /home/vagrant/phpbb/phpBB/store/phpbb.sql
```

Import MySQL:

```sh
$ mysql -uhomestead -psecret phpbb < /home/vagrant/phpbb/phpBB/store/phpbb.sql
```

---

## About the Laravel/Homestead box

### Included Software

* Ubuntu 14.04
* Git
* PHP 5.6
* HHVM
* Nginx
* MySQL
* Sqlite3
* Postgres
* Composer
* Node (With PM2, Bower, Grunt, and Gulp)
* Redis
* Memcached
* Beanstalkd
* Blackfire Profiler

### MySQL Access

- Hostname: 127.0.0.1
- Username: homestead
- Password: secret
- Database: phpbb
- Port: 3306

### PostgreSQL Access

- Hostname: 127.0.0.1
- Username: homestead
- Password: secret
- Database: phpbb
- Port: 5432
