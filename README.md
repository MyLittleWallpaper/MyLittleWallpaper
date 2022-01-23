# My Little Wallpaper

TODO: Write description

# Requirements

* **Linux OS**, will not run on Windows
* **PHP 7.4+**
  * Run `composer install` to see if you are missing any required extensions.
* **MySQL 5.5+** or **MariaDB 5.5+**
* **ImageMagick**, *convert* command line tool used
* **Clam AntiVirus daemon**
* **GNU Wget**

# Installation

Todo: write

Use Vagrant provisioning as an example.

# Development

This repository includes a Vagrantfile for local development. The IP address is 192.168.56.20 and an admin account with following credentials is created in provisioning:

* Username: mlwp
* Password mlwp

Additional steps:

* Make sure temp folder and files folder and all sub folders in it are writable
* Add `192.168.56.20 local.mlwp` to your hosts file