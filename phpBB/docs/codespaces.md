## Using GitHub Codespaces with phpBB

phpBB includes support for [Codespaces](https://docs.github.com/en/codespaces), GitHub's cloud-based software development environment. This allows developers and contributors to run and modify phpBB on a consistent environment through a GitHub account without the need to set up a local web server.

Codespaces is completely web-based and does not require any code to be downloaded locally.

Features include:

* Automatic phpBB installation
* Access to [VS Code](https://docs.github.com/en/codespaces/the-githubdev-web-based-editor) through the web browser 
* [Xdebug](https://github.com/xdebug/vscode-php-debug) pre-configured to step through the phpBB code and add breakpoints
* Full LAMP stack with database access
* Commit, push and test code entirely online

## How it works

### To run phpBB in Codespaces

* On the GitHub.com website, fork the `phpbb/phpbb` repository.
* Under the `Code` button, click the `Codespaces` tab and then the `+` button to create a new Codespace.
* It may take several minutes to configure and install phpBB on the newly created virtual machine. Once it is ready, a web-based VS Code instance will appear.
* Under the `Ports` tab, click on the local address under port 80 to open the private website for the new phpBB installation.
    * By default, the login details for the new phpBB installation are `admin` / `adminadmin`
    * Port 9003 is also open to allow Xdebug connections.

### To use the command line

* Click on the `Terminal` tab at the bottom of VS Code and make sure the `bash` window is selected in the right sidebar.
* The `workspaces/phpbb` directory contains the code from the workspace.
* Run `mysql -h 127.0.0.1 -u phpbb -p` to log into the MySQL database. The password is `phpbb` and the database name is `phpbb`.
    * Tip: type `use phpbb;` after logging in to MySQL to switch to the correct database, then queries can be run for the phpBB tables.

### To debug code

* Click the `Run and Debug` tab on the left sidebar in VS Code, then click the green play button next to the `Debug phpBB` option.
    * Tip: to confirm that Xdebug is working correctly, run `lsof -i :9003` on the command line. It should show that the port is listening for connections.
* In any of the files in the `phpBB/` directory, add a breakpoint by clicking just to the left of a line number in VS Code. Then, access the private website created on port 80 (found under the VS Code `Ports` tab) through the web browser and navigate to the page with a breakpoint. VS Code will automatically pause execution where the breakpoint is hit, and under the `Run and Debug` tab a variable list will be shown.

### To commit and push code

* To save a change made using VS Code on Codespaces, click the `Source Control` tab on the left sidebar in VS Code.
* To stage changes or discard changes, right click the file(s) and select the appropriate option.
* Type a commit message and select the `Commit and Push` option.

**Remember to stop a Codespace once you are finished with it.** GitHub provides all users with a limited number of free core hours per month. A Codespace can be stopped (or deleted) from the main repository page on GitHub.com.

## Technical information

All of the Codespaces configuration can be found in the `.devcontainer/` directory. The `devcontainer.json` holds the general environment information and `Dockerfile` contains the commands to set up the LAMP stack which enables phpBB to run.

`setup.sh` is used to install phpBB from the command line, using pre-determined details from `phpbb-config.yml`.

Codespaces can run without the configuration inside the `.vscode/` directory, however by including this no manual intervention is required to set the Xdebug IDE code to `VSCode` (inside `settings.json`) and  `Debug phpBB` information, such as the path mapping from the Apache webroot to the `phpbb/phpBB` directory (inside `launch.json`).

This configuration information can be safely modified to change the development environment, followed by `Ctrl+Shift+P` in VS Code and selection of the `Full Rebuild Container` option.