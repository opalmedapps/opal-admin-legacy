# opalAdmin

OpalAdmin is the administrative tool for managing and tagging personal health information that is published to Opal. 

## Getting Started

These instructions will get you a copy of the project up and running on your local machine. 

### Prerequisites

For opalAdmin to work, a Linux-based operating system with a local web server, MySQL, PHP (> 5.3, < 7), and perl are required.

### Installing

On your server, navigate into your web server directory (i.e. the "localhost" directory that is accessible via an internet browser). 

Clone this project from Gitlab

```
git clone https://gitlab.com/akimosupremo/opalAdmin-dev.git
```

### 3rd-Party Libraries

Installing 3rd-party libraries require both [NodeJS](https://nodejs.org/en/download/) and [Bower](https://bower.io/#install-bower) to be installed on your server. To install the 3rd-party libraries, navigate to the project directory and execute:

```
bower install 
```

and

```
npm install
```

## Managing Configuration Files

In order for opalAdmin to work, you must create a copy of the existing default configuration files. 
This project consists of a JavaScript, a Perl, and a PHP default configuration file located in:

* js/default-config.js
* php/default-config.php
* publisher/modules/default-Configs.pm

To create a copy of these default files, run the executable bash script located in the project's root:


```
bash ./makeconfigs.sh
```
**NOTE:** opalAdmin **will not work** if copies of the configuration files do not exist.

## Configuring the opalAdmin installation

Navigate to the URL of your opalAdmin site and run the install page (for example http://yourdomain/main.html/#/install, where **_yourdomain_** is the path of your opalAdmin site). Follow the instructions on the page.

### Step 1 : Setting up the Opal database

Complete the form and press "Test Connection" to set up an Opal database. **Note:** This must be a MySQL database. 

### Step 2 : Choose the clinical database(s)

This project comes with pre-defined ARIA queries. Other clinical database queries must be inputted according to your database information. Fill out the appropriate clinical database forms and press "Test Connection" to pass to the next step. 

### Step 3 : Submit credentials

Once the Opal database and clinical database(s) have been configured, press "Submit Configurations" to install the given credentials into the configuration files. This process takes some time. 

### Step 4 : Add a site administrator

Fill out the form to add a site administrator.

### Step 5 : Visit the site

Once everything is complete, click "Visit Site" on the left-hand site to log in to the opalAdmin site.

## Editing Modules

This project comes with pre-configured ARIA database queries to fetch the necessary clinical information. MosaiQ, however, is not set up. Thus, there are several manual configurations involved to fully set up another clinical database other than ARIA. 


## Built With

* [Angular](https://angularjs.org) - The JS web framework used
* [Bootstrap](http://getbootstrap.com) - CSS
* [PHP](http://php.net)
* [Perl](http://perldoc.perl.org) 
* [JavaScript](https://www.javascript.com) 
