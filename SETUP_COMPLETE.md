# 🎉 KHAIRAWANG DAIRY - Setup Complete! ✨

Your project is **RUNNING** on Mac (without XAMPP)!

**🌐 Access Your Application:**
- Homepage: http://localhost:8000
- Admin Panel: http://localhost:8000/admin

**Server Status:** ✅ PHP Development Server is running on port 8000

## ✅ What's Been Done

1. ✓ Installed PHP dependencies (Composer) including MongoDB library
2. ✓ Installed Node.js dependencies (npm)
3. ✓ Created `.env` configuration file (debug mode enabled)
4. ✓ Created MySQL database: `khairawang_dairy`
5. ✓ Imported database schema (20 tables)
6. ✓ Seeded admin user and roles
7. ✓ Built frontend assets (Tailwind CSS + Alpine.js)
8. ✓ Set storage permissions
9. ✓ **Started PHP development server on http://localhost:8000**

## 🚀 How to Run the Project

Since you're using **Homebrew PHP** (not XAMPP), use PHP's built-in server:

### Start the Development Server

```bash
cd /Users/kiranoli/Development/khairawang-dairy
php -S localhost:8000 -t public
```

Then open your browser to: **http://localhost:8000**

### Alternative: Use npm dev server (with hot reload)

```bash
cd /Users/kiranoli/Development/khairawang-dairy
npm run dev
```

## 🔑 Admin Login Credentials

- **Email:** admin@khairawangdairy.com
- **Password:** admin123

## 📁 Project Structure

```
khairawang-dairy/
├── app/              # Application code (Controllers, Models)
├── core/             # Core framework files
├── public/           # Web root (index.php, assets)
├── resources/        # Views, CSS, JS source files
├── routes/           # Route definitions
├── config/           # Configuration files
├── database/         # Database migrations & seeders
├── storage/          # Logs, cache, uploads
└── vendor/           # Composer dependencies
```

## 🛠️ Useful Commands

### Frontend Development
```bash
npm run dev          # Start dev server with hot reload
npm run build        # Build for production
```

### Database
```bash
# Access MySQL CLI
mysql -uroot khairawang_dairy

# Reset database
mysql -uroot -e "DROP DATABASE khairawang_dairy; CREATE DATABASE khairawang_dairy;"
mysql -uroot khairawang_dairy < database/schema.sql
```

### PHP
```bash
php -S localhost:8000 -t public    # Start server
composer install                    # Install dependencies
composer dump-autoload              # Regenerate autoload files
```

## 🔧 Configuration

Your `.env` file is located at the root. Key settings:

```env
DB_HOST=127.0.0.1
DB_DATABASE=khairawang_dairy
DB_USERNAME=root
DB_PASSWORD=

APP_URL=http://localhost:8000
APP_DEBUG=true
PAYMENT_TEST_MODE=true
```

## 💳 eSewa Payment Testing

The project includes eSewa payment integration for Nepal.

**Test Credentials:**
- eSewa ID: 9800000000
- Password: Any password
- MPIN: 1234

**Configuration in `.env`:**
```env
ESEWA_MERCHANT_CODE=EPAYTEST
ESEWA_SECRET_KEY=8gBm/:&EnhH.1/q
PAYMENT_TEST_MODE=true
```

## 📚 Documentation

Check these files for detailed information:
- `README.md` - Main project documentation
- `docs/CART_FUNCTIONALITY.md` - Cart system guide
- `docs/ESEWA_INTEGRATION.md` - Payment integration guide
- `docs/TROUBLESHOOTING.md` - Common issues

## 🐛 Troubleshooting

### Port Already in Use
If port 8000 is busy, use a different port:
```bash
php -S localhost:8080 -t public
```

### Database Connection Issues
Verify MySQL is running:
```bash
mysql -uroot -e "SELECT 1;"
```

### File Permission Issues
```bash
chmod -R 755 storage/ public/uploads/
```

### Clear Cache
```bash
rm -rf storage/cache/*
```

## 🎨 Technology Stack

- **Backend:** PHP 8.2+ (Custom MVC Framework)
- **Database:** MySQL 8+ 
- **Frontend:** Tailwind CSS 3.x, Alpine.js 3.x
- **Build Tool:** Vite 5.x
- **Package Managers:** Composer, npm

## 🔗 Important URLs

Once server is running:

- **Homepage:** http://localhost:8000
- **Admin Panel:** http://localhost:8000/admin (use credentials above)
- **API Docs:** Check `docs/PAYMENT_API.md`

## 📝 Next Steps

1. Start the development server (see above)
2. Visit http://localhost:8000 in your browser
3. Login to admin panel with provided credentials
4. Add some products via admin panel
5. Test the e-commerce features

## 🆘 Need Help?

- Check the `docs/` folder for detailed guides
- Review `TROUBLESHOOTING.md` for common issues
- Check browser console and `storage/logs/` for errors

---

**Happy Coding! 🥛**
