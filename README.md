# DataVizEngine - Wikipedia Table Visualizer

![Version](https://img.shields.io/badge/version-2.0.0-blue.svg)
![PHP](https://img.shields.io/badge/PHP-8.2+-green.svg)
![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)
![React](https://img.shields.io/badge/React-18.x-61dafb.svg)
![Python](https://img.shields.io/badge/Python-3.11+-blue.svg)
![License](https://img.shields.io/badge/license-MIT-green.svg)

## 🚀 Overview

**DataVizEngine** is a modern web application that extracts data from Wikipedia tables and generates beautiful visualizations using Python's powerful data science libraries. The platform seamlessly bridges web technologies with scientific computing to deliver professional-quality charts from any Wikipedia table.

**Built with:**
- **React 18** + TypeScript for a responsive, modern UI
- **Laravel 12** as a robust RESTful API backend
- **Python 3.11** with Matplotlib & Seaborn for publication-quality visualizations

---

## 📐 Architecture

### System Design

This application uses a **modern SPA (Single Page Application)** architecture with separated frontend and backend:

```
┌─────────────────────────────────────────────────────────────────┐
│                         User Browser                            │
│                              ↓                                   │
│                    Nginx (Port 80/443)                          │
└─────────────────────────────────────────────────────────────────┘
                              ↓
         ┌────────────────────┴────────────────────┐
         ↓                                         ↓
┌──────────────────────┐                  ┌──────────────────────┐
│  React Frontend      │                  │  Laravel API         │
│  (Vite Server)       │ ← Proxy /api → │  Backend             │
│  Port: 5000/3000     │                  │  Port: 8000          │
│                      │                  │                      │
│  - React 18          │                  │  - REST API          │
│  - TypeScript        │                  │  - WikipediaExtractor│
│  - Tailwind CSS      │                  │  - Guzzle HTTP       │
│  - Vite 7            │                  │  - DomCrawler        │
└──────────────────────┘                  └──────────────────────┘
                                                   ↓
                         ┌────────────────────────┴────────────┐
                         ↓                                     ↓
              ┌────────────────────┐              ┌────────────────────┐
              │ Python Scripts     │              │ File Storage       │
              │ (Visualization)    │              │ (Generated Charts) │
              │                    │              │                    │
              │ - Matplotlib       │              │ storage/app/public/│
              │ - Seaborn          │              │ viz_*.png          │
              │ - Pandas, NumPy    │              │                    │
              └────────────────────┘              └────────────────────┘
```

### Data Flow

1. **User Input** → User enters Wikipedia URL in React frontend
2. **API Request** → React sends POST to `/api/extract-table`
3. **Web Scraping** → Laravel fetches Wikipedia page using Guzzle
4. **HTML Parsing** → DomCrawler extracts table data
5. **Smart Analysis** → System identifies numeric columns (70% threshold)
6. **Preview** → Frontend displays first 10 rows for user review
7. **Selection** → User selects numeric column and chart type
8. **Visualization Request** → React sends POST to `/api/generate-visualization`
9. **Python Execution** → Laravel calls Python script with data
10. **Chart Generation** → Python creates PNG using Matplotlib/Seaborn
11. **Storage** → Image saved to `storage/app/public/`
12. **Response** → Image URL returned to frontend
13. **Display** → React renders the visualization

### How API Communication Works

When the React app calls an API endpoint like `fetch('/api/extract-table')`:

1. **Vite Proxy Intercepts**: The Vite dev server detects the `/api` prefix
2. **Request Forwarding**: Vite forwards the request to `http://localhost:8000` (configured in `vite.config.js`)
3. **Laravel Processing**: Laravel API receives and processes the request
4. **JSON Response**: Laravel returns data as JSON
5. **Frontend Update**: React receives the response and updates the UI

**Configuration Location:** `vite.config.js` (lines 29-38)
```javascript
proxy: {
    '/api': {
        target: 'http://localhost:8000',  // Laravel backend
        changeOrigin: true,
    },
    '/storage': {
        target: 'http://localhost:8000',  // For images
        changeOrigin: true,
    },
}
```

---

## 🔧 Technology Stack

### Frontend
- **React 18** - Modern UI library with hooks
- **TypeScript** - Type-safe JavaScript
- **Vite 7** - Fast build tool with HMR (Hot Module Replacement)
- **Tailwind CSS 4.0** - Utility-first CSS framework
- **Axios** - HTTP client for API requests

### Backend
- **Laravel 12** - PHP framework for RESTful API
- **PHP 8.2+** - Modern PHP with performance improvements
- **Guzzle HTTP** - HTTP client for Wikipedia requests
- **Symfony DomCrawler** - HTML parsing and table extraction
- **Process Facade** - Execute Python scripts from Laravel

### Data Visualization
- **Python 3.11** - Modern Python runtime
- **Matplotlib** - Core plotting library
- **Seaborn** - Statistical data visualization
- **Pandas** - Data manipulation and analysis
- **NumPy** - Numerical computing

### Database
- **SQLite** (development) - Lightweight embedded database
- **MySQL/PostgreSQL** (production) - Scalable relational databases

### Infrastructure
- **Nginx** - Reverse proxy and web server
- **PM2** - Process manager for Node.js applications
- **Concurrently** - Run multiple servers simultaneously

---

## 🌟 Key Features

### 1. Wikipedia Data Extraction
- Fetch and parse any Wikipedia page with tables
- Intelligent table detection and extraction
- Support for complex HTML table structures
- Handles tables with or without `<thead>` sections

### 2. Smart Column Analysis
- Automatic numeric column detection (70% threshold)
- Type inference for mixed data columns
- Preserves original data for accurate visualization
- Dropdown selector for numeric columns only

### 3. Multiple Visualization Types
- **Bar Charts** - Compare categorical data
- **Line Charts** - Show trends over time
- **Scatter Plots** - Explore relationships between variables
- Professional styling with Seaborn themes

### 4. Real-time Preview
- Display first 10 rows of extracted table
- Review data before generating charts
- Responsive table design with Tailwind CSS

### 5. Professional Output
- High-resolution PNG exports (150 DPI)
- Publication-quality visualizations
- Automatic chart sizing and formatting
- Clean, modern aesthetic

---

## 💻 Quick Start

### Prerequisites

Ensure you have the following installed:

- **PHP 8.2+** with Composer
- **Python 3.11+** with pip
- **Node.js 20+** with npm

### Installation (Development)

```bash
# 1. Clone the repository
git clone https://github.com/your-username/DataVizEngine.git
cd DataVizEngine

# 2. Install PHP dependencies
composer install

# 3. Install Node.js dependencies
npm install

# 4. Install Python dependencies
pip install -r requirements.txt

# 5. Set up environment
cp .env.example .env
php artisan key:generate

# 6. Configure database (SQLite for development)
touch database/database.sqlite
php artisan migrate

# 7. Create storage symlink
php artisan storage:link

# 8. Start development servers (both frontend and backend)
npm run dev

# The application will be available at http://localhost:5000
# - Frontend: http://localhost:5000 (Vite dev server)
# - Backend API: http://localhost:8000 (Laravel)
```

### Development Workflow Command

The development environment runs **two concurrent servers**:

```bash
npx concurrently -c "#93c5fd,#c4b5fd" \
  "npm run dev -- --port 5000" \
  "php artisan serve --host=0.0.0.0 --port=8000" \
  --names=vite,api --kill-others
```

This command:
- Starts Vite dev server on port **5000** (frontend with HMR)
- Starts Laravel API on port **8000** (backend)
- Uses color-coded logs for easy debugging
- Kills all processes if one fails

---

## 📚 API Documentation

### Endpoints

#### 1. Extract Table Data

**Endpoint:** `POST /api/extract-table`

**Request Body:**
```json
{
  "url": "https://en.wikipedia.org/wiki/List_of_countries_by_population_(United_Nations)"
}
```

**Response:**
```json
{
  "headers": ["Country", "Population", "Area"],
  "rows": [
    ["China", "1411778724", "9596961"],
    ["India", "1393409038", "3287263"]
  ],
  "numericColumns": ["Population", "Area"]
}
```

**Validation:**
- `url` - Required, must be a valid URL, must start with `https://en.wikipedia.org/wiki/`

---

#### 2. Generate Visualization

**Endpoint:** `POST /api/generate-visualization`

**Request Body:**
```json
{
  "tableData": {
    "headers": ["Country", "Population"],
    "rows": [["China", "1411778724"], ["India", "1393409038"]]
  },
  "selectedColumns": ["Population"],
  "chartType": "bar"
}
```

**Response:**
```json
{
  "id": 1,
  "imageUrl": "/storage/viz_abc123def456.png",
  "chartType": "bar"
}
```

**Chart Types:**
- `bar` - Bar chart for categorical comparisons
- `line` - Line chart for trends
- `scatter` - Scatter plot for relationships

---

## 🎯 Usage Example

### Step-by-Step Guide

1. **Open the Application**
   - Navigate to `http://localhost:5000` in your browser

2. **Enter Wikipedia URL**
   ```
   https://en.wikipedia.org/wiki/List_of_countries_by_population_(United_Nations)
   ```

3. **Click "Extract Table Data"**
   - The system fetches and parses the Wikipedia page
   - First 10 rows are displayed for preview
   - Numeric columns are automatically detected

4. **Select Visualization Options**
   - Choose a numeric column from the dropdown (e.g., "Population")
   - Select chart type (Bar, Line, or Scatter)

5. **Click "Generate Visualization"**
   - Python generates a professional chart
   - Visualization appears below the form

6. **View Your Chart**
   - High-quality PNG image with proper labels
   - Download or share as needed

---

## 📂 Project Structure

```
DataVizEngine/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── Api/
│   │           └── WikipediaController.php    # API endpoints
│   ├── Services/
│   │   └── WikipediaExtractor.php             # Web scraping service
│   └── Models/                                # Database models
│
├── config/
│   ├── cors.php                               # CORS configuration
│   └── filesystems.php                        # Storage configuration
│
├── database/
│   ├── migrations/                            # Database migrations
│   └── database.sqlite                        # SQLite database
│
├── public/
│   └── storage/                               # Symlink to storage/app/public
│
├── resources/
│   ├── js/
│   │   ├── App.tsx                            # Main React component
│   │   ├── main.tsx                           # React entry point
│   │   └── bootstrap.js                       # Axios configuration
│   └── css/
│       └── app.css                            # Tailwind CSS
│
├── routes/
│   ├── api.php                                # API routes
│   └── web.php                                # Web routes
│
├── scripts/
│   └── generate_visualization.py              # Python visualization script
│
├── storage/
│   ├── app/
│   │   └── public/                            # Generated visualizations
│   └── logs/
│       └── laravel.log                        # Application logs
│
├── .env                                       # Environment configuration
├── .env.example                               # Environment template
├── composer.json                              # PHP dependencies
├── package.json                               # Node.js dependencies
├── requirements.txt                           # Python dependencies
├── vite.config.js                             # Vite configuration
├── tsconfig.json                              # TypeScript configuration
├── tailwind.config.js                         # Tailwind CSS configuration
└── index.html                                 # Vite HTML entry point
```

---

## 🧪 Testing Guide

This section provides comprehensive testing instructions for DataVizEngine, including manual testing, automated unit tests, integration tests, and test criteria.

---

### 📋 Testing Overview

DataVizEngine includes three levels of testing:

1. **Manual Testing** - Browser and API testing for user workflows
2. **Unit Tests** - Isolated component testing (Backend + Frontend)
3. **Integration Tests** - End-to-end API workflow testing

---

### 1️⃣ Manual Testing

#### A. Browser Testing (User Interface)

**Prerequisites:**
- Both servers must be running (frontend on port 5000, backend on port 8000)
- Start servers with: `npm run dev` (in development) or the workflow

**Test Workflow:**

```
Step 1: Access Application
→ Open browser to http://localhost:5000
✅ Expected: Wikipedia Table Visualizer page loads with input form

Step 2: Extract Table Data
→ Enter URL: https://en.wikipedia.org/wiki/List_of_countries_by_population_(United_Nations)
→ Click "Extract Table Data" button
✅ Expected: Table preview appears showing first 10 rows
✅ Expected: Dropdown shows numeric columns (Population, Area, etc.)

Step 3: Generate Visualization
→ Select a numeric column from dropdown (e.g., "Population")
→ Choose chart type (Bar, Line, or Scatter)
→ Click "Generate Visualization" button
✅ Expected: Chart image appears below with proper labels
✅ Expected: High-quality PNG visualization with country names and values

Step 4: Error Handling
→ Clear the URL field and click "Extract Table Data"
✅ Expected: Validation error shown
→ Enter invalid URL: https://google.com
✅ Expected: Error message displayed
```

**Test Criteria:**

| Test Case | Input | Expected Output | Pass/Fail |
|-----------|-------|-----------------|-----------|
| Valid Wikipedia URL | `https://en.wikipedia.org/wiki/List_of_countries_by_population_(United_Nations)` | Table data with headers and rows | ✅ |
| Empty URL | `` (empty string) | Validation error message | ✅ |
| Non-Wikipedia URL | `https://google.com` | "Invalid URL" error | ✅ |
| Wikipedia page without tables | `https://en.wikipedia.org/wiki/Wikipedia` | "No table found" error | ✅ |
| Valid visualization request | Table data + column + chart type | PNG chart image | ✅ |

---

#### B. API Testing (Backend Endpoints)

**Test Extract Table API:**

```bash
# Test 1: Valid Wikipedia URL
curl -X POST http://localhost:8000/api/extract-table \
  -H "Content-Type: application/json" \
  -d '{"url":"https://en.wikipedia.org/wiki/List_of_countries_by_population_(United_Nations)"}'

# ✅ Expected Response (200 OK):
# {
#   "headers": ["Country", "Population", "Date", "% of world", "Source"],
#   "rows": [
#     ["China", "1411750000", "2 Jul 2023", "17.5%", "National estimate"],
#     ["India", "1392329000", "2 Jul 2023", "17.3%", "Official projection"]
#   ],
#   "numericColumns": ["Population"]
# }

# Test 2: Missing URL (should fail)
curl -X POST http://localhost:8000/api/extract-table \
  -H "Content-Type: application/json" \
  -d '{}'

# ✅ Expected Response (422 Unprocessable Entity):
# {
#   "message": "The url field is required.",
#   "errors": {
#     "url": ["The url field is required."]
#   }
# }

# Test 3: Invalid URL (should fail)
curl -X POST http://localhost:8000/api/extract-table \
  -H "Content-Type: application/json" \
  -d '{"url":"https://google.com"}'

# ✅ Expected Response (422 Unprocessable Entity):
# {
#   "message": "The url field must be a valid Wikipedia URL.",
#   "errors": {
#     "url": ["The url field must be a valid Wikipedia URL."]
#   }
# }
```

**Test Generate Visualization API:**

```bash
# Test 1: Valid visualization request
curl -X POST http://localhost:8000/api/generate-visualization \
  -H "Content-Type: application/json" \
  -d '{
    "tableData": {
      "headers": ["Country", "Population"],
      "rows": [
        ["China", "1411750000"],
        ["India", "1392329000"],
        ["USA", "334914895"]
      ]
    },
    "selectedColumns": ["Population"],
    "chartType": "bar"
  }'

# ✅ Expected Response (200 OK):
# {
#   "id": 1,
#   "imageUrl": "/storage/viz_abc123def456.png",
#   "chartType": "bar"
# }

# Test 2: Missing required fields (should fail)
curl -X POST http://localhost:8000/api/generate-visualization \
  -H "Content-Type: application/json" \
  -d '{}'

# ✅ Expected Response (422 Unprocessable Entity):
# Validation errors for missing fields
```

**Example Wikipedia URLs for Testing:**

| Category | URL | Expected Numeric Columns |
|----------|-----|-------------------------|
| **Population** | `https://en.wikipedia.org/wiki/List_of_countries_by_population_(United_Nations)` | Population |
| **GDP** | `https://en.wikipedia.org/wiki/List_of_countries_by_GDP_(nominal)` | GDP (Millions USD), Per capita |
| **Olympics** | `https://en.wikipedia.org/wiki/All-time_Olympic_Games_medal_table` | Gold, Silver, Bronze, Total |
| **Area** | `https://en.wikipedia.org/wiki/List_of_countries_by_area` | Total area (km²) |

---

### 2️⃣ Automated Unit Tests

#### A. Backend Tests (Laravel PHPUnit)

**Running Backend Tests:**

```bash
# Run all backend tests
php artisan test

# Run specific test file
php artisan test tests/Unit/WikipediaExtractorTest.php

# Run with coverage report
php artisan test --coverage

# Run only unit tests
php artisan test --testsuite=Unit

# Run only feature tests
php artisan test --testsuite=Feature
```

**Backend Test Files:**

1. **Unit Tests** (`tests/Unit/WikipediaExtractorTest.php`)
   - Tests Wikipedia scraping service
   - Tests table header extraction
   - Tests numeric column detection
   - Tests data cleaning (removing commas from numbers)
   - Tests error handling for invalid URLs

2. **Feature Tests** (`tests/Feature/WikipediaApiTest.php`)
   - Tests API endpoint validation
   - Tests request/response format
   - Tests error responses
   - Tests required fields

**Test Criteria (Backend Unit Tests):**

| Test | Input | Expected Output | Pass Criteria |
|------|-------|-----------------|---------------|
| Extract headers from `<thead>` | HTML table with `<thead>` | Array of header strings | Headers match exactly |
| Extract headers from first `<tr>` | HTML table without `<thead>` | Array of header strings | Headers extracted from first row |
| Identify numeric columns | Table with mixed data types | Array of numeric column names | Only columns with ≥70% numeric values |
| Clean numeric values | "1,411,778,724" | "1411778724" | Commas removed |
| Invalid URL rejection | `https://google.com` | Exception thrown | Exception message contains "Invalid Wikipedia URL" |
| No table found | HTML without tables | Exception thrown | Exception message contains "No table found" |

**Example: Running Unit Tests**

```bash
$ php artisan test tests/Unit/WikipediaExtractorTest.php

   PASS  Tests\Unit\WikipediaExtractorTest
  ✓ extracts table headers from thead                     0.15s  
  ✓ identifies numeric columns                            0.08s  
  ✓ handles table without thead                           0.06s  
  ✓ cleans numeric values                                 0.05s  
  ✓ throws exception for invalid url                      0.03s  
  ✓ throws exception when no table found                  0.04s  

  Tests:    6 passed (6 assertions)
  Duration: 0.41s
```

---

#### B. Frontend Tests (Vitest + React Testing Library)

**Running Frontend Tests:**

```bash
# Run all frontend tests
npm test

# Run tests in watch mode (auto-rerun on changes)
npm test -- --watch

# Run tests with UI
npm run test:ui

# Run tests with coverage report
npm run test:coverage

# Run specific test file
npm test -- tests/frontend/App.test.tsx
```

**Frontend Test Files:**

1. **Component Tests** (`tests/frontend/App.test.tsx`)
   - Tests component rendering
   - Tests user interactions (form submission, button clicks)
   - Tests loading states
   - Tests error display
   - Tests table data display

**Test Criteria (Frontend Unit Tests):**

| Test | User Action | Expected Behavior | Pass Criteria |
|------|-------------|-------------------|---------------|
| Renders heading | Page load | "Wikipedia Table Visualizer" visible | Element found in DOM |
| Renders input | Page load | URL input field visible | Placeholder text matches |
| Shows loading state | Click "Extract Table Data" | Button text changes to "Loading..." | Button disabled during request |
| Displays table data | Successful API response | Table rows rendered | Data from API visible |
| Shows error message | Failed API response | Error message displayed | Error text matches |
| Validates empty URL | Submit empty form | Validation error | Required attribute enforced |

**Example: Running Frontend Tests**

```bash
$ npm test

 ✓ tests/frontend/App.test.tsx (7)
   ✓ App Component (7)
     ✓ renders Wikipedia Table Visualizer heading
     ✓ renders URL input field
     ✓ renders Extract Table Data button
     ✓ shows error when URL is empty on submit
     ✓ displays loading state during API request
     ✓ displays table data after successful extraction
     ✓ displays error message when API fails

 Test Files  1 passed (1)
      Tests  7 passed (7)
   Start at  14:32:15
   Duration  1.23s
```

---

### 3️⃣ Integration Tests

Integration tests verify the entire workflow from frontend to backend to Python visualization.

**Running Integration Tests:**

```bash
# Run Laravel feature tests (API integration)
php artisan test --testsuite=Feature

# These tests verify:
# - API routing works correctly
# - Request validation is enforced
# - Response format is correct
# - Error handling is consistent
```

**Integration Test Scenarios:**

| Scenario | Steps | Expected Result |
|----------|-------|-----------------|
| **Complete workflow** | 1. POST to `/api/extract-table`<br>2. Verify response contains headers, rows, numericColumns<br>3. POST to `/api/generate-visualization`<br>4. Verify image file created | All steps succeed, image file exists |
| **Validation chain** | 1. POST with empty body<br>2. POST with invalid URL<br>3. POST with non-Wikipedia URL | All return 422 with appropriate error messages |
| **Error propagation** | 1. Request page that doesn't exist<br>2. Request page without tables | Errors caught and returned as JSON |

---

### 4️⃣ Complete Test Suite Execution

**Run All Tests (Backend + Frontend):**

```bash
# Terminal 1: Run backend tests
php artisan test

# Terminal 2: Run frontend tests
npm test

# Or create a test script (add to package.json):
# "test:all": "php artisan test && npm test"
```

**Test Coverage Goals:**

- **Backend Unit Tests**: 80%+ coverage of `app/Services/` and `app/Http/Controllers/`
- **Frontend Unit Tests**: 70%+ coverage of React components
- **Integration Tests**: All API endpoints must have feature tests

---

### 5️⃣ Test Data & Examples

**Sample Table Data for Testing:**

```javascript
// Minimal valid table data
{
  "headers": ["Country", "Population"],
  "rows": [
    ["China", "1411750000"],
    ["India", "1392329000"]
  ]
}

// Table with multiple numeric columns
{
  "headers": ["Country", "Population", "GDP", "Area"],
  "rows": [
    ["China", "1411750000", "14720000", "9596961"],
    ["India", "1392329000", "2875000", "3287263"],
    ["USA", "334914895", "20940000", "9833520"]
  ]
}

// Table with non-numeric column (should not be detected)
{
  "headers": ["Country", "Capital", "Population"],
  "rows": [
    ["China", "Beijing", "1411750000"],
    ["India", "New Delhi", "1392329000"]
  ]
}
```

---

### 6️⃣ Testing Checklist

Before deploying or marking a feature complete, verify:

- [ ] All backend unit tests pass (`php artisan test`)
- [ ] All frontend unit tests pass (`npm test`)
- [ ] Manual browser testing successful
- [ ] API endpoints respond correctly (test with curl)
- [ ] Error messages are clear and helpful
- [ ] Loading states work properly
- [ ] Generated visualizations display correctly
- [ ] No console errors in browser
- [ ] No errors in Laravel logs (`storage/logs/laravel.log`)

---

### 7️⃣ Continuous Testing (Development)

**Watch Mode for Auto-Testing:**

```bash
# Frontend: Auto-run tests on file changes
npm test -- --watch

# Backend: Use Laravel Pint for code quality
./vendor/bin/pint

# Check code style
./vendor/bin/pint --test
```

**Pre-Commit Testing:**

```bash
# Quick smoke test before committing
php artisan test --testsuite=Feature && npm test
```

---

### 🐛 Debugging Failed Tests

**Backend Test Failures:**

```bash
# View detailed error output
php artisan test --testdox

# Run single test with more verbosity
php artisan test --filter=test_extracts_table_headers_from_thead -vvv

# Check Laravel logs
tail -f storage/logs/laravel.log
```

**Frontend Test Failures:**

```bash
# Run with verbose output
npm test -- --reporter=verbose

# Run specific test
npm test -- -t "displays table data"

# Check browser console output (tests run in jsdom)
# Look for error messages in test output
```

---

### 📊 Test Reports

**Generate Coverage Reports:**

```bash
# Backend coverage (requires Xdebug)
php artisan test --coverage-html reports/backend

# Frontend coverage
npm run test:coverage
# Open coverage/index.html in browser
```

---

## 🚀 Production Deployment Guide (Ubuntu VPS + Nginx)

This section provides a **step-by-step guide** for deploying DataVizEngine on a Ubuntu VPS server with Nginx.

### 📋 Architecture Overview

**IMPORTANT**: This application has **TWO separate servers** running concurrently:

1. **Frontend Server (React/Vite)** - Runs on port **5000** (or 3000 in production)
2. **Backend Server (Laravel API)** - Runs on port **8000**

```
User Browser → Nginx (port 80) → Frontend Server (port 5000/3000)
                                          ↓ (proxies /api requests)
                                  Backend Server (port 8000)
```

**How API Communication Works:**

When your React app makes an API call like `fetch('/api/extract-table')`:
1. The frontend server (Vite) intercepts the `/api` request
2. Vite's proxy configuration (in `vite.config.js`) forwards it to `http://localhost:8000`
3. Laravel API processes the request and returns JSON
4. Frontend receives the response and updates the UI

**Configuration Location:** The proxy is configured in `vite.config.js`:
```javascript
proxy: {
    '/api': {
        target: 'http://localhost:8000',  // Backend server address
        changeOrigin: true,
    },
    '/storage': {
        target: 'http://localhost:8000',  // For serving generated images
        changeOrigin: true,
    },
}
```

---

### 📦 Prerequisites Installation

SSH into your Ubuntu VPS and install all required software:

```bash
# Update system packages
sudo apt update && sudo apt upgrade -y

# 1. Install Nginx
sudo apt install nginx -y

# 2. Install PHP 8.2 and required extensions
sudo apt install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install php8.2 php8.2-fpm php8.2-cli php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip php8.2-gd -y

# 3. Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# 4. Install Node.js 20.x
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install nodejs -y

# 5. Install Python 3.11 and pip
sudo apt install python3.11 python3.11-venv python3-pip -y

# 6. Install PM2 (Process Manager for Node.js)
sudo npm install -g pm2

# Verify installations
php --version        # Should show PHP 8.2.x
node --version       # Should show v20.x.x
python3.11 --version # Should show Python 3.11.x
nginx -v             # Should show nginx version
pm2 --version        # Should show PM2 version
```

---

### 🔧 Application Setup

```bash
# 1. Clone the repository
cd /var/www
sudo git clone https://github.com/your-username/DataVizEngine.git
sudo chown -R $USER:$USER DataVizEngine
cd DataVizEngine

# 2. Install PHP dependencies
composer install --optimize-autoloader --no-dev

# 3. Install Node.js dependencies
npm install

# 4. Install Python dependencies
python3.11 -m venv venv
source venv/bin/activate
pip install -r requirements.txt
deactivate

# 5. Set up environment file
cp .env.example .env
nano .env
```

**Configure your `.env` file:**
```env
APP_NAME=DataVizEngine
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://your-domain.com

DB_CONNECTION=sqlite
# Or use MySQL:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=dataviz
# DB_USERNAME=your_db_user
# DB_PASSWORD=your_db_password

# Important: Ensure these are set correctly
FILESYSTEM_DISK=public
```

```bash
# 6. Generate application key
php artisan key:generate

# 7. Create database and run migrations
touch database/database.sqlite  # If using SQLite
php artisan migrate --force

# 8. Create storage symlink
php artisan storage:link

# 9. Set proper permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# 10. Build production assets
npm run build
```

---

### ⚙️ Running Servers as Background Processes

We'll use **PM2** to manage both frontend and backend servers as background processes.

#### Create PM2 Ecosystem File

Create `ecosystem.config.js` in your project root:

```bash
nano ecosystem.config.js
```

Add this configuration:

```javascript
module.exports = {
  apps: [
    {
      name: 'dataviz-frontend',
      script: 'npx',
      args: 'vite preview --host 0.0.0.0 --port 3000',
      cwd: '/var/www/DataVizEngine',
      env: {
        NODE_ENV: 'production',
      },
      instances: 1,
      autorestart: true,
      watch: false,
      max_memory_restart: '500M',
    },
    {
      name: 'dataviz-backend',
      script: 'php',
      args: 'artisan serve --host=0.0.0.0 --port=8000',
      cwd: '/var/www/DataVizEngine',
      env: {
        APP_ENV: 'production',
      },
      instances: 1,
      autorestart: true,
      watch: false,
      max_memory_restart: '500M',
    },
  ],
};
```

#### Start and Manage Processes

```bash
# Start both servers
pm2 start ecosystem.config.js

# View running processes
pm2 list

# View logs
pm2 logs

# View specific app logs
pm2 logs dataviz-frontend
pm2 logs dataviz-backend

# Restart servers
pm2 restart all

# Stop servers
pm2 stop all

# Delete all processes
pm2 delete all

# Save PM2 configuration (to survive server reboots)
pm2 save

# Set PM2 to start on system boot
pm2 startup
# Follow the command it outputs
```

---

### 🌐 Nginx Configuration

Create an Nginx server block for your application:

```bash
sudo nano /etc/nginx/sites-available/dataviz
```

Add this configuration:

```nginx
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;  # Replace with your domain
    
    # Increase client body size for large table data
    client_max_body_size 20M;
    
    # Root directory (not used for React SPA, but good to define)
    root /var/www/DataVizEngine/public;
    
    # Logs
    access_log /var/log/nginx/dataviz-access.log;
    error_log /var/log/nginx/dataviz-error.log;
    
    # Proxy to React/Vite Frontend (port 3000)
    location / {
        proxy_pass http://localhost:3000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_cache_bypass $http_upgrade;
    }
    
    # Proxy API requests to Laravel Backend (port 8000)
    location /api {
        proxy_pass http://localhost:8000;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
    
    # Proxy storage requests (for generated visualizations)
    location /storage {
        proxy_pass http://localhost:8000;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
}
```

#### Enable the Site and Restart Nginx

```bash
# Create symbolic link to enable site
sudo ln -s /etc/nginx/sites-available/dataviz /etc/nginx/sites-enabled/

# Test Nginx configuration
sudo nginx -t

# Restart Nginx
sudo systemctl restart nginx

# Enable Nginx to start on boot
sudo systemctl enable nginx
```

---

### 🧪 Testing Your Deployment

#### 1. Test Backend API Directly

```bash
# Test Laravel API health
curl http://localhost:8000/up

# Test table extraction API
curl -X POST http://localhost:8000/api/extract-table \
  -H "Content-Type: application/json" \
  -d '{"url":"https://en.wikipedia.org/wiki/List_of_countries_by_population_(United_Nations)"}'

# You should get JSON response with headers, rows, and numericColumns
```

#### 2. Test Frontend Server

```bash
# Test frontend server
curl http://localhost:3000

# Should return HTML with React app
```

#### 3. Test Through Nginx (Port 80)

```bash
# Test main page
curl http://your-domain.com

# Test API through Nginx proxy
curl -X POST http://your-domain.com/api/extract-table \
  -H "Content-Type: application/json" \
  -d '{"url":"https://en.wikipedia.org/wiki/List_of_countries_by_population_(United_Nations)"}'
```

#### 4. Browser Testing

1. Open `http://your-domain.com` in a browser
2. Enter a Wikipedia URL with tables
3. Click "Extract Table Data"
4. Check browser Developer Tools → Network tab to see API requests

---

### 🐛 Troubleshooting Guide

#### ❌ 400 Bad Request Error

**What it means:** The server received a malformed request.

**Common Causes and Solutions:**

1. **Missing or Invalid JSON Body**
   ```bash
   # Wrong (will cause 400):
   curl -X POST http://localhost:8000/api/extract-table
   
   # Correct:
   curl -X POST http://localhost:8000/api/extract-table \
     -H "Content-Type: application/json" \
     -d '{"url":"https://en.wikipedia.org/wiki/..."}'
   ```

2. **Invalid Wikipedia URL**
   - Ensure URL is complete: `https://en.wikipedia.org/wiki/...`
   - Check URL validation in Laravel: The API expects a valid URL

3. **CORS Issues (if calling from different domain)**
   - Check `config/cors.php` settings
   - Ensure `bootstrap/app.php` has CORS middleware enabled

4. **Laravel Validation Error**
   ```bash
   # Check Laravel logs
   tail -f storage/logs/laravel.log
   
   # The error message will show which field failed validation
   ```

#### ❌ 404 Not Found Error

**Possible Causes:**

1. **API routes not loaded**
   ```bash
   # Clear route cache
   php artisan route:clear
   php artisan route:cache
   
   # List all routes to verify
   php artisan route:list | grep api
   ```

2. **Nginx misconfiguration**
   ```bash
   # Check Nginx error logs
   sudo tail -f /var/log/nginx/dataviz-error.log
   ```

#### ❌ 500 Internal Server Error

**Solutions:**

```bash
# 1. Check Laravel logs
tail -f storage/logs/laravel.log

# 2. Check PHP-FPM logs
sudo tail -f /var/log/php8.2-fpm.log

# 3. Check file permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# 4. Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

#### ❌ Python Visualization Not Working

**Solutions:**

```bash
# 1. Test Python script directly
cd /var/www/DataVizEngine
source venv/bin/activate
python scripts/generate_visualization.py --help

# 2. Check Python is accessible to PHP
which python3.11

# 3. Verify Python packages installed
pip list | grep -E "matplotlib|seaborn|pandas"

# 4. Check storage directory permissions
ls -la storage/app/public/
```

#### ❌ Frontend Not Loading / Blank Page

**Solutions:**

```bash
# 1. Check if frontend server is running
pm2 list
pm2 logs dataviz-frontend

# 2. Test frontend directly
curl http://localhost:3000

# 3. Rebuild assets
npm run build
pm2 restart dataviz-frontend

# 4. Check browser console for errors (F12)
```

#### ❌ Images Not Displaying (404 for /storage/viz_*.png)

**Solutions:**

```bash
# 1. Verify storage symlink exists
ls -la public/storage

# If not, create it:
php artisan storage:link

# 2. Check generated images exist
ls -la storage/app/public/

# 3. Verify permissions
sudo chmod -R 775 storage/app/public/
```

---

### 📊 Monitoring and Maintenance

```bash
# View PM2 process status
pm2 monit

# View real-time logs
pm2 logs --lines 100

# Check disk space
df -h

# Check memory usage
free -h

# Restart all services
pm2 restart all
sudo systemctl restart nginx

# View Nginx access logs
sudo tail -f /var/log/nginx/dataviz-access.log
```

---

### 🔒 Optional: SSL/HTTPS Setup with Let's Encrypt

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx -y

# Obtain SSL certificate
sudo certbot --nginx -d your-domain.com -d www.your-domain.com

# Certbot will automatically configure Nginx for HTTPS
# Your site will now be accessible at https://your-domain.com

# Auto-renew test
sudo certbot renew --dry-run
```

---

### 📝 Quick Reference Commands

```bash
# Start servers
pm2 start ecosystem.config.js

# Stop servers
pm2 stop all

# View logs
pm2 logs

# Restart Nginx
sudo systemctl restart nginx

# Clear Laravel cache
php artisan cache:clear && php artisan config:clear

# Rebuild frontend
npm run build && pm2 restart dataviz-frontend
```

---

## 🔄 Development Workflow

```
1. User enters URL → 2. Extract table → 3. Analyze columns → 4. Preview data
         ↓                                                           ↑
8. Display chart ← 7. Return image ← 6. Generate PNG ← 5. Select options
```

**For Developers:**
```
1. Edit React component (App.tsx)
2. Vite HMR updates browser automatically
3. No manual refresh needed
4. API changes require Laravel server restart
```

---

## 🤝 Contributing

Contributions are welcome! Please follow these guidelines:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 🙏 Acknowledgments

- **Laravel** - Elegant PHP framework
- **React** - Powerful UI library
- **Vite** - Lightning-fast build tool
- **Matplotlib & Seaborn** - Professional visualization libraries
- **Wikipedia** - Open knowledge source

---

<p align="center">Built with ❤️ using React, Laravel, and Python</p>
<p align="center">© 2025 DataVizEngine</p>
