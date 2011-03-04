# wfEngine

## Introduction

The wfEngine Class allows full control over sequential method calls. The main cause for creating wfEngine was to have a mechanism in our CMS, that allows us to implement a web workflow on every level (backend, frontend, plugins, modules and even plugins called by plugins, etc). The wfEngine is therefore the main mechanism that drives our controllers.

A simple example: if you have a contact form, you want to show the form on default, after submitting the formdata should be validated. On errors, the form should be called again with an error message. On success the data should be saved and a message should be shown to the user.

With wfEngine you can check, that the validation will only be called from the form and that the data should only be saved, if the data was validated. The same accounts for the message, that should only be shown, if the data was successfully saved.

By using Pre- and/or Postfixes in your methods, wfEngine allows only to call wfEngine methods and not any public method via http request, for example.

In addition wfEngine helps to avoid CSRF attacks by using Hashtags for the sequentiel method calls. If you want to be shure, that the validate function was called from the form in the same session, you can check before execution of the validation method, if the submitted hashtag matches the session hastag.

An additional benefit is, that wfEngine encourages to build small functions, because it is easy to navigate between them, either via http request, with ajax or direct call, but with full control over the correct sequential processing.

But the main goal was to have a simple and easy to extend mechanism to navigate through your application.

In addition annotations can be used to simplify the flow control checks and to hold your code a little bit cleaner that way.