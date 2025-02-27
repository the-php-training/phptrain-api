FROM node:20

WORKDIR /var/www

ADD . /var/www

CMD tail -f /dev/null
