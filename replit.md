# DataVizEngine - Development Notes (Replit)

**For full documentation, see [README.md](README.md)**

## Project Overview
DataVizEngine is a modern web application that extracts data from Wikipedia tables and generates visualizations using Python. Built with React 18 (frontend), Laravel 12 (API backend), and Python 3.11 (visualization engine).

**Last Updated:** October 22, 2025

---

## Quick Start (Replit Environment)

### Running the Application
```bash
# Both servers start automatically via workflow
# Frontend: http://localhost:5000 (Vite dev server)
# Backend: http://localhost:8000 (Laravel API)
```

### Technology Stack
- **Frontend:** React 18 + TypeScript + Vite 7 + Tailwind CSS 4.0
- **Backend:** Laravel 12 (PHP 8.2+)
- **Visualization:** Python 3.11 with Matplotlib, Seaborn, Pandas, NumPy
- **Database:** SQLite (development)

---

## Architecture

```
React Frontend (Port 5000) ← Vite Proxy → Laravel API (Port 8000)
                                                  ↓
                                          Python Scripts
                                                  ↓
                                          Generated Images
```

**API Endpoints:**
- `POST /api/extract-table` - Extract Wikipedia table data
- `POST /api/generate-visualization` - Generate chart image

**Proxy Configuration:** `vite.config.js` forwards `/api` and `/storage` requests from port 5000 to port 8000.

---

## Project Structure

```
app/
├── Http/Controllers/Api/WikipediaController.php   # API endpoints
├── Services/WikipediaExtractor.php                # Wikipedia scraping
└── Models/                                        # Database models

resources/
├── js/
│   ├── App.tsx                                   # Main React component
│   └── main.tsx                                  # React entry point
└── css/
    └── app.css                                   # Tailwind CSS

scripts/
└── generate_visualization.py                     # Python visualization

routes/
├── api.php                                       # API routes
└── web.php                                       # Web routes

public/
└── storage/                                      # Generated images (symlinked)

vite.config.js                                    # Vite config with proxy
index.html                                        # Vite entry point
```

---

## Development Workflow

### Concurrent Servers (Workflow)
```bash
npx concurrently -c "#93c5fd,#c4b5fd" \
  "npm run dev -- --port 5000" \
  "php artisan serve --host=0.0.0.0 --port=8000" \
  --names=vite,api --kill-others
```

### Key Configuration

**Vite (vite.config.js):**
- Port 5000 for frontend
- Proxies `/api` → `http://localhost:8000`
- Proxies `/storage` → `http://localhost:8000`
- HMR configured for Replit proxy environment
- File watchers ignore vendor, node_modules, .pythonlibs

**Laravel:**
- API server on port 8000
- CORS enabled for all origins (development)
- Storage symlinked to public directory

---

## Recent Changes

### October 22, 2025
- Merged replit.md content into comprehensive README.md
- Fixed architecture diagram to reflect React SPA architecture
- Added complete production deployment guide with PM2 and Nginx

### October 21, 2025
- Implemented React 18 + TypeScript frontend with Vite
- Created WikipediaExtractor service with Guzzle and DomCrawler
- Fixed critical row-trimming bug in table extraction (architect-verified)
- Configured CORS and Vite proxy for API access
- Integrated Python visualization script with Laravel
- Added comprehensive error handling and loading states

---

## User Preferences
- Modern React SPA architecture (not Laravel Blade)
- TypeScript for type safety
- RESTful API design
- Responsive UI with Tailwind CSS
- Clear, professional documentation

---

## Testing

**Quick Test:**
1. Go to http://localhost:5000
2. Enter Wikipedia URL: `https://en.wikipedia.org/wiki/Women's_high_jump_world_record_progression`
3. Click "Extract Table Data"
4. Select numeric column (Mark) and chart type
5. Click "Generate Visualization"

**API Testing:**
```bash
curl -X POST http://localhost:8000/api/extract-table \
  -H "Content-Type: application/json" \
  -d '{"url":"https://en.wikipedia.org/wiki/Women'\''s_high_jump_world_record_progression"}'
```

---

## Notes

- Frontend uses Vite dev server with Hot Module Replacement (HMR)
- Backend API runs separately on port 8000 with CORS enabled
- Python script executed via Laravel's Process facade
- Images stored in `storage/app/public/` and accessed via symlink
- File watchers configured to prevent ENOSPC errors in Replit

**For production deployment, monitoring, troubleshooting, and full documentation, see [README.md](README.md).**
