# 🎬 AfricanStreams - African Content Streaming Platform

A modern, feature-rich streaming platform built with Laravel 11, designed to showcase and distribute African movies, TV shows, and series. This platform provides a Netflix-like experience with comprehensive content management, user subscriptions, and advanced analytics.

## ✨ Features

### 🎥 Content Management
- **Movies & TV Shows**: Upload, categorize, and manage African content
- **Episodes & Seasons**: Full series management with episode tracking
- **Genres & Categories**: Flexible content organization system
- **Media Assets**: Support for both file uploads and URL-based content
- **Trailers & Thumbnails**: Rich media previews for content discovery

### 👥 User Management
- **JWT Authentication**: Secure user authentication and authorization
- **User Profiles**: Customizable profiles with avatar support
- **Subscription Plans**: Multiple subscription tiers with Paystack integration
- **Watchlists & Favorites**: Personalized content curation
- **Watch History**: Track viewing progress and preferences

### 🔐 Admin Panel
- **Admin Authentication**: Secure admin login with role-based access
- **Content Moderation**: Approve, edit, and manage all content
- **User Management**: Monitor and manage user accounts
- **Analytics Dashboard**: Comprehensive insights and statistics
- **Bulk Operations**: Efficient content and user management

### 📊 Analytics & Insights
- **Real-time Statistics**: Live user counts, content metrics, and engagement
- **Growth Tracking**: Monthly and rolling growth analytics
- **User Behavior**: Watch patterns, ratings, and preferences
- **Content Performance**: View counts, ratings, and popularity metrics
- **Transaction History**: Complete subscription and payment tracking

### 🎯 Advanced Features
- **Activity Logging**: Comprehensive user action tracking
- **Rating & Review System**: Community-driven content evaluation
- **Search & Discovery**: Advanced content search and recommendations
- **Newsletter System**: User engagement and content updates
- **Contact Management**: User support and feedback system

## 🚀 Technology Stack

### Backend
- **Laravel 11** - Modern PHP framework
- **PHP 8.2+** - Latest PHP features and performance
- **MySQL/PostgreSQL** - Robust database management
- **JWT Authentication** - Secure API authentication
- **Laravel Socialite** - Social media integration

## 📋 Prerequisites

Before you begin, ensure you have the following installed:
- **PHP 8.2 or higher**
- **Composer 2.0 or higher**
- **Node.js 18+ and npm**
- **MySQL 8.0+ or PostgreSQL 13+**
- **Redis** (optional, for caching)

## 🛠️ Installation

### 1. Clone the Repository
```bash
git clone https://github.com/Popsonjr/AfricanStreams.git
cd AfricanStreams
```

### 2. Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 3. Environment Configuration
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure your database and other services in .env
```

### 4. Database Setup
```bash
# Run migrations
php artisan migrate

# Seed the database with sample data
php artisan db:seed

# Generate JWT secret
php artisan jwt:secret
```

### 5. Storage Setup
```bash
# Create storage link
php artisan storage:link

# Set proper permissions
chmod -R 775 storage bootstrap/cache
```

### 6. Build Assets
```bash
# Development
npm run dev

# Production
npm run build
```

### 7. Start the Application
```bash
# Using Laravel Sail (Docker)
./vendor/bin/sail up

# Using PHP's built-in server
php artisan serve
```

## 🔧 Configuration

### Environment Variables
Key environment variables to configure:

```env
APP_NAME="African Streams"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=africanstreams
DB_USERNAME=your_username
DB_PASSWORD=your_password

JWT_SECRET=your_jwt_secret
JWT_TTL=60

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password

PAYSTACK_SECRET_KEY=your_paystack_secret
PAYSTACK_PUBLIC_KEY=your_paystack_public
```

### File Storage
Configure your preferred file storage driver in `config/filesystems.php`:
- **Local**: For development
- **S3**: For production (recommended)
- **DigitalOcean Spaces**: Alternative cloud storage

## 📚 API Documentation

### Authentication Endpoints
```
POST /api/auth/register          # User registration
POST /api/auth/login            # User login
POST /api/auth/logout           # User logout
POST /api/auth/refresh          # Refresh JWT token
POST /api/auth/verify-email     # Email verification
POST /api/auth/reset-password   # Password reset
```

### Admin Endpoints
```
POST /api/admin/register        # Admin registration
POST /api/admin/login           # Admin login
GET  /api/admin/users           # Get all admins
PUT  /api/admin/users/{id}      # Update admin user
DELETE /api/admin/users/{id}    # Delete admin user
```

### Content Endpoints
```
GET    /api/movies              # List all movies
POST   /api/movies              # Create new movie
GET    /api/movies/{id}         # Get movie details
PUT    /api/movies/{id}         # Update movie
DELETE /api/movies/{id}         # Delete movie

GET    /api/series              # List all TV series
POST   /api/series              # Create new series
GET    /api/series/{id}         # Get series details
PUT    /api/series/{id}         # Update series
DELETE /api/series/{id}         # Delete series
```

### User Management Endpoints
```
GET    /api/users               # List all users
GET    /api/users/{id}          # Get user details
PUT    /api/users/{id}          # Update user profile
DELETE /api/users/{id}          # Delete user account

GET    /api/watchlist           # User's watchlist
POST   /api/watchlist           # Add to watchlist
DELETE /api/watchlist/{id}      # Remove from watchlist
```

### Analytics Endpoints
```
GET /api/dashboard/stats        # General statistics
GET /api/dashboard/growth       # Growth analytics
GET /api/dashboard/transactions # Transaction history
```

## 🧪 Testing

Run the test suite to ensure everything works correctly:

```bash
# Run all tests
php artisan test

# Run tests with coverage
php artisan test --coverage

# Run specific test file
php artisan test tests/Feature/MovieControllerTest.php
```

## 📁 Project Structure

```
AfricanStreams/
├── app/
│   ├── Http/Controllers/       # API Controllers
│   ├── Models/                 # Eloquent Models
│   ├── Services/               # Business Logic Services
│   ├── Traits/                 # Reusable Traits
│   └── Exceptions/             # Custom Exception Handlers
├── database/
│   ├── migrations/             # Database Schema
│   ├── seeders/                # Sample Data
│   └── factories/              # Model Factories
├── routes/
│   └── api.php                 # API Routes
├── config/                     # Configuration Files
├── storage/                    # File Storage
└── tests/                      # Test Suite
```

## 🔒 Security Features

- **JWT Authentication** with secure token management
- **CSRF Protection** for web routes
- **Input Validation** with comprehensive rules
- **SQL Injection Prevention** through Eloquent ORM
- **File Upload Security** with type and size validation
- **Rate Limiting** on sensitive endpoints
- **Soft Deletes** for data integrity

## 📈 Performance Optimizations

- **Database Indexing** on frequently queried fields
- **Eager Loading** to prevent N+1 queries
- **Pagination** for large datasets
- **Caching** for frequently accessed data
- **Image Optimization** for media assets
- **API Response Caching** for static data

## 🚀 Deployment

### Production Checklist
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Configure production database
- [ ] Set up SSL certificates
- [ ] Configure file storage (S3 recommended)
- [ ] Set up monitoring and logging
- [ ] Configure backup strategies
- [ ] Set up CI/CD pipeline

### Recommended Hosting
- **VPS**: DigitalOcean, Linode, or Vultr
- **Cloud**: AWS, Google Cloud, or Azure
- **PaaS**: Heroku, Railway, or DigitalOcean App Platform

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- **Laravel Team** for the amazing framework
- **Tymon JWT Auth** for JWT implementation
- **Tailwind CSS** for the beautiful UI components
- **African Content Creators** for inspiring this platform

## 📞 Support

If you have any questions or need support:

- **Issues**: [GitHub Issues](https://github.com/Popsonjr/AfricanStreams/issues)
- **Email**: popoolama@gmail.com
- **Documentation**: [Wiki](https://github.com/Popsonjr/AfricanStreams/wiki)

---

**Built with ❤️ for African Content Creators**

*Empowering African stories through modern streaming technology.*
