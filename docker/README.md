The files Dockerfile-appserver and Dockerfile-database cannot be inside 
the images folder. It results in an error of:
> Forbidden path outside the build context

@see https://stackoverflow.com/a/34392052