# wfEngine

## Introduction

The wfEngine Class allows full control over sequential method calls. The main cause for creating wfEngine was to have a mechanism in our CMS, that allows us to implement a web workflow on every level (backend, frontend, plugins, modules and even plugins called by plugins, etc). The wfEngine is therefore the main mechanism that drives our controlers.

A simple example: if you have a contact form, you want to show the form on default, after submitting the formdata should be validated. On errors, the form should be called again with an error message. On success the data should be saved and a message should be shown to the user.

With wfEngine you can check, that the validation will only be called from the form and that the data should only be saved, if the data was validated. The same accounts for the message, that should only be shown, if the data was successfully saved.

By using Pre- and/or Postfixes in your methods, wfEngine allows only to call wfEngine methods and not any public method via http request, for example.

In addition wfEngine helps to avoid CSRF attacks by using Hashtags for the sequentiel method calls. If you want to be shure, that the validate function was called from the form in the same session, you can check before execution of the validation method, if the submitted hashtag matches the session hastag.

An additional benefit is, that wfEngine encourages to build small functions, because it is easy to navigate between them, either via http request, with ajax or direct call, but with full control over the correct sequential processing.

But the main goal was to have a simple and easy to extend mechanism to navigate through your application.

In addition annotations can be used to simplify the flow control checks and to hold your code a little bit cleaner that way.


## A simple Example

For the example we use classic URLs with parameters and a simple session object with a `setValue` and `getValue function.

In the `execute` method we instantiate the wfEngine and start the processing.

    public function execute() {
        $params = array('name' => 'simpleTest',
            'defaultCommand' => 'default',
            'postfix' => 'Action',
            'wfObject' => $this,
            'sessionObject' => new simpleSession(),
            'givenHashTag' => $_GET['hash']);

        $this->wf = new wfEngine($params);
        $this->wf->executeWF($_GET['cmd']);
    }

At first we set the parameters. If we use several wfEngine instances, we have to give each a unique name. Then we have to set a default command, in this example: 'default'. By setting the postfix to 'Action', the class method for the 'default' command must be 'defaultAction'. Then we have to inject the Object that should be processed by the wfEngine. We need a session Object to save the last and current commands in our flow and, if we want to prevent CSRF attacks, we have to set the hashtag coming from the http request.
In this simple example, we use the $_GET` variables. In production code, we usually use a request object with filtered values from the http requests. In the end, we start the flow with the actual command ('default', if `$_GET['cmd']` is empty).
Injecting objects, parameters and variables can either be done via injecting an array in the constructor (in the example above) or by using setter methods.

    public function defaultAction() {

        // Build the form with your favourite template engine.
        // The form action may look like this:
        // index.php?cmd=validate&hash=".$this->wf->getHashTag();
        // the next command to be executed will be 'validate' (method: 'validateAction')
        // and we set the hashtag also.

        $this->output = $out;
    }

The wfEngine will look for any output in the processed object by checking with the `isOutput` method. If true, the processing stops and the output can be presented. If `isOutput` returns false, wfEngine looks for the next command returned by the called method and calls the corresponding method.
In this example, the form will be shown, because we have an output. If the form is submitted, wfEngine executes `validateAction` via `$_GET['cmd']`, which was set to `validate` in the forms action parameter.

    public function validateAction() {
        if (!$this->wf->checkGivenHash() || !$this->wf->checkLast('default')) {
            // show an error or throw an exception...
        }

        // validate the form data

        if ($error) {
            return"default";
        }
        return "save";
    }

At first we check, if the submitted hashtag corresponds to the session hashtag and we check if the last called command was 'default'.
Then we can validate the submitted form data. If there was an error, we show the form again by returning the 'default' command. Otherwise we return the 'save' command.

    public function saveAction() {
        if (!$this->wf->checkGivenHash() || !$this->wf->checkLast('validate') || $this->wf->commandWasCalledExternal()) {
            // show an error or throw an exception...
        }

        // save validated form data

        $this->wf->setMustReload(true);
        if ($error) {
            return "saveerror";
        }
        return "thank";
    }

At first we check again, if the hashtag is correct and that the last command was 'validate'. In addition we also check, if the last command was called via a http request (external). That way we can avoid that important methods can be called directly from the web.
In case of an save error, we return the "saveerror" command, otherwise we return the "thank" command. But before we tell the wfEngine to call the next command via http request (by reloading the page with the new command). That way the "thank" or "saveerror" message will be shown with a fresh URL in the browser and our browser history is "clean".

    public function thankAction() {
        if (!$this->wf->checkGivenHash() || !$this->wf->checkLast('save')) {
            die('Hash not valid or last command was not save!');
        }

        // Build the message with your favourite template engine.
        // You may use a link back to the form like this:
        // index.php?cmd=default

        $this->output = $out;
    }

In the end, we show the "Thank You" Message and put a link back to form in it.

## Annotations

Instead using the `checkGivenHash`, `checkLast`, etc methods, we can also use annotations. Let's have a look at an example:

    /**
     * @wfCheckHash
     * @wfCheckLast('validate')
     * @wfCheckCommandWasCalledInternal
     */
    public function saveAction() {

        // save data

        $this->wf->setMustReload(true);
        return "thank";
    }

Here we can see, that the annotation wfCheckHash has the same function as the `checkGivenHash` method, etc...


## Advanced check methods

If you want to check for the http request method, there are three methods you can use:

    checkIfCalledViaGET()
    checkIfCalledViaPOST()
    checkIfCalledViaAJAX()

That way we can improve the example above, by not only checking, if validate was called from 'default', but also, that it was called by a POST request and the thank method was called by a GET request.
In the following example we used the corresponding annotations:

    /**
     * @wfCheckHash
     * @wfCheckLast('default')
     * @wfCheckCommandWasSendViaPOST
     */
    public function validateAction() {
        ...
        return "save";
    }

and

    /**
     * @wfCheckHash
     * @wfCheckLast('save')
     * @wfCheckCommandWasSendViaGET
     */
    public function thankAction() {
        ...
        $this->_setOutput($out);
    }

