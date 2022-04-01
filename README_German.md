[![Version](https://img.shields.io/badge/Symcon-PHP--Modul-red.svg?style=flat-square)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Version](https://img.shields.io/badge/Symcon%20Version-6.0%20%3E-brightgreen.svg?style=flat-square)](https://www.symcon.de/produkt/)

# IP-Symcon Logger
Das ist ein PHP plugin für die Smart-Home Anwendung [IP-Symcon](https://www.symcon.de). Mit dieser Erweiterung können sie 
über eine nutzerfreundliche Konfiguration und Html Variable den Überblick über ihr laufendes System behalten.

```
```
## Table of Contents

- [Installation](#installation)
- [Setup](#setup)
- [Functions](#functions)
- [Webfront](#webfront)
- [Donate](#donate)

## Installation
Das Modul wird über diese Github Repository installiert.
<p align="center">
  <img width="auto" height="auto" src="https://github.com/parcivad/ips-logger/blob/main/img/install.png?raw=true">
</p>

## Setup
Wenn das Modul dann installiert ist, müssen sie einfach nur eine `Logger` instance erstellen. Danach können sie über die Instanz Mitteilungen, Farben, Höhen und Level von dem Logger ändern.
<p align="center">
  <img width="auto" height="auto" src="https://github.com/parcivad/ips-logger/blob/main/img/instance.png?raw=true">
</p>

### Levels
Logger Level sind wie eine eigene Klasse/Bereich in den sie einschreiben können. Damit können sie den Überblick behalten
und schnell Einstellungen für mehrere Logeinträge ändern.

## Functions
Gerade gibt es eine Funktion, was es für sie einfach hält.

### logger_entry
Logeintrag erstellen mit definiertem Level.
```php
logger_entry($instanceID, "Level", "Message", "Sender");
```
_so einfach is es_, ob der Eintrag benachrichtigt wird hängt von der Einstellung des `"Levels"` ab!

## Webfront
So sieht das Log dann im Webfront aus, die Farben, sowie Größen sind über die Instanz frei einstellbar.
<p align="center">
  <img width="auto" height="auto" src="https://github.com/parcivad/ips-logger/blob/main/img/webfront.png?raw=true">
</p>

# Donate
~_gerade nicht verfügbar_
