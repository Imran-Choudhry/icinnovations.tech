const express = require('express');
const mongoose = require('mongoose');
const cors = require('cors');
const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');
require('dotenv').config();

const app = express();

// Middleware
app.use(cors());
app.use(express.json());

// MongoDB Connection
mongoose.connect('mongodb://localhost:27017/ic_innovations', {
  useNewUrlParser: true,
  useUnifiedTopology: true,
});

// Models
const UserSchema = new mongoose.Schema({
  name: { type: String, required: true },
  country: String,
  mobile: { type: String, required: true, unique: true },
  whatsapp: String,
  email: String,
  role: { type: String, enum: ['Consultant', 'Developer', 'HR Specialist', 'SaaS Provider', 'Admin'], default: 'Consultant' },
  password: { type: String, required: true },
  createdAt: { type: Date, default: Date.now },
  isActive: { type: Boolean, default: true },
});

const ServiceSchema = new mongoose.Schema({
  category: String,
  name: String,
  price: Number,
  tax: { type: Number, default: 16 },
  isActive: { type: Boolean, default: true },
});

const OrderSchema = new mongoose.Schema({
  userId: { type: mongoose.Schema.Types.ObjectId, ref: 'User' },
  services: [{
    serviceId: { type: mongoose.Schema.Types.ObjectId, ref: 'Service' },
    price: Number,
  }],
  totalAmount: Number,
  taxAmount: Number,
  grandTotal: Number,
  status: { type: String, enum: ['pending', 'confirmed', 'in-progress', 'completed'], default: 'pending' },
  progress: { type: Number, default: 0 },
  paymentStatus: { type: String, enum: ['advance', 'partial', 'completed'], default: 'advance' },
  createdAt: { type: Date, default: Date.now },
});

const JobSchema = new mongoose.Schema({
  title: String,
  company: String,
  location: String,
  type: { type: String, enum: ['Full-time', 'Part-time', 'Remote', 'Contract'] },
  salary: String,
  description: String,
  requirements: [String],
  postedBy: { type: mongoose.Schema.Types.ObjectId, ref: 'User' },
  createdAt: { type: Date, default: Date.now },
});

const User = mongoose.model('User', UserSchema);
const Service = mongoose.model('Service', ServiceSchema);
const Order = mongoose.model('Order', OrderSchema);
const Job = mongoose.model('Job', JobSchema);

// Middleware
const auth = async (req, res, next) => {
  try {
    const token = req.header('Authorization')?.replace('Bearer ', '');
    const decoded = jwt.verify(token, process.env.JWT_SECRET || 'secretkey');
    const user = await User.findById(decoded.userId);
    if (!user) throw new Error();
    req.user = user;
    next();
  } catch (error) {
    res.status(401).json({ error: 'Please authenticate' });
  }
};

const adminAuth = async (req, res, next) => {
  await auth(req, res, () => {
    if (req.user.role !== 'Admin') return res.status(403).json({ error: 'Admin access required' });
    next();
  });
};

// Auth Routes
app.post('/api/auth/register', async (req, res) => {
  try {
    const { name, country, mobile, whatsapp, email, role } = req.body;
    const password = Math.random().toString(36).slice(-8);
    const hashedPassword = await bcrypt.hash(password, 10);
    
    const user = new User({
      name, country, mobile, whatsapp, email, role,
      password: hashedPassword,
    });
    await user.save();
    
    res.status(201).json({ message: 'User created', password, userId: user._id });
  } catch (error) {
    res.status(400).json({ error: error.message });
  }
});

app.post('/api/auth/login', async (req, res) => {
  try {
    const { loginId, password } = req.body;
    const user = await User.findOne({ $or: [{ mobile: loginId }, { email: loginId }] });
    if (!user) return res.status(401).json({ error: 'Invalid credentials' });
    
    const isValid = await bcrypt.compare(password, user.password);
    if (!isValid) return res.status(401).json({ error: 'Invalid credentials' });
    
    const token = jwt.sign({ userId: user._id, role: user.role }, process.env.JWT_SECRET || 'secretkey');
    res.json({ token, user: { id: user._id, name: user.name, role: user.role, mobile: user.mobile } });
  } catch (error) {
    res.status(400).json({ error: error.message });
  }
});

app.post('/api/auth/change-password', auth, async (req, res) => {
  try {
    const { oldPassword, newPassword } = req.body;
    const isValid = await bcrypt.compare(oldPassword, req.user.password);
    if (!isValid) return res.status(401).json({ error: 'Invalid old password' });
    
    req.user.password = await bcrypt.hash(newPassword, 10);
    await req.user.save();
    res.json({ message: 'Password updated' });
  } catch (error) {
    res.status(400).json({ error: error.message });
  }
});

// Service Routes
app.get('/api/services', async (req, res) => {
  const services = await Service.find({ isActive: true });
  res.json(services);
});

app.post('/api/services', adminAuth, async (req, res) => {
  const service = new Service(req.body);
  await service.save();
  res.status(201).json(service);
});

app.put('/api/services/:id', adminAuth, async (req, res) => {
  const service = await Service.findByIdAndUpdate(req.params.id, req.body, { new: true });
  res.json(service);
});

// Order Routes
app.post('/api/orders', auth, async (req, res) => {
  const { services: selectedServices } = req.body;
  const services = await Service.find({ _id: { $in: selectedServices } });
  
  let totalAmount = 0;
  const serviceItems = services.map(s => {
    totalAmount += s.price;
    return { serviceId: s._id, price: s.price };
  });
  
  const taxAmount = totalAmount * 0.16;
  const grandTotal = totalAmount + taxAmount;
  
  const order = new Order({
    userId: req.user._id,
    services: serviceItems,
    totalAmount,
    taxAmount,
    grandTotal,
  });
  await order.save();
  
  res.status(201).json(order);
});

app.get('/api/orders/:id', auth, async (req, res) => {
  const order = await Order.findById(req.params.id).populate('services.serviceId');
  if (order.userId.toString() !== req.user._id.toString() && req.user.role !== 'Admin') {
    return res.status(403).json({ error: 'Access denied' });
  }
  res.json(order);
});

app.put('/api/orders/:id/progress', adminAuth, async (req, res) => {
  const { progress, status } = req.body;
  const order = await Order.findByIdAndUpdate(req.params.id, { progress, status }, { new: true });
  res.json(order);
});

// Job Routes
app.get('/api/jobs', async (req, res) => {
  const jobs = await Job.find().sort('-createdAt').limit(50);
  res.json(jobs);
});

app.post('/api/jobs', auth, async (req, res) => {
  const job = new Job({ ...req.body, postedBy: req.user._id });
  await job.save();
  res.status(201).json(job);
});

// Analytics Routes
app.get('/api/analytics/stats', adminAuth, async (req, res) => {
  const totalUsers = await User.countDocuments();
  const totalOrders = await Order.countDocuments();
  const revenue = await Order.aggregate([{ $group: { _id: null, total: { $sum: '$grandTotal' } } }]);
  const recentOrders = await Order.find().sort('-createdAt').limit(10).populate('userId', 'name');
  
  res.json({
    totalUsers,
    totalOrders,
    totalRevenue: revenue[0]?.total || 0,
    recentOrders,
  });
});

app.get('/api/analytics/charts', adminAuth, async (req, res) => {
  const monthlyRevenue = await Order.aggregate([
    { $group: { _id: { $month: '$createdAt' }, total: { $sum: '$grandTotal' } } },
    { $sort: { _id: 1 } }
  ]);
  
  const serviceUsage = await Order.aggregate([
    { $unwind: '$services' },
    { $group: { _id: '$services.serviceId', count: { $sum: 1 } } },
    { $limit: 10 }
  ]);
  
  res.json({ monthlyRevenue, serviceUsage });
});

const PORT = process.env.PORT || 5000;
app.listen(PORT, () => console.log(`Server running on port ${PORT}`));
