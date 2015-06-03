# StreamBed

## Experimental

This is an experimental version of streambed in which the php code is being refactored into js using node.

### Sails setup

All sails files are found in the 'sails' folder.

Copy sails/config/local-example.js into a new file called sails/config/local.js. This is used to store local config settings and will not be committed to the repository.

### Starting sails

Open a terminal or command prompt and cd into the root of the project.
Then cd into the sails directory with

```
cd sails
```

Now, to start the sails server type

```
sails lift
```

Sails will be available on localhost:1337 (or any domain in the hosts file that points to 127.0.0.1).

### Auto restart of sails on code change

Sails needs restarting whenever there is a code change. This can be automated by installing nodemon

```
npm install -g nodemon
```

Then run nodemon with

```
nodemon -w api -w config
```

Files and folders that are not monitored are found in .nodemonignore

## End of experimental notes

This is an implementation of the [Babbling Brook protocol](http://babblingbrook.net), used for creating a shared social networking experience where anyone can host social data and any kind of social networking website can be developed.

See [babblingbrook.net/page/docs](http://babblingbrook.net/page/docs) for documentation on the protocol.

See [cobaltcascade.net](http://cobaltcascade.net) for a running example of this codebase.

### Alpha software

This is Alpha software and some aspects of the protocol are not yet implemented.

It is not yet ready for releasing independent client and datastore websites.

### License

Copyright 2015 Sky Wickenden

This file is part of StreamBed.
An implementation of the Babbling Brook Protocol.

StreamBed is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
at your option any later version.

StreamBed is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with StreamBed.  If not, see <http://www.gnu.org/licenses/>


### Requirements

* PHP 5.5.5
* MySql 5.6
(Marai DB Note: Uses Innodb with foreign keys which requires 5.6, equivalent to MariaDB 10.1 which is currently in alpha).
* Apache 2.4.6 (Should work on earlier versions.)


### Installation

Download from github or clone from github directly.

Place this root folder in your servers public directory.

#### Directory settings

On Linux systems ensure that the following folders are chmod to 770:
The best way to achieve this is to give the apache user (www-data) ownership of the files with :

```
sudo chown -R www-data folder-location
sudo chmod 770 folder-location
```

```
/protected/runtime
/assets
/protected/cookiepath
/protected/log
/css/Minified/
/css/Minified/Client
/css/Minified/Public
/js/Minified
/js/Minified/Client
/js/Minified/Public
/images/user
/images/tmp
```

#### Domains and Hosts file

If setting live then you will need a domain name. Otherwise you will need to choose one to work with locally.
'localhost' will not work. Your domain must have a tld. EG streambed.localhost

From this base domain you will need to setup your hosts file to direct the following seven sub domains to localhost.
```
127.0.0.1       streambed.localhost
127.0.0.1       domus.streambed.localhost
127.0.0.1       scientia.streambed.localhost
127.0.0.1       kindred.streambed.localhost
127.0.0.1       filter.streambed.localhost
127.0.0.1       suggestion.streambed.localhost
127.0.0.1       ring.streambed.localhost
```

#### Apache

Apache will need setting up to direct these to the installation folder.
Here is an example virtual host for the base domain.
```
<VirtualHost *:80>
  # Admin email, Server Name (domain name), and any aliases
  ServerAdmin admin@streambed.localhost
  ServerName  streambed.localhost

  # Index file and Document Root (where the public files are located)
  DirectoryIndex index.php
  DocumentRoot "/path/to/project/root/"

  <Directory "/path/to/project/root/">
    SSLOptions +StdEnvVars
    Options Indexes FollowSymLinks
    Order Deny,Allow
    Allow from all
    AllowOverride All
  </Directory>  

  # Log file locations
  LogLevel warn
  ErrorLog  "/path/to/log/folder/log/error.log"
  CustomLog "/path/to/log/folder/access.log combined"
</VirtualHost>
```

Notes: 

If you already have any Apache VirtualHost settings that point directly at 127.0.0.1 then you will need to use
```
<VirtualHost 127.0.0.1:80>
```
If you have multiple sites running on Apache then you will want to tighten the security of the Directory Allow directive.

#### SSL certificates.

SSL certificates will need creating and installing for streambed.localhost, domus.streambed.localhost and scientia.streambed.localhost. Alternatively you can create a single wildcard certificate for *.streambed.localhost

Self signed certificates are fine for dev work. (You may need to use the --ignore-certificate-errors command line flag in Chrome).

Apache config files will also need creating for them. A seperate vituralhost will need creating for each certificate if a wildcard certificate is not used.
```
<VirtualHost *:443>
  # Admin email, Server Name (domain name), and any aliases
  ServerAdmin admin@streambed.localhost
  ServerName  *.streambed.localhost

  # Index file and Document Root (where the public files are located)
  DirectoryIndex index.php
  DocumentRoot "/path/to/project/root/"

  <Directory "/path/to/project/root/">
    SSLOptions +StdEnvVars
    Options Indexes FollowSymLinks
    Order Deny,Allow
    Allow from all
    AllowOverride All
  </Directory>  

  # Log file locations
  LogLevel warn
  ErrorLog  "/path/to/log/folder/log/error.log"
  CustomLog "/path/to/log/folder/access.log" combined

  SSLCertificateFile "/path/to/certificate/streambed.localhost.cert"
  SSLCertificateKeyFile "/path/to/key/streambed.localhost.key"
</VirtualHost>
```

#### Edit config files.

Edit /index.php

Edit the following line:
```
define("CLIENT_TYPE", "cascade");
```
to the kind of client website type you want to create.
(Currently the only option is 'cascade').

Edit the following line:
```
define("HOST", "streambed.localhost");
```
to be the domain you are hosting this on.


#### Setup the database

Create a new MySQL user and enter the db user details into /protected/config/server.php.

You need to enter the details three times; one for each database connection.

The following three databases will be created. If you want to change the names of these databases you can edit them in
/protected/config/server.php
```
streambed
streambed_test
streambed_log
```

### Ready to roll

You should now be ready to roll. Go to http://streambed.localhost/site/setup

The databases and base data will be created.

Now go to http://streambed.localhost and the site should load.

One last task to do. The self signed SSL certificates are loaded in hidden iframes which causes the bad certificate warnings to be hidden. To resolve this go to each sub domain and permanently accept the ssl certificate:

* https://streambed.localhost
* https://domus.streambed.localhost
* https://scientia.streambed.localhost


### Further documentation

##### Protocol

See [babblingbrook.net/page/docs](http://babblingbrook.net/page/docs) for documentation on the protocol.

##### Framework overview

Almost all classes and methods have doc comments. The [framework overview](/protected/documentation/FrameworkOverview.md) explains the overall structure of the
project. Refer to this when ever you want to find out where something is or where to put something.

##### Coding conventions

There is a [full set of coding conventions](/protected/documentation/coding_conventions) and [a guide to using PHP CodeSniffer](/protected/documentation/coding_conventions/code_sniffer_standards/setting_up_code_sniffer.md) for automated detections of bad code.

##### Testing

There is [an overview of testing procedures here](/protected/documentation/Testing.md).
