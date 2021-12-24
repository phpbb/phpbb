[<img src="phpBB/styles/all/imgs/svg/phpbb_logo_large_cosmic.svg" alt="phpBB" style="max-width:40%" width="400">](https://www.phpbb.com)

phpBB is a free open-source bulletin board written in PHP.

## 🧑🏻‍🤝🏻🧑🏽 Community

Get your copy of phpBB, find support and lots more on [phpBB.com](https://www.phpbb.com). Discuss the development on [area51](https://area51.phpbb.com/phpBB/index.php).

## 👨‍💻 Contribute

1. [Create an account on phpBB.com](https://www.phpbb.com/community/ucp.php?mode=register)
2. [Create a ticket (unless there already is one)](https://tracker.phpbb.com/secure/CreateIssue!default.jspa)
3. Read our [Coding guidelines](https://area51.phpbb.com/docs/dev/development/coding_guidelines.html) and [Git Contribution Guidelines](https://area51.phpbb.com/docs/dev/development/git.html)
4. Send us a pull request

### 🏗️ Setting up a development build of phpBB

To run an installation from the repo (and not from a pre-built package) on a local server, run the following commands:

- Fork phpbb/phpbb to your GitHub account, then create a local clone of it:
  ```
  git clone https://github.com/your_github_name/phpbb.git
  ```
- Install phpBB's dependencies (from the root of your phpbb repo):
  ```
  cd phpBB
  php ../composer.phar install
  ```
- Run without installation [![TeamCode try-it-now](https://static01.teamcode.com/badge/demo.svg)](https://www.teamcode.com/tin/clone?applicationId=273460556549484544)

Alternatively, you can read our [Vagrant documentation](phpBB/docs/vagrant.md) to find out how to use Vagrant to develop and contribute to phpBB.

## 📓 Documentation

phpBB's [Development Documentation](https://area51.phpbb.com/docs/dev/index.html) contains all the information you'll need to learn about developing for phpBB's core, extensions and automated testing.

## 🔬 Automated Testing

We have unit and functional tests in order to prevent regressions. You can view the bamboo continuous integration [here](https://bamboo.phpbb.com) or check our travis builds below:

Branch  | Description | Github Actions |
------- | ----------- | -------------- |
**master** | Latest development version | ![Tests](https://github.com/phpbb/phpbb/workflows/Tests/badge.svg?branch=master) |
**3.3.x** | Development of version 3.3.x | ![Tests](https://github.com/phpbb/phpbb/workflows/Tests/badge.svg?branch=3.3.x) |

## 📜 License

[GNU General Public License v2](http://opensource.org/licenses/gpl-2.0.php)
