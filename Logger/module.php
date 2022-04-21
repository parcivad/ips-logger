<?php

class Logger extends IPSModule {

    /*
    * Internal function of SDK
    */
    public function Create() {
        // declare parent
        parent::Create();

        // inner json saves
        $this->RegisterAttributeString("htmlView", '<head><meta name="viewport" content="width=device-width, initial-scale=1"><style>%s</style></head><body><table class="styled-table"><colgroup><col class="colLevel"><col class="colPriority"><col class="colMessage"><col class="colSender"><col class="colTime"></colgroup><thead><tr><th>Level</th><th>Group</th><th>Message</th><th>Sender</th><th>Time</th></tr></thead><tbody>%s</tbody></table></body>');
        $this->RegisterAttributeString("cssView", '.styled-table {position: relative;display: block;background-color: #%s;border-collapse: collapse;width: %d;height: %dpx;overflow-y: auto;overflow-x: hidden;font-size: 0.9em;font-family: sans-serif;box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);}.styled-table thead tr {background-color: #%s;color: #fff;text-align: left;padding: 7px 15px;}.styled-table th,.styled-table td {padding: %dpx 15px;}.styled-table td {color: #ffffff;}.styled-table tbody tr {border-bottom: 1px solid #%s;}.styled-table tbody tr:last-of-type {border-bottom: 2px solid #%s;}.styled-table tbody tr.active-row {font-weight: bold;color: #818181;}.colLevel {width: 30em;}.colPriority {width: 120em;}.colMessage {width: 1100em;}.colSender {width: 125em;}.colTime {width: 150em;}');
        $this->RegisterAttributeString("log", '[{"time": "0","level": "Info", "group": "default", "message": "This is the beginning of your own log","sender": "Parcivad"}]');
        $this->RegisterPropertyString("logLevels", '[{"name":"Ok","color":2412282367,"font-weight":"normal"},{"name":"Info","color":3685444,"font-weight":"normal"},{"name":"Discovery","color":5482187,"font-weight":"normal"},{"name":"Warning","color":14924843,"font-weight":"bold"},{"name":"Error","color":14896427,"font-weight":"bold"}]');
        $this->RegisterPropertyString("logGroups", '[{"name":"default","mailNotify":false, "visualNotify":false, "displayTime": 0, "sound":"normal", "icon": "Database", "targetInstance": 0}]');

        // user interface settings
        // general settings
        $this->RegisterPropertyInteger("maximumLogRecords", 150);
        $this->RegisterPropertyInteger("entryHeight", 8);
        $this->RegisterPropertyInteger("tableHeight", 500);
        // notifications settings
        $this->RegisterPropertyString("notificationList", "{}");
        $this->RegisterPropertyString("notificationFormat", "Von {sender}: {msg} ({level})");
        $this->RegisterPropertyString("notificationFormatEmail", "Von {sender}: {msg} ({level})");
        // style settings
        $this->RegisterPropertyInteger("backgroundColor", 3685444);
        $this->RegisterPropertyInteger("tableTopColor", 5070960);
        $this->RegisterPropertyInteger("lineColor", 14540253);
        $this->RegisterPropertyInteger("bottomColor", 8487297);

        // vars
        $this->RegisterVariableString("view", "Log View", "~HTMLBox");
    }

    /*
     * Internal function of SDK
     */
    public function ApplyChanges() {
        // declare parent
        parent::ApplyChanges();
        $this->reloadHTMLView();
    }

    /**
     * Function to add an entry into the ongoing log
     * @param string $level     Level of the log message
     * @param string $group     Group of the log message
     * @param string $message   Message itself
     * @param string $sender    String of displayed sender
     * @return void
     * @throws UnexpectedValueException
     */
    public function entry(string $level, string $group, string $message, string $sender) {
        // add into saved json
        $log = $this->readJson("log");
        // check if level exists
        if ($this->getLevel($level) == null) throw new UnexpectedValueException("level does not exist");
        if ($this->getGroup($group) == null) throw new UnexpectedValueException("group does not exist");
        // format new entry
        $log[] = [
            "time" => time(),
            "level" => $level,
            "group" => $group,
            "message" => $message,
            "sender" => $sender
        ];
        // save new entry
        $this->saveJson("log", $log);
        // push information to notification function
        $this->notification($group, $sender, $message, $level);
    }

    /**
     * Function that returns the current log data
     * @return array Log
     */
    public function getLog(): array {
        return $this->readJson("log");
    }

    /**
     * Function to resort and refresh HTML View of the Variable view
     * @return void
     */
    private function reloadHTMLView() {
        // load log in
        $log = $this->readJson("log");
        // buffer in table body var
        $tbody = '';
        for ($i=count($log) - 1; $i >= 0 ; $i--) {
            // get log item
            $item = $log[$i];
            $itemLevel = $this->getLevel($item['level']);
            // form html table body element with given log info
            $tbody .= sprintf('<tr style="background-color:%s"><td style="font-weight:%s;">%s</td>
                <td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
                '#'.dechex($itemLevel['color']), $itemLevel['font-weight'], $item['level'], $item['group'],
                $item['message'], $item['sender'], date("H:i:s d.m.y", $item['time']));
        }
        // form css
        $css = sprintf($this->ReadAttributeString("cssView"),
            dechex($this->ReadPropertyInteger("backgroundColor")),
            100,
            $this->ReadPropertyInteger("tableHeight"),
            dechex($this->ReadPropertyInteger("tableTopColor")),
            $this->ReadPropertyInteger("entryHeight"),
            dechex($this->ReadPropertyInteger("lineColor")),
            dechex($this->ReadPropertyInteger("bottomColor")));
        // set visible value
        $this->SetValue("view", sprintf($this->ReadAttributeString("htmlView"), $css, $tbody));
    }

    /**
     * Function to send a notification on log entry ( called by entry() )
     * @param string $group     Initial Group of the Log entry
     * @param string $sender    Level of the message
     * @param string $message   Core Message
     * @param string $level     Initial Sender
     * @return void
     */
    private function notification(string $group, string $sender, string $message, string $level) {
        $notifyReceivers = json_decode($this->ReadPropertyString("notificationList"), true);
        // format string
        $keys = array("{sender}", "{msg}", "{level}");
        $values = array($sender, $message, $level);
        $msgString = str_replace($keys, $values, $this->ReadPropertyString("notificationFormat"));
        $msgEmailString = str_replace($keys, $values, $this->ReadPropertyString("notificationFormatEmail"));
        // send to each pinned instance
        foreach ($notifyReceivers as $receiver) {
            // try to send notification
            try {
                // difference between SMTP and Visual instance
                if (IPS_GetInstance($receiver['instance'])["ModuleInfo"]["ModuleID"]
                    == "{375EAF21-35EF-4BC4-83B3-C780FD8BD88A}") {
                    // check if mail notifications are allowed from that group
                    if (!$this->getGroup($group)["mailNotify"]) return;
                    // SMTP email
                    SMTP_SendMail($receiver['instance'], "IP-Symcon: " . $group, $msgEmailString);
                } else {
                    // check if visual notifications are allowed from that group
                    if (!$this->getGroup($group)["visualNotify"]) return;
                    // Visual Notify
                    WFC_PushNotification($receiver['instance'], $group, $msgString,
                        $this->getGroup($group)["sound"], $this->getGroup($group)["targetInstance"]);

                    WFC_SendNotification($receiver['instance'], $group, $msgString,
                        $this->getGroup($group)["icon"], 300);
                }
            } catch (Exception $ex) {
                IPS_LogMessage("Logger", "Popup-/Pushnotification could not be sent");
            }
        }
    }

    /**
     * Function to search for a level in the logger level list
     * @param string $levelName Name of the logger level
     * @return mixed|null
     */
    private function getLevel(string $levelName) {
        $itemLevel = null;
        // search for level
        $levelList = json_decode($this->ReadPropertyString("logLevels"), true);
        foreach ($levelList as $level) {
            if ($level['name'] == $levelName) {
                $itemLevel = $level;
                break;
            }
        }
        return $itemLevel;
    }

    /**
     * Function to search for a group in the logger group list
     * @param string $groupName Name of the logger group
     * @return mixed|null
     */
    private function getGroup(string $groupName) {
        $itemGroup = null;
        // search for level
        $groupList = json_decode($this->ReadPropertyString("logGroups"), true);
        foreach ($groupList as $group) {
            if ($group['name'] == $groupName) {
                $itemGroup = $group;
                break;
            }
        }
        return $itemGroup;
    }

    /**
     * Function to read Json Attribute
     * @param string $save
     * @return mixed
     */
    private function readJson(string $save) {
        return json_decode($this->ReadAttributeString($save), true);
    }

    /**
     * Function to write Attribute with json
     * @param string $save
     * @param array $jsonArray
     * @return void
     */
    private function saveJson(string $save, array $jsonArray) {
        // check if log is too long
        // short level on given max
        $log = $this->readJson("log");
        if (count($log) > $this->ReadPropertyInteger("maximumLogRecords")) {
            $this->saveJson("log",
                array_slice($log, count($log) - $this->ReadPropertyInteger("maximumLogRecords")));
            // won't get looped, because its shorted and then the condition is false
            $this->saveJson("log", $log);
        }
        // save json
        $this->WriteAttributeString($save, json_encode($jsonArray));
        // after save, reload html view for live feed
        $this->reloadHTMLView();
    }

    //================ Configuration Formula

    /**
     * Return Configuration on request
     * @return false|string
     */
    public function GetConfigurationForm() {
        // return current form
        $Form = json_encode([
            'elements' => $this->FormElements(),
            'actions'  => $this->FormActions(),
            'status'   => $this->FormStatus(),
        ]);
        // for debug reason
        $this->SendDebug('FORM', $Form, 0);
        $this->SendDebug('FORM', json_last_error_msg(), 0);

        return $Form;
    }

    /**
     * @return array[] Form Actions
     */
    protected function FormActions() {
        return[];
    }

    /**
     * @return array[] Form Elements
     */
    protected function FormElements() {
        return[
            [
                "type" => "Image",
                "image" => "data:image/png;base64, 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"
            ],
            [
                "type" => "ExpansionPanel",
                "caption" => "Design & Style",
                "items" => [
                    [
                        "type" => "RowLayout",
                        "items" => [
                            [
                                "type" => "NumberSpinner",
                                "name" => "maximumLogRecords",
                                "caption" => "Maximum of recorded Log entries",
                                "width" => "250px",
                                "minimum" => 10,
                                "maximum" => 9999
                            ],
                            [
                                "type" => "NumberSpinner",
                                "name" => "entryHeight",
                                "caption" => "Height of entries",
                                "width" => "200px",
                                "minimum" => 2,
                                "suffix" => " px"
                            ],
                            [
                                "type" => "NumberSpinner",
                                "name" => "tableHeight",
                                "caption" => "Height of the table",
                                "width" => "200px",
                                "minimum" => 100,
                                "suffix" => " px"
                            ]
                        ]
                    ],
                    [
                        "type" => "RowLayout",
                        "items" => [
                            [
                                "type" => "SelectColor",
                                "name" => "backgroundColor",
                                "caption" => "Background color"
                            ],
                            [
                                "type" => "SelectColor",
                                "name" => "tableTopColor",
                                "caption" => "Headline color"
                            ],
                            [
                                "type" => "SelectColor",
                                "name" => "lineColor",
                                "caption" => "Line color"
                            ],
                            [
                                "type" => "SelectColor",
                                "name" => "bottomColor",
                                "caption" => "Bottom color"
                            ]
                        ]
                    ],
                ]
            ],
            [
                "type" => "ExpansionPanel",
                "caption" => "Notification Settings",
                "items" => [
                    [
                        "type" => "List",
                        "name" => "notificationList",
                        "caption" => "Select all visual Instances or SMTP Instances that should get a possible notification",
                        "rowCount" => count(json_decode($this->ReadPropertyString("notificationList"), true)),
                        "add" => true,
                        "delete" => true,
                        "columns" => [
                            [
                                "caption" => "Visual Instances or SMTP Mail Instance",
                                "name" => "instance",
                                "width" => "auto",
                                "add" => 0,
                                "edit" => [
                                    "type" => "SelectInstance"
                                ]
                            ]
                        ],
                        "values" => json_decode($this->ReadPropertyString("notificationList"), true),
                    ],
                    [
                        "type" => "RowLayout",
                        "items" => [
                            [
                                "type" => "ValidationTextBox",
                                "name" => "notificationFormat",
                                "caption" => "Notification Format",
                                "width" => "300px"
                            ],
                            [
                                "type" => "ValidationTextBox",
                                "name" => "notificationFormatEmail",
                                "caption" => "Email Format",
                                "width" => "300px"
                            ],
                            [
                                "type" => "Label",
                                "caption" => "Titel of the notification will be the group name (Group is needed to activate notifications)"
                            ]
                        ]
                    ]
                ]
            ],
            [
                "type" => "ExpansionPanel",
                "caption" => "Levels & Groups",
                "expanded" => true,
                "items" => [
                    [
                        "type" => "List",
                        "name" => "logLevels",
                        "caption" => "Log Levels",
                        "rowCount" => count(json_decode($this->ReadPropertyString("logLevels"), true)),
                        "add" => false,
                        "delete" => false,
                        "edit" => true,
                        "columns" => [
                            [
                                "caption" => "Name",
                                "name" => "name",
                                "width" => "180px",
                                "edit" => [
                                    "type" => "ValidationTextBox",
                                    "visible" => false
                                ],
                            ],
                            [
                                "caption" => "Color",
                                "name" => "color",
                                "width" => "auto",
                                "edit" => [
                                    "type" => "SelectColor"
                                ]
                            ],
                            [
                                "caption" => "Font weight",
                                "name" => "font-weight",
                                "width" => "100px",
                                "edit" => [
                                    "type" => "Select",
                                    "options" => [
                                        [
                                            "caption" => "Normal",
                                            "value" => "normal"
                                        ],
                                        [
                                            "caption" => "Bold",
                                            "value" => "bold"
                                        ],
                                        [
                                            "caption" => "Light",
                                            "value" => "lighter"
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        "values" => json_decode($this->ReadPropertyString("logLevels"), true),
                    ],
                    [
                        "type" => "List",
                        "name" => "logGroups",
                        "caption" => "Log Groups",
                        "rowCount" => count(json_decode($this->ReadPropertyString("logGroups"), true)),
                        "add" => true,
                        "delete" => true,
                        "edit" => true,
                        "columns" => [
                            [
                                "caption" => "Name",
                                "name" => "name",
                                "width" => "180px",
                                "add" => "MyGroup",
                                "edit" => [
                                    "type" => "ValidationTextBox"
                                ]
                            ],
                            [
                                "caption" => "Mail Notification",
                                "name" => "mailNotify",
                                "width" => "180px",
                                "add" => false,
                                "edit" => [
                                    "type" => "CheckBox"
                                ]
                            ],
                            [
                                "caption" => "Visual Notification",
                                "name" => "visualNotify",
                                "width" => "180px",
                                "add" => false,
                                "edit" => [
                                    "type" => "CheckBox"
                                ]
                            ],
                            [
                                "caption" => "Display Time",
                                "name" => "displayTime",
                                "width" => "180px",
                                "add" => 0,
                                "edit" => [
                                    "type" => "NumberSpinner",
                                    "suffix" => "sec.",
                                    "minimum" => 0
                                ]
                            ],
                            [
                                "caption" => "Sound",
                                "name" => "sound",
                                "width" => "180px",
                                "add" => "",
                                "edit" => [
                                    "type" => "Select",
                                    "options" => [
                                        [ "caption" => "Normal", "value" => "" ],
                                        [ "caption" => "alarm", "value" => "alarm" ],
                                        [ "caption" => "bell", "value" => "bell" ],
                                        [ "caption" => "boom", "value" => "boom" ],
                                        [ "caption" => "buzzer", "value" => "buzzer" ],
                                        [ "caption" => "connected", "value" => "connected" ],
                                        [ "caption" => "dark", "value" => "dark" ],
                                        [ "caption" => "digital", "value" => "digital" ],
                                        [ "caption" => "drums", "value" => "drums" ],
                                        [ "caption" => "duck", "value" => "duck" ],
                                        [ "caption" => "full", "value" => "full" ],
                                        [ "caption" => "happy", "value" => "happy" ],
                                        [ "caption" => "horn", "value" => "horn" ],
                                        [ "caption" => "inception", "value" => "inception" ],
                                        [ "caption" => "kazoo", "value" => "kazoo" ],
                                        [ "caption" => "roll", "value" => "roll" ],
                                        [ "caption" => "siren", "value" => "siren" ],
                                        [ "caption" => "space", "value" => "space" ],
                                        [ "caption" => "trickling", "value" => "trickling" ],
                                        [ "caption" => "turn", "value" => "turn" ],
                                    ],
                                ]
                            ],
                            [
                                "caption" => "Icon",
                                "name" => "icon",
                                "width" => "180px",
                                "add" => "",
                                "edit" => [
                                    "type" => "Select",
                                    "options" => [
                                        [ "caption" => "Aircraft", "value" => "Aircraft" ],
                                        [ "caption" => "Alert", "value" => "Alert" ],
                                        [ "caption" => "ArrowRight", "value" => "ArrowRight" ],
                                        [ "caption" => "Backspace", "value" => "Backspace" ],
                                        [ "caption" => "Basement", "value" => "Basement" ],
                                        [ "caption" => "Bath", "value" => "Bath" ],
                                        [ "caption" => "Battery", "value" => "Battery" ],
                                        [ "caption" => "Bed", "value" => "Bed" ],
                                        [ "caption" => "Bike", "value" => "Bike" ],
                                        [ "caption" => "Book", "value" => "Book" ],
                                        [ "caption" => "Bulb", "value" => "Bulb" ],
                                        [ "caption" => "Calendar", "value" => "Calendar" ],
                                        [ "caption" => "Camera", "value" => "Camera" ],
                                        [ "caption" => "Car", "value" => "Car" ],
                                        [ "caption" => "Caret", "value" => "Caret" ],
                                        [ "caption" => "Cat", "value" => "Cat" ],
                                        [ "caption" => "Climate", "value" => "Climate" ],
                                        [ "caption" => "Clock", "value" => "Clock" ],
                                        [ "caption" => "Close", "value" => "Close" ],
                                        [ "caption" => "CloseAll", "value" => "CloseAll" ],
                                        [ "caption" => "Cloud", "value" => "Cloud" ],
                                        [ "caption" => "Cloudy", "value" => "Cloudy" ],
                                        [ "caption" => "Cocktail", "value" => "Cocktail" ],
                                        [ "caption" => "Cross", "value" => "Cross" ],
                                        [ "caption" => "Database", "value" => "Database" ],
                                        [ "caption" => "Dining", "value" => "Dining" ],
                                        [ "caption" => "Distance", "value" => "Distance" ],
                                        [ "caption" => "DoctorBag", "value" => "DoctorBag" ],
                                        [ "caption" => "Dog", "value" => "Dog" ],
                                        [ "caption" => "Dollar", "value" => "Dollar" ],
                                        [ "caption" => "Door", "value" => "Door" ],
                                        [ "caption" => "Download", "value" => "Download" ],
                                        [ "caption" => "Drops", "value" => "Drops" ],
                                        [ "caption" => "Duck", "value" => "Duck" ],
                                        [ "caption" => "Edit", "value" => "Edit" ],
                                        [ "caption" => "Electricity", "value" => "Electricity" ],
                                        [ "caption" => "EnergyProduction", "value" => "EnergyProduction" ],
                                        [ "caption" => "EnergySolar", "value" => "EnergySolar" ],
                                        [ "caption" => "EnergyStorage", "value" => "EnergyStorage" ],
                                        [ "caption" => "ErlenmeyerFlask", "value" => "ErlenmeyerFlask" ],
                                        [ "caption" => "Euro", "value" => "Euro" ],
                                        [ "caption" => "Execute", "value" => "Execute" ],
                                        [ "caption" => "Eyes", "value" => "Eyes" ],
                                        [ "caption" => "Factory", "value" => "Factory" ],
                                        [ "caption" => "Favorite", "value" => "Favorite" ],
                                        [ "caption" => "Female", "value" => "Female" ],
                                        [ "caption" => "Fitness", "value" => "Fitness" ],
                                        [ "caption" => "Flag", "value" => "Flag" ],
                                        [ "caption" => "Flame", "value" => "Flame" ],
                                        [ "caption" => "FloorLamp", "value" => "FloorLamp" ],
                                        [ "caption" => "Flower", "value" => "Flower" ],
                                        [ "caption" => "Fog", "value" => "Fog" ],
                                        [ "caption" => "Garage", "value" => "Garage" ],
                                        [ "caption" => "Gas", "value" => "Gas" ],
                                        [ "caption" => "Gauge", "value" => "Gauge" ],
                                        [ "caption" => "Gear", "value" => "Gear" ],
                                        [ "caption" => "Graph", "value" => "Graph" ],
                                        [ "caption" => "GroundFloor", "value" => "GroundFloor" ],
                                        [ "caption" => "Handicap", "value" => "Handicap" ],
                                        [ "caption" => "Heart", "value" => "Heart" ],
                                        [ "caption" => "Help", "value" => "Help" ],
                                        [ "caption" => "HollowArrowDown", "value" => "HollowArrowDown" ],
                                        [ "caption" => "HollowArrowLeft", "value" => "HollowArrowLeft" ],
                                        [ "caption" => "HollowArrowRight", "value" => "HollowArrowRight" ],
                                        [ "caption" => "HollowArrowUp", "value" => "HollowArrowUp" ],
                                        [ "caption" => "HollowDoubleArrowDown", "value" => "HollowDoubleArrowDown" ],
                                        [ "caption" => "HollowDoubleArrowLeft", "value" => "HollowDoubleArrowLeft" ],
                                        [ "caption" => "HollowDoubleArrowRight", "value" => "HollowDoubleArrowRight" ],
                                        [ "caption" => "HollowDoubleArrowUp", "value" => "HollowDoubleArrowUp" ],
                                        [ "caption" => "HollowLargeArrowDown", "value" => "HollowLargeArrowDown" ],
                                        [ "caption" => "HollowLargeArrowLeft", "value" => "HollowLargeArrowLeft" ],
                                        [ "caption" => "HollowLargeArrowRight", "value" => "HollowLargeArrowRight" ],
                                        [ "caption" => "HollowLargeArrowUp", "value" => "HollowLargeArrowUp" ],
                                        [ "caption" => "Hourglass", "value" => "Hourglass" ],
                                        [ "caption" => "HouseRemote", "value" => "HouseRemote" ],
                                        [ "caption" => "Image", "value" => "Image" ],
                                        [ "caption" => "Information", "value" => "Information" ],
                                        [ "caption" => "Intensity", "value" => "Intensity" ],
                                        [ "caption" => "Internet", "value" => "Internet" ],
                                        [ "caption" => "IPS", "value" => "IPS" ],
                                        [ "caption" => "Jalousie", "value" => "Jalousie" ],
                                        [ "caption" => "Key", "value" => "Key" ],
                                        [ "caption" => "Keyboard", "value" => "Keyboard" ],
                                        [ "caption" => "Kitchen", "value" => "Kitchen" ],
                                        [ "caption" => "Leaf", "value" => "Leaf" ],
                                        [ "caption" => "Light", "value" => "Light" ],
                                        [ "caption" => "Lightning", "value" => "Lightning" ],
                                        [ "caption" => "Link", "value" => "Link" ],
                                        [ "caption" => "Lock", "value" => "Lock" ],
                                        [ "caption" => "LockClosed", "value" => "LockClosed" ],
                                        [ "caption" => "LockOpen", "value" => "LockOpen" ],
                                        [ "caption" => "Macro", "value" => "Macro" ],
                                        [ "caption" => "Mail", "value" => "Mail" ],
                                        [ "caption" => "Male", "value" => "Male" ],
                                        [ "caption" => "Melody", "value"=> "Melody" ],
                                        [ "caption" => "Menu", "value" => "Menu" ],
                                        [ "caption" => "Minus", "value" => "Minus" ],
                                        [ "caption" => "Mobile", "value" => "Mobile" ],
                                        [ "caption" => "Moon", "value" => "Moon" ],
                                        [ "caption" => "Motion", "value" => "Motion" ],
                                        [ "caption" => "Move", "value" => "Move" ],
                                        [ "caption" => "Music", "value" => "Music" ],
                                        [ "caption" => "Network", "value" => "Network" ],
                                        [ "caption" => "Notebook", "value" => "Notebook" ],
                                        [ "caption" => "Ok", "value" => "Ok" ],
                                        [ "caption" => "Pacifier", "value" => "Pacifier" ],
                                        [ "caption" => "Paintbrush", "value" => "Paintbrush" ],
                                        [ "caption" => "Pants", "value" => "Pants" ],
                                        [ "caption" => "Party", "value" => "Party" ],
                                        [ "caption" => "People", "value" => "People" ],
                                        [ "caption" => "Plug", "value" => "Plug" ],
                                        [ "caption" => "Plus", "value" => "Plus" ],
                                        [ "caption" => "Popcorn", "value" => "Popcorn" ],
                                        [ "caption" => "Power", "value" => "Power" ],
                                        [ "caption" => "Presence", "value" => "Presence" ],
                                        [ "caption" => "Radiator", "value" => "Radiator" ],
                                        [ "caption" => "Raffstore", "value" => "Raffstore" ],
                                        [ "caption" => "Rainfall", "value" => "Rainfall" ],
                                        [ "caption" => "Recycling", "value" => "Recycling" ],
                                        [ "caption" => "Remote", "value" => "Remote" ],
                                        [ "caption" => "Repeat", "value" => "Repeat" ],
                                        [ "caption" => "Return", "value" => "Return" ],
                                        [ "caption" => "Robot", "value" => "Robot" ],
                                        [ "caption" => "Rocket", "value" => "Rocket" ],
                                        [ "caption" => "Script", "value" => "Script" ],
                                        [ "caption" => "Shift", "value" => "Shift" ],
                                        [ "caption" => "Shower", "value" => "Shower" ],
                                        [ "caption" => "Shuffle", "value" => "Shuffle" ],
                                        [ "caption" => "Shutter", "value" => "Shutter" ],
                                        [ "caption" => "Sink", "value" => "Sink" ],
                                        [ "caption" => "Sleep", "value" => "Sleep" ],
                                        [ "caption" => "Snow", "value" => "Snow" ],
                                        [ "caption" => "Snowflake", "value" => "Snowflake" ],
                                        [ "caption" => "Sofa", "value" => "Sofa" ],
                                        [ "caption" => "Speaker", "value" => "Speaker" ],
                                        [ "caption" => "Speedo", "value" => "Speedo" ],
                                        [ "caption" => "Stars", "value" => "Stars" ],
                                        [ "caption" => "Sun", "value" => "Sun" ],
                                        [ "caption" => "Sunny", "value" => "Sunny" ],
                                        [ "caption" => "Talk", "value" => "Talk" ],
                                        [ "caption" => "Tap", "value" => "Tap" ],
                                        [ "caption" => "Teddy", "value" => "Teddy" ],
                                        [ "caption" => "Tee", "value" => "Tee" ],
                                        [ "caption" => "Telephone", "value" => "Telephone" ],
                                        [ "caption" => "Temperature", "value" => "Temperature" ],
                                        [ "caption" => "Thunder", "value" => "Thunder" ],
                                        [ "caption" => "Title", "value" => "Title" ],
                                        [ "caption" => "TopFloor", "value" => "TopFloor" ],
                                        [ "caption" => "Tree", "value" => "Tree" ],
                                        [ "caption" => "TurnLeft", "value" => "TurnLeft" ],
                                        [ "caption" => "TurnRight", "value" => "TurnRight" ],
                                        [ "caption" => "TV", "value" => "TV" ],
                                        [ "caption" => "Umbrella", "value" => "Umbrella" ],
                                        [ "caption" => "Unicorn", "value" => "Unicorn" ],
                                        [ "caption" => "Ventilation", "value" => "Ventilation" ],
                                        [ "caption" => "Warning", "value" => "Warning" ],
                                        [ "caption" => "Wave", "value" => "Wave" ],
                                        [ "caption" => "Wellness", "value" => "Wellness" ],
                                        [ "caption" => "WindDirection", "value" => "WindDirection" ],
                                        [ "caption" => "WindSpeed", "value" => "WindSpeed" ],
                                        [ "caption" => "Window", "value" => "Window" ],
                                        [ "caption" => "WC", "value" => "WC" ]
                                    ],
                                ]
                            ],
                            [
                                "caption" => "Target Instance",
                                "name" => "targetInstance",
                                "width" => "auto",
                                "add" => $this->InstanceID,
                                "edit" => [
                                    "type" => "SelectInstance",
                                ]
                            ]
                        ],
                        "values" => json_decode($this->ReadPropertyString("logGroups"), true),
                    ]
                ],
            ]
        ];
    }

    /**
     * @return array[] Form Status
     */
    protected function FormStatus() {
        return [
            [
                'code'    => 101,
                'icon'    => 'inactive',
                'caption' => 'Creating instance.',
            ],
            [
                'code'    => 102,
                'icon'    => 'active',
                'caption' => 'Logger created.',
            ],
            [
                'code'    => 104,
                'icon'    => 'inactive',
                'caption' => 'interface closed.',
            ]
        ];
    }

}