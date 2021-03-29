#!/bin/bash

docker build --no-cache --pull . -f Dockerfile.php74 -t jroth/typo3_composerize:php74 && docker run --rm -it -v `pwd`/tests.sh:/tmp/test.sh --entrypoint "bash" jroth/typo3_composerize:php74 /tmp/test.sh
docker build --no-cache --pull . -f Dockerfile.php80 -t jroth/typo3_composerize:php80 && docker run --rm -it -v `pwd`/tests.sh:/tmp/test.sh --entrypoint "bash" jroth/typo3_composerize:php80 /tmp/test.sh
