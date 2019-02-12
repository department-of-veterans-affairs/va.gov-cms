FROM alpine:3.9

RUN apk update && \
    echo "http://dl-cdn.alpinelinux.org/alpine/edge/community/" >> /etc/apk/repositories && \
    apk add --update autossh openssh-client bash && \
    rm -rf /var/lib/apt/lists/*

EXPOSE 2001

ADD ./entrypoint.sh /entrypoint.sh   
RUN chmod 755 /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]
