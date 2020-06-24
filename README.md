# laravel-chat

Build a chat app with Laravel, redis and socket

## Getting Started

```bash
git clone https://github.com/ycsiow888/simple-chat-app.git
```

```bash
composer install
```

Duplicate `.env.example` and rename it `.env`

Then run:

```bash
php artisan key:generate
```


#### Database Migrations

Be sure to fill in your database details in your `.env` file and create database 'simple-chat-app' before running the migrations:

```bash
php artisan migrate
```

And finally, start the application:

```bash
php artisan serve
```
Open another terminal and type
```bash
laravel-echo-server start
```

and visit [http://localhost:8000/](http://localhost:8000/) to see the application in action.
