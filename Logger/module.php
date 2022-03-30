<?php

class Logger extends IPSModule {

    /*
    * Internal function of SDK
    */
    public function Create() {
        // declare parent
        parent::Create();

        // inner json saves
        $this->RegisterAttributeString("htmlView");
        $this->RegisterAttributeString("log");
        $this->RegisterAttributeString("logLevels");

        // user settings
        $this->RegisterPropertyInteger("maximumLogRecords", 100);

        // vars
        $this->RegisterVariableString("view", "Log View", "~HTMLBox");

        // load defaults into registered attributes
        $this->loadDefaults();
    }
    /*
     * Internal function of SDK
     */
    public function ApplyChanges() {
        // declare parent
        parent::ApplyChanges();
    }

    /**
     * Function to resort and refresh HTML View of the Variable view
     * @param array $log
     * @return void
     */
    private function reloadHTMLView(array $log) {
        // load log in
        $log = $this->readJson("log");

        $style = '';
        $tbody = '';

        $final = readfile("defaults.json", dirname("./libs/defaults,json"));
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

    private function loadDefaults() {
        $defaults = json_decode(readfile("defaults.json", dirname("./libs/defaults,json")),
            true);
        // load into ip symcon
        $this->WriteAttributeString("htmlView", $defaults["htmlView"]);
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