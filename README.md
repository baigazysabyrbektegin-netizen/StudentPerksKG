# StudentPerks KG

StudentPerks KG is a PHP/MySQL directory for student software, discounts, courses, subscriptions, and other benefits.

## Local Laragon setup

1. Place this folder inside Laragon's web root, usually `C:\laragon\www`, or create a Laragon virtual host pointing to this folder.
2. Start Apache and MySQL from Laragon.
3. Open phpMyAdmin from Laragon.
4. Import [`database/schema.sql`](database/schema.sql). The script creates the `studentperks_kg` database, tables, categories, and clearly marked sample records.
5. If your local MySQL credentials differ from Laragon's default, set `DB_HOST`, `DB_NAME`, `DB_USER`, and `DB_PASS` in the environment or update the local configuration before running the site.
6. Visit `http://studentperks-kg.test/` or the local URL configured by Laragon.

The default local connection expects MySQL at `127.0.0.1`, database `studentperks_kg`, user `root`, and an empty password. Do not use those defaults in production.

## Current features

- Responsive homepage and shared navigation.
- MySQL schema with foreign keys, indexes, full-text search, and sample benefit records.
- PDO connection using exceptions and native prepared statements.
- Server-side benefit search, category/type/availability filters, and sorting.
- Sample benefit records are marked `needs_verification` until an administrator confirms Kyrgyzstan availability.

The authentication, saved benefits, benefit details, reporting, and admin dashboard are included in the current implementation.

## Creating an administrator

Do not hardcode a production administrator password. After importing the schema, create a normal account through `register.php`, then promote that account locally from phpMyAdmin:

```sql
UPDATE users SET role = 'admin' WHERE email = 'your-local-admin@example.com';
```

Use a unique local password and remove or change administrator access when testing is complete.
