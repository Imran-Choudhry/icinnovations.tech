-- --------------------------------------------------------
-- Host: localhost
-- Database: ic_innovations
-- --------------------------------------------------------

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `country` varchar(50) NOT NULL,
  `mobile` varchar(20) NOT NULL UNIQUE,
  `whatsapp` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `looking_for` enum('freelance_consultant','developer','hr_solvers','provider_saas') DEFAULT NULL,
  `login_id` varchar(50) NOT NULL UNIQUE, -- mobile or email
  `password` varchar(255) NOT NULL, -- auto-generated, changeable
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);

CREATE TABLE `services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(50) NOT NULL,
  `service_name` varchar(255) NOT NULL,
  `charge` decimal(10,2) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`)
);

CREATE TABLE `news_bulletin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_text` varchar(500) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);

CREATE TABLE `quotation_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(100) NOT NULL,
  `selected_services` text NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `tax` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);

CREATE TABLE `projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_name` varchar(255) NOT NULL,
  `completion_percent` int(11) DEFAULT 0,
  `user_id` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);

CREATE TABLE `icorner_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `url` varchar(500) NOT NULL,
  `category` varchar(100) DEFAULT 'general',
  PRIMARY KEY (`id`)
);

CREATE TABLE `user_opinions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `contact` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `message` text NOT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);

CREATE TABLE `visitor_analytics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(45) NOT NULL,
  `page_url` varchar(255) NOT NULL,
  `visit_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);

-- Insert sample services (admin can edit charges later)
INSERT INTO `services` (`category`, `service_name`, `charge`, `description`) VALUES
('Consultancy', 'Study of Existing Business Model', 500.00, 'In-depth analysis of current business processes'),
('Consultancy', 'Develop Business Model', 750.00, 'Create new revenue and operational model'),
('Consultancy', 'Contingency Plan', 300.00, 'Risk management and contingency strategies'),
('Consultancy', 'Organization Structuring & Re-structuring', 600.00, 'Design or reorganize company hierarchy'),
('Consultancy', 'Digital HR & HR Operations', 450.00, 'Digitize HR workflows'),
('Consultancy', 'Content Writing', 200.00, 'Professional content for web/brochures'),
('HR Solutions', 'Effective Recruitment & Selection Process', 400.00, 'End-to-end recruitment setup'),
('HR Solutions', 'Absenteeism of Employees', 250.00, 'Reduce absenteeism strategies'),
('HR Solutions', 'Performance Based incentives & rewards', 350.00, 'Design KPI-based rewards'),
('HR Solutions', 'HR Policies / Guidelines', 300.00, 'Create employee handbook'),
('HR Solutions', 'HR Directory', 150.00, 'Digital directory system'),
('Website Development', 'Static websites', 300.00, 'HTML/CSS based'),
('Website Development', 'Dynamic & Responsive websites', 800.00, 'PHP/JS with mobile friendly'),
('Website Development', 'Data collection websites', 500.00, 'Forms & database backend'),
('Website Development', 'Advanced view UI / UX', 700.00, 'Modern design systems'),
('Website Development', 'Strong Databases; sql, sql pro, mango etc', 600.00, 'Optimized DB architecture'),
('Website Development', 'Integration facility with other websites', 400.00, 'APIs, third-party integration'),
('Mobile Apps Development', 'Android Mobile Apps', 1200.00, 'Java/Kotlin native'),
('Mobile Apps Development', 'IoS Mobile Apps', 1400.00, 'Swift native'),
('Mobile Apps Development', 'Conversion & Integrative Mobile Apps with web', 1000.00, 'Hybrid/React Native'),
('Mobile Apps Development', 'Maintaining of Mobile Apps', 300.00, 'Monthly maintenance'),
('Mobile Apps Development', 'SEO based Mobile Apps', 450.00, 'App Store optimization'),
('Mobile Apps Development', 'Analytical Mobile Apps', 550.00, 'Embedded analytics'),
('Mobile Apps Development', 'Tracking Mobile Apps', 500.00, 'Location/activity tracking'),
('Digitalization', 'Logos Developing', 100.00, 'Custom logo design'),
('Digitalization', 'Slogan Creation', 80.00, 'Catchy taglines'),
('Digitalization', 'Web Content Writing', 150.00, 'SEO-friendly content'),
('Digitalization', 'Conversion organization & Business Manuals into Digital Form', 400.00, 'Digitize manuals'),
('Digitalization', 'Organization ChatGPT Creation', 2000.00, 'Custom GPT for internal use'),
('Digitalization', 'WhatsApp integration', 250.00, 'API integration'),
('Digitalization', 'Web Pages', 100.00, 'Single page development'),
('Digitalization', 'Web Tools', 300.00, 'Custom web-based tools');

-- Sample news
INSERT INTO `news_bulletin` (`news_text`) VALUES
('🎉 New SaaS HR module launched!'),
('📢 Free consultation for NGOs this month'),
('🚀 Website development offer - 20% off');

-- Sample I-Corner links
INSERT INTO `icorner_links` (`title`, `url`, `category`) VALUES
('Jobs Portal', '/jobs', 'portal'),
('Resume Builder', '/resume-builder', 'tool'),
('Education Portal', '/education', 'portal'),
('Collaborative Consultancies', '/collab', 'resource'),
('Useful Links Library', '/resources', 'resource');

-- Sample projects for Gantt view
INSERT INTO `projects` (`project_name`, `completion_percent`) VALUES
('SDN NGO Website', 100),
('AKNMA Mobile App', 85),
('HR SaaS Platform', 45);