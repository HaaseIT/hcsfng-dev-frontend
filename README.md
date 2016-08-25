# hcsfng-frontend

This is a standalone web-frontend supporting multple languages. It is fed by json files which are to be put in the repository folder. The repository folder can be in the local filesystem or even on a remote server using a https connection. See some examples in repository/_examples/.

Find the configuration files in app/config/, copy the *.dist.yml to *.yml but keep the *.dist.yml; these serve as fallbacks.

If you want to use custom directory names, you can configure these in app/config/constants.php.

The cache dir must be writable for the webserver.

This is meant to be fed by a backend which is still in progress of being written (hcsfng-backend).
