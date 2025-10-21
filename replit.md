# DataVizEngine - Laravel API + React SPA Data Visualization Platform

## Project Overview
DataVizEngine is a modern web application that extracts data from Wikipedia tables and generates beautiful visualizations using Python's Matplotlib and Seaborn libraries. Built with Laravel 12 (API backend), React 18 (frontend), and Python 3.11 (visualization engine).

**Last Updated:** October 21, 2025

## Architecture

### Technology Stack
- **Frontend:** React 18 + TypeScript + Vite 7 + Tailwind CSS 4.0
- **Backend API:** Laravel 12 (PHP 8.2+)
- **Data Visualization:** Python 3.11 with Matplotlib, Seaborn, Pandas, NumPy
- **Database:** SQLite (development), supports MySQL/PostgreSQL for production
- **Build Tool:** Vite with HMR support

### Key Features
1. **Wikipedia Data Extraction:** Fetch and parse Wikipedia tables using Guzzle HTTP client and DomCrawler
2. **Smart Column Detection:** Automatically identifies numeric columns for visualization
3. **Multiple Chart Types:** Support for bar charts, line charts, and scatter plots
4. **Real-time Preview:** View extracted table data before generating visualizations
5. **Python Integration:** Laravel seamlessly executes Python scripts for chart generation

## How It Works

### Data Flow
1. User enters Wikipedia URL in React frontend
2. React sends POST request to Laravel API (`/api/extract-table`)
3. Laravel fetches Wikipedia page and extracts table data
4. Frontend displays extracted data with column selection
5. User selects numeric column and chart type
6. React sends visualization request to Laravel API (`/api/generate-visualization`)
7. Laravel prepares data and calls Python visualization script
8. Python generates PNG image using Matplotlib/Seaborn
9. Image is stored and URL returned to frontend
10. React displays the visualization

## Development Environment

### Prerequisites (Already Installed)
- PHP 8.2 with Composer
- Python 3.11 with pip
- Node.js 20 with npm

### Running the Application
The application uses a workflow with two concurrent processes:

1. **Vite Dev Server** (port 5000) - Serves React frontend with HMR
2. **Laravel API Server** (port 8000) - Handles API requests

**Workflow Command:**
```bash
npx concurrently -c "#93c5fd,#c4b5fd" "npm run dev -- --port 5000" "php artisan serve --host=0.0.0.0 --port=8000" --names=vite,api --kill-others
```

### API Endpoints

- `POST /api/extract-table` - Extract table data from Wikipedia URL
- `POST /api/generate-visualization` - Generate chart from table data

### Configuration Notes

#### Vite Configuration
- Host: `0.0.0.0` on port 5000 (frontend)
- Proxies `/api` and `/storage` requests to Laravel on port 8000
- File watcher configured to ignore Python libs, vendor, and cache directories
- HMR configured for Replit's proxy environment

#### Laravel Configuration
- API server runs on `0.0.0.0:8000`
- CORS enabled for all origins (development)
- Storage linked to public directory
- API routes defined in `routes/api.php`

## Project Structure

```
app/
├── Http/Controllers/Api/
│   └── WikipediaController.php      # API endpoints for data extraction & viz
├── Services/
│   └── WikipediaExtractor.php       # Wikipedia scraping & data processing
└── Models/                          # Database models (optional for saving history)

resources/
├── js/
│   ├── App.tsx                      # Main React component
│   ├── main.tsx                     # React entry point
│   └── bootstrap.js                 # Laravel Echo/Axios config
└── css/
    └── app.css                      # Tailwind CSS

scripts/
└── generate_visualization.py        # Python visualization script

routes/
├── api.php                          # API routes
└── web.php                          # Web routes

public/
└── storage/                         # Generated visualizations (symlinked)

index.html                           # Vite HTML entry point
vite.config.js                       # Vite configuration with React plugin
tsconfig.json                        # TypeScript configuration
```

## Key Components

### Frontend (React)
- **App.tsx**: Main application component with form, table display, and visualization viewer
- URL input with validation
- Table data preview (first 10 rows)
- Column selector for numeric columns
- Chart type selector (bar, line, scatter)
- Loading and error states

### Backend (Laravel)
- **WikipediaExtractor Service**: Handles Wikipedia page fetching, HTML parsing, and column detection
- **WikipediaController**: REST API endpoints for extraction and visualization
- Guzzle HTTP client for Wikipedia requests
- Symfony DomCrawler for HTML parsing
- Process execution for Python scripts

### Visualization (Python)
- Accepts JSON input with labels, values, and metadata
- Generates high-quality PNG images
- Supports multiple chart types
- Intelligent chart type selection
- Configurable DPI for image quality

## Deployment

**Target:** VM (stateful deployment)
- **Build:** `npm run build` (builds production React app)
- **Run:** Production would use built assets with Laravel serving API and static files
- Port: 5000

The application requires VM deployment because:
1. Python script execution requires persistent environment
2. File storage for generated images
3. API backend needs to remain running

## Recent Changes

### October 21, 2025 - Full Implementation
- Set up React 18 + TypeScript frontend with Vite
- Implemented Wikipedia data extraction service with Guzzle and DomCrawler
- Created REST API endpoints for table extraction and visualization
- Configured CORS for API access
- Set up Vite proxy to Laravel API backend
- Configured file watchers to avoid system limits
- Created responsive React UI with Tailwind CSS
- Integrated Python visualization script with Laravel
- Added error handling and loading states
- Configured storage symlink for image access

## User Preferences
- Modern React SPA architecture
- TypeScript for type safety
- RESTful API design
- Responsive UI with Tailwind CSS

## Testing

To test the application:

1. Enter a Wikipedia URL with tables (e.g., https://en.wikipedia.org/wiki/List_of_countries_by_population_(United_Nations))
2. Click "Extract Table Data"
3. Review extracted table (first 10 rows shown)
4. Select a numeric column from dropdown
5. Choose chart type (bar, line, or scatter)
6. Click "Generate Visualization"
7. View the generated chart

## Notes
- Frontend runs on Vite dev server with hot module replacement
- API backend runs on separate port (8000) with CORS enabled
- Python script is called via Laravel's Process facade
- Generated images are stored in `storage/app/public/` and accessed via symlink
- File watchers configured to prevent ENOSPC errors in Replit environment
