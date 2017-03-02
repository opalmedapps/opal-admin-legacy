# opalAdmin

OpalAdmin is the administrative tool for managing and tagging personal health information that is published to Opal. 

## Getting Started

These instructions will get you a copy of the project up and running on your local machine. 

### Prerequisites

For opalAdmin to work, a Linux-based operating system with MySQL, PHP (> 5.3, < 7), and perl are required. 

### Installing

On your Linux server, navigate into a project directory of your choosing within your www-Apache directory (i.e. a directory that is accessible via an internet browser). 

Clone this project from Gitlab

```
git clone https://gitlab.com/akimosupremo/opalAdmin.git
```

## Managing Configuration Files

In order for opalAdmin to work, you must create a copy of the existing configuration files. 
This project consists of a JavaScript, a Perl, and a PHP configuration file located in:

* js/default-config.js
* php/default-config.php
* modules/default-Configs.pm

Before manipulating the configuration files, copy each config file as a new file by removing the *default-* prefix.

```
cp php/default-config php/config.php
```
**Note:** Do not simply rename the file

## 

## Editing Modules



Explain how to run the automated tests for this system

### Break down into end to end tests

Explain what these tests test and why

```
Give an example
```

### And coding style tests

Explain what these tests test and why

```
Give an example
```

## Deployment

Add additional notes about how to deploy this on a live system

## Built With

* [Dropwizard](http://www.dropwizard.io/1.0.2/docs/) - The web framework used
* [Maven](https://maven.apache.org/) - Dependency Management
* [ROME](https://rometools.github.io/rome/) - Used to generate RSS Feeds

## Contributing

Please read [CONTRIBUTING.md](https://gist.github.com/PurpleBooth/b24679402957c63ec426) for details on our code of conduct, and the process for submitting pull requests to us.

## Versioning

We use [SemVer](http://semver.org/) for versioning. For the versions available, see the [tags on this repository](https://github.com/your/project/tags). 

## Authors

* **Billie Thompson** - *Initial work* - [PurpleBooth](https://github.com/PurpleBooth)

See also the list of [contributors](https://github.com/your/project/contributors) who participated in this project.

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details

## Acknowledgments

* Hat tip to anyone who's code was used
* Inspiration
* etc




## Code Example

Show what the library does as concisely as possible, developers should be able to figure out **how** your project solves their problem by looking at the code example. Make sure the API you are showing off is obvious, and that your code is short and concise.

## Motivation

A short description of the motivation behind the creation and maintenance of the project. This should explain **why** the project exists.

## Installation

Provide code examples and explanations of how to get the project.

## API Reference

Depending on the size of the project, if it is small and simple enough the reference docs can be added to the README. For medium size to larger projects it is important to at least provide a link to where the API reference docs live.

## Tests

Describe and show how to run the tests with code examples.

## Contributors

Let people know how they can dive into the project, include important links to things like issue trackers, irc, twitter accounts if applicable.

## License

A short snippet describing the license (MIT, Apache, etc.)