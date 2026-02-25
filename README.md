# 🎸 Thunderstorm Rock Festival 2025

A full-stack web application for festival management, built with PHP and MySQL as a school project. Features user registration, event sign-ups with multiple ticket types, an admin panel, feedback system, and a responsive rock-themed UI with animations.

🌐 **Live Demo:** [https://geronimo.okol.org/~markar/Tapahtumasivut/](https://geronimo.okol.org/~markar/Tapahtumasivut/)

<p align="center">
  <img src="assets/images/logo.png" alt="Thunderstorm Rock Festival" width="300">
</p>

---

## ✨ Features

**For Users:**
- User registration and login with secure password hashing (bcrypt)
- Profile management (personal details, password change, account deletion)
- Event registration with three ticket types: Day Pass (35€), Weekend (60€), VIP (120€)
- Contact/feedback form with category selection
- Password reset via email (PHPMailer + Mailtrap)

**For Admins:**
- Admin dashboard with statistics overview
- User management (view, delete)
- Event registration management
- Feedback handling with status tracking (new/read/resolved)

**UI/UX:**
- Rock-themed responsive design with animated backgrounds
- Hero slideshow, gradient buttons, hover effects
- Toast notifications, scroll-to-top, smooth scrolling
- Mobile-friendly with hamburger menu and responsive tables (card layout on small screens)

---

## 🛠️ Technologies

| Layer | Technologies |
|-------|-------------|
| **Backend** | PHP 8.x, MySQL/MariaDB, PDO |
| **Frontend** | HTML5, CSS3 (Custom Properties, Flexbox, Grid, Animations), Vanilla JavaScript (ES6+) |
| **Email** | PHPMailer 6.9.1 (SMTP via Mailtrap for development) |
| **Security** | bcrypt, prepared statements, CSRF tokens, XSS prevention, session management, rate limiting, security logging |

---

## 🔒 Security Implementation

This project goes well beyond basic CRUD functionality with a layered security approach:

- **SQL Injection Prevention** — All database queries use PDO prepared statements
- **XSS Prevention** — Input sanitization + output escaping via `htmlspecialchars()`
- **CSRF Protection** — Unique tokens on every form, verified server-side
- **Password Security** — bcrypt hashing with `password_hash()` / `password_verify()`
- **Session Security** — Timeout (30 min), ID regeneration on login, IP tracking, hijacking prevention
- **Rate Limiting** — Configurable limits on login, registration, and contact form submissions
- **Security Logging** — All authentication events logged to file

---

## 🚀 How to Run

### Prerequisites
- PHP 8.x with PDO extension
- MySQL or MariaDB
- Web server (Apache/Nginx) or PHP built-in server

### Setup

1. **Clone the repository**
   ```bash
   git clone https://github.com/MarKar07/Tapahtumasivut.git
   cd Tapahtumasivut
   ```

2. **Create the database**
   ```bash
   mysql -u your_username -p < database.sql
   ```

3. **Configure database connection**
   ```bash
   cp config/database.example.php config/database.php
   ```
   Edit `config/database.php` with your database credentials.

4. **Create the logs directory**
   ```bash
   mkdir logs
   chmod 755 logs
   ```

5. **Run**
   ```bash
   php -S localhost:8000
   ```
   Open [http://localhost:8000](http://localhost:8000) in your browser.

### Demo Accounts (from database.sql)
- **Admin:** username `admin` / email `admin@rockfestival.com`
- **User:** username `Esko Esimerkki` / email `esko@mail.com`
- *(Passwords are hashed in the dump — create a new account via the registration form)*

---

## 📁 Project Structure

```
├── assets/
│   ├── css/style.css          # All styles (responsive, animations)
│   ├── js/script.js           # UI interactions (menu, toasts, scroll effects)
│   └── images/                # Festival images and logo
├── config/
│   ├── config.php             # App settings, constants, ticket types
│   └── database.example.php   # DB connection template
├── includes/
│   ├── session.php            # Secure session management
│   ├── security.php           # Validation & auth helper functions
│   ├── csrf.php               # CSRF token generation & verification
│   ├── rate_limit.php         # Rate limiting logic
│   ├── error_handler.php      # Error logging & user-friendly messages
│   └── mailer.php             # PHPMailer email functions
├── pages/
│   ├── login.php              # Login with rate limiting
│   ├── register.php           # Registration with validation
│   ├── profile.php            # User profile & event registration
│   ├── admin.php              # Admin dashboard
│   ├── contact.php            # Contact form with feedback
│   ├── event-info.php         # Festival info & artist cards
│   ├── forgot_password.php    # Password reset request
│   ├── reset_password.php     # Password reset with token
│   └── logout.php             # Secure logout
├── PHPMailer/                 # PHPMailer library
├── database.sql               # Full database schema + sample data
└── index.php                  # Landing page with hero slideshow
```

---

## 👨‍💻 Author

**Kari Markus** — Sole developer. All code, design, database schema, and security implementation by me.

- Portfolio: [markar07.github.io/Portfolio](https://markar07.github.io/Portfolio)
- GitHub: [@MarKar07](https://github.com/MarKar07)
