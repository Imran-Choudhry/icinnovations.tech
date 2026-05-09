# icinnovations.tech
Turning Ideas into Digital Reality 
# IC Innovations - Tech & Business Management Consultancy Platform

![Version](https://img.shields.io/badge/version-1.0.0-blue)
![License](https://img.shields.io/badge/license-MIT-green)
![React](https://img.shields.io/badge/React-18.2.0-61dafb)
![Node](https://img.shields.io/badge/Node-18.x-339933)

**Tagline:** *Turning Ideas into Digital Reality*  
**Slogan:** *Our Destination Your Satisfaction*

## 🚀 Features

- ✅ Complete SaaS Platform with Admin Panel
- ✅ Dynamic Service Quotation System
- ✅ Job Portal with Resume Builder
- ✅ Project Tracking with Gantt Charts
- ✅ Real-time Analytics Dashboard
- ✅ Dark/Light Mode Toggle
- ✅ Fully Responsive Design
- ✅ JWT Authentication with Role Management

## 📋 Prerequisites

- Node.js (v18 or higher)
- MongoDB (v6 or higher)
- npm or yarn package manager

## 🛠️ Installation

### 1. Clone the Repository
\`\`\`bash
git clone https://github.com/yourusername/ic-innovations-platform.git
cd ic-innovations-platform
\`\`\`

### 2. Install Backend Dependencies
\`\`\`bash
cd backend
npm install
cp .env.example .env
# Edit .env with your database credentials
\`\`\`

### 3. Install Frontend Dependencies
\`\`\`bash
cd ../frontend
npm install
\`\`\`

### 4. Seed Database
\`\`\`bash
cd ../database
node seed-data.js
\`\`\`

### 5. Run Development Servers

**Backend (Port 5000):**
\`\`\`bash
cd backend
npm run dev
\`\`\`

**Frontend (Port 5173):**
\`\`\`bash
cd frontend
npm run dev
\`\`\`

## 🔐 Default Admin Credentials

| Field | Value |
|-------|-------|
| Email | admin@icinnovations.tech |
| Mobile | 03003319242 |
| Password | admin123 |

## 📁 Project Structure

\`\`\`
ic-innovations-platform/
├── backend/         # Node.js + Express API
├── frontend/        # React + Tailwind CSS
├── database/        # MongoDB schemas & seeders
└── docs/           # Documentation files
\`\`\`

## 🚢 Deployment

### Deploy to Vercel (Frontend)
[![Deploy with Vercel](https://vercel.com/button)](https://vercel.com/new)

### Deploy to Render (Backend)
[![Deploy to Render](https://render.com/images/deploy-to-render-button.svg)](https://render.com)

### Deploy to Railway (Database)
[![Deploy on Railway](https://railway.app/button.svg)](https://railway.app)

## 📊 API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/auth/register` | User registration |
| POST | `/api/auth/login` | User login |
| GET | `/api/services` | Get all services |
| POST | `/api/orders` | Create new order |
| GET | `/api/analytics/stats` | Dashboard stats (admin) |

Full API documentation: [API.md](./docs/API.md)

## 🧪 Testing

\`\`\`bash
# Backend tests
cd backend
npm test

# Frontend tests
cd frontend
npm test
\`\`\`

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 License

Distributed under the MIT License. See `LICENSE` for more information.

## 👨‍💻 Developer

**Imran Choudhry**  
- Portfolio: [icinnovation.tech](https://icinnovation.tech)
- Email: consultant.choudhry@gmail.com

## 🙏 Acknowledgments

- React.js Team
- MongoDB Atlas
- Tailwind CSS
- All contributors

---
⭐ Star this repository if you find it useful!
