# Sun Store Backend – Technical Documentation

## Links
### Live Demo https://frontend-229302450163.europe-central2.run.app/
### Frontend Repository https://github.com/AleksanderSzutBackupAccount/sun-store-frontend
___

## Table of Contents
1. [Overview](#1-overview)
2. [Architecture](#2-architecture)
    - [2.1 System Context (C4 Level 1)](#21-system-context-diagram-ascii--c4-level-1)
    - [2.2 Container Diagram (C4 Level 2)](#22-container-diagram-ascii--c4-level-2)
    - [2.3 Component Diagram – Store Search (C4 Level 3)](#23-component-diagram-ascii--c4-level-3-store-search)
3. [Bounded Contexts](#3-bounded-contexts)
4. [Local Development (Laravel Sail)](#4-local-development--laravel-sail)
5. [API Documentation](#5-api-documentation)
    - [5.1 Healthcheck](#51-healthcheck)
    - [5.2 Product Filters](#52-product-filters)
    - [5.3 Product Search](#53-product-search)
    - [5.4 Product Details](#54-product-details)
6. [Technology Stack](#6-technology-stack)
7. [Deployment (Cloud Run)](#7-deployment--cloud-run)
8. [Known Limitations](#8-known-limitations)
9. [TODO / Future Improvements](#9-todo--future-improvements)
10. [License](#10-license)
---

## 1. Overview

Sun Store Backend is a modular backend system built using Domain-Driven Design (DDD), CQRS, MySQL, Elasticsearch, and Redis.  
It powers two separate bounded contexts:

- **Backoffice** – writes product/catalog data to MySQL and triggers indexing.
- **Store** – exposes a high-performance Elasticsearch-backed product search API.

The system includes:

- DDD + CQRS
- MySQL write models
- Elasticsearch read models
- Dynamic filter generation based on aggregations
- Cursor-based pagination
- Redis caching
- Local dev via Laravel Sail
- Production deployment via Cloud Run

---

## 2. Architecture

### 2.1 System Context Diagram (ASCII – C4 Level 1)

```
                     +----------------------+
                     |      Customer        |
                     |  Uses Store Frontend |
                     +----------+-----------+
                                |
                                | browses products
                                v
                   +-------------------------------+
                   |       Store Search API        |
                   |   Elasticsearch + Redis       |
                   +-------------------------------+
                                ^
                                |
               manages catalog  |
                                |
                     +----------+-----------+
                     |  Backoffice Admin    |
                     | Manages catalog data |
                     +----------------------+

   +---------------------+        +----------------------+
   |       MySQL         |<------>|   Backoffice API     |
   |   (Write Model)     | writes | CRUD for products    |
   +---------------------+        +----------------------+
                                          |
                                indexes   |
                                          v
                                +----------------------+
                                |   Elasticsearch       |
                                | (Read/Search Model)   |
                                +----------------------+
                                          ^
                                          |
                                +----------------------+
                                |       Redis          |
                                |     (Cache)          |
                                +----------------------+
```

---

### 2.2 Container Diagram (ASCII – C4 Level 2)

```
                    +--------------------------------------------+
                    |             Sun Store Backend               |
                    |---------------------------------------------|
                    |                                             |
                    |   +--------------------------------------+  |
                    |   |         Laravel API (PHP-FPM)        |  |
                    |   | - Backoffice HTTP endpoints          |  |
                    |   | - Store search HTTP endpoints        |  |
                    |   +----------------------+---------------+  |
                    |                          |                  |
                    |                          | queue jobs       |
                    |                          v                  |
                    |   +--------------------------------------+  |
                    |   |        Indexing Worker (CLI)         |  |
                    |   | Supervisor: runs sync/index tasks    |  |
                    |   +--------------------------------------+  |
                    |                                             |
                    +------------------+------------+-------------+
                                       |            |
                                       | SQL        | HTTP
                                       |            |
                         +-------------+            +----------------+
                         v                                         v
                 +------------------+                   +--------------------+
                 |      MySQL       |                   |   Elasticsearch    |
                 |   Write Model    |                   |   Read Model       |
                 +------------------+                   +--------------------+
                                                               ^
                                                               |
                                                     +--------------------+
                                                     |      Redis         |
                                                     |      Cache         |
                                                     +--------------------+
```

---

### 2.3 Component Diagram (ASCII – C4 Level 3: Store Search)

```
 +--------------------------------------------------------------+
 |                 Laravel API – Store Context                  |
 |--------------------------------------------------------------|
 |                                                              |
 |  +----------------------------+                              |
 |  | ProductSearchController   |------------------------------+
 |  +--------------+-------------+                              |
 |                 | calls                                         
 |                 v                                               
 |  +----------------------------+                              |
 |  | ProductSearchHandler       |                              |
 |  +--------------+-------------+                              |
 |                 |                                               
 |                 | uses CacheMiddleware                          
 |                 v                                               
 |  +----------------------------+                              |
 |  |      CacheMiddleware       |-----> Redis                  |
 |  +--------------+-------------+                              |
 |                 | queries ES                                  
 |                 v                                               
 |  +----------------------------+                              |
 |  | ProductSearchElasticRepo   |-------> Elasticsearch        |
 |  +--------------+-------------+                              |
 |                 | returns raw hits                            
 |                 v                                               
 |  +----------------------------+                              |
 |  | ProductResultMapper        |                              |
 |  +----------------------------+                              |
 |                                                              |
 +--------------------------------------------------------------+
```

---

## 3. Bounded Contexts

### Backoffice Context
- Source of truth for products and categories
- MySQL Eloquent models
- Commands:
    - CreateCategory
    - CreateProduct
- Responsible for creating indexing jobs into ES

### Store Context
- High-performance product search
- Elasticsearch as read model
- Dynamic filters from aggregations
- Cursor-based pagination
- Redis cache middleware

*Note:* Backoffice search reuses Backoffice Eloquent models intentionally (time constraints).

---

## 4. Local Development – Laravel Sail

### Start environment
```
./vendor/bin/sail up -d
```

### Migrate database
```
./vendor/bin/sail artisan migrate
```

### Seed product catalog
```
./vendor/bin/sail artisan import:products
```

### Reindex Elasticsearch
```
./vendor/bin/sail artisan reindex:product
```

### Run tests
```
./vendor/bin/sail artisan test
```

### ⚠ Notes
Local Sail environment **differs** from production container stack (Cloud Run + Nginx + PHP-FPM + Supervisor).  
This may lead to unexpected differences in caching, performance, and filesystem behaviour.

---

## 5. API Documentation

### 5.1 Healthcheck
```
GET /api/health
```

---

### 5.2 Product Filters
```
GET /api/search/products/filters
```

Example response:
```json
{
  "category": {
    "ui": "select",
    "values": ["batteries", "connectors", "solar_panels"]
  },
  "manufacturer": {
    "ui": "select_many",
    "values": ["AmpJoin","PV-Link","SafeLock","AmpereDrive","Apex Solar"]
  },
  "price": {
    "ui": "range",
    "unit": "zł",
    "min": 6,
    "max": 1999
  },
  "attr_power_output": {
    "ui": "range",
    "unit": "W",
    "min": 300,
    "max": 600
  }
}
```

---

### 5.3 Product Search
```
GET /api/search/products
```

Query parameters:

| Param | Description |
|-------|-------------|
| `cursor` | Cursor ID for pagination |
| `search` | Full-text query |
| `sort_by` | Field (price, created_at) |
| `sort_order` | asc / desc |
| `filters[price][]` | Range min/max |
| `filters[manufacturer][]` | Multi-select |
| `filters[attr_*][]` | Dynamic attributes |

Response example:
```json
{
  "meta": { "next_cursor": "...", "per_page": 15, "count": 15 },
  "filters": { ... },
  "data": [ {
      "id": "4ba518ea-c04d-47bd-9b6d-0a84130aed52",
      "name": "AuraPower Reserve 15",
      "category": "batteries",
      "price": 1999,
      "description": "Achieve energy independence with the AuraPower Reserve 15, a scalable 15kWh storage solution. Its modular, stackable design allows for easy installation and future expansion.",
      "manufacturer": "AuraPower",
      "created_at": "2025-11-21T17:53:22+00:00",
      "attr_capacity": 15
  } ]
}
```

---

### 5.4 Product Details
```
GET /api/search/products/{id}
```

Response example:
```json 
{
      "id": "4ba518ea-c04d-47bd-9b6d-0a84130aed52",
      "name": "AuraPower Reserve 15",
      "category": "batteries",
      "price": 1999,
      "description": "Achieve energy independence with the AuraPower Reserve 15, a scalable 15kWh storage solution. Its modular, stackable design allows for easy installation and future expansion.",
      "manufacturer": "AuraPower",
      "created_at": "2025-11-21T17:53:22+00:00",
      "attr_capacity": 15
},
```
---

## 6. Technology Stack

- PHP 8.3
- Laravel 12
- MySQL 8
- Elasticsearch 8
- Redis
- Docker
- Laravel Sail
- Supervisor
- Cloud Run
- DDD + CQRS
- PHPUnit, PHPStan, Pint, Deptrac

---

## 7. Deployment – Google Cloud Run

Production stack:

- Nginx
- PHP-FPM
- Supervisor (queue workers, indexing worker)
- Private Redis via VPC Connector
- Cloud SQL (MySQL)
- External Elasticsearch cluster

Healthcheck path:

```
/api/health
```

Redis connectivity requires:

- Private VPC connector
- Egress rule for internal IP
- Correct REDIS_HOST and REDIS_PASSWORD

---

## 8. Known Limitations

- Backoffice search still uses Eloquent models (not ES).
- Index versioning is minimal.
- No bulk indexing for high-volume updates.
- ES relevance model simplified for the recruitment task.
- Sail ≠ Production environment.

---

## 9. TODO / Future Improvements

- Add proper read models for Backoffice queries
- Add snapshot-based ES index versioning
- Implement search synonyms and boosting
- Add OpenTelemetry tracing
- Add contract tests between Store and Backoffice
- Graceful ES index rebuild strategy
- Multi-language search support

---

## 10. License

This project is part of a recruitment task.  
All rights reserved.
