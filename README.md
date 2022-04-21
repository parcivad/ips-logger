<img width="auto" height="auto" src="https://github.com/parcivad/ips-logger/blob/main/imgs/logger.png?raw=true">

[![Version](https://img.shields.io/badge/Symcon-PHP--Modul-red.svg?style=flat-square)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Version](https://img.shields.io/badge/Symcon%20Version-6.0%20%3E-brightgreen.svg?style=flat-square)](https://www.symcon.de/produkt/)

# IP-Symcon Logger
This PHP plugin is for the Smart-Home Application [IP-Symcon](https://www.symcon.de). With this extension, you can have a user-friendly and Html readable log plus other comfort features like push notification and mail management. All this with the main goal to get the best overview of your running system.

_[German Documentation](https://github.com/parcivad/ips-logger/blob/main/README_German.md)_

## Table of Contents

- [Installation](#installation)
- [Setup](#setup)
- [Functions](#functions)
- [Webfront](#webfront)
- [Donate](#donate)

## Installation
You can install the Logger via this github repository:
<p align="left">
  <img width="auto" height="auto" src="https://github.com/parcivad/ips-logger/blob/main/imgs/install.png?raw=true">
</p>

## Setup
When you installed the module you just have to create a `Logger` device instance. Then your able to configure Logger Levels, Groups, Notifications Settings, Table Style and other options.
<p align="center">
  <img width="auto" height="auto" src="https://github.com/parcivad/ips-logger/blob/main/imgs/instance.png?raw=true">
</p>

### Design & Style
In the Design and Style panel, you can adjust the used `CSS` to format and color the log table. You can change colors and widths or heights.

### Notification Settings
Here you can add all your Visual Instances that should get notified if the group allows `visual` notifications. SMTP Mail Instances are also possible for E-Mail notifications (group needs to allow `mail` notifications).

Below the instance selection, there are fields to change the message format. 

## Levels & Groups
The Levels represent the typical priority of an entry, you can change the color or font type. More important to you are the Groups, groups define the way of representation of the entry. Should it be sent as a notification or only marked as an entry? For more detail, you can change the icon, target instance, and sound of the notification (sound only on mobile!).

## Functions
At the moment there is one single function that keeps it simple for you. Note that `{sender}, {msg} and {level}` will get replaced with the content of the log entry, but their no needed conditions in the formating.

### logger_entry()
Create a entry with defined Level and Group
```php
logger_entry($instanceID, "level", "group", "message", "sender");
```
_it's that simple_, you created a log entry. If you want this log entry to get notified just change the settings of your group (log levels don't affect any notification settings)

### getLog()
The Getter Log will give you an array with all current saved log entries
```php
logger_getLog($instanceID);
```
the array looks like this
```array
{  
  {
    "time" => "0"
    "level" => "Info"
    "group" => "default"
    "message" => "This is the beginning of your own log"
    "sender" => "Parcivad"
  },
  ...
}
```

## Webfront
Here is a image of the default Log, every Color/Size is customizable to your needs.
<p align="center">
  <img width="auto" height="auto" src="https://github.com/parcivad/ips-logger/blob/main/imgs/webfront.png?raw=true">
</p>

# Donate
~_at the moment not available_
