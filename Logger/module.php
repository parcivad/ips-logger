<?php

class Logger extends IPSModule {

    /*
    * Internal function of SDK
    */
    public function Create() {
        // declare parent
        parent::Create();

        // inner json saves
        $this->RegisterAttributeString("htmlView", '<head><meta name="viewport" content="width=device-width, initial-scale=1"><style>%s</style></head><body><table class="styled-table"><colgroup><col class="colLevel"><col class="colPriority"><col class="colMessage"><col class="colSender"><col class="colTime"></colgroup><thead><tr><th>Level</th><th>Priority</th><th>Message</th><th>Sender</th><th>Time</th></tr></thead><tbody>%s</tbody></table></body>');
        $this->RegisterAttributeString("cssView", '.styled-table {position: relative;display: block;background-color: #%s;border-collapse: collapse;width: %d;height: %dpx;overflow-y: auto;overflow-x: hidden;font-size: 0.9em;font-family: sans-serif;box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);}.styled-table thead tr {background-color: #%s;color: #fff;text-align: left;padding: 7px 15px;}.styled-table th,.styled-table td {padding: %dpx 15px;}.styled-table td {color: #ffffff;}.styled-table tbody tr {border-bottom: 1px solid #%s;}.styled-table tbody tr:last-of-type {border-bottom: 2px solid #%s;}.styled-table tbody tr.active-row {font-weight: bold;color: #818181;}.colLevel {width: 30em;}.colPriority {width: 110em;}.colMessage {width: 1100em;}.colSender {width: 125em;}.colTime {width: 125em;}');
        $this->RegisterAttributeString("log", '[{"time": "0","level": "Info","message": "This is the beginning of your own log","sender": "Parcivad"}]');
        $this->RegisterPropertyString("logLevels", '[{"name":"Ok","priority":5,"color": 2412282367,"font-weight":"normal","notification": false},{"name":"Info","priority":0,"color": 3685444,"font-weight":"normal","notification": false},{"name":"Discovery","priority": 10,"color": 5482187,"font-weight": "normal","notification": false},{"name":"Warning","priority":80,"color": 14924843,"font-weight":"bold","notification": false},{"name":"Error","priority":90,"color": 14896427,"font-weight":"bold","notification": false}]');

        // user interface settings
        // general settings
        $this->RegisterPropertyBoolean("active", true);
        $this->RegisterPropertyInteger("maximumLogRecords", 150);
        $this->RegisterPropertyInteger("entryHeight", 8);
        $this->RegisterPropertyInteger("tableHeight", 500);
        // notifications settings
        $this->RegisterPropertyString("notificationList", "{}");
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
     * @param string $message   Message itself
     * @param string $sender    String of displayed sender
     * @return void
     * @throws UnexpectedValueException
     */
    public function entry(string $level, string $message, string $sender) {
        // is log entry active, else cancel action
        if (!$this->ReadPropertyBoolean("active")) return;

        // add into saved json
        $log = $this->readJson("log");
        // check if level exists
        if ($this->getLevel($level) == null) throw new UnexpectedValueException("level not existing");
        // format new entry
        $log[] = [
            "time" => time(),
            "level" => $level,
            "message" => $message,
            "sender" => $sender
        ];
        // save new entry
        $this->saveJson("log", $log);

        // send notification if allowed
        if ($this->getLevel($level)['notification']) $this->notification($sender, $message, $level);
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
                <td>%d</td><td>%s</td><td>%s</td><td>%s</td></tr>',
                '#'.dechex($itemLevel['color']), $itemLevel['font-weight'], $item['level'], $itemLevel['priority'],
                $item['message'], $item['sender'], date("H:i:s D", $item['time']));
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
     * @param string $titel     Titel of the Notification
     * @param string $message   Core Message
     * @param string $sender    Initial Sender
     * @return void
     */
    private function notification(string $titel, string $message, string $sender) {
        $notifyReceivers = json_decode($this->ReadPropertyString("notificationList"), true);
        foreach ($notifyReceivers as $receiver) {
            // try to send notification
            try {
                WFC_PushNotification($receiver['instance'], $titel, $message. " (" . $sender . ")", "full", $this->InstanceID);
                WFC_SendNotification($receiver['instance'], $titel, $message . " (" . $sender . ")", "Database", 300);
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
                "type" => "CheckBox",
                "caption" => "Log",
                "name" => "active"
            ],
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
                        "caption" => "Table head color"
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
            [
                "type" => "List",
                "name" => "notificationList",
                "caption" => "Select all Visual instanced that should get a possible notification",
                "rowCount" => count(json_decode($this->ReadPropertyString("notificationList"), true)),
                "add" => true,
                "delete" => true,
                "columns" => [
                    [
                        "caption" => "Visual Instances",
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
                "type" => "List",
                "name" => "logLevels",
                "caption" => "Mobile views to notify",
                "rowCount" => count(json_decode($this->ReadPropertyString("logLevels"), true)),
                "sort" => [
                    "column" => "priority"
                ],
                "add" => true,
                "delete" => true,
                "columns" => [
                    [
                        "caption" => "Name",
                        "name" => "name",
                        "width" => "180px",
                        "add" => "Level Name",
                        "edit" => [
                            "type" => "ValidationTextBox"
                        ]
                    ],
                    [
                        "caption" => "Priority",
                        "name" => "priority",
                        "width" => "100px",
                        "add" => 1,
                        "edit" => [
                            "type" => "NumberSpinner",
                            "minimum" => 0
                        ]
                    ],
                    [
                        "caption" => "Color",
                        "name" => "color",
                        "width" => "auto",
                        "add" => 3685444,
                        "edit" => [
                            "type" => "SelectColor"
                        ]
                    ],
                    [
                        "caption" => "Font Weight",
                        "name" => "font-weight",
                        "width" => "100px",
                        "add" => "normal",
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
                    ],
                    [
                        "caption" => "Notifications",
                        "name" => "notification",
                        "width" => "100px",
                        "add" => false,
                        "edit" => [
                            "type" => "CheckBox"
                        ]
                    ]
                ],
                "values" => json_decode($this->ReadPropertyString("logLevels"), true),
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