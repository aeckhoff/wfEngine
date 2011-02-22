<?php

/**************************************************************************
 *  Copyright notice
 *
 *  Copyright 2004-2011 Dr. Andreas Eckhoff
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 *
 ***************************************************************************/

class wfEngine {

    private $name;
    private $defaultCommand;
    private $prefix;
    private $postfix;
    private $wfobject;
    private $mustReload;
    private $calledExternal;
    private $sessionObject;
    private $count = 0;
    private $maxInternalCalls = 20;
    private $givenHashTag = false;
    private $salt = false;
    private $callDefaultOnError;

    public function __construct($arg=false) {
        if (is_array($arg)) {
            if ($arg['name'])
                $this->name = $arg['name'];
            
            if ($arg['callDefaultOnError'] === true)
                $this->setCallDefaultOnError(true);
            
            if (intval($arg['maxInternalCalls'])>0)
                $this->setMaxInternalCalls(intval($arg['maxInternalCalls']));
            
            if ($arg['defaultCommand'])
                $this->setDefaultCommand($arg['defaultCommand']);
            
            if ($arg['prefix'])
                $this->setPrefix($arg['prefix']);
            
            if ($arg['postfix'])
                $this->setPostfix($arg['postfix']);
            
            if ($arg['wfObject'])
                $this->setWFObject($arg['wfObject']);
            
            if ($arg['sessionObject'])
                $this->setSessionObject($arg['sessionObject']);
            
            if ($arg['givenHashTag'])
                $this->setGivenHashTag($arg['givenHashTag']);
        } 
        else {
            if (!$arg) {
                $this->name = "default";
            }
            else {
                $this->name = $arg;
            }
            $this->_setCalledExternal(true);
        }
    }

    public function setCallDefaultOnError($bool) {
        if ($bool === true) {
            $this->callDefaultOnError = true;
        }
        else {
            $this->callDefaultOnError = false;
        }
    }

    public function setMaxInternalCalls($maxInternalCalls) {
        $this->maxInternalCalls = intval($maxInternalCalls);
    }

    public function getMaxInternalCalls() {
        return $this->maxInternalCalls;
    }

    public function setDefaultCommand($cmd) {
        if (!is_string($cmd)) {
            throw new Exception('cmd parameter must be a string');
        }
        $this->defaultCommand = $cmd;
    }

    public function getDefaultCommand() {
        return $this->defaultCommand;
    }

    public function setPrefix($prefix) {
        $this->prefix = $prefix;
    }

    public function getPrefix() {
        return $this->prefix;
    }

    public function setPostfix($postfix = '') {
        $this->postfix = $postfix;
    }

    public function getPostfix() {
        return $this->postfix;
    }

    public function setWFObject(wfObjectInterface $object) {
        $this->wfobject = $object;
    }

    public function getWFObject() {
        return $this->wfobject;
    }

    public function setMustReload($bool) {
        $this->mustReload = false;
        if ($bool) {
            $this->mustReload = true;
        }
    }

    public function mustReload() {
        return $this->mustReload;
    }

    private function _setCalledExternal($bool) {
        $this->calledExternal = false;
        if ($bool) {
            $this->calledExternal = true;
        }
    }

    public function commandWasCalledExternal() {
        if ($this->_wasNotCalledExternalAndCallDefaultOnErrorIsTrue()) {
            $this->wfobject->reload($this->defaultCommand);
        } 
        elseif(!$this->calledExternal) {
            return false;
        }
        return true;
    }

    private function _wasNotCalledExternalAndCallDefaultOnErrorIsTrue() {
        if (!$this->calledExternal && $this->callDefaultOnError == true) return true;
        return false;
    }

    public function setSessionObject($object) {
        $this->sessionObject = $object;
    }

    private function _buildCommand($cmd) {
        if (!$cmd) {
            $cmd = $this->defaultCommand;
        }
        return $cmd;
    }

    public function executeWF($cmd=false) {
        $this->sessionObject->setValue("last", $this->sessionObject->getValue("current"));
        $cmd = $this->_buildCommand($cmd);
        $classMethod = $this->getPrefix().$cmd.$this->getPostfix();

        if (method_exists($this->wfobject, $classMethod)) {
            $this->_callMethod($classMethod, $cmd);
        }
        else {
            throw new Exception("WF Methode ".$cmd." existiert nicht!");
        }
    }

    private function _callMethod($classMethod, $cmd) {
        $nextCommand = call_user_method($classMethod, $this->wfobject);
        $this->sessionObject->setValue("current", $cmd);

        if ($this->wfobject->isOutput()) {
            return true;
        }
        else {
            $this->_callNextMethod($nextCommand);
        }
    }

    private function _callNextMethod($nextCommand) {
        if ($this->mustReload == true) {
            $this->wfobject->reload($nextCommand);
        }
        else {
            $this->_internalCallNextMethod($nextCommand);
        }
    }

    private function _internalCallNextMethod($nextCommand) {
        if ($this->count > $this->getMaxInternalCalls()) {
            throw new Exception("WF internal calls exceed ".$this->getMaxInternalCalls()."!");
        }
        $this->count++;
        $this->_setCalledExternal(false);
        $this->executeWF($nextCommand);
    }

    public function checkLast($cmd) {
        if (is_array($cmd)) {
            if (in_array($this->sessionObject->getValue("last"), $cmd)) {
                return true;
            }
        }
        if ($this->sessionObject->getValue("last") == $cmd) {
            return true;
        }
        else {
            return false;
        }
    }

    public function setGivenHashTag($hashtag) {
        $this->givenHashTag = $hashtag;
    }

    public function getHashTag() {
        if (!$this->sessionObject->getValue("hash")) {
            $this->_createHashTag();
        }
        return $this->_prepareSaltHash($this->sessionObject->getValue("hash"));
    }

    private function _prepareSaltHash($hash) {
        return substr(sha1($hash.$this->salt), 0, 8);
    }

    public function checkGivenHash() {
        if ($this->givenHashTag == $this->getHashTag()) {
            return true;
        }
        return false;
    }

    private function _createHashTag() {
        $hash = substr(sha1(time()), 0, 8);
        $this->sessionObject->setValue('hash', $hash);
    }
}