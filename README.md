# opalAdmin

OpalAdmin is the administrative tool for managing and tagging personal health information that is published to Opal. 

## Prerequisites

For opalAdmin to work, a Linux-based operating system with a local web server, MySQL, PHP (> 5.3, < 7), and perl are required.

## Basic Installation

These instructions will get you a copy of the project up and running on your local machine. 

### Step 1 

On your server, navigate into your web server directory (i.e. the "localhost" directory that is accessible via an internet browser). 

### Step 2

Clone this project from Gitlab

```
git clone https://gitlab.com/akimosupremo/opalAdmin.git
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

Setup the configuration file by running the executable bash script located in the project's root:

```
bash ./makeconfigs.sh
```

This will copy **./default-config.json** to **./config.json** and set preliminary configurations.

### Step 5

Create an empty Opal database using your favourite tool. **Note:** Keep track of the database name.

### Step 6

Open the **config.json** file using your favourite editor and replace the default Opal credentials with your local credentials.

### Step 7

Visit opalAdmin's database version control page in your web browser at:

http://yourdomain/opalAdmin/dbv

Username: dbv -- Password: dbv

### Step 8

On the DBV page, run all revisions by selecting all revisions and clicking *Run selected revisions*

### Step 9

Visit the opalAdmin site:

http://youdomain/opalAdmin/

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


## Built With

* [Angular](https://angularjs.org) - The JS web framework used
* [Bootstrap](http://getbootstrap.com) - CSS
* [PHP](http://php.net)
* [Perl](http://perldoc.perl.org) 
* [JavaScript](https://www.javascript.com) 
