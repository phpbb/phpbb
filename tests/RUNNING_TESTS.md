Running Tests
=============

Prerequisites
=============

PHPUnit
-------

phpBB unit tests use the PHPUnit framework (see http://www.phpunit.de for more
information). Version 3.5 or higher is required to run the tests. PHPUnit can
be installed via Composer together with other development dependencies as
follows.

    $ cd phpBB
    $ php ../composer.phar install --dev
    $ cd ..

PHP extensions
--------------

Unit tests use several PHP extensions that board code does not use. Currently
the following PHP extensions must be installed and enabled to run unit tests:

- ctype (also a PHPUnit dependency)
- dom (PHPUnit dependency)
- json (also a phpBB dependency)

Some of the functionality in phpBB and/or the test suite uses additional
PHP extensions. If these extensions are not loaded, respective tests
will be skipped:

- apc (APC cache driver)
- bz2 (compress tests)
- interbase, pdo_firebird (Firebird database driver)
- mysql, pdo_mysql (MySQL database driver)
- mysqli, pdo_mysql (MySQLi database driver)
- pcntl (flock class)
- pdo (any database tests)
- pgsql, pdo_pgsql (PostgreSQL database driver)
- redis (https://github.com/nicolasff/phpredis, Redis cache driver)
- simplexml (any database tests)
- sqlite, pdo_sqlite (SQLite database driver, requires SQLite 2.x support
  in pdo_sqlite)
- zlib (compress tests)

Database Tests
--------------

By default all tests requiring a database connection will use sqlite. If you
do not have sqlite installed the tests will be skipped. If you wish to run the
tests on a different database you have to create a test_config.php file within
your tests directory following the same format as phpBB's config.php. An
example for mysqli can be found below. More information on configuration
options can be found on the wiki (see below).

    <?php
    $dbms = 'phpbb_db_driver_mysqli';
    $dbhost = 'localhost';
    $dbport = '';
    $dbname = 'database';
    $dbuser = 'user';
    $dbpasswd = 'password';

It is possible to have multiple test_config.php files, for example if you
are testing on multiple databases. You can specify which test_config.php file
to use in the environment as follows:

    $ PHPBB_TEST_CONFIG=tests/test_config.php phpunit

Alternatively you can specify parameters in the environment, so e.g. the
following will run PHPUnit with the same parameters as in the shown
test_config.php file:

    $ PHPBB_TEST_DBMS='mysqli' PHPBB_TEST_DBHOST='localhost' \
      PHPBB_TEST_DBNAME='database' PHPBB_TEST_DBUSER='user' \
      PHPBB_TEST_DBPASSWD='password' phpunit

Special Database Cases
----------------------
In order to run tests on some of the databases that we support, it will be
necessary to provide a custom DSN string in test_config.php. This is only
needed for MSSQL 2000+ (PHP module), MSSQL via ODBC, and Firebird when
PDO_Firebird does not work on your system
(https://bugs.php.net/bug.php?id=61183). The variable must be named `$custom_dsn`.

Examples:
Firebird using http://www.firebirdsql.org/en/odbc-driver/

    $custom_dsn = "Driver={Firebird/InterBase(r) driver};dbname=$dbhost:$dbname";

MSSQL

    $custom_dsn = "Driver={SQL Server Native Client 10.0};Server=$dbhost;Database=$dbname";

The other fields in test_config.php should be filled out as you would normally
to connect to that database in phpBB.

Additionally, you will need to be running the DbUnit fork from
https://github.com/phpbb/dbunit/tree/phpbb.

Redis
-----

In order to run tests for the Redis cache driver, at least one of Redis host
or port must be specified in test configuration. This can be done via
test_config.php as follows:

    <?php
    $phpbb_redis_host = 'localhost';
    $phpbb_redis_port = 6379;

Or via environment variables as follows:

    $ PHPBB_TEST_REDIS_HOST=localhost PHPBB_TEST_REDIS_PORT=6379 phpunit

Running
=======

Once the prerequisites are installed, run the tests from the project root
directory (above phpBB):

    $ phpBB/vendor/bin/phpunit

Slow tests
--------------

Certain tests, such as the UTF-8 normalizer or the DNS tests tend to be slow.
Thus these tests are in the `slow` group, which is excluded by default. You can
enable slow tests by copying the phpunit.xml.all file to phpunit.xml. If you
only want the slow tests, run:

    $ phpBB/vendor/bin/phpunit --group slow

More Information
================

Further information is available on phpbb wiki:
http://wiki.phpbb.com/Unit_Tests
