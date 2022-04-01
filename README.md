[![Version](https://img.shields.io/badge/Symcon-PHP--Modul-red.svg?style=flat-square)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Version](https://img.shields.io/badge/Symcon%20Version-6.0%20%3E-brightgreen.svg?style=flat-square)](https://www.symcon.de/produkt/)

# IP-Symcon Logger
This is a PHP plugin for the Smart-Home Application [IP-Symcon](https://www.symcon.de). With this extension you are able
to have a user-friendly and html readable log plus other comfort features to get the best overview about your running
system.

_[German](https://github.com/parcivad/ips-logger/README_German.md)_
```
```
## Table of Contents

- [Installation](#installation)
- [Setup](#setup)
- [Functions](#functions)
- [Webfront](#webfront)
- [Donate](#donate)

## Installation
You can install the Logger via this github repository:
<p align="center">
  <img width="auto" height="auto" src="https://github.com/parcivad/ips-logger/blob/main/imgs/install.png?raw=true">
</p>

## Setup
When you installed the module you just have to create a `Logger` instance. Then your able to configure Logger Levels, Notifications, Table Style and other options.
<p align="center">
  <img width="auto" height="auto" src="https://github.com/parcivad/ips-logger/blob/main/imgs/instance.png?raw=true">
</p>

### Levels
Logger Levels define the different states that makes you easy to get a better overview. You can display them with different colors to highlight them in your eyes or define if they should get notified when called, so you never miss any important information from your home.

## Functions
At the moment there is one single function that keeps it simple for you.

### logger_entry
Create a entry with defined Level
```php
logger_entry($instanceID, "Level", "Message", "Sender");
```
_it's that simple_, you created a log entry. If it gets notified or highlighted depends on the Level you definied with `"Level"`. If the level is not existing it will throw a `UnexpectedValueException`, but that depends on your configuration of the instance. 

## Webfront
Here is a image of the default Log, every Color/Size is customizable to your needs.
<p align="center">
  <img width="auto" height="auto" src="https://github.com/parcivad/ips-logger/blob/main/imgs/webfront.png?raw=true">
</p>

# Donate
~_at the moment not available_
