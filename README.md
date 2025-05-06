<p align="center"><a href="#" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

# API data importer v3
## Laravel-приложения для импорта API-данных с поддержкой аккаунтов и токенов (Сервер)
**(v1 – https://github.com/Constantine1995/data-importer)**<br><br>
**(v2 – https://github.com/Constantine1995/data-importer-v2)**<br><br>

#### Реализован импорт данных:
- Продажи
- Заказы
- Склады
- Доходы

## Стек:
- `php 8.2`
- `Laravel 12`
- `Docker`

## Установка:
1. Склонировать репозиторий: `git clone https://github.com/Constantine1995/data-importer-v3.git`
2. Скопировать `.env.example` в `.env`: `cp .env.example .env`
3. Добавить ваш `API_KEY` в `.env`
4. Выполнить команду: `docker-compose up --build`
5. Выполнить основные команды для создания записей в таблицах (описаны ниже)
6. Для тестирования `Scheduler`, можно использовать закомментированные команды в `routes/console`

### Доступ к БД:
   **Хост/IP:** ip_address <br>
   **Порт:** 8083<br>
   **Имя БД:** laravel<br>
   **Пользователь:** root<br>
   **Пароль:** rootsecret<br>

### Основные команды:
**Создать Компанию:**<br>
```bash
docker exec -it api-queue php artisan company:create name:"CompanyName"
```
**Создать аккаунт:**<br>

```bash
docker exec -it api-queue php artisan account:create company_name:"CompanyName" name:"Name"
```

**Создать Api сервисы:**<br>
```bash
docker exec -it api-queue php artisan api-service:create name:"Orders" description:"Orders service"
docker exec -it api-queue php artisan api-service:create name:"Incomes" description:"Incomes service"
docker exec -it api-queue php artisan api-service:create name:"Stocks" description:"Stocks service"
docker exec -it api-queue php artisan api-service:create name:"Sales" description:"Sales service"
```

**Создать тип токена:**<br>
```bash
docker exec -it api-queue php artisan token-type:create name:"bearer"
docker exec -it api-queue php artisan token-type:create name:"api-key"
docker exec -it api-queue php artisan token-type:create name:"login-password"
```

**Создать связь сервисов с типом токена:**<br>
```bash
docker exec -it api-queue php artisan api-service-token-type:create api_service:"Orders" token_type:"bearer"
docker exec -it api-queue php artisan api-service-token-type:create api_service:"Orders" token_type:"login-password"
docker exec -it api-queue php artisan api-service-token-type:create api_service:"Orders" token_type:"api-key"
docker exec -it api-queue php artisan api-service-token-type:create api_service:"Sales" token_type:"api-key"
docker exec -it api-queue php artisan api-service-token-type:create api_service:"Incomes" token_type:"bearer"
docker exec -it api-queue php artisan api-service-token-type:create api_service:"Stocks" token_type:"login-password"
```

**Создать токены для сервисов. (Пример на Orders):**<br>
```bash
docker exec -it api-queue php artisan api-token:create id:1 api_service:Orders type_token:bearer
docker exec -it api-queue php artisan api-token:create id:1 api_service:Orders type_token:api-key
docker exec -it api-queue php artisan api-token:create id:1 api_service:Orders type_token:login-password login:myLogin password:myPassword
```

### Запуск команд вручную:
**Импорт данных из API сервисов в локальную БД:**<br>
```bash
docker exec -it api-queue php artisan api:sync --date-from=2025-04-01 --date-to=2025-05-01
```
**Синхронизация данных из локальной БД сервисов с аккаунтами:**<br>
```bash
docker exec -it api-queue php artisan replicate:orders --date-from=2025-04-01 --date-to=2025-05-01
docker exec -it api-queue php artisan replicate:sales --date-from=2025-04-01 --date-to=2025-05-01
docker exec -it api-queue php artisan replicate:incomes --date-from=2025-04-01 --date-to=2025-05-01
docker exec -it api-queue php artisan replicate:stocks
```

### Endpoints:
- `http://ip_address:8082/api/orders`
- `http://ip_address:8082/api/sales`
- `http://ip_address:8082/api/incomes`
- `http://ip_address:8082/api/stocks`

**Пример запроса**
Для получения списка заказов используйте эндпоинт `/api/orders`. Запрос требует авторизации через Bearer-токен или (API-KEY, Login:Password Basic Auth) и передачи параметров в формате формы.
```bash
curl --location 'http://ip_address:8082/api/orders' \
  --header 'Accept: application/json' \
  --header 'Authorization: Bearer f92xpD43jFEqUyMfgLk83TI3YQvQojzhHsN5PZUW5CbQKWHf8FtLPKzpO60S' \
  --form 'dateFrom="2025-04-01"' \
  --form 'dateTo="2025-05-01"' \
  --form 'limit="1"'
 ```

### Tables:
- `incomes`
- `orders`
- `sales`
- `stocks`
- `income_accounts`
- `order_accounts`
- `sale_accounts`
- `stock_accounts`
- `accounts`
- `companies`
- `api_service_token_types`
- `api_services`
- `api_tokens`
- `token_types`

### Command class:
- `SyncApiData` – Команда Laravel для синхронизации данных с API. Выполняет запуск синхронизации данных за указанный период **(--date-from, --date-to)**
- `ReplicateIncomesToAccounts` – Добавляет данные из `incomes` в `income_accounts` для всех аккаунтов
- `ReplicateOrdersToAccounts` – Добавляет данные из `orders` в `order_accounts` для всех аккаунтов
- `ReplicateSalesToAccounts` – Добавляет данные из `sales` в `sale_accounts` для всех аккаунтов
- `ReplicateStocksToAccounts` – Добавляет данные из `stocks` в `stock_accounts` для всех аккаунтов
- `AccountCreate` – Создает новый аккаунт для компании
- `CompanyCreate` – Создает компанию
- `ApiServiceCreate` – Создает новый API-сервис (для аккаунтов)
- `ApiServiceTokenTypeCreate` – Связь API-сервиса с типом токена
- `ApiTokenCreate` – Создает API-токен для аккаунтов
- `TokenTypeCreate` – Создает тип токенов

### Token type:
- `bearer`
- `api-key`
- `login-password`

### Services:
- `ApiService` – Сервис для работы с API. Отвечает за выполнение HTTP-запросов к внешнему API с помощью **GuzzleHttp**
- `IncomesSyncService` – Сервис для синхронизации данных о `доходах` с API
- `OrdersSyncService` – Сервис для синхронизации данных о `заказах` с API
- `SalesSyncService` – Сервис для синхронизации данных о `продажах` с API
- `StocksSyncService` – Сервис для синхронизации данных о `складах` с API
- `LogSyncService` – Абстрактный базовый класс для логирования операций синхронизации.

### Controllers:
- `IncomeApiController` – Выводит JSON ответ с данными о `доходах` с помощью токена, тип токена регулируется в таблице `api-service-token-type`
- `OrderApiController` – Выводит JSON ответ с данными о `заказах` с помощью токена, тип токена регулируется в таблице `api-service-token-type`
- `SaleApiController` – Выводит JSON ответ с данными о `продажах` с помощью токена, тип токена регулируется в таблице `api-service-token-type`
- `StockApiController` – Выводит JSON ответ с данными о `складах` с помощью токена, тип токена регулируется в таблице `api-service-token-type`

### Models:
- `Income`
- `Order`
- `Sale`
- `Stock`
- `Account`
- `ApiService`
- `ApiServiceTokenType`
- `ApiToken`
- `Company`
- `IncomeAccount`
- `OrderAccount`
- `SaleAccount`
- `StockAccount`
- `TokenType`

### Middleware:
- `ApiTokenHeaderAuthMiddleware`

### Jobs:
- `ProcessIncomeAccountChunkJob`
- `ProcessOrderAccountChunkJob`
- `ProcessSaleAccountChunkJob`
- `ProcessStockAccountChunkJob`
- `ReplicateIncomesJob`
- `ReplicateOrdersJob`
- `ReplicateSalesJob`
- `ReplicateStocksJob`

### Repositories:
- `IncomeRepository`
- `OrderRepository`
- `SaleRepository`
- `StockRepository`

### Requests:
- `IncomeApiRequest`
- `OrderApiRequest`
- `SaleApiRequest`
- `StockApiRequest`

### Resources:
- `IncomeCollection`
- `OrderCollection`
- `SaleCollection`
- `StockCollection`
