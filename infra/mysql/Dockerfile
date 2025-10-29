FROM mysql:8.4

ENV TZ=Asia/Tokyo

COPY ./my.cnf /etc/mysql/conf.d/custom.cnf
RUN chmod 644 /etc/mysql/conf.d/custom.cnf