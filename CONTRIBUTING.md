# Contributing

## MySQL

To execute the test suite, you need a running MySQL server.

The easiest way to get them up and running is using Docker:

    docker run --rm --platform=linux/amd64 -e MYSQL_ROOT_PASSWORD=root -e MYSQL_DATABASE=odm -p 3306:3306 -d mysql

Then run the test suite:

    DATABASE_URL='mysql://root:root@127.0.0.1:3306/odm?serverVersion=8.0' ./vendor/bin/simple-phpunit

## Postgres

To execute the test suite, you need a running PostgreSQL server.

The easiest way to get them up and running is using Docker:

    docker run --rm -e POSTGRES_PASSWORD=postgres -p 5432:5432 -d postgres

Then run the test suite:

    DATABASE_URL='postgresql://postgres:postgres@127.0.0.1:5432/postgres?serverVersion=14&charset=utf8' ./vendor/bin/simple-phpunit
