# Yii2 Blog API

REST API for a blog system built with Yii2 Basic, MySQL, Yii RBAC, Cloudflare R2 file storage, and optional Cloudflare
AI helpers.

## Requirements

- PHP >= 8.2
- Composer
- MySQL
- Yii2 Basic
- Laragon, XAMPP, or another local PHP environment

## Setup Steps

1. Clone the project.

```bash
git clone https://github.com/zinh8803/Blog-System.git
cd Blog-System
```

2. Install dependencies.

```bash
composer install
```

3. Create the environment file.

```bash
cp .env.example .env
```

Generate a cookie validation key:

```bash
php -r "echo bin2hex(random_bytes(32));"
```

Configure `.env`:

```env
DB_DSN=mysql:host=localhost;dbname=yii2_blog
DB_USERNAME=root
DB_PASSWORD=
COOKIE_VALIDATION_KEY=your-secret-key

R2_ACCOUNT_ID=
R2_ACCESS_KEY_ID=
R2_SECRET_ACCESS_KEY=
R2_BUCKET=
R2_PUBLIC_URL=

CF_ACCOUNT_ID=
CF_API_TOKEN=
CF_AI_MODEL=
```

4. Run database migrations.

```bash
php yii migrate
php yii migrate --migrationPath=@yii/rbac/migrations
```

5. Seed RBAC roles, permissions, and default users.

```bash
php yii rbac/init
```

Default accounts use password `123456`.

| Role   | Email             |
|--------|-------------------|
| admin  | admin@gmail.com   |
| admin  | admin1@gmail.com  |
| author | author1@gmail.com |
| author | author2@gmail.com |
| author | vinh1@gmail.com   |
| author | vinh2@gmail.com   |
| reader | reader@gmail.com  |
| reader | reader1@gmail.com |
| reader | reader2@gmail.com |

6. Start the local server.

```bash
php yii serve --port=8080
```

Base URL:

```txt
http://localhost:8080/api
```

## ERD

[View ERD on Google Drive](https://drive.google.com/file/d/16d3_A3a7zZ2JLWQmzDSkjQdW2bfDbBvp/view?usp=drive_link)

## Video Postman demo

[View Video Demo on Google Drive](https://drive.google.com/file/d/1KePQTFsemorjM4ZlVLNUY-GrdNA_1xhg/view?usp=drive_link)

## API Response Format

Success:

```json
{
    "code": 200,
    "status": true,
    "data": {},
    "message": "Success"
}
```

Validation error:

```json
{
    "code": 422,
    "status": false,
    "data": {
        "field": [
            "Error message"
        ]
    },
    "message": "Validation failed"
}
```

Paginated response:

```json
{
    "code": 200,
    "status": true,
    "data": [],
    "message": "Data retrieved successfully",
    "_meta": {
        "total": 100,
        "page": 1,
        "limit": 20,
        "total_page": 5
    }
}
```

## Authentication

Protected endpoints require a bearer token:

```txt
Authorization: Bearer <access_token>
```

Login example:

```bash
curl -X POST http://localhost:8080/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@gmail.com","password":"123456"}'
```

Set a shell variable from the returned token before running protected examples:

```bash
TOKEN="<access_token>"
```

## Endpoint List

### Auth

| Method | Endpoint             | Auth   | Description                    |
|--------|----------------------|--------|--------------------------------|
| POST   | `/api/auth/register` | Public | Register a reader account      |
| POST   | `/api/auth/login`    | Public | Login and receive access token |
| GET    | `/api/auth/me`       | Bearer | Get current user profile       |
| POST   | `/api/auth/logout`   | Bearer | Rotate current access token    |

### Categories

| Method    | Endpoint             | Auth   | Description     |
|-----------|----------------------|--------|-----------------|
| GET       | `/api/category`      | Public | List categories |
| GET       | `/api/category/{id}` | Public | Category detail |
| POST      | `/api/category`      | admin  | Create category |
| PUT/PATCH | `/api/category/{id}` | admin  | Update category |
| DELETE    | `/api/category/{id}` | admin  | Delete category |

### Posts

| Method    | Endpoint                 | Auth         | Description                                 |
|-----------|--------------------------|--------------|---------------------------------------------|
| GET       | `/api/post`              | Public       | List active posts                           |
| GET       | `/api/post/{id}`         | Public       | Post detail by ID                           |
| GET       | `/api/post/slug/{slug}`  | Public       | Post detail by slug and increase view count |
| GET       | `/api/post/trash`        | Bearer       | List soft-deleted posts                     |
| POST      | `/api/post`              | author/admin | Create post                                 |
| PUT/PATCH | `/api/post/{id}`         | owner/admin  | Update post                                 |
| DELETE    | `/api/post/{id}`         | owner/admin  | Soft delete post                            |
| POST      | `/api/post/{id}/restore` | owner/admin  | Restore post                                |
| DELETE    | `/api/post/{id}/force`   | owner/admin  | Permanently delete post                     |

### Tags

| Method    | Endpoint        | Auth         | Description |
|-----------|-----------------|--------------|-------------|
| GET       | `/api/tag`      | Public       | List tags   |
| GET       | `/api/tag/{id}` | Public       | Tag detail  |
| POST      | `/api/tag`      | author/admin | Create tag  |
| PUT/PATCH | `/api/tag/{id}` | author/admin | Update tag  |
| DELETE    | `/api/tag/{id}` | author/admin | Delete tag  |

### Comments

| Method    | Endpoint                     | Auth                | Description                            |
|-----------|------------------------------|---------------------|----------------------------------------|
| GET       | `/api/comment`               | Public              | List comments                          |
| GET       | `/api/comment/{id}`          | Public              | Comment detail                         |
| GET       | `/api/comment/post/{postId}` | Public              | List root comments and replies by post |
| POST      | `/api/comment`               | reader/author/admin | Create comment or reply                |
| PUT/PATCH | `/api/comment/{id}`          | owner/admin         | Update comment                         |
| DELETE    | `/api/comment/{id}`          | owner/admin         | Delete comment                         |

### Likes

| Method | Endpoint             | Auth                | Description   |
|--------|----------------------|---------------------|---------------|
| POST   | `/api/like/{postId}` | reader/author/admin | Like a post   |
| DELETE | `/api/like/{postId}` | reader/author/admin | Unlike a post |

### Files

| Method    | Endpoint         | Auth         | Description                         |
|-----------|------------------|--------------|-------------------------------------|
| GET       | `/api/file`      | Public       | List files                          |
| GET       | `/api/file/{id}` | Public       | File detail                         |
| POST      | `/api/file`      | author/admin | Upload image                        |
| PUT/PATCH | `/api/file/{id}` | owner/admin  | Replace image                       |
| DELETE    | `/api/file/{id}` | Bearer       | Delete file route from REST UrlRule |

### AI

| Method | Endpoint                       | Auth         | Description                      |
|--------|--------------------------------|--------------|----------------------------------|
| POST   | `/api/ai/generate-title`       | author/admin | Generate title ideas             |
| POST   | `/api/ai/generate-summary`     | author/admin | Generate post summary            |
| POST   | `/api/ai/generate-description` | author/admin | Rewrite text with an instruction |

### AI Logs

| Method | Endpoint           | Auth  | Description   |
|--------|--------------------|-------|---------------|
| GET    | `/api/ai-log`      | admin | List AI logs  |
| GET    | `/api/ai-log/{id}` | admin | AI log detail |

## Curl Examples

Register:

```bash
curl -X POST http://localhost:8080/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"username":"newreader","email":"newreader@example.com","password":"123456"}'
```

Create category:

```bash
curl -X POST http://localhost:8080/api/category \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"PHP","status":1}'
```

List posts:

```bash
curl "http://localhost:8080/api/post?title=yii&status=published&category_id=1&page=1&per-page=20"
```

Create post:

```bash
curl -X POST http://localhost:8080/api/post \
  -H "Authorization: Bearer $TOKEN" \
  -F "title=Yii2 Blog API" \
  -F "summary=Short summary" \
  -F "content=Post content with at least ten characters." \
  -F "status=published" \
  -F "category_id=1" \
  -F "tags[]=yii2" \
  -F "tags[]=php" \
  -F "imageFile=@/path/to/thumbnail.webp"
```

Update post:

```bash
curl -X PATCH http://localhost:8080/api/post/1 \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title":"Updated Yii2 Blog API","status":"draft"}'
```

View post by slug:

```bash
curl http://localhost:8080/api/post/slug/yii2-blog-api
```

Create comment:

```bash
curl -X POST http://localhost:8080/api/comment \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"post_id":1,"content":"This is a comment"}'
```

Create reply:

```bash
curl -X POST http://localhost:8080/api/comment \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"post_id":1,"parent_id":1,"content":"This is a reply"}'
```

Like and unlike a post:

```bash
curl -X POST http://localhost:8080/api/like/1 \
  -H "Authorization: Bearer $TOKEN"

curl -X DELETE http://localhost:8080/api/like/1 \
  -H "Authorization: Bearer $TOKEN"
```

Upload file:

```bash
curl -X POST http://localhost:8080/api/file \
  -H "Authorization: Bearer $TOKEN" \
  -F "folder=content" \
  -F "imageFile=@/path/to/image.webp"
```

Generate title:

```bash
curl -X POST http://localhost:8080/api/ai/generate-title \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"description":"An article about building a Yii2 blog API"}'
```

Generate summary:

```bash
curl -X POST http://localhost:8080/api/ai/generate-summary \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"content":"Long post content goes here..."}'
```

Rewrite text:

```bash
curl -X POST http://localhost:8080/api/ai/generate-description \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"text":"Yii2 is fast.","instruction":"Rewrite in a friendly tone"}'
```

## Role And Permission Matrix

| Permission                      | reader | author | admin |
|---------------------------------|--------|--------|-------|
| Register/login/logout/me        | Yes    | Yes    | Yes   |
| List/view categories            | Yes    | Yes    | Yes   |
| Create/update/delete categories | No     | No     | Yes   |
| List/view posts                 | Yes    | Yes    | Yes   |
| Create posts                    | No     | Yes    | Yes   |
| Update own posts                | No     | Yes    | Yes   |
| Delete own posts                | No     | Yes    | Yes   |
| Restore own posts               | No     | Yes    | Yes   |
| Manage all posts                | No     | No     | Yes   |
| List/view tags                  | Yes    | Yes    | Yes   |
| Create/update/delete tags       | No     | Yes    | Yes   |
| Create comments                 | Yes    | Yes    | Yes   |
| Update/delete own comments      | Yes    | Yes    | Yes   |
| Manage all comments             | No     | No     | Yes   |
| Like/unlike posts               | Yes    | Yes    | Yes   |
| Upload files                    | No     | Yes    | Yes   |
| Update own files                | No     | Yes    | Yes   |
| Manage all files                | No     | No     | Yes   |
| Use AI helpers                  | No     | Yes    | Yes   |
| View AI logs                    | No     | No     | Yes   |
| Manage users                    | No     | No     | Yes   |

## Notes

- No foreign key constraints are defined in migrations; Yii ActiveRecord relations handle model navigation.
- List endpoints use Yii `ActiveDataProvider` pagination. Use Yii query params such as `page` and `per-page`.
- RBAC uses Yii `yii\rbac\DbManager`.
- Owner-only checks are handled by `app\rbac\OwnerRule`.
- The Postman collection is in `docs/postman/`.
