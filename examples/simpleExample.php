<?php

require_once dirname(__FILE__).'/../wfObjectInterface.php';
require_once dirname(__FILE__).'/../wfEngine.php';
require_once dirname(__FILE__).'/simpleSession.php';

class simpleExample implements wfObjectInterface {

    public function  __construct() {

    }

    public function reload($cmd) {
        ob_end_clean();
        header("Location: index.php?cmd=".$cmd."&hash=".$this->wf->getHashTag());
        header("Connection: close");
        exit();
    }

    public function isOutput() {
        if (!isset($this->output)) return false;
        if (strlen(trim($this->output)) > 0 ) {
            return true;
        }
        return false;
    }

    public function execute() {
        $params = array('name'=>'simpleTest', 
                        'defaultCommand'=>'default', 
                        'postfix' => 'Action', 
                        'wfObject'=>$this, 
                        'sessionObject'=>new simpleSession(), 
                        'givenHashTag'=>$_GET['hash']);
        
        $this->wf = new wfEngine($params);
	$this->wf->executeWF($_GET['cmd']);
    }

    private function _setOutput($output) {
        $this->output = $output;
    }

    public function getOutput() {
        return $this->output;
    }

    public function defaultAction() {
        $action = "index.php?cmd=validate&hash=".$this->wf->getHashTag();

        $out = '<html><head><title>Example</title></head><body>'.PHP_EOL;
        $out.= '<form action="'.$action.'" method="POST">'.PHP_EOL;
        if ($this->error == 1) $out.= '<p style="color:red;">Bitte einen Wert eingeben!</p>'.PHP_EOL;
        $out.= '<input type="text" name="example">&nbsp;<input type="submit" value="submit">'.PHP_EOL;
        $out.= '</form>'.PHP_EOL;
        $out.= '</body></html>';

        $this->_setOutput($out);
    }

    public function validateAction() {
        if (!$this->wf->checkGivenHash() || !$this->wf->checkLast('default') || !$this->wf->checkIfCalledViaPOST()) {
            throw new Exception('WF Error!');
        }
        if (strlen(trim($_POST['example']))<1) {
            $this->error = 1;
            return "default";
        }
        return "save";
    }

    public function saveAction() {
        if (!$this->wf->checkGivenHash() || !$this->wf->checkLast('validate') || $this->wf->commandWasCalledExternal()) {
            throw new Exception('WF Error!');
        }

        // save data

        $this->wf->setMustReload(true);
        return "thank";
    }

    public function thankAction() {
        if (!$this->wf->checkGivenHash() || !$this->wf->checkLast('save') || !$this->wf->checkIfCalledViaGET()) {
            throw new Exception('WF Error!');
        }

        $url = "index.php?cmd=default";

        $out = '<html><head><title>Example</title></head><body>'.PHP_EOL;
        $out.= '<h1>Thanks for all the fish</h1>'.PHP_EOL;
        $out.= '<p><a href="'.$url.'">return to form</a></p>'.PHP_EOL;
        $out.= '</body></html>';

        $this->_setOutput($out);
    }
}