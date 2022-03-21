# Update PATH to put the apps bin in front of system path.
ARG BASE_IMAGE
FROM $BASE_IMAGE
ENV PATH=/var/www/html/vendor/bin:/var/www/html/bin:${PATH}
#RUN sudo dpkg -i $(curl -w "%{filename_effective}" -LO $(curl -s https://api.github.com/repos/DataDog/dd-trace-php/releases | grep browser_download_url | grep 'amd64[.]deb' | head -n 1 | cut -d '"' -f 4))
RUN sudo php $(curl -w "%{filename_effective}" -LO $(curl -s https://api.github.com/repos/DataDog/dd-trace-php/releases | grep browser_download_url | grep 'setup[.]php' | head -n 1 | cut -d '"' -f 4)) --enable-profiling --php-bin=$(basename $(realpath $(which php)))
