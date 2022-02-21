git fetch --all
git reset --hard origin/master
composer dump-autoload
composer install

cd /
sudo chown -R :www-data var/www/html/pozzy_api
sudo chmod -R 775 var/www/html/pozzy_api/storage
sudo chmod -R 775 var/www/html/pozzy_api/bootstrap/cache
sudo chmod +x var/www/html/pozzy_api/deploy.sh
sudo chown -R www-data:www-data /var/www/html/pozzy_api/public
sudo chmod -R 777 var/www/html/pozzy_api/storage

cd var/www/html/pozzy_api

cp .env.demo .env

php artisan optimize:clear
# php artisan migrate
php artisan cache:clear
php artisan view:clear
php artisan config:cache
