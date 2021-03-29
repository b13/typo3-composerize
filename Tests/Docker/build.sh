#!/bin/bash

docker build --no-cache --pull . -f Dockerfile.php74 -t jroth/typo3_composerize:latest && docker run --rm -it -v `pwd`/tests.sh:/tmp/test.sh --entrypoint "bash" jroth/typo3_composerize:latest /tmp/test.sh
docker images | grep gitlab-composer
