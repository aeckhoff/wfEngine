<?php

require_once dirname(__FILE__).'/../wfObjectInterface.php';

class wfEngineMock implements wfObjectInterface {


    public function __construct() {
        
    }

    public function execute() {
    }

    public function setWFObject($object) {
        $this->wf = $object;
    }

    public function wf_start() {
        $this->output = "start";
    }

    public function wf_callstart() {
        return "start";
    }

    public function wf_callchecktest() {
        return "checktest";
    }

    public function wf_callmultiplechecktest() {
        return "multiplechecktest";
    }

    public function wf_callmultiplechecktestagain() {
        return "multiplechecktest";
    }

    public function wf_checktest() {
        if ($this->wf->checkLast('callchecktest')) {
            $this->output = 'last was callchecktest';
        }
        else {
            $this->output = 'last was not callchecktest';
        }
    }

    public function wf_multiplechecktest() {
        if ($this->wf->checkLast(array('callmultiplechecktest','callmultiplechecktestagain'))) {
            $this->output = 'last was callmultiplechecktest or wasnotcalled';
        }
        else {
            $this->output = 'last was not callchecktest';
        }
    }

    public function wf_mustreload() {
        $this->wf->setMustReload(true);
        return "start";
    }

    public function reload($cmd) {
        return 'reload: '.$cmd;
    }

    public function isOutput() {
        if (!isset($this->output)) return false;
        if (strlen(trim($this->output)) > 0 ) {
            return true;
        }
        return false;
    }
}