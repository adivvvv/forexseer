# Real-Time Pipeline

This document describes the end-to-end real-time pipeline we’ve built for ForexSeer. It covers data ingestion, pub/sub bridging, broadcasting, WebSocket server setup, next steps, and a production runbook outline.

---

## 🚀 1. End-to-end Real-Time Pipeline

### 1.1. Data Ingestion (Laravel CLI)

**Command:**  
```bash
php artisan eodhd:listen \
  --endpoint=forex \
  --symbols="EURUSD,GBPUSD,USDJPY"
```

- Connects to EODHD WebSocket (`wss://…/forex`) via ReactPHP/Pawl  
- Subscribes to chosen symbols (e.g. `EURUSD,GBPUSD,USDJPY`)  
- Filters out non-tick (ACK) messages  
- **Publishes** each tick’s raw JSON to Redis channel `ticks`  
- Logs and prints each tick in your terminal  

### 1.2. Pub/Sub Bridge (Laravel CLI)

**Command:**  
```bash
php artisan redis:listen-ticks
```

- Uses a dedicated Redis connection (`subscriber` → DB 2) for `SUBSCRIBE`  
- Listens on Redis channel `ticks`  
- On each message, fires Laravel event:  
  ```php
  event(new RealTimeTickReceived($data));
  ```

### 1.3. Broadcasting (Laravel Events)

#### Event Class

**`app/Events/RealTimeTickReceived.php`** implements `ShouldBroadcastNow`:

```php
public function broadcastOn()
{
    return new Channel('ticks');
}

public function broadcastWith()
{
    return $this->tick; // raw tick payload
}
```

#### Configuration

- **`config/broadcasting.php`**  
  ```php
  'default' => env('BROADCAST_DRIVER', 'redis'),

  'connections' => [
    'pusher' => [ /* for Echo Server */ ],
    'redis'  => [
      'driver'     => 'redis',
      'connection' => env('BROADCAST_REDIS_CONNECTION', 'default'),
    ],
    'log'   => [ 'driver' => 'log' ],
    'null'  => [ 'driver' => 'null' ],
  ],
  ```

- **`.env`** entries:
  ```ini
  BROADCAST_DRIVER=redis
  BROADCAST_REDIS_CONNECTION=default

  REDIS_CLIENT=predis
  REDIS_DB=0
  REDIS_CACHE_DB=1
  REDIS_SUBSCRIBER_DB=2
  ```

### 1.4. WebSocket Server (Laravel Echo Server)

- Installed globally via NPM:  
  ```bash
  npm install -g laravel-echo-server
  ```
- **`laravel-echo-server.json`** configuration:
  ```jsonc
  {
    "authHost": "http://forexseer.test",
    "authEndpoint": "/broadcasting/auth",
    "clients": [
      { "appId": "local", "key": "local" }
    ],
    "database": "redis",
    "databaseConfig": {
      "redis": { "host": "127.0.0.1", "port": "6379", "password": null }
    },
    "devMode": true,
    "host": "127.0.0.1",
    "port": "6001",
    "protocol": "http",
    "subscribers": {
      "http": false,
      "redis": true
    }
  }
  ```
- **Startup Order**:
  1. `php artisan eodhd:listen …`  
  2. `php artisan redis:listen-ticks`  
  3. `laravel-echo-server start`  
  4. `npm run dev` & `php artisan serve`  

> At this point, any Vue3 component using  
> ```js
> window.Echo.channel('ticks')
>   .listen('RealTimeTickReceived', payload => { … })
> ```
> will receive every tick in real time.

---

## 📝 2. Next Steps

1. **Vue3 Components**  
   - Build a **MiniTicker** to render the last _N_ ticks  
   - Create a **Chart** component for historical OHLC data

2. **Historical Persistence & Aggregation**  
   - Persist raw ticks into MySQL table `real_time_ticks`  
   - Schedule cron/queue jobs to aggregate 1m, 5m, 15m, 1h OHLC  
   - Prune/archive old raw tick data

3. **Premium API & Auth**  
   - Switch to `ShouldBroadcast` + `queue:work` for queued broadcasts  
   - Issue API keys (e.g. Sanctum/Passport)  
   - Broadcast on private channels (`ticks.{symbol}`) with channel authorization

---

## 🔒 3. Production Runbook Outline

### 3.1. Infrastructure & Ports

| Service               | Default Port | Exposure       |
|-----------------------|-------------:|----------------|
| HTTP (Laravel/Nginx)  | 80 / 443     | Public         |
| Echo Server (WS)      |      6001    | Public         |
| Redis                 |      6379    | Local only     |
| MySQL                 |      3306    | Local only     |

- **Firewall**:  
  - Open **80/443**, **6001** externally  
  - Block Redis (6379) & MySQL (3306) from public access  

### 3.2. Long-Running Processes

Manage via Supervisor or systemd:

- **`eodhd:listen`** → auto-restart on failure  
- **`redis:listen-ticks`** → subscriber  
- **`queue:work`** → (for queued broadcasts)  
- **`laravel-echo-server start`** → WebSocket server  
- **Asset build**: `npm run production` or CI deploy

### 3.3. Security & Monitoring

- **SSL/TLS**: terminate HTTPS (and `wss://`) at Nginx  
- **Redis auth**: enable `requirepass`, bind to localhost  
- **Log rotation**: daily rotate `storage/logs`  
- **Metrics**: monitor Redis memory, queue lengths, process uptime  

&copy; ForexSeer Team
