<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

setSecurityHeaders();

// Get dynamic data
$newsItems = getActiveNews($pdo);
$projects = getProjects($pdo);
$icornerLinks = getICornerLinks($pdo);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="IC Innovations - Turning ideas into Digital Reality. Business management consultancy, website development, mobile apps, SaaS solutions.">
    <meta name="author" content="Imran Choudhry">
    <title>IC Innovations | Turning Ideas into Digital Reality</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <div class="header">
        <div class="logo-area">
            <div class="logo">IC</div>
            <div class="brand">
                <h1><?php echo htmlspecialchars(SITE_NAME); ?></h1>
                <p>Turning ideas into Digital Reality | Our Destination Your Satisfaction</p>
            </div>
        </div>
        <div class="nav-links">
            <button class="nav-btn" data-section="we">We</button>
            <button class="nav-btn" data-section="foryou">For You</button>
            <button class="nav-btn" data-section="achieved">Achieved</button>
            <button class="nav-btn" data-section="communicate">Communicate</button>
            <button class="nav-btn" data-section="icorner">I-Corner</button>
            <?php if (isLoggedIn()): ?>
                <button class="nav-btn" onclick="location.href='dashboard.php'">Dashboard</button>
                <button class="nav-btn" onclick="location.href='logout.php'">Logout</button>
            <?php else: ?>
                <button class="nav-btn" onclick="location.href='login.php'">Login</button>
                <button class="nav-btn" onclick="location.href='register.php'">Register</button>
            <?php endif; ?>
        </div>
    </div>

    <div id="sidebar" class="sidebar">
        <div id="sidebar-content"></div>
    </div>

    <div class="main-content">
        <div class="news-ticker">
            <span>
                <?php foreach ($newsItems as $news): ?>
                    <?php echo htmlspecialchars($news); ?> &nbsp; ★ &nbsp;
                <?php endforeach; ?>
            </span>
        </div>

        <h2>Welcome to IC Innovations</h2>
        <p>Your trusted partner in digital transformation, business management, and tech solutions.</p>

        <!-- Quotation Area -->
        <div id="quotation-area" style="display:none;">
            <div class="service-selector" id="services-list-container"></div>
            <div class="quote-summary" id="quote-summary">
                <h3>Your Quotation</h3>
                <div id="selected-services-list"></div>
                <p>Subtotal: $<span id="subtotal">0</span></p>
                <p>Tax (<?php echo TAX_RATE; ?>%): $<span id="tax">0</span></p>
                <p><strong>Total incl tax: $<span id="total">0</span></strong></p>
                <div class="payment-policy">
                    <h4>Payment Policy</h4>
                    <ul>
                        <li><?php echo PAYMENT_ADVANCE; ?>% advance before start</li>
                        <li><?php echo PAYMENT_MID; ?>% on submission of completion report</li>
                        <li><?php echo PAYMENT_FINAL; ?>% final after review period</li>
                    </ul>
                </div>
                <div class="flex-btns">
                    <button id="request-quote">Request Quotation</button>
                    <button id="track-order">Track My Order</button>
                </div>
            </div>
        </div>

        <!-- Gantt View -->
        <div id="gantt-view" style="display:none;">
            <h3>Project Completion Tracker</h3>
            <?php foreach ($projects as $proj): ?>
                <div><?php echo htmlspecialchars($proj['project_name']); ?></div>
                <div class="gantt-bar">
                    <div class="gantt-fill" style="width: <?php echo (int)$proj['completion_percent']; ?>%;">
                        <?php echo (int)$proj['completion_percent']; ?>%
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Opinion Form -->
        <div id="opinion-form" style="display:none;">
            <h3>Your Opinion / Query</h3>
            <form id="opinionSubmit">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="text" name="name" placeholder="Full Name *" required>
                <input type="tel" name="contact" placeholder="Contact Number *" required>
                <input type="email" name="email" placeholder="Email (optional)">
                <textarea name="message" rows="4" placeholder="Your query or suggestion..."></textarea>
                <button type="submit">Send Message</button>
            </form>
        </div>

        <!-- Achievements -->
        <div id="achievements-list" style="display:none;">
            <h3>Our Achievements</h3>
            <h4>Registered NGOs/NPOs</h4>
            <ul>
                <li>Social Development Network (SDN)</li>
                <li>Ahmed Khan Niazi Memorial Associations</li>
            </ul>
            <h4>Launched Websites & Apps</h4>
            <ul>
                <li><a href="http://www.sdnngo.org" target="_blank">www.sdnngo.org</a></li>
                <li><a href="http://www.aknma.pk" target="_blank">www.aknma.pk</a></li>
                <li><a href="http://www.mvm.com" target="_blank">www.mvm.com</a></li>
                <li><a href="http://www.consultant-OD.com" target="_blank">www.consultant-OD.com</a></li>
                <li><a href="http://www.babbar&sons.com" target="_blank">www.babbarandsons.com</a></li>
                <li><a href="http://www.pkjobs.com" target="_blank">www.pkjobs.com</a></li>
            </ul>
        </div>

        <!-- I-Corner -->
        <div id="icorner-links" style="display:none;">
            <h3>Innovative Corner</h3>
            <ul>
                <?php foreach ($icornerLinks as $link): ?>
                    <li><a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank"><?php echo htmlspecialchars($link['title']); ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Contact Info -->
        <div id="contact-info" style="display:none;">
            <h3>Contact Us</h3>
            <p>📞 +92 300 331 9242</p>
            <p>💬 WhatsApp: +92 300 331 9242</p>
            <p>🌐 www.icinnovation.tech</p>
            <p>✉️ info@icinnovations.tech | consultant.choudhry@gmail.com</p>
            <p>📍 Building No. 1, Street 2, Commercial Area, PHA, Phase – I, Karachi, Pakistan (12345)</p>
            <iframe width="100%" height="200" style="border:0" loading="lazy" allowfullscreen
                src="https://maps.google.com/maps?q=Karachi+Pakistan&t=&z=13&ie=UTF8&iwloc=&output=embed">
            </iframe>
        </div>
    </div>

    <footer>
        <div style="max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; flex-wrap: wrap;">
            <div>
                © <?php echo date('Y'); ?> <?php echo htmlspecialchars(SITE_NAME); ?><br>
                Developed by Imran Choudhry, Consultant
            </div>
            <div>
                <strong>Support:</strong> <a href="mailto:info@icinnovations.tech" style="color: #ffd966;">info@icinnovations.tech</a>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>

</html>