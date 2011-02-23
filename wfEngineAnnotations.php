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

class wfEngineAnnotations {

    public function  __construct($object, $method) {
        if (method_exists($object, $method)) {
            $reflection = new ReflectionMethod($object, $method);
            $this->doc = $reflection->getDocComment();
        } else {
            throw new Exception("Method doesn't exist");
        }
    }

    public function setWFEngine(wfEngine $wfEngine) {
        $this->wfEngine = $wfEngine;
    }

    public function check() {
        if ($this->doc) {
            $this->_checkHash();
            $this->_checkLast();
            $this->_checkCommandWasCalledInternal();
            $this->_checkCommandWasCalledExternal();
        }
    }

    private function _checkHash() {
        if (strstr($this->doc, '@checkHash')) {
            if (!$this->wfEngine->checkGivenHash()) {
                throw new Exception('Hash is not valid!');
            }
        }
    }

    private function _checkLast() {
        if (strstr($this->doc, '@checkLast')) {
            $pattern = "^".preg_quote("@checkLast('")."(.*?)".preg_quote("')")."^sm";
            $ok = preg_match_all ( $pattern, $this->doc, $result);
            foreach($result[1] as $last) {
                if ($this->wfEngine->checkLast($last)) {
                    $flag2 = true;
                }
            }
            if ($flag2 != true) {
                throw new Exception('Last method not valid!');
            }
        }
    }

    private function _checkCommandWasCalledInternal() {
        if (strstr($this->doc, '@checkCommandWasCalledInternal')) {
            if ($this->wfEngine->commandWasCalledExternal()) {
                throw new Exception('Command was called external!');
            }
        }
    }

    private function _checkCommandWasCalledExternal() {
        if (strstr($this->doc, '@checkCommandWasCalledExternal')) {
            if (!$this->wfEngine->commandWasCalledExternal()) {
                throw new Exception('Comand was call internal!');
            }
        }
    }
}