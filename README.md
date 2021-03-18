metadata-validator [![Build Status](https://travis-ci.org/tenet-ac-za/metadata-validator.svg?branch=master)](https://travis-ci.org/tenet-ac-za/metadata-validator)
==================

metadata-validator is a simple web-based application that's intended
to allow federation participants to validate their own metadata before
submitting it to the federation for review and possible inclusion in a
metadata registry.

It does schema validation and applies [Ian
Young](https://wiki.shibboleth.net/confluence/spaces/viewspace.action?key=~ian@iay.org.uk)'s
XSLT metadata testing rules. It also allows for local schemas and XSLT
rules to suppliment the default set. It tries as far as possible to apply
rules in the same was as WAYF's [PHPH](https://github.com/wayf-dk/phph)
metadata aggregator, since that's the aggregator in use by the authors.

Whilst some parts of it may be specific to the [South African Identity
Federation](https://safire.ac.za/) (e.g. the UI skin and sample local
rules), we've tried to keep it as generic as possible in the hopes that
it may be of use to another emerging federation.

Installation
------------

This respository is intended to be a self-contained document root suitable
for use on any web server that supports [PHP](http://php.net). All
paths should be relative, meaning that it does not matter whether this
is placed at the root of your web server or within a sub-directory.

Installation should be as simple as downloading the source and unpacking
it in an appropriate place.

Configuration
-------------

As a relatively simple application, there's no real configuration
required.  You should review (and possibly delete) the local schemas
and rules that are contained in the [`local/`](local/) directory. You
probably also want to re-skin it to match your own look and feel.

If it exists, the file [`local/xsltfunc.inc.php`](local/xsltfunc.inc.php)
is included. It is expected to define an `xsltfunc` class, and
any static public functions from this class are registered with
[XSLTProcessor](http://php.net/manual/en/xsltprocessor.registerphpfunctions.php)
so that they can be used in local XSLT stylesheets.

Templating/UI
-------------

There's no real templating system. However, to
make skinning the application a little simpler,
there are [`header.inc.php`](ui/header.inc.php) and
[`footer.inc.php`](ui/footer.inc.php) includes in [`ui/`](ui/) directory.
The application itself creates a `<div id="validator">` and tries to
ensure that all jQuery and CSS selectors are locked to within that div.

What's here has been tested and works reasonably consistently with recent
versions of Chrome, Firefox, Edge & IE11. It makes use of some HTML5
elements and so may not work properly with older browsers.

Demo
----

A reasonably recent version of this code runs the SAFIRE metadata validator
at [https://validator.safire.ac.za/](https://validator.safire.ac.za/).

