<?php

class simpleSession {

    public function  __construct() {
        session_start();
    }

    public function setValue($key, $value) {
        $_SESSION['wfValues'][$key] = $value;
    }

    public function getValue($key) {
        if (!isset($_SESSION['wfValues'][$key])) return false;
        return $_SESSION['wfValues'][$key];
    }

}
