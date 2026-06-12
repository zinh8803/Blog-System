# Yii2 Blog API

REST API Blog System built with Yii2.

## Requirements

* PHP >= 8.2
* Composer
* MySQL
* Yii2 Basic
* Laragon/XAMPP hoặc môi trường PHP local

## Setup Project

### 1. Clone project

```bash
git clone https://github.com/zinh8803/Blog-System.git
cd Blog-System
```

### 2. Install dependencies

```bash
composer install
```

### 3. Create `.env`

Copy file example:

```bash
cp .env.example .env
```

Generate cookie validation key:

```bash
php -r "echo bin2hex(random_bytes(32));"
```

Cấu hình database:

```env
DB_DSN=mysql:host=localhost;dbname=yii2_blog
DB_USERNAME=root
DB_PASSWORD=

COOKIE_VALIDATION_KEY=your-secret-key
```

### 4. Run migration

```bash
php yii migrate
```

Chạy migration RBAC Yii2:

```bash
php yii migrate --migrationPath=@yii/rbac/migrations
```

### 5. Seed RBAC and users

Khởi tạo roles, permissions, owner rules:

```bash
php yii rbac/init
```

### Default Accounts

After running the seed command, the following accounts are available:

| Role    | Email             | Password |
|---------|-------------------|----------|
| Admin   | admin@gmail.com   | 123456   |
| Author1 | author1@gmail.com | 123456   |
| Author2 | author2@gmail.com | 123456   |
| Reader  | reader@gmail.com  | 123456   |
| Reader1 | reader1@gmail.com | 123456   |
| Reader2 | reader2@gmail.com | 123456   |

You can use these accounts to test authentication and RBAC permissions.

### 6. Start server

```bash
php yii serve --port=8080
```

Base URL:

```txt
http://localhost:8080/api
```

## API Response Format

Success:

```json
{
    "code": 200,
    "status": true,
    "data": [],
    "message": "Success"
}
```

Validation error:

```json
{
    "code": 422,
    "status": false,
    "data": [],
    "message": "Validation failed"
}
```

Pagination:

```json
{
    "code": 200,
    "status": true,
    "data": [],
    "_meta": {
        "total": 100,
        "page": 1,
        "limit": 10,
        "total_page": 10
    },
    "message": "Data retrieved successfully"
}
```

## Authentication

Use Bearer Token:

```txt
Authorization: Bearer <access_token>
```

### Auth Routes

| Method | Endpoint             | Description       |
|--------|----------------------|-------------------|
| POST   | `/api/auth/register` | Register new user |
| POST   | `/api/auth/login`    | Login             |
| POST   | `/api/auth/logout`   | Logout            |
| GET    | `/api/auth/me`       | Current user info |

## Category Routes

| Method | Endpoint             | Description     |
|--------|----------------------|-----------------|
| GET    | `/api/category`      | List categories |
| GET    | `/api/category/{id}` | Category detail |
| POST   | `/api/category`      | Create category |
| PUT    | `/api/category/{id}` | Update category |
| DELETE | `/api/category/{id}` | Delete category |

Query example:

```txt
GET /api/category?name=php&page=1&limit=10
```

## Post Routes

| Method | Endpoint                 | Description                                        |
|--------|--------------------------|----------------------------------------------------|
| GET    | `/api/post`              | List posts                                         |
| GET    | `/api/post/{id}`         | Post detail by ID                                  |
| GET    | `/api/post/slug/{slug}`  | Public post detail by slug and increase view count |
| POST   | `/api/post`              | Create post                                        |
| PUT    | `/api/post/{id}`         | Update post                                        |
| DELETE | `/api/post/{id}`         | Soft delete post                                   |
| GET    | `/api/post/trash`        | List deleted posts                                 |
| POST   | `/api/post/{id}/restore` | Restore deleted post                               |
| DELETE | `/api/post/{id}/force`   | Force delete post                                  |

Query example:

```txt
GET /api/post?title=yii2&status=published&category_id=1&tag=php&page=1&limit=10
```

Create post example:

```json
{
    "title": "Yii2 Blog",
    "summary": "Short summary",
    "content": "Post content minimum 10 characters",
    "status": "published",
    "category_id": 1,
    "thumbnail_file_id": null,
    "tags": [
        "yii2",
        "php",
        "backend"
    ]
}
```

## Tag Routes

| Method | Endpoint        | Description |
|--------|-----------------|-------------|
| GET    | `/api/tag`      | List tags   |
| GET    | `/api/tag/{id}` | Tag detail  |
| POST   | `/api/tag`      | Create tag  |
| PUT    | `/api/tag/{id}` | Update tag  |
| DELETE | `/api/tag/{id}` | Delete tag  |

## Comment Routes

| Method | Endpoint                 | Description           |
|--------|--------------------------|-----------------------|
| GET    | `/api/comment/post/{id}` | List comments by post |
| POST   | `/api/comment`           | Create comment/reply  |
| PUT    | `/api/comment/{id}`      | Update comment        |
| DELETE | `/api/comment/{id}`      | Delete comment        |

Create root comment:

```json
{
    "post_id": 1,
    "content": "This is a comment"
}
```

Create reply:

```json
{
    "post_id": 1,
    "parent_id": 1,
    "content": "This is a reply"
}
```

Comment only supports 2 levels:

```txt
Root comment
└── Reply
```

Reply to reply is not allowed.

## Like Routes

| Method | Endpoint                | Description             |
|--------|-------------------------|-------------------------|
| POST   | `/api/like/toggle/{id}` | Toggle like/unlike post |

Response:

```json
{
    "code": 200,
    "status": true,
    "data": {
        "liked": true
    },
    "message": "like successfully"
}
```

or:

```json
{
    "code": 200,
    "status": true,
    "data": {
        "liked": false
    },
    "message": "Unlike successfully"
}
```

## RBAC Roles

### reader

* View posts
* View tags
* View categories
* Create comment
* Toggle like

### author

* All reader permissions
* Create post
* Update own post
* Delete own post
* Restore own post

### admin

* Full permissions
* Manage all posts
* Manage categories
* Manage users
* Manage comments

## Acceptance Test Flow

1. Run migrations.
2. Run RBAC init.
3. Create admin, author, reader users.
4. Login admin.
5. Create category.
6. Login author.
7. Create post.
8. View post by slug.
9. Login reader.
10. Create comment.
11. Toggle like.
12. Test RBAC:

    * Reader cannot create post.
    * Author cannot update/delete another author's post.
    * Admin can manage all posts.

## Postman Collection

Postman collection is located in:

```txt
docs/postman/
```

Import the collection into Postman and set environment variable:

```txt
host=http://localhost:8080/api
token=<access_token>
```

Then run requests in order:

```txt
Auth → Category → Post → Comment → Like
```

## Notes

* No foreign key constraints are used in migrations.
* Relations are handled by Yii2 ActiveRecord.
* API response is standardized with `code`, `status`, `data`, and `message`.
* List endpoints return pagination meta.
* RBAC uses Yii2 native `yii\rbac\DbManager`.
* Owner check is handled by RBAC Rule.
