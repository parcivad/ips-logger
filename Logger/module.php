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
        $this->RegisterAttributeString("cssView", '.styled-table {position: relative;display: block;background-color: #383c44;border-collapse: collapse;width: 100%;height: 500px;overflow-y: auto;overflow-x: hidden;font-size: 0.9em;font-family: sans-serif;box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);}.styled-table thead tr {background-color: #4d6070;color: #fff;text-align: left;padding: 7px 15px;}.styled-table th,.styled-table td {padding: 8px 15px;}.styled-table td {color: #ffffff;}.styled-table tbody tr {border-bottom: 1px solid #dddddd;}.styled-table tbody tr:last-of-type {border-bottom: 2px solid #818181;}.styled-table tbody tr.active-row {font-weight: bold;color: #818181;}.colLevel {width: 30em;}.colPriority {width: 110em;}.colMessage {width: 1100em;}.colSender {width: 125em;}.colTime {width: 125em;}');
        $this->RegisterAttributeString("log", '[{"time": "1648661547","level": "Info","message": "this is a message","sender": "lol"},{"time": "1648661547","level": "Warning","message": "this is a message tagged with warning","sender": "me"},{"time": "1648661547","level": "Info","message": "this is a message","sender": "lol"},{"time": "1648661547","level": "Discovery","message": "Junge ich hab seine Ehre wiedergefunden","sender": "RIP"},{"time": "1648661547","level": "Error","message": "Einfach Ehre verloren","sender": "eric"}]');
        $this->RegisterAttributeString("logLevels", '{"Info":{"priority":0,"color":"#383c44","font-weight":"normal"},"Discovery": {"priority": 5,"color": "#53a6cb","font-weight": "normal"},"Warning":{"priority":80,"color":"#e3bc2b","font-weight":"bold"},"Error":{"priority":90,"color":"#e34d2b","font-weight":"bold"}}');
        $this->RegisterPropertyInteger("maximumLogRecords", 100);

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
     * @param string $level     Level of
     * @param string $message
     * @param string $sender
     * @return void
     */
    public function entry(string $level, string $message, string $sender) {
        // add into saved json
        $log = $this->readJson("log");
        // format new entry
        $log[] = [
            "time" => time(),
            "level" => $level,
            "message" => $message,
            "sender" => $sender
        ];
        // save new entry
        $this->saveJson("log", $log);
    }

    /**
     * Function to resort and refresh HTML View of the Variable view
     * @return void
     */
    private function reloadHTMLView() {
        // load log in
        $log = $this->readJson("log");
        $level = $this->readJson("logLevels");

        // buffer in table body var
        $tbody = '';
        for ($i=count($log) - 1; $i >= 0 ; $i--) {
            // get log item
            $item = $log[$i];
            $itemLevel = $level[$item['level']];
            // form html table body element with given log info
            $tbody .= sprintf('<tr style="background-color:%s"><td style="font-weight:%s;">%s</td>
                <td>%d</td><td>%s</td><td>%s</td><td>%s</td></tr>',
                $itemLevel['color'], $itemLevel['font-weight'], $item['level'], $itemLevel['priority'],
                $item['message'], $item['sender'], date("H:i:s D", $item['time']));
        }
        // set visible value
        $this->SetValue("view", sprintf($this->ReadAttributeString("htmlView"),
            $this->ReadAttributeString("cssView"), $tbody));
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
        $this->WriteAttributeString($save, json_encode($jsonArray));
        // after chance reload html view
        $this->reloadHTMLView($jsonArray);
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
                "type" => "NumberSpinner",
                "name" => "maximumLogRecords",
                "caption" => "Maximum of recorded Log entries",
                "minimum" => 100,
                "maximum" => 9999,
                "suffix" => " entries"
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