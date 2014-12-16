#!/bin/bash

DOCKER_IMAGE_TAG="phpsnapchat"
DOCKER_CONTAINER="snapdownloader"
SNAPCHAT_USERNAME=$1
SNAPCHAT_PASSWORD=$2

DOCKER_OPTIONS="-d" # reasonable additional options can be set here. My advice is to use -d or --rm.

DOCKER=$(which docker)
if [ -z "$DOCKER" ]; then
    echo "you must have docker installed"
    exit 1
fi

HAS_SNAP_DOWNLOADER_CONTAINER=$($DOCKER ps -a | grep "$DOCKER_CONTAINER")
if [ -n "$HAS_SNAP_DOWNLOADER_CONTAINER" ]; then
    echo "'$HAS_SNAP_DOWNLOADER_CONTAINER'"
    echo "$DOCKER_CONTAINER already exists, starting it up again"
    $DOCKER start $DOCKER_CONTAINER
else

    PROJECT_ROOT=$(git rev-parse --show-toplevel)

    HAS_SNAP_DOWNLOADER=$($DOCKER images | grep "$DOCKER_IMAGE_TAG")
    if [ -z "$HAS_SNAP_DOWNLOADER" ]; then
        # ensure we're at the top level dir for the project where the Dockerfile should be
        pushd $PROJECT_ROOT
        echo "$DOCKER_IMAGE_TAG not found. Building $DOCKER_IMAGE_TAG from $PROJECT_ROOT/Dockerfile"
        $DOCKER build --tag="$DOCKER_IMAGE_TAG" .
        popd
    fi

    echo "Running $DOCKER_IMAGE_TAG"
    $DOCKER run \
        $DOCKER_OPTIONS \
        -e "SNAPCHAT_USERNAME=$SNAPCHAT_USERNAME" \
        -e "SNAPCHAT_PASSWORD=$SNAPCHAT_PASSWORD" \
        --volume="$PROJECT_ROOT/src:/app" \
        --volume="$PROJECT_ROOT/docker:/docker" \
        --name="$DOCKER_CONTAINER" \
        $DOCKER_IMAGE_TAG
fi
