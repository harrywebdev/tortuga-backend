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