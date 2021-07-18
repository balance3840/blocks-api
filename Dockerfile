FROM nginx:1.20 as nginx

COPY --from=repo.treescale.com/tfmblocks/blocks-php:latest --chown=nginx:nginx /var/www/blocks-api /var/www/blocks-api
COPY ./.docker/nginx/conf.d/default.conf /etc/nginx/conf.d/default.conf

RUN chmod -R 777 /var/www/blocks-api/

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]