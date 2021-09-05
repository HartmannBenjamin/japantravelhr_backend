<p align="center">
  <img src="http://api.benjamin-hartmann.fr/logo/logo.png"  alt=""/>
</p>

# JapanTravelHR Back-end API (Laravel)

## ➤ Database setup
- Install a sql relational database management system (ex mysql, mariadb, ...).

## ➤ .env file configuration
- Duplicate the `.env.example` and name it `.env`.

- Configure the database access in this file.
```
DB_CONNECTION=<database_system>
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=jptravel_hr
DB_USERNAME=<user>
DB_PASSWORD=<password>
```

## ➤ Run installation script command
```
composer run install-project
```

## ➤ Run npm installation
```
npm install
```

## ➤ Run the project
```
php artisan serve
```

---

## ➤ Default users informaiton
```
User :
    - email: user@japantravel.com
    - password: 1234
    
HR staff :
    - email: hr@japantravel.com
    - password: 1234
    
Manager :
    - email: manager@japantravel.com
    - password: 1234
```

## ➤ Launch tests
```
php artisan test
```

## ➤ Links

- [Postman Workspace (Endpoint collection)](https://www.postman.com/benjaminhartmann/workspace/japantravelhr/overview)
- [API Documentation](https://documenter.getpostman.com/view/17271595/TzzHksRe)
- [Front-end git repository](https://github.com/HartmannBenjamin/japantravelhr_client)
- [Back-end git repository](https://github.com/HartmannBenjamin/japantravelhr_backend)
