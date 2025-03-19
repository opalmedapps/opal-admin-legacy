# opalAdmin

OpalAdmin is the administrative tool for managing and tagging personal health information that is published to Opal. 

## Docker Installation

Note: A docker installation installs all the Prerequisites in the container many of the server/local machine steps are done for you.

This project contains a `Dockerfile` as well as a `docker-compose.yml` to run it within a Docker container. To do so, call `docker-compose up` from the project's root. Once the image is built and the container running, you can access it via `http://localhost:8082/opalAdmin` from your browser.

In order to access the database running on your machine (whether in a container or not) you need to change the host in your `config.json` to `host.docker.internal` (macOS and Windows).

If port `8082` is already in use, change the port mapping in `docker-compose.yml`.

To force a re-build of the image. You may call `docker-compose build` before running or `docker-compose up --build` to force a re-build when running the container.

## Server/Local Machine Installation

## Prerequisites

For opalAdmin to work, a Linux-based operating system with a local web server, MySQL, PHP (> 5.3, < 7), and perl are required.

## Basic Installation

These instructions will get you a copy of the project up and running on your local machine. 

### Step 1 

On your server, navigate into your web server directory (i.e. the "localhost" directory that is accessible via an internet browser). 

### Step 2

Clone this project from Gitlab using ssh

```
git clone git@gitlab.com:opalmedapps/opalAdmin.git
```


### Step 3

Installing 3rd-party libraries require both [NodeJS](https://nodejs.org/en/download/) and [Bower](https://bower.io/#install-bower) to be installed on your server. To install the 3rd-party libraries, navigate to the project directory and issue the install commands:

```
cd opalAdmin
```

then

```
bower install 
```

and

```
npm install
```

### Step 4

Setup the environment file by running the executable bash script located in the project's root:

```
bash ./make_env_file.sh
```

This will copy **./.env.sample** to **./.env** and set preliminary configurations.

### Step 5

Create an empty Opal database using your favourite tool. **Note:** Keep track of the database name.

### Step 6

Open the **.env** file using your favourite editor and replace the default Opal credentials with your local credentials.

Copy the `firebase-admin-key.json` file to `config/firebase/`

If your database is being run with secure transport required (SSL/TLS traffic encryption), also update the values for the SSL environment variables: `DATABASE_USE_SSL=1` and `SSL_CA=/var/www/html/certs/ca.pem` after copying the `ca.pem` file into the certs directory. Detailed instructions on how to generate SSL certificates can be found either in the [documentation repository](https://gitlab.com/opalmedapps/docs/-/blob/main/docs/guides/self_signed_certificates.md) or in the [db-docker README](https://gitlab.com/opalmedapps/db-docker).

### Step 7

Visit opalAdmin's database version control page in your web browser at:

http://localhost:8091/dbv

Username: dbv -- Password: dbv

### Step 8

On the DBV page, run all revisions by selecting all revisions and clicking *Run selected revisions*

### Step 9

Visit the opalAdmin site:

http://<your host IP>/

Username: admin -- Password: 123456

## Configuring the clinical databases

This project comes with pre-defined [ARIA](https://www.varian.com/oncology/products/software/information-systems/aria-ois-radiation-oncology) queries and WRM queries to get various oncology data.  
The following steps will allow a connection between the Opal database and the clinical database. Note that you must be able to access these clinical databases using some sort of basic authentication. 

### Step 1 

Open the **config.json** file using your favourite editor and replace the default ARIA or WRM credentials. Set the **enabled** flag to **1** for databases that will be used. 

### Step 2

Refresh opalAdmin on the browser.

### Step 3

You should be able to access data. Visit Tasks/Appts/Docs page and click on the "+ Add" button on the top. You should be able to see the enabled clinical databases listed in "Source Database" section as well as a generated list of clinical codes after selecting a type.  

## Editing Modules

This project comes with pre-configured ARIA database queries to fetch the necessary clinical information. MosaiQ, however, is not set up. Thus, there are several manual configurations involved to fully set up another clinical database other than ARIA. 

## Troubleshooting Installation Errors

 * First verify the integrity of your databases. It is a common issue with importing MySQL databases that capital letters get converted to lowercase. Table names in OpalDB and QuestionnaireDB should usually be capitalized. To fix this issue, drop your current copies of opaldb and questionnairedb, then go into your MySQL my.ini file (accesible through the XAMPP control panel if you can't find it). Scroll down to the [mysqld] code block and add 
```
lower_case_table_names = 2
```
then re-import your databases and verify that they are correctly capitalized. Database names and Table names should be Capitalized. Refresh your server and try again.

* Check what branch you are on. When you first clone the repo, you will be in master by default. If so...

```
git fetch

git pull

git checkout staging
```

* Check what ports your server is listening on, and verify the ports you want to use are free using the Netstat tool.

* Connect to your localhost using https, even if your web browser throws exceptions. There are security settings within opalAdmin that will require you to attempt a connection using https. Connect to https://localhost/ instead of http://localhost/. Your browser might throw a security exception. Just click advanced > proceed anyway.

* If you are getting a 401 error from opalAdmin, then your database does not have the standard admin / 123456 login credentials. Try '1234' as the password, or ask a member of opal for help. They might try sending you their copy of OpalDB and let you use their login credentials for now.

## Built With

* [Angular](https://angularjs.org) - The JS web framework used
* [Bootstrap](http://getbootstrap.com) - CSS
* [PHP](http://php.net)
* [Perl](http://perldoc.perl.org)
* [JavaScript](https://www.javascript.com)
