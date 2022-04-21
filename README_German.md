<img width="auto" height="auto" src="https://github.com/parcivad/ips-logger/blob/main/imgs/logger.png?raw=true">

[![Version](https://img.shields.io/badge/Symcon-PHP--Modul-red.svg?style=flat-square)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Version](https://img.shields.io/badge/Symcon%20Version-6.0%20%3E-brightgreen.svg?style=flat-square)](https://www.symcon.de/produkt/)

# IP-Symcon Logger
IP-Symcon Logger ist ein PHP plugin für die Smart-Home Anwendung [IP-Symcon](https://www.symcon.de). Mit dieser Erweiterung lassen sich Benachrichtigungen und Vorgänge im eigenen Symcon System besser überwachen und überprüfen. Einträge werden nicht einfach nur als Log verzeichnet, sondern sollen viel mehr den Nutzer ansprechen, als das eher technische Symcon Log.

## Table of Contents

- [Installation](#installation)
- [Setup](#setup)
- [Functions](#functions)
- [Webfront](#webfront)
- [Donate](#donate)

## Installation
Das Modul kann direkt über diese Github Repository installiert.
<p align="left">
  <img width="auto" height="auto" src="https://github.com/parcivad/ips-logger/blob/main/imgs/install.png?raw=true">
</p>

## Setup
Wenn das Modul installiert ist, musst du einfach nur eine `Logger` instance erstellen. Danach lassen sich über die Instanz Mitteilungen, Farben, Höhen und Level/Gruppen von dem Logger ändern.
<p align="center">
  <img width="auto" height="auto" src="https://github.com/parcivad/ips-logger/blob/main/imgs/instance.png?raw=true">
</p>

### Design & Style
In den Design und Style Einstellungen kannst du Hintergrundfarben ändern, sowie die Höhe und Breite einzelner Elemente der Tabelle.

### Notification Settings
Hier kannst du deine Visuellen Instanzen hinzufügen die Benachrichtigt werden sollen, sofern die Gruppe dies erlaubt. Auch SMTP Mail Instanzen lassen sich hinzufügen, dafür muss auch die mail Benachrichtigung in der jeweiligen Gruppe aktiviert sein.

Darunter findet sich die Formatierung der Benachrichtigung. Diese kann beliebig geändert werden, dabei werden die Werte `{sender}, {msg} und {level}` immer durch die Informationen aus dem Eintrag getauscht.

## Level & Gruppen
Die Level stellen die üblichen Log Einheiten dar, du kannst jenachdem die Farbe ändern. Wichtiger sind die Gruppen, hier kannst du neue hinzufügen und damit nicht nur deine Log Einträge ordnen, sondern auch die Mitteilungen einstellen.

## Functions
Funktionen vom Modul

### logger_entry()
Erstellen einen neuen Log eintrag mit festgeleter Gruppe und Level
```php
logger_entry($instanceID, "level", "group", "message", "sender");
```
_so einfach_, kannst du Einträge erstellen. Wenn du willst das dieser Eintrag dir per Nachricht zugestellt wird kannst du dies in der jeweiligen Gruppe genauer einstellen.

### getLog()
Der Getter Log gibt dir ein Array mit allen gerade gespeicherten Log Einträgen zurück
```php
logger_getLog($instanceID);
```
aubau vom Array:
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
So sieht das Log dann im Webfront aus, die Farben, sowie Größen sind über die Instanz frei einstellbar.
<p align="center">
  <img width="auto" height="auto" src="https://github.com/parcivad/ips-logger/blob/main/imgs/webfront.png?raw=true">
</p>

# Donate
~_gerade nicht verfügbar_
