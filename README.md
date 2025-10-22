
# DataVizEngine

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![PHP](https://img.shields.io/badge/PHP-8.1+-green.svg)
![Laravel](https://img.shields.io/badge/Laravel-10.x-red.svg)
![Python](https://img.shields.io/badge/Python-3.9+-blue.svg)
![Seaborn](https://img.shields.io/badge/Seaborn-0.12.x-orange.svg)
![License](https://img.shields.io/badge/license-MIT-green.svg)

## 🚀 Bridging Worlds: Web Application Meets Data Science

**DataVizEngine** is a groundbreaking integration platform that seamlessly connects Laravel's robust web framework capabilities with Python's scientific computing ecosystem. This project represents a paradigm shift in how web applications can harness the power of advanced data visualization without compromising on performance or developer experience.

<p align="center">
  <img src="docs/architecture-diagram.png" alt="DataVizEngine Architecture" width="720">
</p>

### 🌟 Key Innovations

- **Cross-Language Synergy**: Bridged the gap between PHP and Python ecosystems
- **Real-Time Processing**: Asynchronous processing of data visualization requests
- **Dynamic Rendering**: On-demand generation of publication-quality visualizations
- **Developer-Centric Design**: Intuitive APIs that abstract away complexity

## 🔧 Technology Stack

The project leverages cutting-edge technologies to deliver a seamless experience:

- **Frontend**: Laravel Blade + Alpine.js + Tailwind CSS
- **Backend**: Laravel 10+ Framework
- **Data Processing**: Python 3.9+ with NumPy and Pandas
- **Visualization**: Seaborn with Matplotlib
- **Integration**: Custom PHP-Python bridge with process management
- **Storage**: MySQL + filesystem-based visualization cache

## 📊 Visualization Capabilities

DataVizEngine supports a wide range of visualization types through Seaborn:

- Statistical plots (boxplots, violinplots)
- Distribution plots (histograms, KDEs)
- Relational plots (scatterplots, lineplots)
- Categorical plots (barplots, countplots)
- Matrix plots (heatmaps, clustermap)
- Regression plots (regplot, residplot)

## 💻 Quick Start

### Prerequisites

- PHP 8.1+
- Composer
- Python 3.9+
- Node.js and NPM

### Installation

```bash
# Clone the repository
git clone -b main https://github.com/dieterlab969/DataVizEngine.git
cd DataVizEngine

# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install

# Set up environment
cp .env.example .env
php artisan key:generate

# Configure your database in .env
# ...

# Run migrations
php artisan migrate

# Set up Python environment
python -m venv venv
source venv/bin/activate  # On Windows: venv\Scripts\activate
pip install -r requirements.txt

# Build assets
npm run build

# Start the development server
php artisan serve
```

## 🔍 Usage Examples

### Creating a Basic Visualization

```php
// In your controller
public function generateVisualization(Request $request)
{
    $data = $request->validate([
        'dataset' => 'required|array',
        'type' => 'required|in:bar,scatter,line,heatmap,box',
        'x_column' => 'required|string',
        'y_column' => 'required|string',
        'title' => 'nullable|string',
    ]);
    
    $vizService = new VisualizationService();
    $result = $vizService->generateVisualization($data);
    
    return response()->json([
        'success' => true,
        'visualization_url' => asset('visualizations/' . $result['file']),
        'metadata' => $result['metadata'] ?? null,
    ]);
}
```

### Python Visualization Component

```python
def create_visualization(data, type, x_column, y_column, title=None):
    """
    Create a visualization based on provided parameters
    """
    df = pd.DataFrame(data['dataset'])
    
    plt.figure(figsize=(10, 6))
    sns.set_theme(style="whitegrid")
    
    if type == 'bar':
        ax = sns.barplot(x=x_column, y=y_column, data=df)
    elif type == 'scatter':
        ax = sns.scatterplot(x=x_column, y=y_column, data=df)
    # Additional plot types...
    
    if title:
        plt.title(title)
        
    filename = f"viz_{uuid.uuid4()}.png"
    filepath = os.path.join(OUTPUT_DIR, filename)
    plt.savefig(filepath, bbox_inches='tight', dpi=300)
    
    return {
        "status": "success",
        "file": filename,
        "metadata": {
            "dimensions": plt.gcf().get_size_inches(),
            "data_points": len(df),
            "columns_used": [x_column, y_column]
        }
    }
```

## 🛠️ Advanced Features

### 1. Intelligent Caching System

DataVizEngine implements a smart caching system that:
- Stores generated visualizations with their parameters
- Automatically regenerates when underlying data changes
- Efficiently serves cached visualizations for identical parameters

```php
// Example of the cache-aware visualization service
public function getOrGenerateVisualization(array $params)
{
    $cacheKey = $this->generateCacheKey($params);
    
    if ($this->visualizationCache->has($cacheKey)) {
        return $this->visualizationCache->get($cacheKey);
    }
    
    $result = $this->generateVisualization($params);
    $this->visualizationCache->put($cacheKey, $result, now()->addDays(7));
    
    return $result;
}
```

### 2. Asynchronous Processing

For complex visualizations, DataVizEngine leverages Laravel's queue system:

```php
// Dispatch a job to generate visualization asynchronously
VisualizationJob::dispatch($data)
    ->onQueue('visualizations');
```

### 3. Interactive Visualizations

Beyond static images, DataVizEngine can generate interactive visualizations using Plotly:

```python
def create_interactive_visualization(data, type):
    """Generate an interactive visualization using Plotly"""
    df = pd.DataFrame(data['dataset'])
    
    if type == 'scatter':
        fig = px.scatter(df, x=data['x_column'], y=data['y_column'])
    # Additional plot types...
    
    html_file = f"interactive_{uuid.uuid4()}.html"
    filepath = os.path.join(OUTPUT_DIR, html_file)
    fig.write_html(filepath)
    
    return {
        "status": "success",
        "file": html_file,
        "type": "interactive"
    }
```

## 🌈 Future Roadmap

The DataVizEngine is designed with extensibility in mind:

- **Machine Learning Integration**: Predictive analytics and model visualization
- **Real-time Data Processing**: WebSocket-based live updating visualizations
- **Data Source Connectors**: Integration with various data sources (APIs, databases)
- **Customizable Themes**: Advanced styling options for visualizations
- **Export Capabilities**: PDF reports and presentation-ready exports

## 📚 Documentation

For comprehensive documentation, please visit:
- [Installation Guide](docs/installation.md)
- [API Reference](docs/api.md)
- [Visualization Types](docs/visualizations.md)
- [Advanced Configuration](docs/configuration.md)
- [Examples & Tutorials](docs/examples.md)

## 🔄 Development Workflow

```
1. Define data structure ➡️ 2. Configure visualization ➡️ 3. Process in Python
       ⬆️                                                           ⬇️
6. Analyze & iterate ⬅️ 5. Display in Laravel app ⬅️ 4. Return results
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

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

<p align="center">Built with ❤️ by [Dieter R.]</p>
<p align="center">© 2025 DataVizEngine</p>
