# SpeakBetter - AI-Powered Interactive English Learning Platform

An AI-powered interactive English learning environment designed for Saudi high school students (CEFR B1 level). The platform features a virtual tutor named **Ahmad** who engages students in realistic conversational scenarios with real-time corrective feedback.

## 🎯 Project Overview

This platform is part of a research project at **King Khalid University**, Saudi Arabia, investigating the effectiveness of AI-powered conversational practice for English language learning.

- **Participants**: 120 students (60 experimental, 60 control) from 5 high schools in Abha
- **Duration**: 10 sessions × 40 minutes each, over 5 weeks
- **Level**: CEFR B1 (Intermediate)
- **Language Focus**: Speaking & conversational English

## ✨ Features

- 🤖 **AI Virtual Tutor (Ahmad)** - Powered by OpenAI GPT for natural conversation
- 🗣️ **Voice Cloning** - ElevenLabs API with Ahmad's cloned voice for realistic TTS
- 🎭 **Animated Avatar** - Simli AI for real-time lip-synced video avatar via WebRTC
- 🎤 **Speech Recognition** - Browser Web Speech API for student voice input
- 📚 **8 Conversational Scenarios** - Real-life situations (park, home, club, school, etc.)
- 🔄 **Corrective Feedback** - Automatic recast, clarification, and model feedback
- 📊 **Admin Dashboard** - Filament-based panel for tracking student progress
- 📈 **Performance Analytics** - Per-session accuracy, duration, and turn tracking

---

## 🔄 How It Works — Full Session Cycle

### Step 1: Student Login
Student enters their unique code (e.g., `STU001`).

```
POST /api/v1/auth/login
Body: { "code": "STU001" }

Response: { "token": "abc123", "student": { "name": "...", "group": "experimental" } }
```

### Step 2: Choose a Scenario
Frontend fetches available scenarios and displays them.

```
GET /api/v1/scenarios
→ Returns 8 scenarios (At the Park, At Home, At the Club, etc.)
```

### Step 3: Start a Session
Student picks a scenario → a new learning session begins.

```
POST /api/v1/sessions/start
Body: { "scenario_id": 1 }

Response: {
  "session_id": 5,
  "phase": "greeting",
  "ahmad_message": "Hello! I'm Ahmad...",
  "audio": "base64...(MP3)"        ← Ahmad's voice via ElevenLabs
}
```

### Step 4: Simli Avatar (Animated Ahmad)
In parallel, the frontend connects to Simli for the animated avatar:

```
POST /api/v1/simli/token → Get WebRTC session token
GET  /api/v1/simli/ice   → Get ICE servers for peer connection

→ WebRTC connection opens
→ Ahmad's animated face appears on screen
→ Audio is sent to Simli → lips move in sync
```

### Step 5: Session Phases
Each session progresses through 5 phases:

```
greeting → vocabulary → conversation → feedback → completed
```

The frontend calls `POST /sessions/{id}/advance-phase` to move to the next phase. Each phase returns Ahmad's message + audio.

### Step 6: Conversation Loop ⭐ (Core Feature)
During the `conversation` phase, the student talks with Ahmad:

```
1. 🎤 Student presses mic button
2. 🗣️ Student speaks English → Web Speech API converts to text
3. 📤 Text sent to backend:
       POST /api/v1/conversation/speak
       { "session_id": 5, "text": "I go to park" }

4. 🤖 Backend sends to OpenAI GPT:
       "Student said: I go to park"
       "Respond as Ahmad. Correct errors with recast."

5. 💬 GPT responds:
       "You mean 'I go to THE park' 😊 The park is beautiful today!"

6. 📝 Backend records in DB:
       is_correct: false
       feedback_type: "recast"
       feedback_text: "I go to THE park"

7. 🔊 ElevenLabs converts response to audio (Ahmad's voice)

8. Response sent to frontend:
       {
         "ahmad_response": "The park is beautiful today!",
         "audio": "base64...",
         "is_correct": false,
         "feedback_type": "recast"
       }

9. 🎭 Simli syncs Ahmad's lips to the audio
10. 🔁 Loop repeats until conversation is complete
```

### Step 7: Session End
```
advance-phase → feedback (Ahmad gives overall feedback + stats)
advance-phase → completed (session ends, duration saved)
```

---

## 🛠️ Tech Stack

### Backend
| Technology | Purpose |
|---|---|
| **Laravel 12** | PHP Framework |
| **PHP 8.2+** | Runtime |
| **MySQL 8.0+** | Database |
| **Filament v3** | Admin Dashboard |
| **Laravel Sanctum** | Token-based Authentication |

### AI Services
| Service | Purpose |
|---|---|
| **OpenAI GPT** | Conversation generation + student evaluation |
| **ElevenLabs** | Text-to-Speech with Ahmad's cloned voice |
| **Simli AI** | Real-time animated lip-synced avatar (WebRTC) |
| **Web Speech API** | Browser-based speech recognition (ASR) |

### Frontend (Separate Repository)
| Technology | Purpose |
|---|---|
| **Vue.js 3** | Frontend Framework |
| **Vite** | Build Tool |
| **WebRTC** | Simli avatar streaming |
| **Web Speech API** | Voice input |

---

## 📡 API Endpoints

### Authentication
| Method | Endpoint | Description |
|--------|----------|-------------|
| `POST` | `/api/v1/auth/login` | Login with student code, returns Sanctum token |

### Scenarios
| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/v1/scenarios` | List all available scenarios |
| `GET` | `/api/v1/scenarios/{id}` | Get scenario details with vocabulary & prompts |

### Sessions
| Method | Endpoint | Description |
|--------|----------|-------------|
| `POST` | `/api/v1/sessions/start` | Start a new learning session for a scenario |
| `GET` | `/api/v1/sessions/{id}` | Get session details and current phase |
| `POST` | `/api/v1/sessions/{id}/advance-phase` | Move to next phase (greeting→vocab→conversation→feedback→completed) |

### Conversation
| Method | Endpoint | Description |
|--------|----------|-------------|
| `POST` | `/api/v1/conversation/speak` | Send student's speech text, get Ahmad's AI response + audio + feedback |

### Text-to-Speech
| Method | Endpoint | Description |
|--------|----------|-------------|
| `POST` | `/api/v1/tts` | Convert text to speech using ElevenLabs (Ahmad's voice) |

### Simli Avatar
| Method | Endpoint | Description |
|--------|----------|-------------|
| `POST` | `/api/v1/simli/token` | Get Simli WebRTC session token for avatar |
| `GET` | `/api/v1/simli/ice` | Get ICE servers for WebRTC peer connection |

---

## 🗄️ Database Schema

```
students
├── id, code, group (experimental/control), is_active

scenarios
├── id, number, title, title_ar, topic
├── system_prompt, scenario_module, completion_criteria
├── vocabulary (JSON), b1_axes (JSON)

learning_sessions
├── id, student_id, scenario_id, session_number
├── status (not_started/in_progress/completed)
├── current_phase, started_at, ended_at, duration_seconds

conversation_turns
├── id, session_id, turn_number, speaker (student/ahmad)
├── text_content, audio_url
├── is_correct, attempt_number
├── feedback_type (none/recast/clarification/model)
├── feedback_text

incidents
├── id, session_id, type, description

activity_logs
├── id, student_id, action, details
```

---

## 🏫 Admin Dashboard

Access at `/admin` — built with Filament v3.

**Features:**
- 👥 **Students** — View all students, filter by group (experimental/control)
- 📚 **Scenarios** — Create/edit scenarios, manage system prompts & vocabulary
- 🎓 **Sessions** — Monitor all learning sessions, view conversation turns
- 📊 **Statistics** — Dashboard with total students, completed sessions, accuracy rates, avg duration

**Login:** Email + Password (created via artisan command)

---

## 📁 Project Structure

```
├── app/
│   ├── Enums/                  # StudentGroup, SessionStatus, FeedbackType, etc.
│   ├── Filament/
│   │   ├── Resources/          # StudentResource, SessionResource, ScenarioResource
│   │   └── Widgets/            # StatsOverview dashboard widget
│   ├── Http/Controllers/Api/V1/
│   │   ├── AuthController.php          # Login/Logout
│   │   ├── SessionController.php       # Start, end, advance phase, Simli
│   │   ├── ConversationController.php  # Process speech, GPT interaction
│   │   └── ScenarioController.php      # List/show scenarios
│   ├── Models/                 # Student, Session, Scenario, ConversationTurn, etc.
│   ├── Providers/
│   │   ├── AppServiceProvider.php      # Service bindings (TTS, LLM, ASR)
│   │   └── Filament/AdminPanelProvider.php
│   └── Services/
│       ├── OpenAILLMService.php        # GPT conversation + evaluation
│       ├── ElevenLabsTTSService.php    # Text-to-Speech (Ahmad's voice)
│       ├── ConversationService.php     # Orchestrates the conversation flow
│       └── Contracts/                  # Service interfaces
├── config/
│   └── services.php            # API keys config (elevenlabs, openai, simli)
├── database/
│   ├── migrations/             # All table schemas
│   └── seeders/
│       └── ScenarioSeeder.php  # Seeds 8 conversational scenarios
├── routes/
│   ├── api.php                 # All 10 API routes
│   └── web.php                 # Web routes (admin)
└── public/
    ├── css/filament/           # Admin panel styles
    └── js/filament/            # Admin panel scripts
```

---

## 🚀 Installation

### Prerequisites
- PHP 8.2+
- Composer
- MySQL 8.0+

### Setup

```bash
# 1. Clone
git clone https://github.com/mennazm/Interactive_learning.git
cd Interactive_learning

# 2. Install dependencies
composer install

# 3. Environment
cp .env.example .env
php artisan key:generate

# 4. Configure .env
# Set DB_*, OPENAI_API_KEY, ELEVENLABS_API_KEY, ELEVENLABS_VOICE_ID,
# SIMLI_API_KEY, SIMLI_FACE_ID

# 5. Migrate & Seed
php artisan migrate
php artisan db:seed --class=ScenarioSeeder

# 6. Create admin user
php artisan tinker --execute="App\Models\User::create(['name'=>'Admin','email'=>'admin@example.com','password'=>bcrypt('password')])"

# 7. Run
php artisan serve
```

---

## 🐳 Docker Deployment (Railway)

The project includes a `Dockerfile` for deployment on Railway.app:

```dockerfile
FROM php:8.2-cli
# Installs: pdo_mysql, mbstring, gd, zip, intl, bcmath
# Runs: composer install, filament:assets
# Starts: php artisan serve --port=10000
```

### Required Environment Variables
```
APP_KEY=base64:...
APP_ENV=production
APP_URL=https://your-app.up.railway.app
DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_PORT=your-db-port
DB_DATABASE=railway
DB_USERNAME=root
DB_PASSWORD=your-password
OPENAI_API_KEY=sk-...
ELEVENLABS_API_KEY=sk_...
ELEVENLABS_VOICE_ID=...
SIMLI_API_KEY=...
SIMLI_FACE_ID=...
```

---

## 🔄 Corrective Feedback System

The platform implements three types of corrective feedback (based on SLA research):

| Type | Description | Example |
|------|-------------|---------|
| **Recast** | Ahmad repeats the correct form naturally | Student: "I go park" → Ahmad: "Yes, I **go to the** park too!" |
| **Clarification** | Ahmad asks for clarification | Student: "I has book" → Ahmad: "Sorry, could you say that again?" |
| **Model** | Ahmad provides explicit correction | Student: "She go" → Ahmad: "We say 'She **goes**'" |

GPT automatically selects the appropriate feedback type based on the error severity.

---

## 👩‍💻 Team

- **Researcher**: Ahmad Al-Hayani — King Khalid University, Abha, Saudi Arabia
- **Backend Developer**: Menna Mahmoud

## 📄 License

This project is developed for academic research purposes.
