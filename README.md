Docker + Composer = Awesome
===========================

You must have bash and docker to use this project. Tested on OSX using boot2docker v1.3.1

```bash
.
├── Dockerfile         ### The Dockerfile used to build the base image for this project
├── docker             ### Scripts related to managing/running docker images/containers
│   ├── run.sh         ### Run from the docker host, this script will build the image
│   │                  # if not found (by tag) and will run a container. Requires 2
│   │                  # parameters, username and password for a snapchat account
│   └── start.sh       ### The start script run on container start. This is run inside
│                      # the docker container.
│
└── src                ### The directory where the application runs. The application
    │                  # creates directories of the format YYYYMMDD in here every day
    │                  # it runs. It stores new story media in these directories.
    ├── composer.json  ### Defines requirements for this project
    ├── composer.lock  ### Defines specific versions required for this project
    ├── index.php      ### The application itself.
    ├── stories.json   ### Maintained by the application, this includes the recent
    │                  # recently downloaded file data. It is cleaned up every time
    │                  # the application runs to include only files downloaded in the
    │                  # last two days.
    └── vendor         ### Composer stores supporting libraries here.
```

## Usage:

```bash
$ docker/run.sh [snapchat_username] [snapchat_password]
Running phpsnapchat
f0728e59a8627ae3bb5b745bc52d2fab82c07877e2b837b13df9b6788b69e696
$ ls src/$(date +%Y%m%d)
ALL THE IMAGES IT JUST DOWNLOADED
```

## Specifics:

This project mounts $PROJECT_ROOT/src to /app in the container and also mounts $PROJECT_ROOT/docker to /docker.
Any changes you make to this code is avialable to the container when next it is run.
Also, any data written to the src directory in the container can be viewed from the host machine.

An update to use Cilex was applied. You can now customize the locations for file writes and where the log of downloaded stories is kept. This will require modifications to docker/start.sh.
