# JapanTravelHR Back-end API (Laravel)

## Database setup
Install a sql relational database management system (ex mysql, mariadb, ...).

## .env file configuration
Duplicate the `.env.example` and name it `.env`.

Configure the access of database in this file.
```
DB_CONNECTION=<database_system>
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=<name_of_database>
DB_USERNAME=<user>
DB_PASSWORD=<password>
```

## Run installation script command
```
composer run install-project
```

## Run the project
```
php artisan serve
```
