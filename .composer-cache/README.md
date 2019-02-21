This file exists because Dockerfile doesn't support conditional COPY.
@see https://stackoverflow.com/a/46801962/292408

Nothing should ever be committed to this directory besides this file.
This directory is purely for the Docker image building process and for
it to store composer-cache at build time.
