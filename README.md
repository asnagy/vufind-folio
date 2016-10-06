# vufind-folio
scripts for connecting VuFind with the FOLIO platform

## Requirements

* PEAR
* HTTP_Request
* File_MARC

## Configuration

As FOLIO is multi-tenant, you will need to specify the tenant that you are interacting with. You will need to manually change the `$tenant` variable within the PHP scripts.

## Usage

To load MARC records into FOLIO, take the following steps:
# create a MARC file named `load.mrc`
# Run the folio-load.php script
```
$ php folio-load.php
```

To import bibliographic records from FOLIO into VuFind, run the `folio-vufind-solr.php` script:
```
$ php folio-vufind-solr.php
```
