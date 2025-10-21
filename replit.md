# DataVizEngine - Laravel + Python Data Visualization Platform

## Project Overview
DataVizEngine is a Laravel 12 application that integrates Python data visualization capabilities (using Matplotlib, Seaborn, Pandas) to generate publication-quality charts from Wikipedia table data and other data sources.

**Last Updated:** October 21, 2025

## Architecture

### Technology Stack
- **Backend Framework:** Laravel 12 (PHP 8.2+)
- **Frontend:** Laravel Blade templates with Tailwind CSS 4.0 and Vite 7
- **Data Visualization:** Python 3.11 with Matplotlib, Seaborn, Pandas, NumPy
- **Database:** SQLite (development), supports MySQL/PostgreSQL for production
- **Queue System:** Laravel Queue (database driver) for async job processing
- **Asset Building:** Vite with HMR support

### Key Components
1. **Page Request System:** Users can input Wikipedia URLs or data sources
2. **Table Data Extraction:** Processes tabular data for visualization
3. **Python Visualization Engine:** `scripts/generate_visualization.py` generates charts
4. **Async Job Processing:** Queue worker processes visualization requests asynchronously
5. **Storage System:** Generated visualizations stored in public/storage

## Development Environment Setup

### Prerequisites (Already Installed via Replit Modules)
- PHP 8.2 with Composer
- Python 3.11 with pip
- Node.js 20 with npm

### Database
- Uses SQLite by default (`database/database.sqlite`)
- Migrations have been run successfully

### Python Dependencies
All Python dependencies are listed in `requirements.txt`:
- matplotlib>=3.5.0
- seaborn>=0.12.0
- pandas>=1.5.0
- numpy>=1.23.0

### Running the Application
The application uses a workflow that runs:
1. PHP Artisan server on port 5000 (0.0.0.0)
2. Queue listener for background jobs
3. Pail for log tailing
4. Vite dev server for HMR on port 5173

**Command:** Already configured as "Server" workflow

### Important Configuration Notes

#### Vite Configuration
The `vite.config.js` has been configured for Replit environment with:
- Host: `0.0.0.0` to allow external access
- HMR configured for Replit proxy support
- Port: 5173 (internal Vite dev server)

#### Laravel Server
- Serves on `0.0.0.0:5000` in development
- Configured for Replit's proxy system

## Project Structure

```
app/
├── Http/Controllers/
│   └── PageRequestController.php    # Main controller for data viz requests
├── Jobs/
│   └── ProcessPageRequestJob.php    # Queue job for async processing
└── Models/
    ├── PageRequest.php              # Stores user requests
    ├── TableData.php                # Extracted table data
    └── Visualization.php            # Generated visualization metadata

resources/
├── views/
│   ├── page_requests/               # UI for creating/viewing requests
│   └── components/                  # Blade components

scripts/
└── generate_visualization.py        # Python script for chart generation

database/migrations/                  # Database schema
```

## Deployment

**Target:** VM (stateful deployment)
- **Build:** `npm run build` (builds production assets)
- **Run:** Concurrent PHP server and queue worker
- Port: 5000

The application requires a VM deployment (not autoscale) because:
1. Needs persistent queue worker
2. SQLite database requires filesystem persistence
3. Background job processing

## Recent Changes (Initial Setup)
- October 21, 2025: Imported from GitHub and configured for Replit
  - Installed all PHP, Python, and Node.js dependencies
  - Configured Vite for Replit proxy support
  - Set up SQLite database and ran migrations
  - Created workflow for development server with queue and logging
  - Configured deployment settings for production
  - Added Python-specific entries to .gitignore

## User Preferences
- No specific preferences set yet

## Notes
- The application uses Laravel's queue system with database driver for job processing
- Python visualization script accepts JSON input and outputs PNG images
- Vite HMR is configured to work through Replit's proxy
- All environment variables are stored in `.env` (not tracked in git)
