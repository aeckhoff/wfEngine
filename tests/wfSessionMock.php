<?php
class wfSessionMock {

    public function  __construct() {
        $this->value = array();
    }

    public function setValue($key, $value) {
        $this->value[$key] = $value;
    }

    public function getValue($key) {
        if (!isset($this->value[$key])) return false;
        return $this->value[$key];
    }
}
?>
