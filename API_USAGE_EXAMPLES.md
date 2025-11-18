# API Usage Examples

## Authentication

### Register
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

### Login
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

Response:
```json
{
  "user": {...},
  "token": "1|abcdef..."
}
```

---

## Games

### List Games (Catalog)
```bash
curl http://localhost:8000/api/games
```

### Search Games
```bash
curl "http://localhost:8000/api/games?type=murder&difficulty=medium&search=mansion"
```

### Get Game Details
```bash
curl http://localhost:8000/api/games/1
```

### Get Landing Page
```bash
curl http://localhost:8000/api/games/1/landing
```

---

## Sessions

### Create Session
```bash
curl -X POST http://localhost:8000/api/session/create \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "game_id": 1,
    "max_players": 6,
    "mode": "group"
  }'
```

Response:
```json
{
  "session": {
    "id": 1,
    "session_code": "ABC123",
    "game_id": 1,
    "status": "waiting",
    ...
  }
}
```

### Join Session
```bash
curl -X POST http://localhost:8000/api/session/join \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "session_code": "ABC123"
  }'
```

### Start Session
```bash
curl -X POST http://localhost:8000/api/session/1/start \
  -H "Authorization: Bearer YOUR_TOKEN"
```

Response:
```json
{
  "session": {...},
  "scene": {
    "scene_id": "scene_intro",
    "type": "message",
    "content": "La tormenta azota la mansión...",
    "actions": [
      {
        "id": "examine_body",
        "label": "Examinar el cuerpo"
      }
    ]
  }
}
```

### Get Session State
```bash
curl http://localhost:8000/api/session/1/state \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Perform Action
```bash
curl -X POST http://localhost:8000/api/session/1/action \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "action_id": "examine_body",
    "data": {}
  }'
```

---

## Admin

### Create Game with AI
```bash
curl -X POST http://localhost:8000/api/admin/games/create-with-ai \
  -H "Authorization: Bearer ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "prompt": "Create a murder mystery in a Victorian mansion with 6 suspects",
    "game_type": "murder",
    "min_players": 4,
    "max_players": 8,
    "duration": 90,
    "difficulty": "medium"
  }'
```

### Publish Game
```bash
curl -X POST http://localhost:8000/api/admin/games/1/publish \
  -H "Authorization: Bearer ADMIN_TOKEN"
```

### Regenerate Part
```bash
curl -X POST http://localhost:8000/api/admin/games/1/regenerate \
  -H "Authorization: Bearer ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "part": "scenes",
    "prompt": "Make the scenes more atmospheric and add more red herrings"
  }'
```

### Get Platform Statistics
```bash
curl http://localhost:8000/api/admin/statistics \
  -H "Authorization: Bearer ADMIN_TOKEN"
```

---

## Complete Flow Example

### 1. Admin creates a game
```bash
# Create game with AI
curl -X POST http://localhost:8000/api/admin/games/create-with-ai \
  -H "Authorization: Bearer ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "prompt": "A mysterious disappearance in a small coastal town",
    "game_type": "mystery",
    "max_players": 4,
    "duration": 60
  }'

# Publish it
curl -X POST http://localhost:8000/api/admin/games/1/publish \
  -H "Authorization: Bearer ADMIN_TOKEN"
```

### 2. User discovers and plays
```bash
# Browse games
curl http://localhost:8000/api/games

# View details
curl http://localhost:8000/api/games/1

# Create session (as host)
curl -X POST http://localhost:8000/api/session/create \
  -H "Authorization: Bearer USER_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"game_id": 1, "max_players": 4}'

# Friends join with code
curl -X POST http://localhost:8000/api/session/join \
  -H "Authorization: Bearer FRIEND_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"session_code": "ABC123"}'

# Host starts the game
curl -X POST http://localhost:8000/api/session/1/start \
  -H "Authorization: Bearer USER_TOKEN"

# Players take actions
curl -X POST http://localhost:8000/api/session/1/action \
  -H "Authorization: Bearer USER_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"action_id": "investigate_pier"}'

# Check state
curl http://localhost:8000/api/session/1/state \
  -H "Authorization: Bearer USER_TOKEN"
```

---

## Error Responses

### 400 Bad Request
```json
{
  "error": "Invalid action_id"
}
```

### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```

### 403 Forbidden
```json
{
  "error": "You do not have access to this game"
}
```

### 404 Not Found
```json
{
  "message": "Resource not found"
}
```

### 500 Server Error
```json
{
  "error": "Internal server error"
}
```

---

## Rate Limits

- Public endpoints: 60 requests/minute
- Authenticated endpoints: 1000 requests/minute
- Admin endpoints: Unlimited
- AI generation: 10 requests/hour per user

---

## Webhooks

### Stripe Webhook
```bash
curl -X POST http://localhost:8000/api/webhooks/stripe \
  -H "Content-Type: application/json" \
  -H "Stripe-Signature: YOUR_SIGNATURE" \
  -d '{...}'
```

---

## Testing with Postman

Import the following collection:

```json
{
  "info": {
    "name": "Narrative Game Platform",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "auth": {
    "type": "bearer",
    "bearer": [
      {
        "key": "token",
        "value": "{{token}}",
        "type": "string"
      }
    ]
  },
  "variable": [
    {
      "key": "baseUrl",
      "value": "http://localhost:8000/api"
    },
    {
      "key": "token",
      "value": ""
    }
  ]
}
```
