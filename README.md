# DevTrack

A modern project management application built with Laravel 13, Tailwind CSS, and Alpine.js.

## Features

- **Project Management**: Create, edit, archive, and restore projects
- **Task Management**: Full CRUD operations for tasks within projects
- **User Authentication**: Secure registration and login system
- **RESTful API**: Complete API endpoints for projects and tasks
- **Modern UI**: Beautiful gradient-based design with Tailwind CSS
- **Responsive**: Mobile-friendly interface

## Tech Stack

- **Backend**: Laravel 13.7
- **Database**: MySQL
- **Frontend**: Tailwind CSS, Alpine.js
- **Authentication**: Laravel Breeze
- **Icons**: Heroicons (via SVG)

## Routes

### Web Routes
| Method | URI | Description |
|--------|-----|-------------|
| GET | / | Redirects to register |
| GET | /register | User registration |
| GET | /login | User login |
| GET | /dashboard | User dashboard |
| GET | /projects | List all projects |
| GET | /projects/create | Create project form |
| GET | /projects/{project} | View project details |
| GET | /projects/{project}/edit | Edit project form |
| POST | /projects | Store new project |
| PUT/PATCH | /projects/{project} | Update project |
| DELETE | /projects/{project} | Archive project |
| GET | /archives/projects | View archived projects |
| PATCH | /archives/projects/{id}/restore | Restore archived project |
| GET | /projects/{project}/tasks | List project tasks |
| GET | /projects/{project}/tasks/create | Create task form |
| GET | /projects/{project}/tasks/{task} | View task details |
| GET | /projects/{project}/tasks/{task}/edit | Edit task form |
| POST | /projects/{project}/tasks | Store new task |
| PUT/PATCH | /projects/{project}/tasks/{task} | Update task |
| DELETE | /projects/{project}/tasks/{task} | Delete task |

### API Routes
| Method | URI | Description |
|--------|-----|-------------|
| GET | /api/v1/projects | List all projects |
| POST | /api/v1/projects | Create project |
| GET | /api/v1/projects/{project} | Get project |
| PUT/PATCH | /api/v1/projects/{project} | Update project |
| DELETE | /api/v1/projects/{project} | Delete project |
| GET | /api/v1/projects/{project}/tasks | List project tasks |
| POST | /api/v1/projects/{project}/tasks | Create task |
| GET | /api/v1/projects/{project}/tasks/{task} | Get task |
| PUT/PATCH | /api/v1/projects/{project}/tasks/{task} | Update task |
| DELETE | /api/v1/projects/{project}/tasks/{task} | Delete task |

## Installation

```bash
# Clone the repository
git clone <repository-url>
cd DEVTRACK

# Install dependencies
composer install
npm install

# Copy environment file
cp .env.example .env

# Configure database in .env
# DB_DATABASE=devtrack
# DB_USERNAME=root
# DB_PASSWORD=

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate

# Build assets
npm run dev

# Start the server
php artisan serve
```

## API Response Format

**Project Response:**
```json
{
  "data": {
    "id": 1,
    "title": "Project Name",
    "description": "Project description",
    "deadline": "2026-06-05",
    "created_at": "2026-05-06",
    "status": "Active",
    "tasks": [...]
  }
}
```

**Task Response:**
```json
{
  "data": {
    "id": 1,
    "title": "Task title",
    "description": "Task description",
    "status": "todo",
    "priority": "high",
    "deadline": "2026-05-10",
    "project": { "id": 1, "name": "Project Name" },
    "user": { "id": 14, "name": "John Doe" },
    "created_at": "2026-05-06 11:50"
  }
}
```

## Database Schema

### MCD (Modele Conceptuel de Données)

```
┌─────────────┐         ┌───────────────────┐         ┌─────────────┐
│    USER     │         │     PROJECT       │         │     TASK    │
├─────────────┤         ├───────────────────┤         ├─────────────┤
│ #id         │1       N│ #id               │1       N│ #id         │
│ name        ├─────────┤ title             ├─────────┤ title       │
│ email       │         │ description       │         │ description │
│ password    │         │ deadline          │         │ status      │
│ created_at  │         │ user_id (FK)      │         │ priority    │
│ updated_at  │         │ created_at       │         │ deadline    │
└─────────────┘         │ updated_at        │         │ project_id  │
      │                 │ deleted_at        │         │ user_id (FK)│
      │                 └───────────────────┘         │ created_at  │
      │                         ▲                     │ updated_at  │
      │                         │                     └─────────────┘
      │                         │                            ▲
      │                         └────────────────────────────┘
      │
      │ N
      ▼
┌──────────────────────────────────────────────────┐
│           PROJECT_USER (pivot table)             │
├──────────────────────────────────────────────────┤
│ #project_id (FK)  ◄──┐                           │
│ #user_id (FK)      ◄──┼──┐                       │
│ role                ◄──┼──┼──► (many-to-many)    │
│ created_at          ◄──┼──┘                       │
│ updated_at          ◄──┘                          │
└──────────────────────────────────────────────────┘
```

### MLD (Modele Logique de Données)

```sql
-- Table: users
-- CREATE TABLE users (
--     id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
--     name VARCHAR(255) NOT NULL,
--     email VARCHAR(255) UNIQUE NOT NULL,
--     email_verified_at TIMESTAMP NULL,
--     password VARCHAR(255) NOT NULL,
--     remember_token VARCHAR(100) NULL,
--     created_at TIMESTAMP NULL,
--     updated_at TIMESTAMP NULL
-- );

-- Table: projects
-- CREATE TABLE projects (
--     id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
--     title VARCHAR(255) NOT NULL,
--     description TEXT NULL,
--     deadline DATE NULL,
--     user_id BIGINT UNSIGNED NOT NULL,
--     created_at TIMESTAMP NULL,
--     updated_at TIMESTAMP NULL,
--     deleted_at TIMESTAMP NULL,
--     FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
-- );

-- Table: project_user (many-to-many)
-- CREATE TABLE project_user (
--     project_id BIGINT UNSIGNED NOT NULL,
--     user_id BIGINT UNSIGNED NOT NULL,
--     role VARCHAR(50) DEFAULT 'member',
--     created_at TIMESTAMP NULL,
--     updated_at TIMESTAMP NULL,
--     PRIMARY KEY (project_id, user_id),
--     FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
--     FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
-- );

-- -- Table: tasks
-- CREATE TABLE tasks (
--     id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
--     title VARCHAR(255) NOT NULL,
--     description TEXT NULL,
--     status ENUM('todo', 'in_progress', 'done') DEFAULT 'todo',
--     priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
--     deadline DATE NULL,
--     project_id BIGINT UNSIGNED NOT NULL,
--     user_id BIGINT UNSIGNED NULL,
--     created_at TIMESTAMP NULL,
--     updated_at TIMESTAMP NULL,
--     FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
--     FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
-- );

-- Table: password_reset_tokens (Laravel Breeze)
-- CREATE TABLE password_reset_tokens (
--     email VARCHAR(255) PRIMARY KEY,
--     token VARCHAR(255) NOT NULL,
--     created_at TIMESTAMP NULL
-- );

-- Table: sessions (Laravel Breeze)
-- CREATE TABLE sessions (
--     id VARCHAR(255) PRIMARY KEY,
--     user_id BIGINT UNSIGNED NULL,
--     ip_address VARCHAR(45) NULL,
--     user_agent TEXT NULL,
--     payload LONGTEXT NOT NULL,
--     last_activity INT NOT NULL,
--     INDEX idx_user_id (user_id)
-- );
```

### Relationships

| Relation | Type | Description |
|----------|------|-------------|
| User → Project | 1:N | A user can have multiple projects (as lead) |
| Project → Task | 1:N | A project can have multiple tasks |
| User → Task | 1:N | A user can be assigned to multiple tasks |
| Project ↔ User | N:N | A project can have multiple members |
| Task → User | N:1 | Each task is assigned to one user |

## License

MIT License