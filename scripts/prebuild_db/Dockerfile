FROM drud/ddev-dbserver-mariadb-10.3:v1.19.3 as builder

# From https://serverfault.com/a/915845
RUN ["sed", "-i", "s/exec \"$@\"/echo \"not running $@\"/", "/usr/local/bin/docker-entrypoint.sh"]
ENV MYSQL_ROOT_PASSWORD=root
RUN curl https://dsva-vagov-prod-cms-backup-sanitized.s3-us-gov-west-1.amazonaws.com/database/cms-prod-db-sanitized-latest.sql.gz | gunzip > /docker-entrypoint-initdb.d/va.sql
RUN ["/usr/local/bin/docker-entrypoint.sh", "mysqld", "--datadir", "/initialized-db", "--aria-log-dir-path", "/initialized-db"]

FROM drud/ddev-dbserver-mariadb-10.3:v1.19.3
COPY --from=builder /initialized-db /var/lib/mysql
