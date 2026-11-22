# ReFind — Campus Lost & Found Platform

> "Lost something? ReFind it."

ReFind is a database-driven web application that centralizes reporting, searching, claiming, and returning lost and found items across the Dong-Eui University campus.

## 📌 Project Overview

| | |
|---|---|
| **Course** | Web Programming 2 (Course 505514) |
| **Institution** | Dong-Eui University |
| **Instructor** | Prof. Hamdi Abdurhman |

## 👥 Team Members & Roles

| Member | Role |
|---|---|
| Yigitaliev Mukhsinjon | Backend Lead |
| Yigitalieva Mubina | Database Lead |
| Abdumutallibov Muhammadali | Frontend Lead |
| Nuriddinov Asror | Auth & Security Lead |

## 🧱 Tech Stack

- **Backend:** PHP (PDO prepared statements)
- **Database:** MySQL (InnoDB, `utf8mb4`)
- **Frontend:** HTML5, CSS3 (JavaScript planned)
- **Environment:** XAMPP (Apache + MySQL)

## 📁 Current Project Structure

```text
refind/
├── .gitignore
├── README.md
├── config/
│   ├── config.example.php   # configuration template
│   └── config.php           # local config (gitignored)
├── database/
│   └── schema.sql           # DDL + seed data
├── includes/
│   ├── auth.php             # session bootstrap + auth helpers
│   ├── db.php               # PDO connection
│   ├── footer.php           # shared footer template
│   └── header.php           # shared header/navigation template
└── public/
    ├── index.php            # home page
    └── assets/
        ├── css/
        │   └── style.css    # theme styles (navy/blue accent)
        ├── js/              # (empty — reserved)
        └── uploads/
            └── .gitkeep
```

## 🗄️ Database Architecture

`database/schema.sql` defines a normalized InnoDB schema (`utf8mb4`) with six tables:

| Table | Purpose |
|---|---|
| `users` | Registered users — roles (`admin`/`user`), unique `email` and `student_id`, hashed passwords |
| `categories` | Item categories (seeded with 7 values) |
| `locations` | Campus locations (seeded with 4 values) |
| `listings` | Lost/found item reports linked to a reporter, category, and location |
| `claims` | Ownership claims on a listing (unique per listing + claimant) |
| `returns` | Completed handovers of claimed items |

Relationships are enforced with foreign keys (`CASCADE` / `RESTRICT` / `SET NULL`) and unique constraints, so referential integrity is maintained end-to-end.

## 🚀 Local Setup (XAMPP)

1. **Clone the repository** into XAMPP's web root:

   ```bash
   git clone <repository-url> C:/xampp/htdocs/refind
   ```

2. **Configure the database connection** — copy the template and adjust if needed:

   ```bash
   copy config/config.example.php config/config.php
   ```

   Defaults target a local XAMPP MySQL (`localhost`, database `refind`, user `root`, empty password).

3. **Import the schema** in phpMyAdmin:

   - Open `http://localhost/phpmyadmin`
   - Create/select the `refind` database (or let the script create it)
   - Go to the **Import** tab and upload `database/schema.sql`

   Alternatively, run it from the command line:

   ```bash
   mysql -u root -p < database/schema.sql
   ```

   The script creates the `refind` database, all six tables, and the seed data for categories and locations.

4. **Verify the connection** by opening:

   ```
   http://localhost/refind/public/index.php
   ```

   You should see the ReFind home page with the navigation bar and hero section.

## 📈 Development Progress

- [x] Core Scaffolding — directory structure, config, shared includes
- [x] PDO Connection — `includes/db.php`
- [x] Database Schema — `database/schema.sql` (6 tables + seed data)
- [ ] Authentication — login / register / logout
- [ ] Item Listings — lost & found CRUD
- [ ] Claims & Returns — claim review and item handover
- [ ] File Uploads — item image uploads
