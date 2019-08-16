## Tortuga Backend

### Deployment

Deploys current branch to `api.tatrgel.cz`

```
php artisan deploy
```

### Development - initial setup / reset

```
php artisan migrate:refresh
php artisan db:seed
php artisan tortuga:import:products
```

Eventually can also create fake customers and orders

```
php artisan db:seed --class=CustomersSeeder
php artisan db:seed --class=OrdersSeeder
```

### Development - working with queue

```
# run container
docker run -d -p 6379:6379 --name redis1 redis

# let's test it
docker exec -it redis1 sh
redis-cli
ping

# should get PONG
```

Set your `.env` key `QUEUE_CONNECTION=redis` and run

```
php artisan horizon
```

Then can visit http://project.test/horizon. More info
https://laravel.com/docs/5.8/horizon