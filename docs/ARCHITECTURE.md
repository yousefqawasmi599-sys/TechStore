# Architecture

TechStore uses PHP templates and PDO without a framework or frontend build system.

```mermaid
flowchart LR
    Visitor[Arabic RTL browser] --> PHP[PHP pages and POST handlers]
    PHP --> Shared[Session / CSRF / validation helpers]
    PHP --> PDO[PDO prepared statements]
    PDO --> DB[(MariaDB / InnoDB)]
    Admin[Administrator] --> Guard[Session and database role verification]
    Guard --> PHP
```

## Data model

```mermaid
erDiagram
    brands ||--o{ products : contains
    products ||--o{ order_items : references
    orders ||--|{ order_items : contains
    brands { int id PK string name string logo_url }
    products { int id PK int brand_id FK string name decimal price string image_url }
    orders { int id PK string name string phone decimal total_amount string status }
    order_items { int id PK int order_id FK int product_id FK int quantity decimal price }
    users { int id PK string email UK string password string user_type }
    auth_attempts { string attempt_key PK int attempts bigint reset_at }
```

Orders support guest checkout: contact details belong to the order, so there is no user foreign key. Each line stores its price at purchase time. Administrator access relies on `user_type`; the legacy `role` column remains for compatibility with the original schema.

## Order creation

`place_order.php` validates delivery data, opens a transaction, reads and locks the current product records, computes the total in cents, inserts the order and its lines, then commits. It only clears the cart after success. The browser's submitted total is never trusted.

## Public/private boundary

Serve only `public`. `.runtime`, `backups`, tests, tooling, and SQL scripts belong outside the document root. The release archive is built from an explicit list of public project paths; private data is not copied.

## Screenshots

Screenshots were captured from the actual application using an isolated database initialized from `database/schema.sql` and `database/seed.sql`. Customer names, phone numbers, and orders shown there are fictional.
