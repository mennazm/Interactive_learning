# SpeakBetter - Interactive English Learning Platform

An AI-powered interactive English learning environment designed for Saudi high school students (CEFR B1 level). The platform features a virtual tutor named **Ahmad** who engages students in realistic conversational scenarios.

## 🎯 Project Overview

This platform is part of a research project at **King Khalid University**, Saudi Arabia, investigating the effectiveness of AI-powered conversational practice for English language learning.

- **Participants**: 120 students (60 experimental, 60 control) from 5 high schools in Abha
- **Duration**: 10 sessions × 40 minutes each, over 5 weeks
- **Level**: CEFR B1 (Intermediate)

## ✨ Features

- 🤖 **AI Virtual Tutor (Ahmad)** - Powered by OpenAI GPT for natural conversation
- 🗣️ **Text-to-Speech** - ElevenLabs API with Ahmad's cloned voice
- 🎭 **Animated Avatar** - Simli AI for lip-synced video avatar
- 📚 **8 Conversational Scenarios** - Real-life situations (park, home, club, school, etc.)
- 📊 **Admin Dashboard** - Filament-based admin panel for tracking student progress
- 🔄 **Corrective Feedback** - Automatic recast and clarification feedback
- 📈 **Performance Tracking** - Per-session grades, correct/incorrect turns, duration

## 🛠️ Tech Stack

### Backend
- **Framework**: Laravel 12
- **PHP**: 8.2+
- **Database**: MySQL
- **Admin Panel**: Filament v3
- **Authentication**: Laravel Sanctum (token-based)

### AI Services
- **LLM**: OpenAI GPT (conversation generation)
- **TTS**: ElevenLabs (text-to-speech with custom voice)
- **Avatar**: Simli AI (animated lip-synced avatar)
- **ASR**: Browser Web Speech API (speech recognition)

### Frontend
- **Framework**: Vue.js 3 (separate repository)
- **Build Tool**: Vite
- **Real-time**: WebRTC (Simli avatar streaming)

## 📋 Prerequisites

- PHP 8.2 or higher
- Composer
- MySQL 8.0+
- Node.js 18+ (for frontend)

## 🚀 Installation

### 1. Clone the repository
```bash
git clone https://github.com/mennazm/Interactive_learning.git
cd Interactive_learning
```

### 2. Install dependencies
```bash
composer install
```

### 3. Environment setup
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configure `.env`
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=interactive_learn_db
DB_USERNAME=root
DB_PASSWORD=

OPENAI_API_KEY=your_openai_key
ELEVENLABS_API_KEY=your_elevenlabs_key
ELEVENLABS_VOICE_ID=your_voice_id
SIMLI_API_KEY=your_simli_key
SIMLI_FACE_ID=your_face_id
```

### 5. Run migrations & seed
```bash
php artisan migrate
php artisan db:seed --class=ScenarioSeeder
```

### 6. Create admin user
```bash
php artisan tinker --execute="App\Models\User::create(['name'=>'Admin','email'=>'admin@example.com','password'=>bcrypt('password')])"
```

### 7. Start the server
```bash
php artisan serve
```

## 📡 API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| `POST` | `/api/v1/auth/login` | Student login by code |
| `GET` | `/api/v1/scenarios` | List all scenarios |
| `GET` | `/api/v1/scenarios/{id}` | Get scenario details |
| `POST` | `/api/v1/sessions/start` | Start a learning session |
| `GET` | `/api/v1/sessions/{id}` | Get session details |
| `POST` | `/api/v1/sessions/{id}/advance-phase` | Advance session phase |
| `POST` | `/api/v1/conversation/speak` | Process student speech |
| `POST` | `/api/v1/tts` | Text-to-speech synthesis |
| `POST` | `/api/v1/simli/token` | Get Simli session token |
| `GET` | `/api/v1/simli/ice` | Get ICE servers for WebRTC |

## 🏫 Admin Dashboard

Access the admin panel at `/admin` to:
- View and manage students
- Monitor learning sessions
- Track conversation turns and feedback
- View scenarios and edit content
- See overall statistics (completion rates, accuracy, etc.)

## 📁 Project Structure

```
├── app/
│   ├── Enums/              # Status enums (SessionStatus, StudentGroup, etc.)
│   ├── Filament/           # Admin panel resources & widgets
│   ├── Http/Controllers/   # API controllers
│   ├── Models/             # Eloquent models
│   ├── Providers/          # Service providers
│   └── Services/           # AI service integrations (OpenAI, ElevenLabs, etc.)
├── config/                 # Configuration files
├── database/
│   ├── migrations/         # Database schema
│   └── seeders/            # Scenario data seeder
├── routes/
│   ├── api.php             # API routes
│   └── web.php             # Web routes
└── public/                 # Public assets
```

## 👩‍💻 Researcher

**Ahmad Al-Hayani** - King Khalid University, Abha, Saudi Arabia

## 📄 License

This project is developed for academic research purposes.
