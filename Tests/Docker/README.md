# Docker Containter with xdebug enabled

Provides a `build.sh` script to trigger builds for `php74` and `php80` tags locally.

To publish a tag use the following commands:

* Build image - `docker build -f <Your Dockerfile> -t registry.b13.com/jroth/typo3_composerize:<tag name> .`
* Push to registry - `docker push registry.b13.com/jroth/typo3_composerize:<tag name>` 
