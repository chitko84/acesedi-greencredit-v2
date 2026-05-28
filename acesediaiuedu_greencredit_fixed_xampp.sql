-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 28, 2026 at 04:30 PM
-- Server version: 8.0.34
-- PHP Version: 8.1.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `acesediaiuedu_greencredit`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_activity_log`
--

CREATE TABLE `admin_activity_log` (
  `id` int NOT NULL,
  `admin_id` int DEFAULT NULL,
  `action` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_user_actions`
--

CREATE TABLE `admin_user_actions` (
  `id` int NOT NULL,
  `admin_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `action_type` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `action_details` text COLLATE utf8mb4_general_ci,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`) VALUES
(1, 'Recycling', 'Actions related to recycling materials like plastic, paper, glass, etc.'),
(2, 'Tree Planting', 'Activities related to planting and maintaining trees and greenery.'),
(3, 'Waste Reduction', 'Actions related to reducing waste production through various methods.'),
(4, 'Sustainable Transportation', 'Encouraging the use of eco-friendly transportation like biking, carpooling, etc.'),
(5, 'Water Conservation', 'Initiatives aimed at conserving water and promoting efficient water usage.'),
(6, 'Energy Efficiency', 'Actions focused on reducing energy consumption through various practices.');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `message` text COLLATE utf8mb4_general_ci NOT NULL,
  `response` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_admin_initiated` tinyint(1) DEFAULT '0',
  `is_read` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leaderboard`
--

CREATE TABLE `leaderboard` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `points` int DEFAULT NULL,
  `rank` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `news_events`
--

CREATE TABLE `news_events` (
  `id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `news_images`
--

CREATE TABLE `news_images` (
  `id` int NOT NULL,
  `news_id` int NOT NULL,
  `image` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `message` text COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('unread','read') COLLATE utf8mb4_general_ci DEFAULT 'unread',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`email`, `token`, `expires_at`) VALUES
('abdalle.warsame@student.aiu.edu.my', '04e73c01d19440b64027c4fc9f34d21d2ffe6fc5fb030ee332e3e6624cd7ccb7', '2026-04-22 04:57:58'),
('aiu25102188@student.aiu.edu.my', '2812a9bc5d2856d9e846b0570d28ba61d0eed96916f18ae6aad039ea16607d85', '2026-04-27 08:16:00'),
('aiu25102521@student.aiu.edu.my', '11a457ca9f11a4aa68d72f4629a01f98f61650cd9ec68a1bbb92ed9558da9f31', '2026-04-28 01:56:45'),
('aiu25102602@student.aiu.edu.my', 'fdf192402f8a218ed00be42d48fe24ed2ec653df6e34a39b9182271cbb0ff6a4', '2026-04-28 05:42:02'),
('chitko.ko@student.aiu.edu.my', '89544b1c2c84116afce9e6148e4c65cd798e43b2e24abc58df4989dae3f1e9c9', '2026-01-28 19:12:32'),
('chitkoko.ali@gmail.com', '19531468e013da427c918dedc7b3b83749fb76b863c08bc7ff551a0a6c40e419', '2025-07-17 18:12:37'),
('fawzia.rahim@student.aiu.edu.my', '8daf5be3e204d756a095e4fb4528a835e5a8e8709264470a2b2c5914c556c780', '2026-04-28 05:43:14'),
('khadija.oubbih@student.aiu.edu.my', 'ce434b67690c757105db5dbee4e6423d36b02dc6d625e9e19be1bb933da5b4c3', '2026-04-28 05:49:56'),
('mdparvej.ahmedrafi@student.aiu.edu.my', 'c24b0c75750586d2e7690f2b1225a5671eef09a88db3eb60898d0ef879994ad1', '2026-04-28 06:05:11'),
('nurfatinzainon@student.aiu.edu.my', 'aeb431bfa0ff5d47448eecaa515888dc9da06887075455aaa12fd3e6df0e7c9d', '2026-04-14 02:10:58'),
('rola.salem@student.aiu.edu.my', '7d820655d93c7d2fa1e3b2d08bcf34047a3120cee2b017eb5567b373fee4b752', '2026-04-28 06:28:20');

-- --------------------------------------------------------

--
-- Table structure for table `rewards`
--

CREATE TABLE `rewards` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci NOT NULL,
  `cost` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reward_point_adjustments`
--

CREATE TABLE `reward_point_adjustments` (
  `id` int NOT NULL,
  `admin_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `points_adjusted` int DEFAULT NULL,
  `reason` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `adjustment_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reward_redemption_history`
--

CREATE TABLE `reward_redemption_history` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `reward_id` int DEFAULT NULL,
  `points_redeemed` int DEFAULT NULL,
  `redemption_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `submissions`
--

CREATE TABLE `submissions` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `category` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `action` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `points` int NOT NULL,
  `proof_image` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reward` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `admin_remarks` text COLLATE utf8mb4_general_ci,
  `verified_date` datetime DEFAULT NULL,
  `team_number` int DEFAULT '0',
  `team_members` text COLLATE utf8mb4_general_ci,
  `three_zero_cluster` text COLLATE utf8mb4_general_ci,
  `club_id` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `superadmin_remarks` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `submissions`
--

INSERT INTO `submissions` (`id`, `user_id`, `category`, `action`, `points`, `proof_image`, `status`, `created_at`, `reward`, `description`, `admin_remarks`, `verified_date`, `team_number`, `team_members`, `three_zero_cluster`, `club_id`, `superadmin_remarks`) VALUES
(172, 105, 'Low Impact', 'L1-Use Reusable Items', 25, '[\"69716c3ca14bc-WhatsAppImage2026-01-22at08.12.46.jpeg\",\"69716c3ca2441-WhatsAppImage2026-01-22at08.12.44.jpeg\"]', 'approved', '2026-01-22 00:15:56', NULL, 'bottles and reused container. 22/01/2026. 8.17 AM ', NULL, '2026-04-13 11:43:56', 1, '[\"MUHAMMAD ARIA SAPUTRA\"]', '[\"Zero Net Carbon Emission\"]', '', ''),
(177, 111, 'Low Impact', 'L6-Participate in the Sustainability Action Programme on Campus', 25, '[\"6979c51b7bdad-IMG-20260121-WA0035.jpg\",\"6979c51b7d1cd-IMG-20260121-WA0033.jpg\"]', 'approved', '2026-01-28 08:13:15', NULL, 'Logic Model Workshop by Prof Suraya\r\n\r\n21 January 2026 in Auditorium', NULL, '2026-04-13 11:44:03', 1, '[\"Yusuf Hadiwijaya\"]', '[\"Zero Poverty\"]', '', ''),
(178, 111, 'Low Impact', 'L6-Participate in the Sustainability Action Programme on Campus', 25, '[\"6984d93c149b4-WhatsAppImage2026-02-04at9.05.28PM.jpeg\",\"6984d93c15230-WhatsAppImage2026-02-06at1.55.52AM.jpeg\"]', 'approved', '2026-02-05 17:54:04', NULL, 'Progamme Name: Logic Model Workshop\r\nSpeaker: Prof Suraya & Mr. Masrani\r\nDate & Time: Wednesday, 4 February 2026 (8.30-10.30 PM)\r\nVenue: Auditorium', NULL, '2026-04-13 11:40:04', 1, '[\"Yusuf Hadiwijaya\"]', '[\"Zero Poverty\"]', '', ''),
(179, 104, 'Low Impact', 'L3-Use Eco-Friendly or Biodegradable Products', 25, '[\"6986c2780deb5-IMG_8033.jpeg\",\"6986c27810420-IMG_8031.jpeg\"]', 'approved', '2026-02-07 04:41:28', NULL, 'Tumbler in hand, purpose in mind.\r\nSustainable choices, one sip at a time. \r\nSaturday 7 February 2026, at Auditorium AIU.', NULL, '2026-04-13 11:39:47', 1, '[\"Desti Anggri\"]', '[\"Zero Net Carbon Emission\"]', '', ''),
(180, 135, 'High Impact', 'H7-Write AIU 3ZERO Club Profile/Activity Book', 75, '[\"69dc9893ae26b-e6676db7-0539-42c8-9b10-02fd954a54c4.jpeg\",\"69dc9893ae5e2-3178eaa1-57be-437c-a56d-37ffb446161c.jpeg\"]', 'pending', '2026-04-13 07:17:39', NULL, 'Albukhary International University 3Z review is a tri-nominal magazine by Albukhary Social Business Journal. Our team collected all writings from the author, fixing the errors. Led by Muntashir Angel, who designed the entire magazine. We hope it will be highly impactful and inspiring for students. ', NULL, NULL, 3, '[\"Sanaa Mohammed Said Al-rabeei \",\"Muntashir Mahmud Angel \",\"Md Shabbir Hossain Showne\"]', '[\"Zero Unemployment\"]', '458-021-0184', NULL),
(181, 111, 'Low Impact', 'L6-Participate in the Sustainability Action Programme on Campus', 25, '[\"69e2e3daaaf6d-Proof.jpeg\",\"69e2e3daab5ca-WhatsAppImage2026-04-18at9.51.50AM.jpeg\"]', 'pending', '2026-04-18 01:52:26', NULL, 'Marketing & Financial Management Session with Baitulhusna Ahmad Zamri\r\n\r\nDate: Friday, 17/04/2026\r\nTime: 03.00 - 05.00 PM\r\nVenue: LT 5', NULL, NULL, 1, '[\"Yusuf Hadiwijaya\"]', '[\"Zero Unemployment\"]', '', NULL),
(182, 105, 'Low Impact', 'L3-Use Eco-Friendly or Biodegradable Products', 25, '[\"69e2ecd99f01e-IMG-20260410-WA0035.jpg\",\"69e2ecd99f4b1-IMG_20260402_145537.jpg\",\"69e2ecd9a0c3a-IMG_20260418_101710.jpg\"]', 'pending', '2026-04-18 02:30:49', NULL, 'I use Salam rancage product, produced from used paper', NULL, NULL, 1, '[\"MUHAMMAD ARIA SAPUTRA\"]', '[\"Zero Net Carbon Emission\"]', '458-021-0082', NULL),
(183, 105, 'Low Impact', 'L5-Report Waste Issue/Incidence', 25, '[\"69e2eeb4c0c8e-wasteissue.pdf\"]', 'pending', '2026-04-18 02:38:44', NULL, 'fabric and paper waste ( 25c-4)', NULL, NULL, 1, '[\"MUHAMMAD ARIA SAPUTRA\"]', '[\"Zero Net Carbon Emission\"]', '', NULL),
(186, 222, 'Low Impact', 'L1-Use Reusable Items', 25, '[\"69e7295e8e9a4-1000371857.pdf\"]', 'approved', '2026-04-21 07:38:06', NULL, 'Usage of tote bag', NULL, '2026-04-27 09:34:27', 1, '[\"Lovely Amanda\"]', '[\"Zero Net Carbon Emission\"]', '', ''),
(190, 150, 'Medium Impact', 'M1-Register in the 3ZERO Club', 50, '[\"69e9a54870305-Screenshot_20260423_125155_Chrome.jpg\",\"69e9a54871088-Screenshot_20260422_115801_Chrome.jpg\"]', 'approved', '2026-04-23 04:51:20', NULL, 'Date: 11 February 2026\r\nTime: 10:00 AM\r\nVenue: Albukhary International University\r\nActivity: Registered as a member of 3ZERO Club under Zero Unemployment cluster under Educationpart. Participated in awareness session.', NULL, '2026-04-27 09:36:02', 5, '[\"Karshni A\\/P Karunanidhi\",\"Nurul huda dachanee\",\"Nurhaniza Binti Ahmad\",\"Imman Nurbalqish Nabilah Binti Suhaimi\",\"MUHAMMAD MUKHRIZ DANIEL BIN MAHADZIR\"]', '[\"Zero Unemployment\"]', '458-021-0180', ''),
(191, 150, 'Low Impact', 'L7-Participate in a Sustainability Action Programme Outside Campus', 25, '[\"69e9aadd6d523-IMG-20260423-WA0014.jpg\",\"69e9aadd6db1e-IMG-20260423-WA0015.jpg\"]', 'pending', '2026-04-23 05:15:09', NULL, 'Date: 16 OCTOBER 2025\r\nTime: 9.00PM (ON ZOOM)\r\nVenue: ONLINE \r\nActivity: Participated in Industry Talk Siri 1 organized by Majlis Ikatan Mahasiswa Darul Aman. This program supports the 3ZERO Initiative, particularly Zero Unemployment, by providing students with industry exposure, career guidance, and employability skills. The session aimed to prepare mahasiswa with knowledge and practical insights to enhance job readiness, reduce unemployment risks, and encourage sustainable career development.', NULL, NULL, 1, '[\"Karshni A\\/P Karunanidhi\"]', '[\"Zero Unemployment\"]', '', NULL),
(192, 150, 'Low Impact', 'L7-Participate in a Sustainability Action Programme Outside Campus', 25, '[\"69e9b1ef030e3-IMG-20260423-WA0017.jpg\",\"69e9b1ef03abb-IMG-20260423-WA0025.jpg\"]', 'pending', '2026-04-23 05:45:19', NULL, 'Date: 6 DECEMBER 2025\r\nTime: 3.30 PM\r\nVenue: ALBUKHARY INTERNATIONAL UNIVERSITY \r\nActivity: This activity involved participating in an international dialogue session focused on global issues affecting youth. The session provided a platform for students to share ideas, discuss challenges, and explore solutions related to social, economic, and environmental sustainability. It encouraged critical thinking, communication skills, and global awareness through collaboration with participants from different universities.', NULL, NULL, 1, '[\"Karshni A\\/P Karunanidhi\"]', '[\"Zero Net Carbon Emission\"]', '', NULL),
(193, 105, 'Low Impact', 'L2-Waste Sorting', 25, '[\"69e9fdb82a173-Petwaterbottlealuminiumcan.pdf\"]', 'approved', '2026-04-23 11:08:40', NULL, 'I collect it from 1 hostel 25c outside the door. I will try to segragate and will sell it to e idaman.\r\n\r\n', NULL, '2026-04-27 09:40:18', 1, '[\"MUHAMMAD ARIA SAPUTRA\"]', '[\"Zero Net Carbon Emission\"]', '', ''),
(198, 166, 'Low Impact', 'L6-Participate in the Sustainability Action Programme on Campus', 25, '[\"69eb7c51e3ce2-WhatsAppImage2026-04-24at10.16.01PM.jpeg\",\"69eb7c51e4271-WhatsAppImage2026-04-24at10.25.06PM.jpeg\"]', 'pending', '2026-04-24 14:21:05', NULL, 'I volunteered in the SoftNex 3ZERO Club Opening Ceremony on Saturday, 13 September 2025 (8:30 PM – 10:50 PM) at AIU Auditorium, Albukhary International University, assisting with decoration, setup, and event support.', NULL, NULL, 1, '[\"NUR ARIFAH BINTI MUHAMAD ZAHARI\"]', '[\"Zero Net Carbon Emission\"]', '458-021-0109', NULL),
(199, 166, 'Low Impact', 'L7-Participate in a Sustainability Action Programme Outside Campus', 25, '[\"69eb878f1ff92-IMG_667311.jpg\",\"69eb878f2285f-IMG_68161.jpg\",\"69eb878f25590-IMG_67981.jpg\"]', 'pending', '2026-04-24 15:09:03', NULL, 'I joined as a volunteer in the “JOM TIP PAH” programme held on 31 January 2026 at Pantai Leman, Kuala Kedah. I helped collect rubbish with primary school students and guided them during the clean-up activity. I also encouraged them to practice proper waste collection and be more aware of environmental care.', NULL, NULL, 1, '[\"NUR ARIFAH BINTI MUHAMAD ZAHARI\"]', '[\"Zero Net Carbon Emission\"]', '', NULL),
(200, 166, 'Medium Impact', 'M1-Register in the 3ZERO Club', 50, '[\"69ec868fc83e0-24.04.2026_21.34.48_REC.png\",\"69ec868fc894d-WhatsAppImage2026-04-25at5.15.08PM.jpeg\"]', 'approved', '2026-04-25 09:17:03', NULL, 'The club focuses on Zero Unemployment with a focus on education. It was established on 15 April 2026 under the guidance of Associate Professor Dr. Norizan Binti Azizan and has 5 members. Our group organises and works together with other groups in educational programmes. We act as facilitators to teach, guide, and support students. Through these activities, we help students improve their knowledge and skills while supporting the 3ZERO mission.', NULL, '2026-04-27 09:05:54', 5, '[\"NUR ARIFAH BINTI MUHAMAD ZAHARI\",\"NUR AFINI NADIHA BT SHAHIZAM\",\"NUR AISYAH NAJWA BINTI K OTHMAN\",\"Nur Alya Maisara Binti Gamal El Sorany\",\"MUHAMMAD DANIAL BIN MOHD RADZEE\"]', '[\"Zero Unemployment\"]', '458-021-0172', ''),
(203, 166, 'Medium Impact', 'M2-Group Volunteering (Outside Campus Event)', 50, '[\"69ec89218fc36-WhatsAppImage2026-04-25at5.14.16PM1.jpeg\",\"69ec892190559-WhatsAppImage2026-04-25at5.14.17PM.jpeg\",\"69ec8921908b4-WhatsAppImage2026-04-25at5.14.16PM.jpeg\"]', 'approved', '2026-04-25 09:28:01', NULL, 'We collaborated with other 3ZERO clubs under the InspireED programme, working together as facilitators. We taught and guided students, managed their movement, and ensured the programme ran smoothly. Plastic bottles and other recyclable items were collected from students. As an encouragement, a small amount of money (RM10) from their participation fee was returned to those who brought recyclable items to promote recycling and environmental awareness.', NULL, '2026-04-27 09:06:56', 5, '[\"Dwi Ambara Dzakiyarani\",\"NUR FATIN BINTI ZAINON\",\"NUR ARIFAH BINTI MUHAMAD ZAHARI\",\"Nur Alia Safiya Binti Rombly\",\"Zobayer Mahmud\"]', '[\"Zero Unemployment\"]', '458-021-0109', ''),
(205, 166, 'Medium Impact', 'M2-Group Volunteering (Outside Campus Event)', 50, '[\"69ec914154ccb-WhatsAppImage2026-04-25at5.52.16PM.jpeg\",\"69ec914155581-IMG_20260418_1103181.jpg\",\"69ec914156ac4-IMG_20260418_1102331.jpg\"]', 'approved', '2026-04-25 10:02:41', NULL, 'This time, our group organised the SmartQuest programme in collaboration with another 3ZERO club and worked together to ensure the programme’s success. We also approached the PERKIM orphanage and conducted teaching sessions for the students. We taught English and Mathematics every two weeks, planned lessons together, and supported the students in their learning activities.', NULL, '2026-04-27 09:34:02', 5, '[\"NUR ARIFAH BINTI MUHAMAD ZAHARI\",\"NUR AFINI NADIHA BT SHAHIZAM\",\"NUR AISYAH NAJWA BINTI K OTHMAN\",\"Nur Alya Maisara Binti Gamal El Sorany\",\"MUHAMMAD DANIAL BIN MOHD RADZEE\"]', '[\"Zero Unemployment\"]', '458-021-0172', ''),
(208, 166, 'Medium Impact', 'M1-Register in the 3ZERO Club', 50, '[\"69ec95f51a9b5-WhatsAppImage2026-04-25at6.21.27PM.jpeg\",\"69ec95f51b1c9-24.04.2026_21.49.53_REC.png\"]', 'approved', '2026-04-25 10:22:45', NULL, 'The club focuses on the Zero Net Carbon Emissions cluster with an emphasis on agriculture. It was established on 10 February 2026 under the guidance of Associate Professor Dr. Tengku Shahrom Bin Tengku Shahdan. Our club aims to promote sustainable agricultural practices and environmental awareness through activities related to reducing carbon emissions, supporting eco-friendly farming, and encouraging green initiatives among students and the community.\r\n\r\nThe objectives of the club are to educate members on sustainable agriculture, reduce environmental impact through eco-friendly practices, and encourage active participation in green and climate-friendly projects within and outside the university.', NULL, '2026-04-27 09:09:04', 5, '[\"Dwi Ambara Dzakiyarani\",\"NUR AIMAN FIRAS BINTI SULAIMAN\",\"Yusuf Abdulbasit \",\"NUR FATIN BINTI ZAINON\",\"NUR ARIFAH BINTI MUHAMAD ZAHARI\"]', '[\"Zero Net Carbon Emission\"]', '458-021-0109', ''),
(210, 149, 'Medium Impact', 'M1-Register in the 3ZERO Club', 50, '[\"69ecfc7176869-Screenshot2026-04-26013218.png\",\"69ecfc7176fc6-Screenshot2026-04-26012804.png\"]', 'approved', '2026-04-25 17:40:01', NULL, 'Our club operates under the Zero Net Carbon Emissions focus, with a strong commitment to promoting environmentally responsible practices through creative and sustainable initiatives. It was officially formed on 13 January 2026, under the supervision of Ms. Nur Faridatul Jamalia Radzali, with Madam Tun Syima serving as co-advisor.\r\n\r\nBebeArt is a social business project that produces natural, non-toxic, and eco-friendly crayons for young children using materials like beeswax and plant-based colours. It is designed to make art materials safe, affordable, and accessible, especially for children in underserved communities.', NULL, '2026-04-27 09:22:33', 5, '[\"Yusuf Abdulbasit \",\"Wareesha Khan\",\"Suwaiba Yayaji Yusuf \",\"KANIZ fATEMA SWORNA\",\"Riadah\"]', '[\"Zero Net Carbon Emission\"]', '458-021-0202', ''),
(212, 105, 'Medium Impact', 'M1-Register in the 3ZERO Club', 50, '[\"69ee27adef035-3zeroclubregist.pdf\"]', 'approved', '2026-04-26 14:56:45', NULL, '3 zero club registration\r\n', NULL, '2026-04-27 09:23:30', 5, '[\"MUHAMMAD ARIA SAPUTRA\",\"Lutfi Hakim Bin Roz\\u2019aidie\",\"DANIAL AFIF HAFIZIN BIN MOHD MUSABAKOH \",\"MUHAMMAD DANIAL BIN MOHD RADZEE\",\"Muhammad Nor Haifan Bin Hamjah\"]', '[\"Zero Net Carbon Emission\"]', '458-021-0082', ''),
(213, 105, 'Medium Impact', 'M3-Sustainability Content Creation', 50, '[\"69ee28273472f-sustainablityvideo.pdf\"]', 'approved', '2026-04-26 14:58:47', NULL, 'sustainability  video and post awareness content creation ', NULL, '2026-04-27 09:16:50', 5, '[\"MUHAMMAD ARIA SAPUTRA\",\"Lutfi Hakim Bin Roz\\u2019aidie\",\"DANIAL AFIF HAFIZIN BIN MOHD MUSABAKOH \",\"MUHAMMAD DANIAL BIN MOHD RADZEE\",\"Muhammad Nor Haifan Bin Hamjah\"]', '[\"Zero Net Carbon Emission\"]', '458-021-0082', ''),
(214, 105, 'High Impact', 'H1-Organize 3R (Reduce-Reuse-Recycle) Programme on Campus', 75, '[\"69ee2a13cba8f-dropzoneeidaman.pdf\"]', 'approved', '2026-04-26 15:06:59', NULL, 'dropping the trash in order to sell it through e-idaman, and collecting the points to redeem it to touch n go', NULL, '2026-04-27 09:22:58', 5, '[\"MUHAMMAD ARIA SAPUTRA\",\"Lutfi Hakim Bin Roz\\u2019aidie\",\"DANIAL AFIF HAFIZIN BIN MOHD MUSABAKOH \",\"MUHAMMAD DANIAL BIN MOHD RADZEE\",\"Muhammad Nor Haifan Bin Hamjah\"]', '[\"Zero Net Carbon Emission\"]', '458-021-0082', ''),
(215, 314, 'Low Impact', 'L2-Waste Sorting', 25, '[\"69ef301575744-image.jpg\",\"69ef301577327-image.jpg\"]', 'pending', '2026-04-27 09:44:53', NULL, '27/4/2026, 5:48PM, YAB Building', NULL, NULL, 1, '[\"NURASYIQIN SYAMIMI BINTI MOHD NASIR\"]', '[\"Zero Net Carbon Emission\"]', '', NULL),
(219, 338, 'Low Impact', 'L6-Participate in the Sustainability Action Programme on Campus', 25, '[\"69f00cca656aa-WhatsAppImage2026-04-28at09.21.55.jpeg\",\"69f00cca65b3f-WhatsAppImage2026-04-28at09.21.542.jpeg\",\"69f00cca65ed9-WhatsAppImage2026-04-28at09.21.541.jpeg\"]', 'pending', '2026-04-28 01:26:34', NULL, 'Served as volunteer in the collaboration between Trashformers and Softnex, by guided and teach students to using their both hand or fingers to typing in \'Typing Fun Workshop\'. As an initiative hands-on session promoting safe and creative digital learning.\r\n\r\nTeam members:\r\nDwi Ambara Dzakiyarani\r\nNur Arifah binti Muhamad Zahari', NULL, NULL, 1, '[\"Dwi Ambara Dzakiyarani\"]', '[\"Zero Unemployment\"]', '', NULL),
(220, 338, 'Low Impact', 'L6-Participate in the Sustainability Action Programme on Campus', 25, '[\"69f00e353ba61-WhatsAppImage2026-04-28at09.22.00.jpeg\",\"69f00e353c2af-WhatsAppImage2026-04-28at09.22.001.jpeg\"]', 'pending', '2026-04-28 01:32:37', NULL, 'Served as volunteer for the Grand Ceremony event of Softnex, decorating (Decor team) the stage and help to guide the audience on the day of event to make it run smoothly. \r\n\r\nTeam Member:\r\nDwi Ambara Dzakiyarani', NULL, NULL, 1, '[\"Dwi Ambara Dzakiyarani\"]', '[\"Zero Unemployment\"]', '', NULL),
(221, 338, 'Low Impact', 'L6-Participate in the Sustainability Action Programme on Campus', 25, '[\"69f010cad6f12-1766422769724.jpg\",\"69f010cad7532-1765168991018.jpg\",\"69f010cad77eb-1765167680559.jpg\",\"69f010cad7a6f-WhatsAppImage2026-04-28at09.42.35.jpeg\"]', 'pending', '2026-04-28 01:43:38', NULL, 'Served as the volunteer in 3 events, which are \'Night Market\', \'Artful children\', and \'Convocation\'. Also being as a part of the Elite Gallery club in role of Artist for making products and Marketing helper. Manage to take care of the room of Elite Gallery and support full to their events. ', NULL, NULL, 1, '[\"Dwi Ambara Dzakiyarani\"]', '[\"Zero Unemployment\"]', '', NULL),
(222, 225, 'Medium Impact', 'M1-Register in the 3ZERO Club', 50, '[\"69f03787a5d6f-96130a2b-ea5a-4a24-be41-f396ed99ba06.jpeg\",\"69f03787a60ff-IMG_9462.jpeg\"]', 'pending', '2026-04-28 04:28:55', NULL, '-', NULL, NULL, 5, '[\"Ingyin May\",\"Muh Nur Qadri\",\"Mina Wardah\",\"Puteri Habibah binti Rosli\",\"Mila Fahilan\"]', '[\"Zero Unemployment\"]', '20458-021-0182', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `submission_feedback`
--

CREATE TABLE `submission_feedback` (
  `id` int NOT NULL,
  `submission_id` int DEFAULT NULL,
  `admin_id` int DEFAULT NULL,
  `feedback` text COLLATE utf8mb4_general_ci,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `submission_team_members`
--

CREATE TABLE `submission_team_members` (
  `id` int NOT NULL,
  `submission_id` int NOT NULL,
  `user_id` int NOT NULL,
  `member_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `member_email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sustainability_scores`
--

CREATE TABLE `sustainability_scores` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `water_usage` float NOT NULL,
  `sustainability_score` float NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `reusable_items` int DEFAULT NULL,
  `walking_days` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sustainability_scores`
--

INSERT INTO `sustainability_scores` (`id`, `user_id`, `water_usage`, `sustainability_score`, `created_at`, `reusable_items`, `walking_days`) VALUES
(25, 167, 50, 85, '2026-04-15 03:00:13', 7, 6),
(26, 209, 67, 94, '2026-04-20 13:13:36', 10, 6);

-- --------------------------------------------------------

--
-- Table structure for table `transaction_logs`
--

CREATE TABLE `transaction_logs` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `action_type` enum('earn','redeem') COLLATE utf8mb4_general_ci NOT NULL,
  `points` int NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `date_of_birth` date NOT NULL,
  `phone_number` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('admin','user') COLLATE utf8mb4_general_ci DEFAULT 'user',
  `eco_points` int DEFAULT '0',
  `profile_pic` varchar(255) COLLATE utf8mb4_general_ci DEFAULT 'default-profile.jpg',
  `program_of_study` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `intake` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `country` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `gender` enum('Male','Female','Other') COLLATE utf8mb4_general_ci NOT NULL,
  `department` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `expected_graduation_year` year NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reset_token` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `date_of_birth`, `phone_number`, `email`, `password`, `role`, `eco_points`, `profile_pic`, `program_of_study`, `intake`, `country`, `gender`, `department`, `expected_graduation_year`, `created_at`, `reset_token`, `token_expiry`) VALUES
(47, 'Chit Ko Ko Admin Account', '2025-07-27', '9974459597', 'chitkoko.ali@gmail.com', '$2y$10$olL6/gyeo.pQzE1UzD0XKePuyiuFfqHOnZoVvwYOisee/jGWFtYxK', 'admin', 0, 'profile_6978be46910a95.03122935.jpeg', 'Bachelor of Business Administration (Honours) (Marketing)', 'March 2023', 'Cambodia', 'Male', 'School Of Business & Social Sciences', '2038', '2025-08-18 19:03:08', NULL, NULL),
(86, 'Aliaa Diyana Zamri', '1992-05-29', '0197995759', 'aliaa.zamri@aiu.edu.my', '$2y$10$Zri/JHiqzwinBgC8lHieD.XS9uUUPjYIl00p4VEws.o0/hLn0ZMTu', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours) (Human Resource Management)', 'March 2020', 'Malaysia', 'Female', 'School Of Business & Social Sciences', '2025', '2025-08-22 07:03:56', NULL, NULL),
(88, 'MASRANI BIN AWANG', '1996-06-12', '0178323818', 'masraniawang@gmail.com', '$2y$10$dAQu5NfldWql.CsaVqj2S.t5vMqasUfxZHqkkNX.2Zv4aKkcfuaNu', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours) (Marketing)', 'March 2021', 'Malaysia', 'Male', 'School Of Business & Social Sciences', '2025', '2025-08-22 07:04:41', NULL, NULL),
(89, 'suraya hanim mokhtar', '1980-06-22', '0192612901', 'suraya.mokhtar@aiu.edu.my', '$2y$10$GzSSwPoGUjbrIPoqO6wsluq8i/nfoj2MzBxvsYSIxsZTJlMalPaxe', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours)', 'October 2021', 'Malaysia', 'Female', 'School Of Business & Social Sciences', '2027', '2025-08-22 07:06:07', NULL, NULL),
(90, 'Chit Ko Ko', '2003-04-08', '01112476299', 'chitko.ko@student.aiu.edu.my', '$2y$10$dWzS1flTloB16ak0DN1rHOV02shPjbObFwvFgr6gOdVzp75fT2u7S', 'user', 0, 'profile_6978bfa524e675.85654990.jpeg', 'Bachelor in Computer Science (Honours)', 'October 2023', 'Myanmar', 'Male', 'School Of Computing and Informatics', '2027', '2025-08-22 07:07:49', NULL, NULL),
(91, 'Arezo Jafari', '2003-08-22', '01139679917', 'arezo.jafari@student.aiu.edu.my', '$2y$10$0jEvOm/WUU0i8/i8o5Nctulwb258sSMlt9QYtFQTOas62QHKXBgJC', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours)', 'October 2022', 'Malaysia', 'Female', 'School Of Business & Social Sciences', '2026', '2025-08-22 07:08:08', NULL, NULL),
(92, 'Nurul Huda', '1985-02-22', '0166748646', 'huda.bakri@aiu.edu.my', '$2y$10$FpbUm7T/Y/2F/.Za.KEu7uv0ypx6u9RKx9e8Yj1hy2yKLtuxjspba', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'March 2020', 'Malaysia', 'Female', 'School Of Education & Human Sciences', '2025', '2025-08-22 07:35:06', NULL, NULL),
(93, 'MD HASIB UL HAIDER ', '2001-01-01', '01127261217', 'mdhasib.ulhaider@student.aiu.edu.my', '$2y$10$t.a7oJ8krGe8ZkAMN36qUOyFEXxQUZiEstQv9860rVECqTK9RxhCS', 'user', 0, 'default-profile.jpg', 'Bachelor in Computer Science (Honours)', 'October 2024', 'Bangladesh', 'Male', 'School Of Computing and Informatics', '2027', '2025-08-31 09:16:26', NULL, NULL),
(96, 'Abdirahman Muhyadin Ali', '2005-11-22', '0142774486', 'abdirahman.muhyadin@student.aiu.edu.my', '$2y$10$cAOWMSrVatCNzGgxLjZ5yusQxIaV2dpvoDldgzK5EWPp8XXQ66qEy', 'user', 0, 'default-profile.jpg', 'Bachelor of Economics (Honours)', 'March 2024', 'Somalia', 'Male', 'School Of Business & Social Sciences', '2028', '2026-01-14 13:43:07', NULL, NULL),
(97, 'Hafnan Mamah', '2004-02-26', '0172794680', 'hafnan.mamah@student.aiu.edu.my', '$2y$10$Y/2JNLZNP899bM0Zc49f.u6b5ouZtxRC6ojzhP2twlAoJe8OL75D6', 'user', 0, 'default-profile.jpg', 'Bachelor of Economics (Honours)', 'October 2024', 'Thailand', 'Female', 'School Of Business & Social Sciences', '2027', '2026-01-14 13:46:38', NULL, NULL),
(98, 'Sanaa Mohammed Said Al-rabeei ', '2001-05-03', '01170113984', 'sanaa.said@student.aiu.edu.my', '$2y$10$i056Wxy//PjUY9Ka7VFFCehV9yoHEmb8nImnYVRYLD3BZDpflikCm', 'user', 0, 'default-profile.jpg', 'Bachelor of Politics and International Relations (Honours)', 'October 2023', 'Yemen ', 'Female', 'School Of Business & Social Sciences', '2027', '2026-01-14 13:46:45', NULL, NULL),
(99, 'Safeer Ullah', '2005-03-12', '01139776726', 'safeer.ullah@student.aiu.edu.my', '$2y$10$s.1W9gGLqy5ldvxjJJ.Cx.Aj3wAN6QqoYqDQ5ZC/NRkYoN.umVFIG', 'user', 0, 'default-profile.jpg', 'Bachelor of Economics (Honours)', 'March 2025', 'Pakistan ', 'Male', 'School Of Business & Social Sciences', '2028', '2026-01-14 13:46:49', NULL, NULL),
(100, 'Fauziah', '2004-11-22', '01157765957', 'fauziah.fauziah@student.aiu.edu.my', '$2y$10$MsE4dsP2uz8j695wK6agDu6iVktcePcgPZ7l2U8LAKPz9qkDT6qhS', 'user', 0, 'default-profile.jpg', 'Bachelor of Economics (Honours)', 'October 2024', 'Indonesia', 'Female', 'School Of Business & Social Sciences', '2028', '2026-01-14 14:10:56', NULL, NULL),
(101, 'Nashir Ngoobi', '2000-12-25', '0176547946', 'nashir.ngoobi@student.aiu.edu.my', '$2y$10$D0ZC6wiA5uvNw5wCW.9nuun8viMOUXyR9ubuv.Id4BGSRnLmRBgWG', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours)', 'October 2023', 'Uganda', 'Male', 'School Of Business & Social Sciences', '2026', '2026-01-21 13:27:40', NULL, NULL),
(102, 'Evelyn Usaiwevhu', '2003-06-29', '01137170160', 'evelyn.usaiwevhu@student.aiu.edu.my', '$2y$10$mi4ZaXb0F6YKjM.0hKXJ7ea446rpjaLpuaL.wzwIKxPIPZgEpdTGG', 'user', 0, 'default-profile.jpg', 'Bachelor of Media and Communication (Honours)', 'October 2023', 'Zimbabwe', 'Female', 'School Of Education & Human Sciences', '2026', '2026-01-21 14:06:53', NULL, NULL),
(104, 'Desti Anggri', '2004-12-09', '1164092676', 'desti.anggri@student.aiu.edu.my', '$2y$10$xcsI.pgLIo/FeyMVC6lwcOA8DCMjU0HuhtmcbnodRqdkRnTkC04PG', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'October 2024', 'Indonesia', 'Female', 'School Of Education & Human Sciences', '2027', '2026-01-21 14:11:05', NULL, NULL),
(105, 'MUHAMMAD ARIA SAPUTRA', '2001-04-21', '01156559835', 'aria.saputra@student.aiu.edu.my', '$2y$10$9m15BX8BiGynrtNSIgcq.ewZdgoMGgqtHr45mWUN9zxLUL1Bm1PAi', 'user', 0, 'profile_69e1072800e793.63902590.jpeg', 'Bachelor of Elementary Education (Honours)', 'October 2024', 'Malaysia', 'Male', 'School Of Education & Human Sciences', '2027', '2026-01-21 23:57:25', NULL, NULL),
(106, 'Sufwan Suwae', '2005-06-27', '01125536425', 'sufwan.suwae@student.aiu.edu.my', '$2y$10$BlGxO/4mRPJUfO.SO1PWBOkpXh27SGRkZ2X86bNW35h7AoRd0QILi', 'user', 0, 'default-profile.jpg', 'Bachelor of Politics and International Relations (Honours)', 'October 2024', 'Thailand', 'Male', 'School Of Business & Social Sciences', '2028', '2026-01-22 07:37:51', NULL, NULL),
(107, 'MUHAMAD DAUD SULAEMAN ', '2005-04-21', '60103045356', 'daud.sulaeman@student.aiu.edu.my', '$2y$10$FLNZHHDJdlRBR1k15FR1..b2wqxNRT86/cdxg562N1lA8tzKJHbDO', 'user', 0, 'default-profile.jpg', 'Bachelor of Social Development (Honours)', 'March 2024', 'Indonesia ', 'Male', 'School Of Business & Social Sciences', '2027', '2026-01-22 15:24:42', NULL, NULL),
(108, 'Nurlailah', '2005-02-18', '01159574969', 'nurlailah.nurlailah@student.aiu.edu.my', '$2y$10$AcateEqIEgaQXBUn.DCOAOnyILrn89E033bnhdMYePQkHE5Z2JFzC', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours)', 'October 2023', 'Indonesia', 'Female', 'School Of Business & Social Sciences', '2027', '2026-01-22 15:32:19', NULL, NULL),
(111, 'Yusuf Hadiwijaya', '2004-06-02', '85813962534', 'yusuf.hadiwijaya@student.aiu.edu.my', '$2y$10$Jj6h.0jAMsQeOQgoG/tche4KhApspdzc33Bzlee2lhm5FKxxLMTTO', 'user', 0, 'profile_6984d9bd4f1509.25534390.jpeg', 'Bachelor of Economics (Honours)', 'October 2024', 'Indonesia', 'Male', 'School Of Business & Social Sciences', '2027', '2026-01-28 08:02:41', NULL, NULL),
(112, 'Maryam Hussaina', '2026-05-29', '+60132793202', 'maryam.rafeek@student.aiu.edu.my', '$2y$10$yS56ins4EYfbMPDiD9Erpu/O6yWabMDYcjU70rjKOfFIc79jyWo5m', 'user', 0, 'default-profile.jpg', 'Bachelor in Computer Science (Honours)', 'October 2023', 'Sri Lanka', 'Female', 'School Of Computing and Informatics', '2027', '2026-01-29 08:42:50', NULL, NULL),
(113, 'Fusimah Musa', '2004-07-23', '0172065910', 'fusimah.musa@student.aiu.edu.my', '$2y$10$Vzd4Vq3YxMhbZdezjI9pGOCyTOYKsZFYcuie5dYsmutQr8fxAeeAy', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'October 2023', 'Thailand', 'Female', 'School Of Education & Human Sciences', '2027', '2026-01-31 11:26:53', NULL, NULL),
(114, 'Nurul Alia Atiqah Binti Mohamad Rosli', '1999-12-09', '019-4027231', 'alia.rosli@aiu.edu.my', '$2y$10$BpaASId5OC7qtZPpD7Det.KOWqHwqbrtCJALg/rg7WL6Vfq7o8UwO', 'admin', 0, 'default-profile.jpg', 'N/A', 'OCTOBER', 'Malaysia', 'Female', 'ACE-SEDI', '2024', '2026-02-03 08:39:30', NULL, NULL),
(115, 'Firdow Salae', '2005-02-15', '0147354487', 'firdow.salae@student.aiu.edu.my', '$2y$10$y.86QKlNZcfIyhgzEgRv.Og27/BABR2x5nMpdjpfVJRnH9aPA2PMS', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'October 2023', 'Thailand', 'Female', 'School Of Education & Human Sciences', '2027', '2026-02-04 12:38:03', NULL, NULL),
(116, 'Azhari Bin Hussaim', '2001-07-01', '0174832498', 'azhari.hussain@student.aiu.edu.my', '$2y$10$kasbg7p45xJyg.RroZCU/.nmADTv3zRnkjavPMuFnameE2UbxKqgG', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours) (Human Resource Management)', 'October 2023', 'Malaysia', 'Male', 'School Of Business & Social Sciences', '2026', '2026-02-08 06:12:04', NULL, NULL),
(117, 'Anas Sani Lawal Muhammed', '2003-08-10', '+60179926759', 'Anas.lawal@student.aiu.edu.my', '$2y$10$1InMHQZumqI6EzuCk0bBfuiyqU.o5hNgUfMF9oQEL9fWjV6y8Lk6a', 'user', 0, 'default-profile.jpg', 'Bachelor in Computer Science (Honours)', 'October 2023', 'Nigeria', 'Male', 'School Of Computing and Informatics', '2027', '2026-02-08 09:18:27', NULL, NULL),
(118, 'Amaneena Cheha', '2006-04-30', '0973879814', 'amaneena.cheha@student.aiu.edu.my', '$2y$10$d830FjUz7r8u3ekJDTpuCOzjHOzqk/QCP/HtDWAs.Gm8pu8tyvxUi', 'user', 0, 'default-profile.jpg', 'Bachelor in Early Childhood Education (Honours)', 'October 2024', 'Thailand', 'Female', 'School Of Education & Human Sciences', '2028', '2026-02-08 09:44:34', NULL, NULL),
(119, 'Nurhasnee Salama ', '2004-07-20', '0179984287', 'nurhasnee.salama@student.aiu.edu.my', '$2y$10$Mrv6kbYdDbJS4dxYakWvJuEpvZAGeGacabz07LzrzcBnLoy/uZ7kK', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours)', 'October 2025', 'Thailand', 'Female', 'School Of Business & Social Sciences', '2028', '2026-02-08 09:49:41', NULL, NULL),
(120, 'AHMAD FAIZ NAJMI BIN MUHAMMAD AZMI', '2005-11-24', '01135272427', 'faiz.azmi@student.aiu.edu.my', '$2y$10$.dLMDxR3AzZtzKMKMMP/7uLyQY62OqYTMZu3GK8U6LPHjn.N2zx/O', 'user', 0, 'default-profile.jpg', 'Bachelor in Data Science (Honours)', 'October 2024', 'Malaysia', 'Male', 'School Of Computing and Informatics', '2027', '2026-02-08 10:32:55', NULL, NULL),
(121, 'Sakinah Wohni', '2005-08-11', '0163504721', 'sakinah.wohni@student.aiu.edu.my', '$2y$10$1KeiPSvj716Z4B8VXyRnH.Di6B9JGjKejhQbAcXaHZsdm2mxLOl3u', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours)', 'October 2025', 'Thailand', 'Female', 'School Of Business & Social Sciences', '2028', '2026-02-08 12:45:37', NULL, NULL),
(122, 'Hnin Pwint Phyu', '2003-12-13', '1128906865', 'hnin.phyu@student.aiu.edu.my', '$2y$10$t2P637oJhk5tAyamAQKsH.OoQROb2SEpDZqmpSh879CMWmTxSleLm', 'user', 0, 'default-profile.jpg', 'Bachelor of Finance (Islamic Finance) (Honours)', 'October 2023', 'Myanmar', 'Female', 'School Of Business & Social Sciences', '2026', '2026-02-09 03:03:10', NULL, NULL),
(123, 'Widia', '2006-07-15', '087823244197', 'widia.widia@student.aiu.edu.my', '$2y$10$yx4ukA5HUfqNX1Zbh2F/Ou3KNGqOdeAWAWpbsFwJr9KbAW.JhF/Wa', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours)', 'October 2025', 'Indonesia', 'Female', 'School Of Business & Social Sciences', '2028', '2026-04-13 05:25:53', NULL, NULL),
(124, 'Muhammad Kelvin Tri Athira Abel', '2004-06-30', '083195062023', 'kelvin.athiraabel@student.aiu.edu.my', '$2y$10$iArYPQItR5YDJG3tkghGiOvD5C5lj3vGhTs1VPtUP.liPzO6uGR0K', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours) (Marketing)', 'October 2023', 'Indonesia', 'Male', 'School Of Business & Social Sciences', '2027', '2026-04-13 05:27:11', NULL, NULL),
(125, 'Muh. Sarkowi Ramadan ', '2004-10-16', '0115779034', 'muhsarkowi.ramadan@student.aiu.edu.my', '$2y$10$mkcg1YwHsGfXhqQNTDmhc.VICp11/zzN3oRagP4aBrU5lX1HLHXu2', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours)', 'October 2025', 'Indonesia', 'Male', 'School Of Business & Social Sciences', '2028', '2026-04-13 05:30:20', NULL, NULL),
(126, 'Farhan Fathurahman', '2005-01-04', '+60 176142913 ', 'farhan.fathurahman@student.aiu.edu.my', '$2y$10$uxTBAn2O1Vz7iAVc9COtXuPy0/syItFTIrETpeFZqIx6iWwTIkLa.', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours) (Marketing)', 'October 2023', 'Indonesia', 'Male', 'School Of Business & Social Sciences', '2027', '2026-04-13 05:37:11', NULL, NULL),
(127, 'Vanessa Veneka', '2003-10-01', '01139538053', 'vanessaveneka@student.aiu.edu.my', '$2y$10$QdMt9CaohWtmdaWXwSFx3ecnvUJs7I4aTrHX1nW8.g/gwwK22uJ8C', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours)', 'October 2023', 'Zimbabwe', 'Female', 'School Of Business & Social Sciences', '2026', '2026-04-13 06:11:15', NULL, NULL),
(128, 'Bernard Muzenda ', '2005-06-08', '01127401547', 'aiu25102139@student.aiu.edu.my', '$2y$10$c3YX16m4L1eIU26dNlb5XeCck2i.PoM5BQZT/znk9B7QRl1sHlyTq', 'user', 0, 'profile_69dc91f358d754.13622078.jpeg', 'Bachelor in Computer Science (Honours)', 'October 2025', 'Zimbabwe', 'Male', 'School Of Computing and Informatics', '2028', '2026-04-13 06:46:53', NULL, NULL),
(129, 'Sajjad Sajjad', '2003-03-15', '+601128117201', 'aiu25102278@student.aiu.edu.my', '$2y$10$qX7eXzQCXPtJfSfqGJBe2u51r1yjUk98AYaGaR3nullVHPoSLrqOe', 'user', 0, 'default-profile.jpg', 'Bachelor in Computer Science (Honours)', 'March 2026', 'Pakistan', 'Male', 'School Of Computing and Informatics', '2029', '2026-04-13 06:52:31', NULL, NULL),
(130, 'Sanusi Sani Muhammad ', '2005-06-28', '+60164027424', 'aiu25102628@student.aiu.edu.my', '$2y$10$bHZrWTXkpQdZZ0ysozlwv.xGt8zlo/czB4Gh5dF76jQPEWGH5Wkmm', 'user', 0, 'default-profile.jpg', 'Bachelor in Data Science (Honours)', 'March 2025', 'Nigeria ', 'Male', 'School Of Computing and Informatics', '2028', '2026-04-13 06:55:43', NULL, NULL),
(131, 'Muntashir Mahmud Angel ', '2001-08-23', '0142077861', 'muntashir.angel@student.aiu.edu.my', '$2y$10$hljdwS3lArKvRL.XW6Z9ruU3Go6Mdr8AwkHe3oxlunm38YWFfo3nq', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours)', 'October 2023', 'Bangladesh', 'Male', 'School Of Business & Social Sciences', '2026', '2026-04-13 06:56:42', NULL, NULL),
(132, 'Belal Ahmad', '2006-05-13', '+601157783498', 'belal.ahmad@student.aiu.edu.my', '$2y$10$nO6ST9CIZcfmudNyJASaL.g9zWnbt4DxBTR6svoc0m7n403N7zNUu', 'user', 0, 'profile_69dc9476474736.04026504.jpeg', 'Bachelor of Business Administration (Honours) (Marketing)', 'October 2025', 'Nepal', 'Male', 'School Of Business & Social Sciences', '2028', '2026-04-13 06:57:18', NULL, NULL),
(133, 'NASSIR ALI IBRAHIM MOHAMMEDSAEED ', '2002-06-18', '01125163735', 'nassir.mohammedsaeed@student.aiu.edu.my', '$2y$10$WIWuaA2KTke8dL0tbGoqre6GuenIWNaXMN5Zjn6OaI5iEV3DC2BN6', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours) (Marketing)', 'October 2025', 'Sudan', 'Male', 'School Of Business & Social Sciences', '2028', '2026-04-13 06:57:28', NULL, NULL),
(134, 'Aysha Shahani Mohammed Asad', '2002-06-09', '+60132801085', 'aysha.asad@student.aiu.edu.my', '$2y$10$5objPwKa3MM.gMHfBil47OV.qCvn.l2hwMoa7AjgjKbGjECiYtu4O', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours)', 'October 2023', 'Sri Lanka', 'Female', 'School Of Business & Social Sciences', '2027', '2026-04-13 06:58:39', NULL, NULL),
(135, 'Md Shabbir Hossain Showne', '2005-01-01', '0172663274', 'mdshabbir.showne@student.aiu.edu.my', '$2y$10$qivoCu9DB4DJtBHKlUcnKOhRp/IkoNs4gI3Mks/RhRTQRfcHHT1Je', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours)', 'October 2025', 'Bangladesh', 'Male', 'School Of Business & Social Sciences', '2028', '2026-04-13 06:59:27', NULL, NULL),
(136, 'Dewan Md Julfiqur ', '2003-12-10', '0177456425', 'aiu25102619@student.aiu.edu.my', '$2y$10$llVUjCgWNgiGxw4PWJGuN.wlVGHIVnlKDW7PJSPXPLU1Erscz1e2S', 'user', 0, 'default-profile.jpg', 'Bachelor of Media and Communication (Honours)', 'March 2026', 'Bangladesh ', 'Male', 'School Of Education & Human Sciences', '2029', '2026-04-13 07:09:18', NULL, NULL),
(137, 'Shawwal Afridi Adib ', '2005-11-27', '+8801716053620', 'aiu25102106@student.aiu.edu.my', '$2y$10$d38OgdjLOd4w7qgN3r38yeBkGpdi4ntX3rI68.rbimnOhP50T5lLC', 'user', 0, 'default-profile.jpg', 'Bachelor in Data Science (Honours)', 'October 2025', 'Bangladesh', 'Male', 'School Of Computing and Informatics', '2028', '2026-04-13 07:28:01', NULL, NULL),
(138, 'Rivaldo Hungwe', '2002-08-14', '01161403926', 'rivaldo.hungwe@student.aiu.edu.my', '$2y$10$pA1tO.sjcwshV.LkoOE2ou/PCGwkTk5Pc7EAlZTzLdgMLLpxT9I8m', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours)', 'October 2023', 'Malaysia', 'Male', 'School Of Business & Social Sciences', '2026', '2026-04-13 07:31:00', NULL, NULL),
(139, 'Tanha Beentey Ali ', '2006-02-13', '+60172792766', 'aiu25102102@student.aiu.edu.my', '$2y$10$ziR2M1ro7z7PGOJUCqHzLuzbjdHBC//NbID6DmbwudjWzQgrO2Kka', 'user', 0, 'default-profile.jpg', 'Bachelor in Computer Science (Honours)', 'October 2025', 'Bangladesh', 'Female', 'School Of Computing and Informatics', '2028', '2026-04-13 07:34:17', NULL, NULL),
(140, 'MUHAMMAD ZULKIFLI BIN MOHAMAD BADLE ', '2006-02-06', '01117724509', 'aiu25102194@student.aiu.edu.my', '$2y$10$fdlDLnN.K9cICeoYeRKdt.8ewk39fM48oqW4OYMZUTCMRtbhVX.j2', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours) (Human Resource Management)', 'October 2025', 'Malaysia ', 'Male', 'School Of Business & Social Sciences', '2028', '2026-04-13 07:34:57', NULL, NULL),
(141, 'Ma hui', '2006-06-11', '0175489514', 'aiu25102433@student.aiu.edu.my', '$2y$10$4IMEwcJp7sG/h2fDRA7OYOTDiU/oaf027lV1whXLTbCe.xgXrSuQa', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours) (Marketing)', 'March 2027', 'China', 'Female', 'School Of Business & Social Sciences', '2029', '2026-04-13 07:42:05', NULL, NULL),
(142, 'Mohamed Anwar Fathima Ramzana', '2004-11-05', '0109618579', 'aiu25102626@student.aiu.edu.my', '$2y$10$.ApBR1wkDoEQeHhaG4i1/ujsqjnS9sru/oOZO2eS0Ep45jONYvcka', 'user', 0, 'default-profile.jpg', 'Bachelor of Finance (Islamic Finance) (Honours)', 'March 2026', 'Sri Lanka', 'Female', 'School Of Business & Social Sciences', '2028', '2026-04-13 07:44:10', NULL, NULL),
(143, 'Bayan Idris Mahmoud Idris ', '2006-12-17', '01116157455', 'AIU25101059@student.aiu.edu.my', '$2y$10$i3qWfupaiIwcxQq1bbgN4eUYuo9t/dtl..SIvt3vq/vmyt1wRPaeK', 'user', 0, 'default-profile.jpg', 'Foundation in Computing', 'October 2025', 'Eritrea ', 'Female', 'Centre for Foundation and General Studies', '2029', '2026-04-13 08:15:36', NULL, NULL),
(144, 'Dwi Ambara Dzakiyarani', '2004-03-09', '0172793309', 'dwiambara.dzkiyarani@student.aiu.edu.my', '$2y$10$Hl/Q6d1j8enlmiWbgakaV.S5vLAPuym/YpfnU0W1R8JebKtDYZhMO', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'March 2025', 'Indonesia', 'Female', 'School Of Education & Human Sciences', '2028', '2026-04-13 09:09:24', NULL, NULL),
(145, 'NUR AIMAN FIRAS BINTI SULAIMAN', '2004-07-11', '0163574931', 'nuraiman.sulaiman@student.aiu.edu.my', '$2y$10$Kq3.ikkh5IoaHFaPtiCar.57irUHaZf6.0mh5jsQLr54Lu.NAXTi6', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'October 2024', 'Malaysia', 'Female', 'School Of Education & Human Sciences', '2027', '2026-04-13 09:21:47', NULL, NULL),
(146, 'Andile Siso', '2001-03-12', '1161406896', 'andile.siso@student.aiu.edu.my', '$2y$10$09q81gXctzf2neVph.an5OHIGD9atLIycbcB4smEk/AiGdN55MelC', 'user', 0, 'default-profile.jpg', 'Bachelor of Social Development (Honours)', 'October 2023', 'Zimbabwe', 'Male', 'School Of Business & Social Sciences', '2026', '2026-04-13 09:21:59', NULL, NULL),
(147, 'Muhsin Jamal Essa', '2005-05-13', '01128997052', 'muhsin.essa@student.aiu.edu.my', '$2y$10$3aQMWO9In.PHF3QqgRrILejSt29mntAWLHax6qowWwjBskl6eOPT.', 'user', 0, 'default-profile.jpg', 'Bachelor of Social Development (Honours)', 'October 2025', 'Somalia', 'Male', 'School Of Business & Social Sciences', '2028', '2026-04-13 09:32:30', NULL, NULL),
(148, 'Divyashree Ramesh ', '2006-11-01', '0172957561', 'divyashree.ramesh@student.aiu.edu.my', '$2y$10$LYA70YB2fViFGNXwWyZw/Oca6e0fSMpWqLHLDXQiCiQ8fD4VsVOuC', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours)', 'March 2025', 'India', 'Female', 'School Of Business & Social Sciences', '2028', '2026-04-13 09:37:52', NULL, NULL),
(149, 'Yusuf Abdulbasit ', '2006-08-13', '‪1129268235‬', 'abdulbasit.adeyemi@student.aiu.edu.my', '$2y$10$t62TJeTS6z.IW.4hcmn0ceN8/eCGT3MAsnvizvFoo1OTV3nB7kCrq', 'user', 0, 'default-profile.jpg', 'Bachelor in Early Childhood Education (Honours)', 'October 2024', 'Nigeria', 'Male', 'School Of Education & Human Sciences', '2027', '2026-04-13 09:39:14', NULL, NULL),
(150, 'Karshni A/P Karunanidhi', '2003-06-01', '01158669714', 'karshni.karunanidhi@student.aiu.edu.my', '$2y$10$bfBGBj.z5Ir/YstIcfR3y./85ZzSzQcvgB5.chNweu.sicAI7/CxS', 'user', 0, 'profile_69e9a5a02c6ac5.79791642.jpeg', 'Bachelor of Elementary Education (Honours)', 'October 2024', 'Malaysia', 'Female', 'School Of Education & Human Sciences', '2027', '2026-04-13 09:44:37', NULL, NULL),
(151, 'Adebambo Wariz Adegbenro', '2004-08-13', '+601129268617', 'adebambo.adegbenro@student.aiu.edu.my', '$2y$10$ePRWr5NN.0zE1gLsancubO6yEwbQPwUQjbNGN6xd8UJj6GOCwCUcS', 'user', 0, 'default-profile.jpg', 'Bachelor of Politics and International Relations (Honours)', 'October 2024', 'Nigeria ', 'Male', 'School Of Business & Social Sciences', '2027', '2026-04-13 09:45:59', NULL, NULL),
(152, 'Abul Hosen', '2003-06-01', '01156987991', 'abul.hosen@student.aiu.edu.my', '$2y$10$3/OusIGiyKpEqRTRN.g/K.uL.MeV8WzfrziyI4ShFHPb26A2lKorO', 'user', 0, 'default-profile.jpg', 'Bachelor of Finance (Islamic Finance) (Honours)', 'October 2024', 'Bangladesh', 'Male', 'School Of Business & Social Sciences', '2027', '2026-04-13 09:47:54', NULL, NULL),
(153, 'Amatullah Ali ', '2006-04-05', '013-2127421', 'aiu25102012@student.aiu.edu.my', '$2y$10$I1t/RVHiP/7v2sqdPPkuJepHJc8pgU7Dm1SiendZ8RMM1e9Ae5u5G', 'user', 0, 'default-profile.jpg', 'Bachelor in Computer Science (Honours)', 'October 2025', 'Ghana ', 'Female', 'School Of Computing and Informatics', '2028', '2026-04-13 09:48:00', NULL, NULL),
(154, 'Hawa Abdul-Salam', '2003-08-27', '148658435', 'aiu25102013@student.aiu.edu.my', '$2y$10$x/lp5QEq8ZGSNVJgBJxG9e2Jo.ggnScS5h59jus.wQV/DCMLtF8Aa', 'user', 0, 'default-profile.jpg', 'Bachelor in Computer Science (Honours)', 'October 2025', 'Ghana', 'Female', 'School Of Computing and Informatics', '2028', '2026-04-13 09:57:12', NULL, NULL),
(155, 'Mst Farjana Akter Bithi', '2005-01-01', '+60 17-369 4916', 'AIU25102359@student.aiu.edu.my', '$2y$10$G6z8s.BpiqIx8ZQdKYo.2Oury6uMgelyP6plFB1iy11J712l5S8lK', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours)', 'October 2025', 'Bangladesh ', 'Female', 'School Of Business & Social Sciences', '2030', '2026-04-13 10:59:23', NULL, NULL),
(156, 'Friba Bahrami', '2002-06-15', '+601162218185', 'aiu25102448@aiu.edu.my', '$2y$10$nu3Kyq5FpO.TCZBFKQsOv.HRFrStJlixMy3A9f31s2Rh2zkZh27m.', 'user', 0, 'default-profile.jpg', 'Bachelor of Economics (Honours)', 'March 2026', 'Afghanistan', 'Female', 'School Of Business & Social Sciences', '2029', '2026-04-13 10:59:57', NULL, NULL),
(157, 'Khalid Abdussamii', '2006-06-28', '+60173889576', 'khalid.abdussamii@student.aiu.edu.my', '$2y$10$lb2dZv7IZs4KrmFBwOM3R.1UAry/Mwma2S3DvS5qf9MPiLFTYBn92', 'user', 0, 'default-profile.jpg', 'Bachelor in Computer Science (Honours)', 'October 2024', 'Nigeria', 'Male', 'School Of Computing and Informatics', '2027', '2026-04-13 11:01:34', NULL, NULL),
(158, 'Nurul huda dachanee', '2003-02-23', '142125264', 'nurulhuda.dachanee@student.aiu.edu.my', '$2y$10$PA2og3ROItCqw1ze9eOFweVmOedkj9BTIndJ8NJ/iFa6T1xJQNzb6', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'October 2024', 'Thailand', 'Female', 'School Of Education & Human Sciences', '2027', '2026-04-13 13:15:55', NULL, NULL),
(159, 'SANGEETHA ANANTHAN ', '2007-10-28', '173688435', 'aiu25102591@student.aiu.edu.my', '$2y$10$70Tvaf7AFuNheudgzDVOi.k32XDX8ZRKhmKUoXI17zgbIqayemhne', 'user', 0, 'default-profile.jpg', 'Bachelor of Economics (Honours)', 'March 2026', 'India', 'Female', 'School Of Business & Social Sciences', '2030', '2026-04-14 01:01:32', NULL, NULL),
(160, 'Nur fatin binti zainon', '2004-06-12', '0177339702', 'nurfatinzainon@student.aiu.edu.my', '$2y$10$PnLStsWiuy386NvhR1Lpoe7xps5caiPi/Ttuw9DQAkhxe5HDWXusW', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'October 2024', 'Malaysia', 'Female', 'School Of Education & Human Sciences', '2027', '2026-04-14 01:06:09', NULL, NULL),
(161, 'NUR FATIN BINTI ZAINON', '2004-06-12', '0177339702', 'nurfatin.zainon@student.aiu.edu.my', '$2y$10$eZSKs7v1x6z3DFrAm7yYZeP.8PpveHCcUo/nZhlNoB8mn2AcTvq2a', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'October 2024', 'Malaysia', 'Female', 'School Of Education & Human Sciences', '2027', '2026-04-14 01:12:10', NULL, NULL),
(162, 'Irfan Noorzad', '2002-02-05', '+601168331440', 'irfan.noorzad@student.aiu.edu.my', '$2y$10$MV4qRNdsX433uArRqayT/eZLvmrVy3AGLdoL7krhVXZZD8AvEIEdS', 'user', 0, 'default-profile.jpg', 'Bachelor of Economics (Honours)', 'October 2023', 'Afghanistan', 'Male', 'School Of Business & Social Sciences', '2026', '2026-04-14 01:30:05', NULL, NULL),
(163, 'Khant Eaint Hmoo', '2004-01-04', '01161434977', 'khanteaint.hmoo@student.aiu.edu.my', '$2y$10$SYU0gy.JW7VNni8N1BRfS.2VGulAajo3DUECYVJHsfaTRdu9bKs6G', 'user', 0, 'default-profile.jpg', 'Bachelor of Economics (Honours)', 'October 2024', 'Myanmar', 'Female', 'School Of Business & Social Sciences', '2027', '2026-04-14 02:55:31', NULL, NULL),
(164, 'Fahima Naluzze ', '2004-12-10', '01126378278', 'fahima.naluzze@student.aiu.edu.my', '$2y$10$rfGCtBNy5yXDH7fjlO4PcOWlnZwnlHBu.49R5Y4lnw9/BbsjGdCSq', 'user', 0, 'default-profile.jpg', 'Bachelor in Early Childhood Education (Honours)', 'October 2024', 'Uganda', 'Female', 'School Of Education & Human Sciences', '2027', '2026-04-14 08:26:20', NULL, NULL),
(165, 'ALHAM AMEERUL AZHAR', '2004-10-19', '01113315304', 'alham.azhar@student.aiu.edu.my', '$2y$10$gaA4zb7CV5kxmZUiQ49gruVHpdsoRBBg2XZ4NkaiUIjfIms6KeU0O', 'user', 0, 'default-profile.jpg', 'Bachelor in Computer Science (Honours)', 'October 2025', 'Sri Lanka', 'Male', 'School Of Computing and Informatics', '2028', '2026-04-14 12:51:52', NULL, NULL),
(166, 'NUR ARIFAH BINTI MUHAMAD ZAHARI', '2002-12-21', '0178321485', 'nurarifah.zahari@student.aiu.edu.my', '$2y$10$J98lbVtQ6IcHJTGOeLC70.sbxPsmN4rDDkMDR6yOoUuohev7jHYdm', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'October 2024', 'Malaysia', 'Female', 'School Of Education & Human Sciences', '2027', '2026-04-15 01:01:15', NULL, NULL),
(167, 'SEBIRE HAKYAR', '2006-11-20', '01140606768', 'sebire.hakyar@student.aiu.edu.my', '$2y$10$9xpgMPs3P6Kn0Duc1sT8aOdv6pOUr8Fo0gHK3ZUdlB3/lt7vh7aN.', 'user', 0, 'default-profile.jpg', 'Bachelor in Computer Science (Honours)', 'March 2025', 'Türkiye', 'Female', 'School Of Computing and Informatics', '2028', '2026-04-15 02:57:06', NULL, NULL),
(168, 'Moilim Ahamada Aboud', '2006-09-26', '0137591044', 'moilim.aboud@student.aiu.edu.my', '$2y$10$TZDJ/SMeLzOal/0YzetR/us5DJeHMu.LbmavgVqnSee2iu1GCnaS6', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours)', 'October 2025', 'Comoros', 'Male', 'School Of Business & Social Sciences', '2029', '2026-04-15 03:14:13', NULL, NULL),
(169, 'Alamin Mahmud Laminu ', '2003-11-25', '+2347067207508', 'aiu25102040@student.aiu.edu.my', '$2y$10$x.wUQ521ZDdNYja/Wj.Cfu35kZ3B/V87nyyhJ4eselmgILgM8PoM.', 'user', 0, 'default-profile.jpg', 'Bachelor of Social Development (Honours)', 'October 2025', 'Nigeria ', 'Male', 'School Of Business & Social Sciences', '2028', '2026-04-15 13:49:05', NULL, NULL),
(170, 'Ruvimbo Ruth Masauso ', '2004-08-25', '01162008690', 'ruvimbo.masauso@student.aiu.edu.my', '$2y$10$vQK5FgFI2559KP8RALSZ8On947XolnZSqCtjCMcrBgSDWsdnRx8iy', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours)', 'October 2024', 'Zimbabwe ', 'Female', 'School Of Business & Social Sciences', '2027', '2026-04-15 15:41:26', NULL, NULL),
(171, 'Nur Alia Safiya Binti Rombly', '2004-10-18', '0133513778', 'nuralia.rombly@student.aiu.edu.my', '$2y$10$X5KQhfpahcoTimoXeCD97OMX9EW1BCgWFr1Ihaqs0POeFUJK66mle', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'October 2024', 'Malaysia', 'Female', 'School Of Education & Human Sciences', '2027', '2026-04-16 01:27:08', NULL, NULL),
(172, 'Abdirahman Dahir Hassan ', '2003-11-10', '01168777948', 'aiu25102027@student.aiu.edu.my', '$2y$10$YQWL0ZkKygOVtF6G2DP9.O/tPqLNUrGbUgVjN/dGEfjBuhaCM4oJC', 'user', 0, 'default-profile.jpg', 'Bachelor of Social Development (Honours)', 'March 2026', 'Somalia', 'Male', 'School Of Business & Social Sciences', '2029', '2026-04-16 02:17:37', NULL, NULL),
(173, 'NURSYAFIQA BINTI HAMID', '2002-02-04', '0198408305', 'nursyafiqa.hamid@student.aiu.edu.my', '$2y$10$cGki5X/JUzIH6BB1Fc1YteVZNHlwQ5euLoEAtPgK5IDE8NA0.BVqq', 'user', 0, 'profile_69e075c80e8475.34395512.jpeg', 'Bachelor of Business Administration (Honours) (Human Resource Management)', 'October 2024', 'Malaysia', 'Female', 'School Of Business & Social Sciences', '2027', '2026-04-16 05:35:08', NULL, NULL),
(174, 'Nurhaniza Binti Ahmad', '2005-06-01', '+6013-5162952', 'nurhaniza.ahmad@student.aiu.edu.my', '$2y$10$QBfSKh.i5ksiYmOVQgyqkOg520LnCVLr15vZZ1B05Xn/L3VwzCSPm', 'user', 0, 'default-profile.jpg', 'Bachelor in Computer Science (Honours)', 'October 2024', 'Malaysia', 'Female', 'School Of Computing and Informatics', '2027', '2026-04-16 06:48:02', NULL, NULL),
(175, 'Zohal', '2003-09-20', '+601164139664', 'AIU25102370@student.aiu.edu.my', '$2y$10$XNgi5GLsO966CTTvZ09KHuzJCKSLG7YlwF0ecT0JSrJbKyihaUl2y', 'user', 0, 'default-profile.jpg', 'Bachelor in Data Science (Honours)', 'March 2026', 'Afghanistan', 'Female', 'School Of Computing and Informatics', '2029', '2026-04-16 12:38:50', NULL, NULL),
(176, 'Fatimatou Diallo', '2004-10-08', '01161434816', 'fatimatou.diallo@student.aiu.edu.my', '$2y$10$xZQlabX.eQNXOpxA13eXBOHj/UkHfb.t8T0lF.2SCI001/kU.7Zdu', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours)', 'October 2023', 'Guinea', 'Female', 'School Of Business & Social Sciences', '2027', '2026-04-16 15:38:31', NULL, NULL),
(177, 'Lutfi Hakim Bin Roz’aidie', '2003-10-23', '0183676741', 'aiu25102067@student.aiu.edu.my', '$2y$10$sdSZZLTHSj1.BYK13a9Izuf59T5UTAXiI8qUIMeBrZxTm4NNgK1g6', 'user', 0, 'profile_69e103db170b93.88245505.jpeg', 'Bachelor of Business Administration with Computer Science (Honours)', 'October 2025', 'Malaysia', 'Male', 'School Of Business & Social Sciences', '2028', '2026-04-16 15:40:24', NULL, NULL),
(178, 'Maralbyek Tilyek', '2005-06-18', '01114014221', 'maralbyek.tilyek@student.aiu.edu.my', '$2y$10$t4vvddbH/umHSQaKUN/5kuUQguvo8JTEvR1tAyvsWjEr7vmWcLYKS', 'user', 0, 'default-profile.jpg', 'Bachelor in Computer Science (Honours)', 'October 2024', 'Mongolia', 'Male', 'School Of Computing and Informatics', '2027', '2026-04-18 01:58:39', NULL, NULL),
(179, 'Ali Sina Rahimi', '2005-10-16', '+60 11-1330 4526', 'aiu25102044@student.aiu.edu.my', '$2y$10$FA6xiMQfoHcanQwd3ymfPetWFDYvD7LOigXfzbkIhvKfQLzwj1KF6', 'user', 0, 'default-profile.jpg', 'Bachelor of Media and Communication (Honours)', 'October 2025', 'Afghanistan', 'Male', 'School Of Education & Human Sciences', '2028', '2026-04-18 02:14:58', NULL, NULL),
(180, 'Nasuuna Eve', '2004-06-10', '01153423504', 'AIU25102488@student.aiu.edu.my', '$2y$10$UdPxK/sy5z7kJtH90Hj4LOkpf92mj5xygSE9eEZOGPWsNK.QpEH2G', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'March 2026', 'Uganda', 'Female', 'School Of Education & Human Sciences', '2029', '2026-04-18 02:17:46', NULL, NULL),
(181, 'Imman Nurbalqish Nabilah Binti Suhaimi', '2005-06-06', '0104290498', 'imman.suhaimi@student.aiu.edu.my', '$2y$10$16KHxayxDsgLRO3X/LdqhutDQ3rfNfyI8xJrxQcaFRSEg6SMlCP56', 'user', 0, 'default-profile.jpg', 'Bachelor in Computer Science (Honours)', 'October 2024', 'Malaysia', 'Female', 'School Of Computing and Informatics', '2027', '2026-04-18 02:27:13', NULL, NULL),
(182, 'NUR AFINI NADIHA BT SHAHIZAM', '2005-11-22', '0136521249', 'nurafini.shahizam@student.aiu.edu.my', '$2y$10$gP7w2biTZDqy4h1Xcw/q..4yYATJ.mMCKFy63bKC3UER1NU.tytoC', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'October 2024', 'Malaysia', 'Female', 'School Of Education & Human Sciences', '2027', '2026-04-18 02:28:37', NULL, NULL),
(183, 'Kayanja Lhaihan ', '2005-09-10', '01165628705', 'aiu25102403@student.aiu.edu.my', '$2y$10$HkSjb/YZBKTITeCr0/rpx.0gPi6Jl5xa97WI2OAxI69tLKnTmiocy', 'user', 0, 'default-profile.jpg', 'Bachelor in Early Childhood Education (Honours)', 'March 2026', 'Uganda', 'Male', 'School Of Education & Human Sciences', '2029', '2026-04-18 02:29:04', NULL, NULL),
(184, 'Ayuba malami', '2000-01-01', '01121354267', 'ayuba.malami@student.aiu.edu.my', '$2y$10$KxxWL63rlJIMMYd84yICN.F/DZ1EgF2UoUOHf0cTdAStYgQ8JtLGK', 'user', 0, 'profile_69e2eef131ba05.13593480.jpeg', 'Bachelor in Early Childhood Education (Honours)', 'October 2024', 'Nigeria', 'Male', 'School Of Education & Human Sciences', '2027', '2026-04-18 02:32:09', NULL, NULL),
(185, 'Muhammad sani haruna ', '2005-10-17', '1168232631', 'aiu25102345@student.aiu.edu.my', '$2y$10$RoiQ/v4Y0bZtd2Hhifkv5OUClvbGP0BnPcvAo4e4Yc8pb9AfRb7qi', 'user', 0, 'default-profile.jpg', 'Bachelor of Media and Communication (Honours)', 'October 2025', 'Nigeria', 'Male', 'School Of Education & Human Sciences', '2026', '2026-04-18 02:57:15', NULL, NULL),
(186, 'Muhammad Abubakar Musa', '2006-04-06', '0196853948', 'AIU25102627@student.aiu.edu.my', '$2y$10$KpYzEMx5LASr1fDxBUAHCOMJsC6M5zVaTOHvNAHjmZew8DN0qa/vy', 'user', 0, 'default-profile.jpg', 'Bachelor of Media and Communication (Honours)', 'March 2026', 'Nigeria', 'Male', 'School Of Education & Human Sciences', '2029', '2026-04-18 03:00:01', NULL, NULL),
(187, 'Huzaifa Mohammedsalih', '2005-12-16', '0199249800', 'aiu25102608@student.aiu.edu.my', '$2y$10$kSrdW99LEy7jB/4xQDO51eXaGZhRsUrNk8an3RD6S7owY8SL10hBa', 'user', 0, 'default-profile.jpg', 'Bachelor in Computer Science (Honours)', 'March 2026', 'Ethiopia', 'Male', 'School Of Computing and Informatics', '2029', '2026-04-18 03:39:50', NULL, NULL),
(188, 'Abdalle Yusuf Warsame', '2001-12-04', '+252 3563089', 'abdalle.warsame@student.aiu.edu.my', '$2y$10$h0Og0Yl1gTMOjlmx9Wu9JeqlHaQ495hS/MB5tW5voQw/XjEPoQkUy', 'user', 0, 'default-profile.jpg', 'Bachelor of Media and Communication (Honours)', 'October 2025', 'Somalia', 'Female', 'School Of Education & Human Sciences', '2028', '2026-04-18 03:45:09', NULL, NULL),
(189, 'Saran Kaba', '2006-10-31', '01128117843', 'Saran.kaba@student.aiu.edu.my', '$2y$10$PJx7fDocdlCssg1Rv06UfOnwRyFw5QbUdQP2p2brTU.bJCNzudJpS', 'user', 0, 'default-profile.jpg', 'Bachelor of Social Development (Honours)', 'October 2026', 'Guinea ', 'Female', 'School Of Business & Social Sciences', '2028', '2026-04-18 04:15:55', NULL, NULL),
(190, 'Abdul Karim Bangura', '2005-10-02', '+60132536440', 'aiu25102558@student.aiu.edu.my', '$2y$10$8bolomK4juURcV01EKlVcup9X/Crw4TMEBFTR0A3gj4LAvlm1TzFa', 'user', 0, 'default-profile.jpg', 'Bachelor in Computer Science (Honours)', 'March 2026', 'Sierra Leone', 'Male', 'School Of Computing and Informatics', '2029', '2026-04-18 04:19:22', NULL, NULL),
(191, 'Modou Lamin Sanno', '2005-09-27', '01127465362', 'aiu25102025@student.aiu.edu.my', '$2y$10$cf97OWnUM5JvFhsPGPhudemkgvBWySBTcdlgdTkCfK9Ypp9HIiZ5u', 'user', 0, 'default-profile.jpg', 'Bachelor in Computer Science (Honours)', 'October 2025', 'Gambia', 'Male', 'School Of Computing and Informatics', '2028', '2026-04-18 05:21:28', NULL, NULL),
(192, 'Siti Saidatulsyah binti Ab Wahab ', '2007-01-01', '01139203272', 'aiu25102185@student.aiu.edu.my', '$2y$10$Vcdf1SdgUBWl9/VxY9uplO45AZXSKlAYNXLgCqyBhyzrnTbBq2aQ6', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'October 2025', 'Malaysia ', 'Female', 'School Of Education & Human Sciences', '2028', '2026-04-18 06:28:09', NULL, NULL),
(193, 'Aya Tarig Salaheldein Bashir', '2003-07-21', '01140740166', 'ayatarig.salaheldein@student.aiu.edu.my', '$2y$10$laEloTegPYgC.o8uwU8b9uTzb6NvOQi48DcY0VKCZZeAUYMR7oL72', 'user', 0, 'default-profile.jpg', 'Bachelor in Computer Science (Honours)', 'October 2024', 'Sudan', 'Female', 'School Of Computing and Informatics', '2027', '2026-04-18 06:40:23', NULL, NULL),
(194, 'Mohamed Khalid Mohamedahmed Mustafa', '2004-04-12', '0173917703', 'khalid.mustafa@student.aiu.edu.my', '$2y$10$SwAc2wegfhlXYvrOQ4eHq.bdOjW4d/tMB.CLI8UuVLpiONVaRKjpO', 'user', 0, 'default-profile.jpg', 'Bachelor in Computer Science (Honours)', 'October 2024', 'Sudan', 'Male', 'School Of Computing and Informatics', '2027', '2026-04-18 06:42:30', NULL, NULL),
(195, 'Asif Ahmed ', '2003-12-18', '01128667172', 'asif.ahmed@student.aiu.edu.my', '$2y$10$e7iAxVUy1kxg1wu6j47ZVO86rAhnCoHGlxb/2ynGsvN8qgWHB2qZW', 'user', 0, 'default-profile.jpg', 'Bachelor in Computer Science (Honours)', 'October 2024', 'Bangladesh', 'Male', 'School Of Computing and Informatics', '2027', '2026-04-18 06:43:58', NULL, NULL),
(196, 'Eshraga Adil Yousif Alsmani', '2005-05-10', '0136087162', 'Eshraga.alsmani@student.aiu.edu.my', '$2y$10$OGGJWdRiNrHFYASOFVzb9uuvz9WHY744Cl7f5tWsU5tU/A.lpYmYi', 'user', 0, 'default-profile.jpg', 'Bachelor in Computer Science (Honours)', 'March 2025', 'Malaysia', 'Female', 'School Of Computing and Informatics', '2028', '2026-04-18 06:44:29', NULL, NULL),
(197, 'NURUL RAHIMAH BINTI AHMAD SUBRI', '2005-08-23', '0163954389', 'rahimah.subri@student.aiu.edu.my', '$2y$10$0tjC4uOculy7bWFdTcLfrOOzLboV1/pD1YZudjKh7TIgyJt945VNS', 'user', 0, 'default-profile.jpg', 'Bachelor in Computer Science (Honours)', 'October 2024', 'Malaysia', 'Female', 'School Of Computing and Informatics', '2027', '2026-04-18 06:51:41', NULL, NULL),
(198, 'Ali Ahmad Mohammadi', '2005-03-05', '01170133519', 'aiu25102046@student.aiu.edu.my', '$2y$10$rO.QT7U.iZBpAAUKHdBSY.xS8ljLCJOutUi9YyFHqNl70.elu2tAW', 'user', 0, 'profile_69e32a701b71c9.78906794.jpeg', 'Bachelor of Media and Communication (Honours)', 'October 2025', 'Afghanistan', 'Male', 'School Of Education & Human Sciences', '2028', '2026-04-18 06:51:50', NULL, NULL),
(199, 'Friba Bahrami', '2002-06-15', '01162218185', 'aiu25102448@student.aiu.edu.my', '$2y$10$jpE21h5FS9h/WDE8oSgR.ODkwQcapLjekzZr79As1.evkSB5TOPp2', 'user', 0, 'default-profile.jpg', 'Bachelor of Economics (Honours)', 'March 2026', 'Afghanistan', 'Female', 'School Of Business & Social Sciences', '2029', '2026-04-18 06:59:46', NULL, NULL),
(200, 'Weam Ahmed', '2003-05-04', '0194679446', 'weam.mohamed@student.aiu.edu.my', '$2y$10$bxP6KYCW4k5NJ3JKhjR95.kWGWPy.wdY7df4imT5Ge5/Df8ZUF3h2', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours)', 'October 2024', 'Sudan', 'Female', 'School Of Business & Social Sciences', '2027', '2026-04-18 07:25:20', NULL, NULL),
(201, 'Behzod Hasanov', '2005-03-31', '1127617600', 'aiu25102248@student.aiu.edu.my', '$2y$10$0/CtSMRi3iC2vn.ZciE.N.x9xFUb3v5MOru0cOLUrawCRXVaIkdnK', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours) (Marketing)', 'October 2024', 'Tajikistan', 'Male', 'School Of Business & Social Sciences', '2028', '2026-04-18 15:00:19', NULL, NULL),
(202, 'Mubarak Mainasara', '2000-03-17', '011128117941', 'mubarak.mainasara@student.aiu.edu.my', '$2y$10$tyvwMWRpenBidQy.4DUtE.uAXtchB2WOlfyrQbK.nYpNAkRLEFoEe', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours) (Marketing)', 'October 2023', 'Nigeria', 'Male', 'School Of Business & Social Sciences', '2026', '2026-04-18 15:04:37', NULL, NULL),
(203, 'NUR DINI ALIAH IRDINA BINTI ZULKIFLI', '2005-06-16', '01133050081', 'aiu25102057@student.aiu.edu.my', '$2y$10$LsrNqNvktRtKAnDhcqj3TO9gyK9VKspmDcVCehnlOFqDwJwoabcm2', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'October 2025', 'Malaysia', 'Female', 'School Of Education & Human Sciences', '2028', '2026-04-19 12:51:43', NULL, NULL),
(204, 'NAJAA AYUNI BINTI ISHAK', '2005-09-27', '01117585791', 'AIU25102061@student.aiu.edu.my', '$2y$10$yQfjOfqU9e6IhhBfyfN/CeIG6noPDhcmodnDk.p4ymx/Rbo5wWsHm', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'October 2025', 'Malaysia', 'Female', 'School Of Education & Human Sciences', '2028', '2026-04-19 12:51:59', NULL, NULL),
(205, 'DANIAL AFIF HAFIZIN BIN MOHD MUSABAKOH ', '2004-03-16', '0178671899', 'AIU25102159@student.aiu.edu.my', '$2y$10$9cunx21Gn32BsVTHCiO6KuTmMvHLHYVx7CwuB1HJRKxoO/jZ23ca.', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'October 2025', 'MALAYSIA', 'Male', 'School Of Education & Human Sciences', '2028', '2026-04-19 13:11:10', NULL, NULL),
(206, 'MUHAMMAD RUSYDI BIN KAMARUDDIN', '2006-08-01', '01121022889', 'aiu25102166@student.aiu.edu.my', '$2y$10$AccLZXxDacr.C/tDJImAguz.hnMqCI2qP6oaQu1dpP8A2vpB8.3ie', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'October 2024', 'MALAYSIA', 'Male', 'School Of Education & Human Sciences', '2028', '2026-04-19 13:43:59', NULL, NULL),
(207, 'Hassaan Ssejjengo', '2003-06-06', '01115014071', 'aiu25102394@student.aiu.edu.my', '$2y$10$Y94VmZdfh.IFF85hojkV0eiQ7E/3dXlpEvEReIC72JN6IYXkJuCVS', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'October 2025', 'Uganda', 'Male', 'School Of Education & Human Sciences', '2028', '2026-04-19 23:32:43', NULL, NULL),
(208, 'Diva Yuninursasih', '2004-06-07', '0103041424', 'Diva.yuninursasih@student.aiu.edu.my', '$2y$10$nMWtjc550oz9P04zaltEpeWGM0dGnWowJr.Le2B5Hej6l9QeW0WFK', 'user', 0, 'default-profile.jpg', 'Bachelor of Politics and International Relations (Honours)', 'October 2024', 'Indonesia', 'Female', 'School Of Business & Social Sciences', '2027', '2026-04-20 11:04:37', NULL, NULL),
(209, 'Alva Riansyah ', '2003-02-03', '01156611716', 'alva.riansyah@student.aiu.edu.my', '$2y$10$yBKDbcm11vxSdn427FfRieXwJLAlirc.PXNvpprBLC8EnWsY3nqCq', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours)', 'October 2022', 'Indonesia', 'Male', 'School Of Business & Social Sciences', '2026', '2026-04-20 13:11:25', NULL, NULL),
(210, 'Abdulkarem Abubakar Ajogal ', '2003-12-22', '+2348135959589', 'ajogal.abubakar@student.aiu.edu.my', '$2y$10$ugbyF7ISDnNOt70tJs6sYuM3BJnCnlKOeegXXax6Ro.TwmxH7nX6S', 'user', 0, 'default-profile.jpg', 'Bachelor in Computer Science (Honours)', 'October 2024', 'Nigeria', 'Male', 'School Of Computing and Informatics', '2027', '2026-04-21 01:24:51', NULL, NULL),
(211, 'Anabe Benigay', '2004-03-28', '01167896279', 'anabe.benigay@student.aiu.edu.my', '$2y$10$JDMUPsu4kmy221mIJ/BZSe7pswoME5JFZ4QI3SD3THnhLVl34DWV2', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'October 2023', 'Philippines', 'Female', 'School Of Education & Human Sciences', '2027', '2026-04-21 01:37:18', NULL, NULL),
(212, 'afiena zhafriesha alawi binti abdul razzaq', '2006-06-25', '01158750376', 'aiu25102208@student.aiu.edu.my', '$2y$10$5gyt.IfJY4BlN4D1u6pU9e59u0cw5JsQL4P6RX0M0g8YLTDZPJlhy', 'user', 0, 'default-profile.jpg', 'Bachelor of Media and Communication (Honours)', 'October 2025', 'Malaysia', 'Female', 'School Of Education & Human Sciences', '2028', '2026-04-21 01:37:56', NULL, NULL),
(213, 'Hnin Shwe Yee Htun', '2002-06-29', '01114202774', 'shweyee.htun@student.aiu.edu.my', '$2y$10$eO0.OSdsQBk.X8QfO3N3/uU9JTPWICTx.8h8z7tVl5EdETkaxffUq', 'user', 0, 'default-profile.jpg', 'Bachelor in Data Science (Honours)', 'October 2023', 'Malaysia', 'Female', 'School Of Computing and Informatics', '2027', '2026-04-21 01:44:45', NULL, NULL),
(214, 'Khin Yadanar Phyo', '2001-12-11', '+601123379343', 'khinyadanar.phyo@student.aiu.edu.my', '$2y$10$A3crvpga51czFwS6vaYFauecc9xBaExQPu5y1z5ssxva5g/jsr7aq', 'user', 0, 'default-profile.jpg', 'Bachelor in Data Science (Honours)', 'October 2023', 'Myanmar', 'Female', 'School Of Computing and Informatics', '2027', '2026-04-21 01:45:38', NULL, NULL),
(215, 'Putri Intan Dewiyanti', '2004-03-12', '0108724437', 'intan.dewiyanti@student.aiu.edu.my', '$2y$10$kxYGgfZnmr7KYUrVBRnaZuZkJkdGlA6Evwj59t2V/osVzxJcRV6T.', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'October 2023', 'Indonesia', 'Female', 'School Of Education & Human Sciences', '2027', '2026-04-21 02:00:01', NULL, NULL),
(216, 'Abdikhaliq Mohamed Ibrahim', '2003-10-25', '0172120954', 'abdikhaliq.ibrahim@student.aiu.edu.my', '$2y$10$olyC8eBFndpcJ0s3bCCETuUCOqfeN68tiqX/ZFb.Q4AIZJ./7jbR.', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours)', 'October 2024', 'Somalia', 'Male', 'School Of Business & Social Sciences', '2027', '2026-04-21 02:12:56', NULL, NULL),
(217, 'Nur Khairunnisa Dayana Binti Mohd Affendy', '2005-03-05', '0164005732', 'khairunnisa.affendy@student.aiu.edu.my', '$2y$10$zy27bpJw0zXAwreWvjQrBeNvwYsKw4Q7uFg5GpedXb9DbYLNFc5BW', 'user', 0, 'default-profile.jpg', 'Bachelor of Politics and International Relations (Honours)', 'October 2024', 'Malaysia', 'Female', 'School Of Business & Social Sciences', '2027', '2026-04-21 02:21:55', NULL, NULL),
(218, 'Nay Paing Oo', '2002-03-23', '01124279421', 'naypaing.oo@student.aiu.edu.my', '$2y$10$vP72UeNVMDRJBHT08VDqcOsHHeR25tCR/mrbRLmxm4OO4eGm1Lu9G', 'user', 0, 'default-profile.jpg', 'Bachelor in Computer Science (Honours)', 'October 2023', 'Myanmar', 'Male', 'School Of Computing and Informatics', '2027', '2026-04-21 03:10:42', NULL, NULL),
(219, 'NUR UMAIRAH SHUHADAH BINTI MOHD NAZRI', '2006-12-28', '0193940260', 'aiu25102176@student.aiu.edu.my', '$2y$10$qrohBLhwKFgaYgiAbVTTl.qX2A5wfrl5XarfDJrjMdp7SL/puzZ3O', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'October 2024', 'Malaysia', 'Female', 'School Of Education & Human Sciences', '2028', '2026-04-21 03:37:28', NULL, NULL),
(220, 'Hammad Hassan', '2005-09-20', '01140735953', 'hammad.hassan@student.aiu.edu.my', '$2y$10$9BCVa6gAYfFBAQeaQkRk7e1UEbqPaItJRZcBikS1B5f6NvNaD/APi', 'user', 0, 'default-profile.jpg', 'Bachelor in Computer Science (Honours)', 'October 2024', 'Pakistan', 'Male', 'School Of Computing and Informatics', '2027', '2026-04-21 04:26:25', NULL, NULL),
(221, 'Mohamed Lamine Camara ', '2006-03-26', '01137248414', 'lamine.camara@student.aiu.edu.my', '$2y$10$G0yU2qx7.vTJXUb8GHDo0OBmcijSOyEGT143ZQUK/RtVduNHuXkRi', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours)', 'October 2025', 'Guinea', 'Male', 'School Of Business & Social Sciences', '2028', '2026-04-21 06:15:14', NULL, NULL),
(222, 'Lovely Amanda', '2003-10-25', '0103039940', 'lovely.amanda@student.aiu.edu.my', '$2y$10$05wKqoCMM2plgmd/WdvA4uATIJ12LWr1swqIGHLrer0iHlKsey1Hi', 'user', 0, 'default-profile.jpg', 'Bachelor of Media and Communication (Honours)', 'October 2023', 'Indonesia', 'Female', 'School Of Education & Human Sciences', '2027', '2026-04-21 06:47:34', NULL, NULL),
(223, 'Wareesha Khan', '2004-11-01', '+601139065140', 'wareesha.khan@student.aiu.edu.my', '$2y$10$xW7lDMfoaVpoykG8OpybaOliwEvLAwbWsxc75crLNlifjuNBiM4GO', 'user', 0, 'default-profile.jpg', 'Bachelor in Early Childhood Education (Honours)', 'March 2025', 'Pakistan', 'Female', 'School Of Education & Human Sciences', '2028', '2026-04-21 14:00:01', NULL, NULL),
(224, 'Nargis Ibrahimi', '2004-01-02', '0173481727', 'aiu25102410@student.aiu.edu.my', '$2y$10$6vyX7HguLGMiIyNTSUtBEeonxtMHxcnC0.C87Z9B2QTD.n6Kqft8y', 'user', 0, 'default-profile.jpg', 'Bachelor of Economics (Honours)', 'March 2026', 'Afghanistan', 'Female', 'School Of Business & Social Sciences', '2028', '2026-04-21 14:39:32', NULL, NULL),
(225, 'Ingyin May', '2003-02-26', '01164072389', 'ingyin.may@student.aiu.edu.my', '$2y$10$gYuEyQHEJWEj/hPnjb8ytu8ensvuWUkWduirWIXsMXmWRpr6Lu3pq', 'user', 0, 'profile_69e846f6c85a51.78322392.jpeg', 'Bachelor of Media and Communication (Honours)', 'October 2023', 'Myanmar', 'Female', 'School Of Education & Human Sciences', '2026', '2026-04-22 03:53:20', NULL, NULL),
(226, 'Muh Nur Qadri', '2003-08-22', '017 9359 790', 'muh.qadri@student.aiu.edu.my', '$2y$10$buEhRRD0xNM3v7.MBTaxa.pWcw25iqmVa7mQH47Dl1QrGXZz1UI2G', 'user', 0, 'profile_69e8472de0c8d9.86491357.jpeg', 'Bachelor of Media and Communication (Honours)', 'October 2023', 'Indonesia', 'Male', 'School Of Education & Human Sciences', '2026', '2026-04-22 03:53:37', NULL, NULL),
(227, 'Mina Wardah', '2003-06-26', '01156642915', 'mina.wardah@student.aiu.edu.my', '$2y$10$kuGKd9P2rA3lDNN1GEecje/C/1bT9BXyMGc5yAtrIIwM3h.eyEMcq', 'user', 0, 'profile_69e848728d5ca2.01363571.jpeg', 'Bachelor of Media and Communication (Honours)', 'October 2023', 'Indonesia', 'Male', 'School Of Education & Human Sciences', '2026', '2026-04-22 03:53:42', NULL, NULL),
(228, 'Fatima Abdulkarim Musa', '2006-06-15', '08137252208', 'aiu25102351@student.aiu.edu.my', '$2y$10$wVS2AHUJhc1Ld0mEmo8nQuNlVaWR8e9exl3GvUst8APlpt2283Zpa', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'October 2022', 'Nigeria', 'Female', 'School Of Education & Human Sciences', '2028', '2026-04-22 03:54:17', NULL, NULL);
INSERT INTO `users` (`id`, `name`, `date_of_birth`, `phone_number`, `email`, `password`, `role`, `eco_points`, `profile_pic`, `program_of_study`, `intake`, `country`, `gender`, `department`, `expected_graduation_year`, `created_at`, `reset_token`, `token_expiry`) VALUES
(229, 'Aida Nasuha Binti Mohd Sohkeri Hadafi ', '2004-09-11', '01136139235', 'aidanasuha.hadafi@student.aiu.edu.my', '$2y$10$FGklEInooM7TA6UYMZRcbuHc0kPL716yacb/K3JN3nKi85Oov9gSe', 'user', 0, 'default-profile.jpg', 'Bachelor in Early Childhood Education (Honours)', 'October 2024', 'Malaysia', 'Female', 'School Of Education & Human Sciences', '2027', '2026-04-22 03:54:46', NULL, NULL),
(230, 'Puteri Habibah binti Rosli', '2002-03-14', '0135502279', 'habibah.rosli@student.aiu.edu.my', '$2y$10$E7TC7lEWgRfiWTvm6XapdeG9kn46Sfv2Qgc.y5v5meq0f.vDrTJHW', 'user', 0, 'profile_69e848327f5770.47990142.jpeg', 'Bachelor of Media and Communication (Honours)', 'October 2023', 'Malaysia', 'Female', 'School Of Education & Human Sciences', '2026', '2026-04-22 03:59:57', NULL, NULL),
(231, 'Ummusalma Adam Othman', '2003-07-10', '0145558006', 'ummusalma.othman@student.aiu.edu.my', '$2y$10$9ybT2xxXMG6GSsOudMxBhOLSq9ghnbubaIQm4.neduxsbBwyic2oW', 'user', 0, 'default-profile.jpg', 'Bachelor of Media and Communication (Honours)', 'March 2024', 'Nigeria', 'Female', 'School Of Education & Human Sciences', '2027', '2026-04-22 04:00:05', NULL, NULL),
(232, 'Nur Alya Iman Binti Idris', '2005-03-04', '0179945311', 'aiu25102079@student.aiu.edu.my', '$2y$10$WA6ZMtDxWU9A2sj6qz0lSee1iUwBNVmzXlRh1RzEZgH0kxfF.ySSO', 'user', 0, 'default-profile.jpg', 'Bachelor in Early Childhood Education (Honours)', 'October 2025', 'Malaysia', 'Female', 'School Of Education & Human Sciences', '2028', '2026-04-22 04:03:11', NULL, NULL),
(233, 'Reka Tatian', '2002-05-06', '‪+60 11‑6123 9163‬', 'reka.tatian@student.aiu.edu.my', '$2y$10$oR2gFyVy454kfMCtW3frGuRi0m5UYp9sf/Lw7HHv91f39eyFRfk4C', 'user', 0, 'profile_69e849a28bcea7.67733264.jpeg', 'Bachelor of Elementary Education (Honours)', 'October 2023', 'Indonesia', 'Female', 'School Of Education & Human Sciences', '2026', '2026-04-22 04:06:19', NULL, NULL),
(234, 'ainin sofiya binti shaufi', '2004-08-16', '0175172341', 'aiu25102076@student.aiu.edu.my', '$2y$10$6owF3BHZaWmB6ObSkY9uduIFsV7NKoXtujVQTUThZmnKxtlTvu0NC', 'user', 0, 'default-profile.jpg', 'Bachelor in Early Childhood Education (Honours)', 'October 2025', 'Malaysia', 'Female', 'School Of Education & Human Sciences', '2028', '2026-04-22 04:07:16', NULL, NULL),
(235, 'NURUL AINA BINTI AZHAR', '2006-02-02', '0172130764', 'aiu25102178@student.aiu.edu.my', '$2y$10$DGeYpSslmSbBCIHhftha0uau33Ju3ikRm5RQSHwn6ZyaBtqP9ela6', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'October 2025', 'MALAYSIA', 'Female', 'School Of Education & Human Sciences', '2028', '2026-04-22 04:08:15', NULL, NULL),
(236, 'Aulia Zahratul Aini', '2004-05-08', '0103039636', 'zahratul.aini@student.aiu.edu.my', '$2y$10$5Cg6RZ.qyynKM7vcDVuqi.IqbtF8iNeWT5JbpF1DrD6KxrGYKVJuy', 'user', 0, 'default-profile.jpg', 'Bachelor in Early Childhood Education (Honours)', 'October 2023', 'Indonesia', 'Female', 'School Of Education & Human Sciences', '2027', '2026-04-22 04:08:43', NULL, NULL),
(237, 'ATHIRAH SYAHIRAH BINTI JOHARI', '2004-07-22', '0174009371', 'aiu25102062@student.aiu.edu.my', '$2y$10$tDX6FkSdTMFNDthNOIdy7uhqqZnyukvdPMtqJRFHYPXw8xBxgG/eq', 'user', 0, 'default-profile.jpg', 'Bachelor in Early Childhood Education (Honours)', 'October 2025', 'Malaysia', 'Female', 'School Of Education & Human Sciences', '2028', '2026-04-22 04:08:49', NULL, NULL),
(238, 'AFRINA BATRISYA BINTI AZMAN FIRDAUS', '2005-01-24', '01126482995', 'aiu25102070@student.aiu.edu.my', '$2y$10$7XLVaeFsE6e3rYIKH92wkOkVwFddysP0OiC5EoRTkehcInrALerC2', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'October 2025', 'Malaysia', 'Female', 'School Of Education & Human Sciences', '2028', '2026-04-22 04:09:24', NULL, NULL),
(239, 'Nur Ain Mohd Zaidi', '2003-02-20', '0175005237', 'aiu25102072@student.aiu.edu.my', '$2y$10$sWCxuf3QPtShrf0xNgJnWOk1RVjm1GA9DqtXBn2Dlwxo9D0DP7y3K', 'user', 0, 'default-profile.jpg', 'Bachelor in Early Childhood Education (Honours)', 'October 2025', 'Malaysia', 'Female', 'School Of Education & Human Sciences', '2028', '2026-04-22 04:10:20', NULL, NULL),
(240, 'Nur Aimy Najwa binti Hisharuddin', '2005-07-03', '0193037837', 'aiu25102078@student.aiu.edu.my', '$2y$10$MUhFmqx2i8Msfd/okQ0lUuH22phy3z3t6QGYyWYFWawtjPYfhyEQG', 'user', 0, 'default-profile.jpg', 'Bachelor in Early Childhood Education (Honours)', 'October 2025', 'Malaysia', 'Female', 'School Of Education & Human Sciences', '2028', '2026-04-22 04:10:41', NULL, NULL),
(241, 'ADRIEANA BINTI AZMAN', '2003-09-09', '0174599093', 'aiu25102156@student.aiu.edu.my', '$2y$10$h7qxAMvC/r5VDpY2RTR0q.9K0uukGNXX6E.O67E07oTb2nWKGBMxO', 'user', 0, 'profile_69e84add9e9a17.66217827.jpeg', 'Bachelor of Media and Communication (Honours)', 'March 2026', 'Malaysia', 'Female', 'School Of Education & Human Sciences', '2029', '2026-04-22 04:11:45', NULL, NULL),
(242, 'NUR AQILAH BINTI MD SA’ID', '2006-07-29', '01157572362', 'aiu25102170@student.aiu.edu.my', '$2y$10$fEsg233H3y3iyuC/q.AaKOzTOiL.2rU10w6cch6XcM5AfoRF1YZnC', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'October 2025', 'MALAYSIA', 'Female', 'School Of Education & Human Sciences', '2028', '2026-04-22 04:12:24', NULL, NULL),
(243, 'NUR ATHIRAH SYAZWANA BINTI SABARIZAN ', '2004-04-10', '0195335307', 'aiu25102172@student.aiu.edu.my', '$2y$10$PjtxrjtKPqAabeGKiSk75uwW6Xc/ORrOwTS/yAk9mS7uSf77XhV/u', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'October 2025', 'Malaysia', 'Female', 'School Of Education & Human Sciences', '2028', '2026-04-22 04:12:51', NULL, NULL),
(244, 'NUR ATHIRAH BINTI AIZUAN ', '2006-11-28', '01111584795', 'aiu25102171@student.aiu.edu.my', '$2y$10$XzSpIjusedCMt5ettl6buebpztgjZxATKzc3B21DDS7DxhCGBFyZ6', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'October 2025', 'Malaysia', 'Female', 'School Of Education & Human Sciences', '2028', '2026-04-22 04:13:06', NULL, NULL),
(245, 'Nurul Hamimi Syafiqah binti Roshisham', '2006-05-05', '01162055023', 'aiu25102180@student.aiu.edu.my', '$2y$10$/UyrqvABBncgrB3Qehu.xewWF/ptLIOtu.wnvCVb7hUZK93uGhEoa', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'October 2026', 'Malaysia', 'Female', 'School Of Education & Human Sciences', '2028', '2026-04-22 04:13:10', NULL, NULL),
(246, 'Mohammad Atif', '2005-09-10', '+601111604732', 'aiu25102553@student.aiu.edu.my', '$2y$10$aIAdb31zqdaZr14xGe2d7Ojn8vx9CRHpnefHJx4oYDm8pC0S7xdvi', 'user', 0, 'profile_69e84bb9a2a979.91561673.jpeg', 'Bachelor in Early Childhood Education (Honours)', 'March 2026', 'Pakistan', 'Male', 'School Of Education & Human Sciences', '2026', '2026-04-22 04:13:48', NULL, NULL),
(247, 'SELEMANI ABDALLAH MKUMBA', '2006-03-20', '136183747', 'aiu25102495@student.aiu.edu.my', '$2y$10$twKnMJi6Ug3w/qtLyme9SOoV5/Fc5ssXll7dgJWYiEj8WRVwsHOwa', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'March 2026', 'Tanzania', 'Male', 'School Of Education & Human Sciences', '2029', '2026-04-22 04:14:27', NULL, NULL),
(248, 'SITI AISYAH BINTI ABDUL LATIFF', '2004-05-04', '01124226761', 'sitiaisyah.latiff@student.aiu.edu.my', '$2y$10$PjQBADMZRd5CBBI33oZ61Ovn5jjP1QI8nRI7/3w1xReXzX1Fn6/Ua', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'October 2024', 'MALAYSIAN', 'Female', 'School Of Education & Human Sciences', '2027', '2026-04-22 04:14:33', NULL, NULL),
(249, 'NUR AISYAH NAJWA BINTI K OTHMAN', '2003-05-07', '0194693025', 'aisyahnajwa.othman@student.aiu.edu.my', '$2y$10$G5ZMwVMKd62IhMW0pYdOSO6WPAshsEn2dEq58xs1v4JlIJO1zKpvO', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'October 2024', 'Malaysia', 'Female', 'School Of Education & Human Sciences', '2027', '2026-04-22 04:14:33', NULL, NULL),
(250, 'NUR ATIKAH NAJWA BINTI AZRIL SHAHRIN', '2006-06-22', '01124273162', 'aiu25102173@student.aiu.edu.my', '$2y$10$biOEdmFXgMAxvcqLbKBpS.NhAqT/P7uXpUV4rUtqIfRif9KN9p2ES', 'user', 0, 'default-profile.jpg', 'Bachelor of Media and Communication (Honours)', 'October 2025', 'Malaysia', 'Female', 'School Of Education & Human Sciences', '2028', '2026-04-22 04:15:04', NULL, NULL),
(251, 'Faezan Sokry', '2003-09-29', '0146458860', 'faezan.sokry@student.aiu.edu.my', '$2y$10$wf9Q3id79YpvNy95dzhtg.I.R4E0QZIDYu8ipGFjMonISsSrPVpuG', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'October 2024', 'Cambodia', 'Female', 'School Of Education & Human Sciences', '2027', '2026-04-22 04:17:48', NULL, NULL),
(252, 'Jage Terune Syaddad Wathoni', '2005-04-17', '087835510142', 'jageterune.wathoni@student.aiu.edu.my', '$2y$10$c6jnGGO9mcBfJ7D2bMyF3ujT19EqeM2PglDW9TFSzlVA1pUg5899e', 'user', 0, 'default-profile.jpg', 'Bachelor of Media and Communication (Honours)', 'October 2024', 'Indonesia', 'Male', 'School Of Education & Human Sciences', '2028', '2026-04-22 04:18:41', NULL, NULL),
(253, 'NUR ALIA MAISARH BINTI MAZLAN', '2004-08-07', '0132201165', 'aiu25102169@student.aiu.edu.my', '$2y$10$UHfW2HFrmp1QGdLA6Tv7weAyDo/aEH9HmmHjB1.FJxstOAEVjmuO2', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'October 2026', 'Malaysia', 'Female', 'School Of Education & Human Sciences', '2028', '2026-04-22 04:22:16', NULL, NULL),
(254, 'KHAIRUL FIRHAH BINTI MOHD ZAWAWI', '2004-10-22', '01115501022', 'AIU25102165@student.aiu.edu.my', '$2y$10$SCx/2BwZCUqQ5Ln0kVc3n.Nu96HzRn0.pue55MnU5rMMEM0.rM3wK', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'October 2025', 'Malaysia', 'Female', 'School Of Education & Human Sciences', '2028', '2026-04-22 04:22:21', NULL, NULL),
(255, 'NURSALMA DALILA BINTI MOHD FADZIL', '2004-02-29', '0168176599', 'aiu25102177@student.aiu.edu.my', '$2y$10$kpJf2OjWQgeTnc45i7ytteWC.wtAM6SAsjNV592BwuVTa1MHPpW2O', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'October 2025', 'Malaysia', 'Female', 'School Of Education & Human Sciences', '2028', '2026-04-22 04:49:14', NULL, NULL),
(256, 'Diaby Mahamadou Dit Bassaro', '2004-12-19', '01161434807', 'mahamadou.bassaro@student.aiu.edu.my', '$2y$10$MM0da4OW0Fn3QV1AlVIiZuOVmhE081KqDRSvU7oGcObYtgXE/fV/e', 'user', 0, 'default-profile.jpg', 'Bachelor of Social Development (Honours)', 'October 2025', 'Mali', 'Male', 'School Of Business & Social Sciences', '2028', '2026-04-22 06:33:35', NULL, NULL),
(257, 'Asy Syifa Sabrina', '2006-03-16', '+601113050854', 'aiu25102461@student.aiu.edu.my', '$2y$10$0Rgk1LdpaVvgws1Laeo2UO2ZgEbbdvzfVaYL1PY7Y0LSTMBZDgu2e', 'user', 0, 'default-profile.jpg', 'Bachelor of Politics and International Relations (Honours)', 'October 2026', 'Indonesia', 'Female', 'School Of Business & Social Sciences', '2029', '2026-04-22 07:01:59', NULL, NULL),
(258, 'Niken Dwinta Sari', '2005-04-26', '0193603476', 'aiu25102474@student.aiu.edu.my', '$2y$10$Z/Orw1Ts7jR/6/unePusr.11roS3.cqdFncXqO.X5YdW/X5PLtKmK', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours)', 'October 2026', 'Indonesia', 'Female', 'School Of Business & Social Sciences', '2029', '2026-04-22 07:06:10', NULL, NULL),
(259, 'Aman vishwas', '2006-06-09', '+60166307698', 'aiu25102601@student.aiu.edu.my', '$2y$10$aFHvj.Fl6r53NIvl9ww4Uu7dpgt8NBy.1plUaVKIpuH1ERiKYOge2', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration with Computer Science (Honours)', 'March 2026', 'Nepal', 'Male', 'School Of Business & Social Sciences', '2029', '2026-04-22 07:07:04', NULL, NULL),
(260, 'YOUSSOUF HISSEINE KOROM ', '2005-07-07', '175062981', 'aiu25102544@student.aiu.edu.my', '$2y$10$L6tLELYzoNswBScDo2/.I.7.jkiDD9turhZdHUGW3HTI2Fkt0Blau', 'user', 0, 'default-profile.jpg', 'Bachelor in Computer Science (Honours)', 'October 2026', 'Chad ', 'Male', 'School Of Computing and Informatics', '2029', '2026-04-22 07:07:09', NULL, NULL),
(261, 'Wancharip Waehayee', '2005-05-05', '01117511356', 'AIU25102326@student.aiu.edu.my', '$2y$10$F5RQv5OnQzKnGRf5mVzz3OOcKhRqnOgx24ZUgSpc9a73Yfik2YWhu', 'user', 0, 'default-profile.jpg', 'Bachelor in Computer Science (Honours)', 'October 2026', 'Thailand', 'Male', 'School Of Computing and Informatics', '2029', '2026-04-22 07:08:25', NULL, NULL),
(262, 'Diva salmayra gunawan', '2005-12-06', '01113326425', 'aiu25102353@student.aiu.edu.my', '$2y$10$3KVQHoa4nmQBftwJIsaBcuQ98cK0Lf3vkuONkIA0.VmF/PjAbD5P2', 'user', 0, 'default-profile.jpg', 'Bachelor of Media and Communication (Honours)', 'October 2026', 'Indonesia', 'Female', 'School Of Education & Human Sciences', '2029', '2026-04-22 07:08:40', NULL, NULL),
(263, 'Hamidy Ali', '2004-04-02', '0136469550', 'aiu25102085@student.aiu.edu.my', '$2y$10$DJe4YIOYcNE96CNRJSxYN.1Zkah345kUJ.2bb8O3qrEmIpkfQzCvG', 'user', 0, 'default-profile.jpg', 'Bachelor in Computer Science (Honours)', 'March 2026', 'Cambodia', 'Male', 'School Of Computing and Informatics', '2029', '2026-04-22 07:10:27', NULL, NULL),
(264, 'Maya Adelia sapitri ', '2007-05-20', '+60 11-1283 5569', 'AIU25102665@student.aiu.edu.my', '$2y$10$1S6j9GYxN.f2g5/FUjEAWep5evIvvO3exQ6MlklFlksIlH647vqjS', 'user', 0, 'default-profile.jpg', 'Bachelor of Media and Communication (Honours)', 'October 2026', 'Indonesia ', 'Female', 'School Of Education & Human Sciences', '2029', '2026-04-22 07:10:56', NULL, NULL),
(265, 'Haneefah lama', '2005-11-22', '0178227977', 'AIU25102253@student.aiu.edu.my', '$2y$10$dVVG9f5FWGzF9ryN0NLvde8V97.J9xkypTqd4ivAZf4eZRBpouL/.', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'October 2025', 'Thailand', 'Female', 'School Of Education & Human Sciences', '2029', '2026-04-22 07:11:05', NULL, NULL),
(266, 'Bushra Ayoub', '2006-01-01', '01121523760', 'aiu25102491@student.aiu.edu.my', '$2y$10$LMB.j6gIHn/ZPa5QF33dD.RdGN5ZscF.DjeMaKs8U34cyHKhZRx2K', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours) (Human Resource Management)', 'October 2026', 'Pakistan ', 'Female', 'School Of Business & Social Sciences', '2029', '2026-04-22 07:11:16', NULL, NULL),
(267, 'Rajib Kamat', '2007-02-23', '166307702', 'aiu25102602@student.aiu.edu.my', '$2y$10$PIFNLbx7v378ZYzuZDHedu/BBffHDslMqgOdvhnGt5LCXAThOpJjy', 'user', 0, 'profile_69e875d9310686.47698497.jpeg', 'Bachelor of Business Administration (Honours) (Marketing)', 'October 2025', 'Nepal', 'Male', 'School Of Business & Social Sciences', '2029', '2026-04-22 07:13:53', NULL, NULL),
(268, 'Almumtahanah Elis', '2005-08-23', '103624825', 'aiu25102360@student.aiu.edu.my', '$2y$10$eAKIuVvKdiug58G/BSwspO.Ly/NHB9nM8.4JFJ1D5S6MZtQjMh7IG', 'user', 0, 'default-profile.jpg', 'Bachelor of Media and Communication (Honours)', 'October 2026', 'Indonesia', 'Female', 'School Of Education & Human Sciences', '2029', '2026-04-22 07:16:19', NULL, NULL),
(269, 'FAJRIN ', '2006-07-04', '01120910923', 'aiu25102658@student.aiu.edu.my', '$2y$10$V5AWvsmAC9UuTEHZdbUPdOZE5uSLAdLqJLhPXH8ShqPJnOxa.xYim', 'user', 0, 'default-profile.jpg', 'Bachelor of Social Development (Honours)', 'October 2026', 'Indonesia ', 'Male', 'School Of Business & Social Sciences', '2029', '2026-04-22 07:17:19', NULL, NULL),
(270, 'Ashrof Tohming ', '2004-05-07', '01115138148', 'aiu25102521@student.aiu.edu.my', '$2y$10$Epq15iz0QatP5lHhJclPHuYd64cf9.2hXotI5.ZPBLO7ZFS7r4SEa', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours) (Marketing)', 'October 2026', 'Thailand', 'Male', 'School Of Business & Social Sciences', '2029', '2026-04-22 07:17:42', NULL, NULL),
(271, 'Affan masapha ', '2006-05-01', '0175947624', 'aiu25102416@student.aiu.edu.my', '$2y$10$l7d/pZiWmOC6JLoErhxo9OAIq8U2bVQanNvOCmuBtHps81RLwFc8q', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours) (Marketing)', 'October 2026', 'Thailand', 'Male', 'School Of Business & Social Sciences', '2026', '2026-04-22 07:18:36', NULL, NULL),
(272, 'Shazeli Suliman Mohamedibrahim Idris ', '2004-01-01', '+60146020574', 'aiu25102630@student.aiu.edu.my', '$2y$10$HxFqitm4ZSmK5JXQ1PgGnuVam515dQYJ88DzwQmMXDTpBWOAnOsUy', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours)', 'October 2026', 'Sudan', 'Male', 'School Of Business & Social Sciences', '2029', '2026-04-22 07:19:06', NULL, NULL),
(273, 'Jairil', '2007-01-16', '0179359731', 'aiu25102566@student.aiu.edu.my', '$2y$10$98JaXWntu6cqv4SmtnX9vuEw1bjIgXMVvsxPIdNQ1ew1AxFp3ZDIK', 'user', 0, 'default-profile.jpg', 'Bachelor of Social Development (Honours)', 'October 2025', 'Indonesia', 'Male', 'School Of Business & Social Sciences', '2029', '2026-04-22 07:29:16', NULL, NULL),
(274, 'Math Sameymey', '2005-04-05', '0134029201', 'aiu25102421@student.aiu.edu.my', '$2y$10$/w/07SjN2G3BH9mM9toAyunnDOW5CJxq5WzT7MwVZ9vfVw4jrrboS', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours)', 'October 2025', 'Cambodia', 'Female', 'School Of Business & Social Sciences', '2029', '2026-04-22 07:43:06', NULL, NULL),
(275, 'TOALY VOLAMASY NIRINA MELANIE ', '2005-05-15', '+60176140334', 'aiu25102412@student.aiu.edu.my', '$2y$10$u1TCcUHJOCPaFjAHTvzRLu7ToID51.b8hjmFrf4xA1.z0UzoGpQGS', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours)', 'October 2026', 'Madagascar', 'Female', 'School Of Business & Social Sciences', '2029', '2026-04-22 07:45:49', NULL, NULL),
(276, 'AVOJANAHARY Andrea', '2005-09-27', '+261349226563', 'aiu25102415@student.aiu.edu.my', '$2y$10$SuSYsrS9f5RRtmVq6e4pzui1oD6JI47.xgpnGmM7aWbvuR2eSw4Fq', 'user', 0, 'default-profile.jpg', 'Bachelor of Social Development (Honours)', 'October 2026', 'Madagascar', 'Female', 'School Of Business & Social Sciences', '2029', '2026-04-22 07:46:06', NULL, NULL),
(277, 'Nantenaina Lee Randrianirina', '2006-05-27', '01166094707', 'aiu25102299@student.aiu.edu.my', '$2y$10$44gKP2/ZAWZW9vaw5Qfpz.byOmm5kp/Un1IZXxJe1iN4b/VUQANNe', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours)', 'October 2026', 'Madagascar', 'Male', 'School Of Business & Social Sciences', '2029', '2026-04-22 07:47:23', NULL, NULL),
(278, 'Avotraniaina Stephie Johanah ', '2003-06-24', '01119471216', 'aiu25102300@student.aiu.edu.my', '$2y$10$7FFV1nowJsT.8r0jRLMjDuvHkJhLdWd8xPpZmZS1byZ5WqpgsToRC', 'user', 0, 'default-profile.jpg', 'Bachelor of Media and Communication (Honours)', 'October 2026', 'Madagascar', 'Female', 'School Of Education & Human Sciences', '2029', '2026-04-22 07:48:32', NULL, NULL),
(279, 'Nurul Azizah Rohmatus Sholihah ', '2005-06-14', '01167646592', 'AIU25102476@student.aiu.edu.my', '$2y$10$bBFX.eP4Zv1hj7FVKVAvP.YgxuwT1iq8wYfo0jZur7dIC0i.J50Fe', 'user', 0, 'default-profile.jpg', 'Bachelor of Media and Communication (Honours)', 'October 2025', 'Indonesia ', 'Female', 'School Of Education & Human Sciences', '2029', '2026-04-22 08:05:28', NULL, NULL),
(280, 'Ismadiana elisa', '2005-04-19', '01153248020', 'aiu25102066@student.aiu.edu.my', '$2y$10$91uAK/ceCQ0LDZ2UHM198eNp.MH0qMSJQ9SYQWO42SMzG5JaYjuaW', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'October 2025', 'Malaysia', 'Female', 'School Of Education & Human Sciences', '2028', '2026-04-22 11:50:20', NULL, NULL),
(281, 'Marina Santri', '2004-12-20', '01159557029', 'marina.santri@student.aiu.edu.my', '$2y$10$6UJw0RAlfP8FuWN0/Mi.Ru.HLPlr.QV/1Ll6OQLyic1yVgNhpBkpS', 'user', 0, 'default-profile.jpg', 'Bachelor of Media and Communication (Honours)', 'October 2023', 'Indonesia', 'Female', 'School Of Education & Human Sciences', '2027', '2026-04-22 13:57:41', NULL, NULL),
(282, 'Fatimoh Ayoola', '2003-05-21', '01119695018', 'fatimoh.ayoola@student.aiu.edu.my', '$2y$10$./bDyz2kFcgkXSHQEkW7auytyr/nAH8hg20zYHpL6G6/Ntyw0xxJ2', 'user', 0, 'default-profile.jpg', 'Bachelor of Politics and International Relations (Honours)', 'October 2024', 'Nigeria', 'Female', 'School Of Business & Social Sciences', '2027', '2026-04-22 14:40:40', NULL, NULL),
(283, 'MUHAMMAD MUKHRIZ DANIEL BIN MAHADZIR', '2005-11-14', '01151361613', 'daniel.mahadzir@student.aiu.edu.my', '$2y$10$uYUANh5JCir/en6J9WRV5ed36k4X7uATCIa3O7V27x3D7K4vO1Fmi', 'user', 0, 'default-profile.jpg', 'Bachelor of Politics and International Relations (Honours)', 'October 2024', 'MALAYSIA', 'Male', 'School Of Business & Social Sciences', '2027', '2026-04-23 04:42:55', NULL, NULL),
(284, 'Fathima Nafha Mohamed Nabeel ', '2005-04-04', '+60 13-641 8754', 'aiu25102533@student.aiu.edu.my', '$2y$10$xT6coWdZBeiEcOje5Dh14OUR2Q2wzhDxE/oKYDslGViPq.V2NxfY6', 'user', 0, 'default-profile.jpg', 'Bachelor in Early Childhood Education (Honours)', 'March 2026', 'Sri Lanka', 'Female', 'School Of Education & Human Sciences', '2029', '2026-04-23 06:02:06', NULL, NULL),
(285, 'Muhammad Danish bin Mohd Fazli ', '2005-04-05', '01158714342', 'aiu25102009@student.aiu.edu.my', '$2y$10$YuZU8oD27S9tHQzcVngtfueEf4mm2UzMt6xUXgk9x4q9Z9VZP2YVu', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'October 2025', 'Malaysia', 'Male', 'School Of Education & Human Sciences', '2028', '2026-04-23 08:00:59', NULL, NULL),
(286, 'NURUL INSYIRAH BT ZAID', '2005-04-20', '0175939526', 'aiu25102317@student.aiu.edu.my', '$2y$10$jGbeSKQO.n4qUV4jgrfwR.mpWNloNXydnU6pYX3euBCSAPb3I.aO2', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'October 2025', 'Malaysia', 'Female', 'School Of Education & Human Sciences', '2028', '2026-04-23 08:47:31', NULL, NULL),
(287, 'NUR AWATIF BINTI ROHIM ', '2005-09-24', '0135693418', 'AIU25102319@student.aiu.edu.my', '$2y$10$hVRnmkjMfgcVrSyMJlrQkecrR0zi9gFki30jsfBQPOqNQo9F4tQBy', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'October 2025', 'Malaysia', 'Female', 'School Of Education & Human Sciences', '2028', '2026-04-23 09:25:58', NULL, NULL),
(288, 'Zakiyah Hasanah', '2004-06-03', '01159594274', 'zakiyah.hasanah@student.aiu.edu.my', '$2y$10$CKEeeM00OjGXKfJqtdBc3OUtaW8V3MFHaj.CKNCbOUTvP8CCSMk3m', 'user', 0, 'default-profile.jpg', 'Bachelor of Economics (Honours)', 'October 2023', 'Malaysia', 'Female', 'School Of Business & Social Sciences', '2027', '2026-04-23 13:36:04', NULL, NULL),
(289, 'HANIS UZMA BINTI HALIM ', '2005-11-06', '0174029203', 'aiu25102006@student.aiu.edu.my', '$2y$10$w9vYUbvYVQnD.Naz88lD2eex3QlXNzVBTsgqyWMl/q/DSzFh/D1wi', 'user', 0, 'default-profile.jpg', 'Bachelor in Early Childhood Education (Honours)', 'October 2025', 'MALAYSIA ', 'Female', 'School Of Education & Human Sciences', '2028', '2026-04-24 04:07:10', NULL, NULL),
(290, 'Nur Alya Maisara Binti Gamal El Sorany', '2005-08-23', '0174040259', 'maisara.sorany@student.aiu.edu.my', '$2y$10$1JQRWRaGoAZUhXFLslL9ReJdm8V4nzVTaJsvBoxwX7WDUPKPQupdG', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'October 2024', 'Malaysia', 'Female', 'School Of Education & Human Sciences', '2027', '2026-04-24 13:46:06', NULL, NULL),
(291, 'Zobayer Mahmud', '2002-07-14', '01159592725', 'zobayer.zobayer@student.aiu.edu.my', '$2y$10$2cFDaKcD73sj0Tv9RtrC6uYVllAiHEdzxe733H3hJLSiFxymrMhB2', 'user', 0, 'default-profile.jpg', 'Bachelor in Early Childhood Education (Honours)', 'October 2024', 'Bangladesh ', 'Male', 'School Of Education & Human Sciences', '2027', '2026-04-25 05:17:23', NULL, NULL),
(292, 'Suwaiba Yayaji Yusuf ', '2004-09-04', '01167663582', 'aiu25102344@student.aiu.edu.my', '$2y$10$CsLnzOQvEdh53D9oNa6o4ehbsAPGlG7et7MYphedrQ15zuSlNPcUG', 'user', 0, 'default-profile.jpg', 'Bachelor in Early Childhood Education (Honours)', 'October 2025', 'Nigeria', 'Female', 'School Of Education & Human Sciences', '2028', '2026-04-25 06:11:40', NULL, NULL),
(293, 'KANIZ fATEMA SWORNA', '2003-04-11', '+60178466376', 'Kaniz.sworna@student.aiu.edu.my', '$2y$10$NyR2f.wz3NhubSpd2MOEs.8DJi7ZQjuwuIzC0CuEXqzWTaOKxbsPK', 'user', 0, 'profile_69ed97c29685e9.87322207.jpeg', 'Bachelor in Early Childhood Education (Honours)', 'October 2022', 'Bangladesh', 'Female', 'School Of Education & Human Sciences', '2028', '2026-04-25 06:14:19', NULL, NULL),
(294, 'Riadah', '2005-05-22', '01159583002', 'riadah.riadah@student.aiu.edu.my', '$2y$10$YCuz53xnQd/hIEc0ObD80.LcHHiJlvRssiEo9OWtmQ0oOMl1mFSI6', 'user', 0, 'default-profile.jpg', 'Bachelor in Early Childhood Education (Honours)', 'March 2024', 'Indonesia', 'Female', 'School Of Education & Human Sciences', '2028', '2026-04-25 06:16:38', NULL, NULL),
(295, 'MUHAMMAD DANIAL BIN MOHD RADZEE', '2005-11-16', '0179262495', 'danial.radzee@student.aiu.edu.my', '$2y$10$hGlrBVdrls2GiA.R6fu0ueLouIPYGG/ASyt6czkseTS3qnbDMGV2y', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'October 2024', 'Malaysia', 'Male', 'School Of Education & Human Sciences', '2027', '2026-04-25 08:53:08', NULL, NULL),
(296, 'AHMED MUFIDAT ', '2003-09-30', '01168223254', 'aiu25102339@student.aiu.edu.my', '$2y$10$ydPsNL45NSBKotePJEUT8eDnbTl4ttZ.A5wu6yZWvht5Q/gnT3eWa', 'user', 0, 'default-profile.jpg', 'Bachelor of Social Development (Honours)', 'October 2025', 'Nigeria', 'Female', 'School Of Business & Social Sciences', '2028', '2026-04-26 05:34:17', NULL, NULL),
(297, 'nur eiyra maisarah bt mohd reduwan', '2006-02-23', '01113228487', 'aiu25102188@student.aiu.edu.my', '$2y$10$dSTHV8zzUMlrokRa8F854.0cYQKFVj6reVoF6J8PUQFKp5.9M3pc2', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'March 2025', 'Malaysia', 'Female', 'School Of Education & Human Sciences', '2028', '2026-04-26 14:22:42', NULL, NULL),
(298, 'Muhammad Nor Haifan Bin Hamjah', '2022-05-16', '01170193509', 'hafizan.hamjah@student.aiu.edu.my', '$2y$10$Y8dGuyhsHM7SNQHp6Z3gI.si/E9yeTJ/VkENX4Ug7bMEyoY/6mCsW', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'October 2024', 'Malaysia', 'Male', 'School Of Education & Human Sciences', '2027', '2026-04-26 14:29:37', NULL, NULL),
(299, 'NASRA ALI', '2004-08-29', '+60 147675012', 'aiu25102595@student.aiu.edu.my', '$2y$10$hB9v3xeEy1D.8KunAzE3mea3pcoxOMayNwXGTLv5sUdr4i46TIN6e', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours) (Human Resource Management)', 'March 2026', 'UGANDA', 'Female', 'School Of Business & Social Sciences', '2029', '2026-04-27 00:25:49', NULL, NULL),
(300, 'widial hasani ', '2004-08-15', '01158746387', 'widial.hasani@student.aiu.edu.my', '$2y$10$jT5v0oSDCij8Od.a9KitHe.SNLIUvI4SeoVgqn0nqJqlvH/l9qfMm', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours)', 'October 2023', 'Indonesia', 'Female', 'School Of Business & Social Sciences', '2027', '2026-04-27 01:16:20', NULL, NULL),
(301, 'Nur Ilfi Aisah', '2002-12-02', '0103032735', 'nurilfi.aisah@student.aiu.edu.my', '$2y$10$s.zqofq6IlsrMxCai62H0.aEFJaL7H6hAA1yeO602wk0VnQYYEvJ6', 'user', 0, 'default-profile.jpg', 'Bachelor of Media and Communication (Honours)', 'October 2024', 'Indonesia', 'Female', 'School Of Education & Human Sciences', '2027', '2026-04-27 04:00:46', NULL, NULL),
(302, 'Ainembabazi Rossete', '2003-04-12', '01139067890', 'aiu25102579@student.aiu.edu.my', '$2y$10$DjLIcnW00erBnSIM0dI4Xe3hLFUWxjxaTQRuVQb5DiGcUMM4Nc2u2', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'October 2026', 'Uganda', 'Female', 'School Of Education & Human Sciences', '2029', '2026-04-27 04:15:10', NULL, NULL),
(303, 'Tengku Ruzana Zakirah binti Tengku Zainul Akmal', '2004-03-25', '0182165172', 'ruzana.zainul@student.aiu.edu.my', '$2y$10$EXK/CDrbxFOqbeB5W.iwDesgA06ZdG1DATh4cFPnR4qpMJzK4ENFa', 'user', 0, 'default-profile.jpg', 'Bachelor of Media and Communication (Honours)', 'October 2023', 'Malaysia', 'Female', 'School Of Education & Human Sciences', '2026', '2026-04-27 04:23:18', NULL, NULL),
(304, 'NAJIHAH HANI BINTI MOHD REDZUAN ', '2002-03-25', '01127102961', 'najihah.redzuan@student.aiu.edu.my', '$2y$10$2yz1/xmFabhF8G5t5.qUDO0TBc/uZ8rbn/eQlbAVXjghWlXHAMagq', 'user', 0, 'default-profile.jpg', 'Bachelor in Early Childhood Education (Honours)', 'October 2023', 'Malaysia', 'Female', 'School Of Education & Human Sciences', '2026', '2026-04-27 05:24:39', NULL, NULL),
(305, 'Thassnee Roh', '2004-03-26', '01160741421', 'thassnee.roh@student.aiu.edu.my', '$2y$10$D2GOF8iQmijhYOlRviwBO.QCuPM1eDQg0PeykSqFLxtZIkqWmr3X.', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'October 2024', 'Thailand', 'Female', 'School Of Education & Human Sciences', '2027', '2026-04-27 05:29:51', NULL, NULL),
(306, 'Simon Bangd', '2004-12-31', '+60 146985314', 'simon.bangs@student.aiu.edu.my', '$2y$10$RkYfLguZqsZNQsm9GHjfOuDxfeMFWHcpFkv.Ufk/8GOnhnmgGw94O', 'user', 0, 'default-profile.jpg', 'Bachelor of Media and Communication (Honours)', 'October 2024', 'Malaysia', 'Male', 'School Of Education & Human Sciences', '2027', '2026-04-27 07:18:48', NULL, NULL),
(307, 'Khandokar Sakibur Rahman ', '2000-10-15', '0132786631', 'khandokar.rahman@student.aiu.edu.my', '$2y$10$PDjn.Chtwo9y5IdxiAvJJODz0wrqVHlQb23d5OIHcqwrTNit/mLX6', 'user', 0, 'default-profile.jpg', 'Bachelor of Politics and International Relations (Honours)', 'October 2023', 'Bangladesh ', 'Male', 'School Of Business & Social Sciences', '2026', '2026-04-27 07:26:20', NULL, NULL),
(308, 'PUTERI IZZ AQILAH BINTI MEGAT HAMIDI', '2006-12-08', '0128126640', 'aiu25102190@student.aiu.edu.my', '$2y$10$fCKVXEJ0iKrNpLb.HBjM5uQD4U9X38k3n5stAAeI0mVxaLgG6LPnW', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'October 2025', 'Malaysia', 'Female', 'School Of Education & Human Sciences', '2028', '2026-04-27 07:28:32', NULL, NULL),
(309, 'Salmanu saidu Abubakar', '2003-06-29', '1133184746', 'salmanu.saidu@student.aiu.edu.my', '$2y$10$1EblvTJuE/vJO5.4LIsche4sD1OUA5Uw/IFcc0.GUigfl7P3fiCma', 'user', 0, 'default-profile.jpg', 'Bachelor of Media and Communication (Honours)', 'October 2024', 'Nigeria', 'Male', 'School Of Education & Human Sciences', '2027', '2026-04-27 07:29:53', NULL, NULL),
(310, 'Abdulsalam Aminu', '2001-02-25', '+2347060831116', 'abdulsalam.aminu@student.aiu.edu.my', '$2y$10$x858YFV.QpbsmQQUMwQOEOyc.Kx3s5TjFTxjyv7idO1YLuJMiTfQi', 'user', 0, 'default-profile.jpg', 'Bachelor of Media and Communication (Honours)', 'October 2023', 'Nigeria', 'Male', 'School Of Education & Human Sciences', '2026', '2026-04-27 08:59:06', NULL, NULL),
(311, 'Sri Fitriani', '2001-12-18', '0182990043', 'fitriani.sri@student.aiu.edu.my', '$2y$10$SRXthCrJHRopKk4yiJIFNuKmRde85sX5gjcVduq3KbbP3gaP45/k.', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours)', 'October 2023', 'indonesia', 'Female', 'School Of Business & Social Sciences', '2026', '2026-04-27 09:22:52', NULL, NULL),
(312, 'Iman Zahra Ahmad nawi', '2026-10-27', '01162155862', 'imanzahra.nawi@student.aiu.edu.my', '$2y$10$ycJg9loQZ4pS68vVaqBEnuPYDHpYUljYlJP5sIIxlr9E/OuXi5J.y', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours)', 'October 2023', 'Malaysia', 'Female', 'School Of Business & Social Sciences', '2026', '2026-04-27 09:22:56', NULL, NULL),
(313, 'Ummeeislah Mahama', '2001-06-03', '0172803115', 'ummeeislah.mahama@student.aiu.edu.my', '$2y$10$/1.p8Gz0ynENnJjHF023mOVv7UYxmK1NJRBbWnI.OQb2ltUMl1FHO', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours)', 'October 2022', 'Thailand', 'Female', 'School Of Business & Social Sciences', '2026', '2026-04-27 09:22:58', NULL, NULL),
(314, 'NURASYIQIN SYAMIMI BINTI MOHD NASIR', '2003-06-20', '0198605546', 'nurasyiqin.mohdnasir@student.aiu.edu.my', '$2y$10$Q0mxNPqSYm.cjUbc8Me1cePJlz15zWu1FUT1bBMkYnk6Mswfh7tB6', 'user', 0, 'default-profile.jpg', 'Bachelor of Finance (Islamic Finance) (Honours)', 'October 2024', 'Malaysia', 'Female', 'School Of Business & Social Sciences', '2027', '2026-04-27 09:40:49', NULL, NULL),
(316, 'NUR UMMAIRAH BINTI MUHAMAD AZAHAR', '2005-02-25', '0175982872', 'aiu25102150@student.aiu.edu.my', '$2y$10$7/rL8KTFDNoJunX.yJs/d.37p.f9nmnM/1.8pTlvwt2t37XHqbvAO', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'October 2025', 'Malaysia', 'Female', 'School Of Education & Human Sciences', '2028', '2026-04-27 11:53:57', NULL, NULL),
(317, 'NUR HAFIZA BINTI ROSLI', '2003-09-08', '0142710747', 'nurhafiza.rosli@student.aiu.edu.my', '$2y$10$VhikEjm8YFjnjQGJxQJniuweeV7Zhn3ndcZrfxBkvI5c0riYR5F1m', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'October 2023', 'MALAYSIA', 'Female', 'School Of Education & Human Sciences', '2026', '2026-04-27 12:28:04', NULL, NULL),
(318, 'Mila Fahilan', '2002-10-17', '01159563440', 'mila.fahilan@student.aiu.edu.my', '$2y$10$tXrjaFEbjfuTkjtGQHDhfegBj4Jeu4U8RDkxX00SOQSr5wIHMgPeC', 'user', 0, 'default-profile.jpg', 'Bachelor of Media and Communication (Honours)', 'October 2023', 'Indonesia', 'Female', 'School Of Education & Human Sciences', '2026', '2026-04-27 13:31:32', NULL, NULL),
(319, 'Muhammad Fazreen bin Rosli', '2002-12-10', '01128308059', 'fazreen.rosli@student.aiu.edu.my', '$2y$10$EviVhPejHFmRCEz2D6.y1uXX2Te5sJmw1jXN0eJgvWfdUOWoNzqy.', 'user', 0, 'default-profile.jpg', 'Bachelor of Media and Communication (Honours)', 'October 2023', 'Malaysia', 'Male', 'School Of Education & Human Sciences', '2026', '2026-04-27 14:42:57', NULL, NULL),
(320, 'Nurul Ajibah Auni Ahmadie', '2003-06-19', '0139782698', 'AIU25102077@student.aiu.edu.my', '$2y$10$cB99NKpHGSxex8iXaOih4uMBnqDXF4SCruY5QXwFTSRfD8ac8/qbS', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'October 2025', 'Malaysia', 'Female', 'School Of Education & Human Sciences', '2028', '2026-04-27 17:03:00', NULL, NULL),
(321, 'Maryam Alqadriyyah', '2006-10-19', '1112825902', 'aiu25102653@student.aiu.edu.my', '$2y$10$u7xsuIoJKRoyE9moBjetwO8n87GELQMwX3PQgxuNVvSxivWNR94J.', 'user', 0, 'default-profile.jpg', 'Language Center', 'October 2025', 'Indonesia', 'Female', 'Language Center (LC)', '2029', '2026-04-28 00:51:01', NULL, NULL),
(322, 'Ganesh Moorthi Prema', '2007-10-09', '01123874454', 'aiu25102625@student.aiu.edu.my', '$2y$10$fpLz5CAY1mbbrlCcGlwTRu4tYWaZFgAhxJJgQpmCebNJnT89VYIXC', 'user', 0, 'default-profile.jpg', 'Language Center', 'October 2026', 'India', 'Female', 'Language Center (LC)', '2029', '2026-04-28 00:52:11', NULL, NULL),
(323, 'MO HAM MACH SA PHI Y', '2006-04-28', '01121636815', 'aiu25102297@student.aiu.edu.my', '$2y$10$WX/AuJnZZdB6ISacSSFcDeq6x1VGfGev4ai6sDuDe.juX2jxdkKhi', 'user', 0, 'default-profile.jpg', 'Language Center', 'October 2025', 'Vietnam', 'Male', 'Language Center (LC)', '2029', '2026-04-28 00:52:50', NULL, NULL),
(324, 'Hamidzan Pranajanala Bhamakerti', '2006-03-29', '0103174135', 'aiu25102467@student.aiu.edu.my', '$2y$10$TaBeXQ8tffdZYoKlUhQhCeYE7OsOUG44ubRI9ceWnM5ckIFXL8sh6', 'user', 0, 'default-profile.jpg', 'Language Center', 'October 2025', 'indonesia', 'Male', 'Language Center (LC)', '2029', '2026-04-28 00:53:05', NULL, NULL),
(325, 'Kashif Ayaz', '2005-12-15', '0601137246512', 'AIU25102093@student.aiu.edu.my', '$2y$10$X5If5Hr0zuiIhU28UxKvJOO9yR9hpsMhlY19BW0lUgQDJ5P0ZZQM.', 'user', 0, 'default-profile.jpg', 'Language Center', 'October 2026', 'Pakistan', 'Male', 'Language Center (LC)', '2029', '2026-04-28 00:53:45', NULL, NULL),
(326, 'Ben Halidi Abadjamil', '2004-05-09', '+601162219295', 'aiu25102616@student.aiu.edu.my', '$2y$10$.k39pkdT4RqS6cBvxUoXDe6wBhdT7OYwWiA9tl9g6S1XR1B5.TCM2', 'user', 0, 'default-profile.jpg', 'Language Center', 'October 2025', 'Comoros', 'Male', 'Language Center (LC)', '2029', '2026-04-28 00:53:54', NULL, NULL),
(327, 'ZURO AINUNNAJAH', '2007-03-19', '011-12835609', 'AIU25102647@student.aiu.edu.my', '$2y$10$EQhbepxPfdLIwa6iqAVueeSXN5vaQsaLpVJ3QsSfZzZ5th7OQemaW', 'user', 0, 'default-profile.jpg', 'Language Center', 'October 2025', 'INDONESIA', 'Female', 'Language Center (LC)', '2029', '2026-04-28 00:54:39', NULL, NULL),
(328, 'Md Yusha Bin Nur', '2006-03-01', '+601170125742', 'aiu25102100@student.aiu.edu.my', '$2y$10$8Pze/lEQz6VxrhLUShZgveKZ2qz8im/FtjyzJT5EI8hQxkSrfNV3G', 'user', 0, 'default-profile.jpg', 'Language Center', 'October 2025', 'Bangladesh', 'Male', 'Language Center (LC)', '2029', '2026-04-28 00:54:49', NULL, NULL),
(329, 'yuxuan ma', '2006-07-01', '1139508272', 'aiu25102503@student.aiu.edu.my', '$2y$10$FxYd0PdIOOUKUcblJYkvG.WYEHknd7yqfxZkCcFF6TJypfbn0mg.C', 'user', 0, 'default-profile.jpg', 'Language Center', 'March 2026', 'China', 'Female', 'Language Center (LC)', '2029', '2026-04-28 00:54:53', NULL, NULL),
(330, 'wangxiqing', '2006-08-04', '175907243', 'AIU25102440@student.aiu.edu.my', '$2y$10$R1.49u4iG4yOWLaY1R484uXXgkgM4KsEjbv8wfQqv4/v3NeUowyMu', 'user', 0, 'default-profile.jpg', 'Language Center', 'October 2025', 'China', 'Male', 'Language Center (LC)', '2029', '2026-04-28 00:55:51', NULL, NULL),
(331, 'Khaidar Faris Hasimy', '2006-04-28', '01112826867', 'aiu25102664@student.aiu.edu.my', '$2y$10$1M/wtYR4cOq7wQ.SRS7k8evgxvb2ne.wqIoLA1xU2ukqGfKkSTHXW', 'user', 0, 'default-profile.jpg', 'Language Center', 'October 2026', 'Indonesia', 'Male', 'Language Center (LC)', '2029', '2026-04-28 00:57:54', NULL, NULL),
(332, 'RAKOTOHARIVONY DAMIE', '2004-04-13', '01119469827', 'aiu25102301@student.aiu.edu.my', '$2y$10$elXK7sp4d8WFGntximmCme0OP4RauaGh/xPnTGwFCR/Vyw4erkXji', 'user', 0, 'default-profile.jpg', 'Language Center', 'October 2020', 'Madagascar', 'Male', 'Language Center (LC)', '2029', '2026-04-28 01:00:13', NULL, NULL),
(333, 'OSAMA A A ALSOUSI', '2005-01-04', '0175929869', 'aiu25102573@student.aiu.edu.my', '$2y$10$U6LNuNnmCQJLiYMo33g02OHeM3kf2VOs0D59vbPO07MR/eGkQ4uNS', 'user', 0, 'default-profile.jpg', 'Language Center', 'October 2026', 'Palestine', 'Male', 'Language Center (LC)', '2029', '2026-04-28 01:00:33', NULL, NULL),
(334, 'Nadia Dorokee', '2004-05-27', '0133467831', 'aiu25102255@student.aiu.edu.my', '$2y$10$eeknGxV58FEMMhnNB6vxquaJtxGxDH5h7yqLajajlnMn3TDZlWCFK', 'user', 0, 'default-profile.jpg', 'Language Center', 'October 2026', 'Thailand', 'Female', 'Language Center (LC)', '2029', '2026-04-28 01:01:57', NULL, NULL),
(335, 'Zaskia Rizki Aditama', '2006-11-15', '0108640701', 'aiu25102650@student.aiu.edu.my', '$2y$10$25SLioO6l/eMZJvpKfc5IOFtXnQVnkrschOpt.VeczYFgvKP1is32', 'user', 0, 'default-profile.jpg', 'Language Center', 'October 2025', 'Indonesia', 'Female', 'Language Center (LC)', '2029', '2026-04-28 01:06:11', NULL, NULL),
(336, 'Zahra', '2003-09-13', '01127402506', 'AIU25102367@student.aiu.edu.my', '$2y$10$r.y1I4eBXZw.ue55w43nSObWzHN903ArDfOVu2nm6Kp1266FVOAyq', 'user', 0, 'default-profile.jpg', 'Language Center', 'October 2025', 'Afghanistan', 'Female', 'Language Center (LC)', '2029', '2026-04-28 01:06:12', NULL, NULL),
(337, 'WANMEENA BINDEN', '2006-03-13', '011 2740 2488', 'AIU25102324@student.aiu.edu.my', '$2y$10$0J7z2q3WKGY4FH1IIlhjDuBnIqzFtyz44mLznd5INkZzrJCkeU/eG', 'user', 0, 'default-profile.jpg', 'Language Center', 'October 2025', 'Thailand', 'Female', 'Language Center (LC)', '2029', '2026-04-28 01:08:30', NULL, NULL),
(338, 'Dwi Ambara Dzakiyarani', '2004-03-09', '0172793309', 'dwiambara.dzakiyarani@student.aiu.edu.my', '$2y$10$WTLSwxYEqKqq/m8P6pCbweoUDo1oVesVmEp8n5XYbUpIQSyCMc326', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'March 2025', 'Indonesia', 'Female', 'School Of Education & Human Sciences', '2028', '2026-04-28 01:09:21', NULL, NULL),
(339, 'Kanyawee Adam', '2005-06-09', '0194895788', 'aiu25102487@student.aiu.edu.my', '$2y$10$tpJ7ZkefWM9fMm5SJZsTHOqPOt7XFZN6bQ.BkcZ9VfdNaGlCWu8T2', 'user', 0, 'default-profile.jpg', 'Language Center', 'October 2026', 'Thailand', 'Female', 'Language Center (LC)', '2029', '2026-04-28 01:35:01', NULL, NULL),
(340, 'Shokrullah Pasoon', '2003-05-29', '1137968203', 'shokrullah.pasoon@student.aiu.edu.my', '$2y$10$g02GB2U7GdgaX41dhBBnkurEuKHIWJcvQ7ZZvlgLo5qM4CspfxUES', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours) (Marketing)', 'October 2024', 'Afghanistan', 'Male', 'School Of Business & Social Sciences', '2028', '2026-04-28 01:37:01', NULL, NULL),
(341, 'Muhammad Salisu Turai', '2005-10-16', '01168231982', 'aiu25102338@student.aiu.edu.my', '$2y$10$g1z9GqST8PKoDqA9Xd3S1..Fa9d1CFazD2vQkYSzf8YB.RxIR48gO', 'user', 0, 'default-profile.jpg', 'Bachelor in Computer Science (Honours)', 'October 2025', 'Nigeria', 'Male', 'School Of Computing and Informatics', '2028', '2026-04-28 01:38:35', NULL, NULL),
(342, 'Hikma Siraj Temam', '2005-05-10', '+601167814601', 'aiu25102676@student.aiu.edu.my', '$2y$10$wdF8wsoylKGKtqRDZFZ2rOjeQRlDg3JqFmZL.hlgZDZlADpey1eDK', 'user', 0, 'default-profile.jpg', 'Bachelor of Politics and International Relations (Honours)', 'October 2025', 'Ethiopia', 'Female', 'School Of Business & Social Sciences', '2028', '2026-04-28 01:42:41', NULL, NULL),
(343, 'SITI NOOR ALIEYA BINTI JAMAL', '2007-09-02', '0132647218', 'aiu25101036@student.aiu.edu.my', '$2y$10$Mn3.k8iqXZ1pEFAM5MQjOOqb3e3LoLY6tw2zB1yZFpQ3UPZpSQ4He', 'user', 0, 'default-profile.jpg', 'Foundation in Arts', 'October 2025', 'Malaysia', 'Female', 'Centre for Foundation and General Studies', '2029', '2026-04-28 01:52:17', NULL, NULL),
(344, 'Abdulselam Shemsu Bedir', '2005-04-10', '0194793692', 'aiu25102529@student.aiu.edu.my', '$2y$10$K1Gm8BItViaCBBIDGbMDvudWCmtV8PcqWL4c1GbOpVqSI4PaGYmzq', 'user', 0, 'default-profile.jpg', 'Language Center', 'October 2025', 'Ethiopia', 'Male', 'Language Center (LC)', '2029', '2026-04-28 02:06:44', NULL, NULL),
(345, 'Fatima Sakhizada', '2006-07-25', '1127402427', 'aiu25102381@student.aiu.edu.my', '$2y$10$Iub4YWcHT6MY03Mq0fdu8eQdg3frWmG2tvi9SpzKXAauPFnWgGXBK', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration with Computer Science (Honours)', 'October 2025', 'Afghanistan ', 'Female', 'School Of Business & Social Sciences', '2028', '2026-04-28 02:17:38', NULL, NULL),
(346, 'Muhim Abdilahi Jama', '2005-04-04', '01168331420', 'aiu25102086@student.aiu.edu.my', '$2y$10$Uw0xzpr20u.ZspYpr.fODe8U3dsAkiI1yYBY2A0QvT/khVGLCAVlC', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours) (Marketing)', 'March 2026', 'Malaysia', 'Female', 'School Of Business & Social Sciences', '2029', '2026-04-28 02:32:25', NULL, NULL),
(347, 'Abeddin Mosawi', '2006-01-03', '+601170124629', 'aiu25102048@student.aiu.edu.my', '$2y$10$5P5eoY8anV2WAAFZ39xLAu6yuB1zbHYBuc7zFl6yuA0xr9zFyUN2a', 'user', 0, 'default-profile.jpg', 'Language Center', 'March 2025', 'Afghanistan', 'Male', 'Language Center (LC)', '2029', '2026-04-28 02:56:08', NULL, NULL),
(348, 'Layla Abdirahman Ahmed', '2003-02-02', '0133715048', 'layla.abdirahmanahmed@student.aiu.edu.my', '$2y$10$tcgpo174M00IIE6KFQOH3eEs5tiH/qQ7BSqPhzAoAqw.Y//RXam1W', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours) (Marketing)', 'October 2023', 'Somali', 'Female', 'School Of Business & Social Sciences', '2027', '2026-04-28 02:56:59', NULL, NULL),
(349, 'ZHANG ZHIZHONG', '2007-05-09', '01127402493', 'aiu25102505@student.aiu.edu.my', '$2y$10$XtRPjdDVReXWJKOHTufo8.wkjWxmcXVaGjB0906UOYKa/4DQtzmg6', 'user', 0, 'default-profile.jpg', 'Language Center', 'October 2025', 'China', 'Male', 'Language Center (LC)', '2029', '2026-04-28 02:59:03', NULL, NULL),
(350, 'Mohammad Muzammil', '2003-11-28', '1137266017', 'aiu25102041@student.aiu.edu.my', '$2y$10$U9gPt2JZBtVJ0RrMLHXNfOs9Yw5iL3Ab1l/XdlOrJ1dSsfMPbO8Eq', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'October 2025', 'Afghanistan', 'Male', 'School Of Education & Human Sciences', '2029', '2026-04-28 02:59:56', NULL, NULL),
(351, 'Abdullah Mohammad Faisal', '2005-06-28', '60132344929', 'aiu25102540@student.aiu.edu.my', '$2y$10$9JY2ekW2fsm0GkDK0h5scu5e.sIVim90Vz9z/etdLEHsLA/6FC1uC', 'user', 0, 'default-profile.jpg', 'Bachelor of Media and Communication (Honours)', 'October 2026', 'Bangladesh', 'Male', 'School Of Education & Human Sciences', '2030', '2026-04-28 03:11:51', NULL, NULL),
(352, 'Olagunju Fathia Adenike', '2006-06-24', '+60 11 1766 1528', 'aiu25102578@student.aiu.edu.my', '$2y$10$52teUlOeT1WUHpbwR92.Uu6bCvrkykiQ1IAub2XoBU7ad6PhZTtbS', 'user', 0, 'default-profile.jpg', 'Bachelor in Computer Science (Honours)', 'March 2026', 'Nigeria', 'Female', 'School Of Computing and Informatics', '2029', '2026-04-28 03:16:09', NULL, NULL),
(353, 'TONGXIN', '2005-09-30', '60175932454', 'AIU25102430@student.aiu.edu.my', '$2y$10$lMrNpIaHhy2qB4O7BB46EuQJHE2VbZPyA24tSGV5MKbk.2l0EsyTa', 'user', 0, 'default-profile.jpg', 'Language Center', 'October 2025', 'China', 'Male', 'Language Center (LC)', '2029', '2026-04-28 03:43:00', NULL, NULL),
(354, 'Sarina', '2004-01-18', '01158509652', 'sarina.sarina@student.aiu.edu.my', '$2y$10$rTv7N0OzsKsZck02A.Izpem/ouWqlrXgJD53zinEANOAOhoF.KN0u', 'user', 0, 'default-profile.jpg', 'Bachelor of Media and Communication (Honours)', 'October 2025', 'Indonesia', 'Female', 'School Of Education & Human Sciences', '2028', '2026-04-28 04:11:37', NULL, NULL),
(355, 'Aiman Mustafa', '2004-01-14', '1159589591', 'aiman.mustafa@student.aiu.edu.my', '$2y$10$jHMdEgHjqOfYb7AOlToM1eKAXrInqtCsi1fGr2Ttptq6dQou6UcVu', 'user', 0, 'default-profile.jpg', 'Bachelor of Media and Communication (Honours)', 'October 2024', 'Indonesia', 'Male', 'School Of Education & Human Sciences', '2027', '2026-04-28 04:16:45', NULL, NULL),
(356, 'ZAKARIA ADAM ISHAG ABAKAR', '2000-01-01', '01163956508', 'zakaria.ishag@student.aiu.edu.my', '$2y$10$rRvvi.F56pwUamIoYHoQgOeeLqTEGdHD4W5guuVUpfjXHHfQviPBq', 'user', 0, 'default-profile.jpg', 'Bachelor in Computer Science (Honours)', 'October 2024', 'Sudan ', 'Male', 'School Of Computing and Informatics', '2027', '2026-04-28 04:17:58', NULL, NULL),
(357, 'parvej rafi', '2004-07-06', '0175089947', 'mdparvej.ahmedrafi@student.aiu.edu.my', '$2y$10$hTh42yXV3S0hNe8OmkaIdOBwBAYQegDLXOuBpKP9WgbtcRgIPeiQm', 'user', 0, 'default-profile.jpg', 'Bachelor in Computer Science (Honours)', 'October 2023', 'Bangladesh', 'Male', 'School Of Computing and Informatics', '2026', '2026-04-28 04:28:33', NULL, NULL),
(358, 'Mostafa Anwar', '2002-02-05', '0179591714', 'mostafa.anwar@student.aiu.edu.my', '$2y$10$LLTDFaU/oB3pWE6dnqJ.Cucj6Mqbx7I9oqqW5i2THhmrxyxl.LaGe', 'user', 0, 'default-profile.jpg', 'Bachelor in Computer Science (Honours)', 'October 2022', 'Bangladesh', 'Male', 'School Of Computing and Informatics', '2026', '2026-04-28 04:32:08', NULL, NULL),
(359, 'Nurul Atirah Binti Zulkifli', '2006-09-14', '0174525201', 'aiu25102059@student.aiu.edu.my', '$2y$10$OPJaoauOqWMwyubbzInJg.AXe.zmS0l8vDUlsgBYVQ3Fa9hvzmdrO', 'user', 0, 'default-profile.jpg', 'Bachelor in Computer Science (Honours)', 'October 2025', 'Malaysia ', 'Female', 'School Of Computing and Informatics', '2028', '2026-04-28 04:32:57', NULL, NULL),
(360, 'Mohamed Alhadi Ibrahim Alneel', '2004-01-01', '01137246893', 'aiu25102267@student.aiu.edu.my', '$2y$10$.xU9M1E5oF..OarbT1/f9Oh.P8r9HrHcIZG.3fy8AXkQeBEZpgXNm', 'user', 0, 'default-profile.jpg', 'Language Center', 'March 2025', 'Sudan', 'Male', 'Language Center (LC)', '2029', '2026-04-28 04:35:06', NULL, NULL),
(361, 'SITI NAZIRA BINTI AZIZAN', '2004-11-13', '0132354669', 'aiu25102181@student.aiu.edu.my', '$2y$10$UEfRM/c3yP5S/wlc8OUcTe0g6Ic9DfDINGvmSAypxCft0mIJUeIFy', 'user', 0, 'default-profile.jpg', 'Bachelor in Early Childhood Education (Honours)', 'October 2025', 'Malaysia', 'Female', 'School Of Education & Human Sciences', '2028', '2026-04-28 04:37:25', NULL, NULL),
(362, 'Ihsanuddin Fath', '2006-11-06', '0103159370', 'aiu25102468@student.aiu.edu.my', '$2y$10$1RDJme867.YB7XUfwAFqp.zhztQEphpYzi3vyl5nKXPZ1GF5FQvkm', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours)', 'March 2026', 'Indonesia', 'Male', 'School Of Business & Social Sciences', '2029', '2026-04-28 04:38:24', NULL, NULL),
(363, 'Samar osama abdalla osman', '2005-09-25', '01114725513', 'aiu25102479@student.aiu.edu.my', '$2y$10$6emfpK5zFmBWKlyaUYPoIuA8ZXSR/S8d3Jsf2BoxtkkftH6355uRG', 'user', 0, 'default-profile.jpg', 'Language Center', 'October 2025', 'Sudan', 'Female', 'Language Center (LC)', '2029', '2026-04-28 04:38:28', NULL, NULL),
(364, 'heheheheeeeeeeee', '2002-04-02', '01851489652', 'fawzia.rahim@student.aiu.edu.my', '$2y$10$/80HieJ/S.N5Ef2dBijZM.HzAdzFMXWfmxvzryl91Hqb4E1FB1B6S', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration with Computer Science (Honours)', 'March 2023', 'Afghanistan', 'Female', 'School Of Business & Social Sciences', '2029', '2026-04-28 04:41:55', NULL, NULL),
(365, 'Sara Mahmoud', '2006-06-24', '143419363', 'aiu25102417@student.aiu.edu.my', '$2y$10$2Lh6EalehMTB35lzpaH1wORohbiVYMOd8YYW6WS0DWVZhUDezNbw6', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours) (Human Resource Management)', 'March 2026', 'Syria', 'Female', 'School Of Business & Social Sciences', '2029', '2026-04-28 04:43:52', NULL, NULL),
(366, 'hehehehehehehehe khadijaaaaa', '2005-08-05', '0135542458', 'khadija.oubbih@student.aiu.edu.my', '$2y$10$IIDwCQ2do5tYjeEw0xz4Lubk9qENGxgEmR1sSYY82R55Yld5NWu0O', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours) (Human Resource Management)', 'March 2024', 'Morocco', 'Female', 'School Of Business & Social Sciences', '2027', '2026-04-28 04:48:34', NULL, NULL),
(367, 'Luh Nila Dhaniswara', '2007-05-08', '0172408957', 'aiu25102552@student.aiu.edu.my', '$2y$10$gQC1u5SU67O.RLU/zMhALO7e4i9n7wyFaGRJXKbLeytYZoYtEr4.e', 'user', 0, 'default-profile.jpg', 'Bachelor in Data Science (Honours)', 'March 2026', 'Indonesia', 'Female', 'School Of Computing and Informatics', '2029', '2026-04-28 04:50:18', NULL, NULL),
(368, 'Faryaan Khan Boodhoo', '2004-04-19', '01162982434', 'faryaan.boodhoo@student.aiu.edu.my', '$2y$10$JUTiiWwv1PGQ2mZkuXlLseZaHtkaFyTIUwD.D99BWY/TP2BhlffSS', 'user', 0, 'default-profile.jpg', 'Bachelor in Computer Science (Honours)', 'October 2024', 'Mauritius', 'Male', 'School Of Computing and Informatics', '2027', '2026-04-28 05:04:09', NULL, NULL),
(369, 'NURUL ALIA SAFIYYAH BINTI MOHD SUHAIRI', '2004-09-26', '01124120338', 'aiu25102179@student.aiu.edu.my', '$2y$10$gCZRM2X5iD22RRQjVXMxOuHWDBdlo1RDP9WizlTjiwD9B6KDGcIca', 'user', 0, 'default-profile.jpg', 'Bachelor in Early Childhood Education (Honours)', 'October 2025', 'Malaysia', 'Female', 'School Of Education & Human Sciences', '2028', '2026-04-28 05:17:38', NULL, NULL);
INSERT INTO `users` (`id`, `name`, `date_of_birth`, `phone_number`, `email`, `password`, `role`, `eco_points`, `profile_pic`, `program_of_study`, `intake`, `country`, `gender`, `department`, `expected_graduation_year`, `created_at`, `reset_token`, `token_expiry`) VALUES
(370, 'LOLA LOLA LOLAA', '2001-02-05', '0138765489', 'rola.salem@student.aiu.edu.my', '$2y$10$XqdrnQnvv0vYCisyRx2DLuyEb5VSiWjxNKJK7cdjb.4pjs9yusOD2', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours) (Human Resource Management)', 'March 2022', 'Palestine', 'Female', 'School Of Business & Social Sciences', '2038', '2026-04-28 05:27:13', NULL, NULL),
(371, 'Wanmisbah Yaya', '2007-04-05', '01162217328', 'aiu25102327@student.aiu.edu.my', '$2y$10$7k/dN5jcEQxamTBCxVLOHudwQj7BZDbHh1SUz4kaoSQi4mT2LVHya', 'user', 0, 'default-profile.jpg', 'Language Center', 'October 2025', 'Thailand', 'Female', 'Language Center (LC)', '2030', '2026-04-28 05:34:29', NULL, NULL),
(372, 'Tabita Veronica Mncube', '2006-02-10', '01164112480', 'aiu25102554@student.aiu.edu.my', '$2y$10$akqPcPLDcJiqfrYo/Jmtve0HP9qC7Z1WH..iqg/FL6JvyfdDz1Bki', 'user', 0, 'default-profile.jpg', 'Bachelor of Politics and International Relations (Honours)', 'March 2026', 'South Africa', 'Female', 'School Of Business & Social Sciences', '2030', '2026-04-28 05:38:07', NULL, NULL),
(373, 'AOUDA HOUSSEINI', '2007-03-05', '01165683091', 'aiu25102414@student.aiu.edu.my', '$2y$10$dRR2UljTJFp8AG2iCa0vXe/0/i1pfKVzAdz5aL2SDr5s3o0KDuesW', 'user', 0, 'default-profile.jpg', 'Bachelor of Economics (Honours)', 'March 2026', 'Mali', 'Female', 'School Of Business & Social Sciences', '2029', '2026-04-28 05:43:09', NULL, NULL),
(374, 'Khalid Naji Mohammed Naji Alqawsi', '2004-10-13', '+601128993200', 'khalid.alqawsi@student.aiu.edu.my', '$2y$10$.qEW//jlRS/a3cILw/bSt.oPcUx/q6FFPProVbIxm9QyLpNkK34/u', 'user', 0, 'default-profile.jpg', 'Bachelor in Computer Science (Honours)', 'October 2024', 'Yemen', 'Male', 'School Of Computing and Informatics', '2027', '2026-04-28 05:54:46', NULL, NULL),
(375, 'Usama Yousaf', '2004-04-07', '1117886218', 'usama.yousaf@student.aiu.edu.my', '$2y$10$a8LaZgaGv8S7cOqxZzGwmOpoqMO9r/YkBx75/2zkfisiUsAvSDdCi', 'user', 0, 'default-profile.jpg', 'Bachelor of Politics and International Relations (Honours)', 'October 2025', 'Pakistan', 'Male', 'School Of Business & Social Sciences', '2028', '2026-04-28 06:04:39', NULL, NULL),
(376, 'Benat Siraj Ahmed', '2002-06-07', '01172564030', 'benat.ahmed@student.aiu.edu.my', '$2y$10$ZKs3pm3g.CDY8IdT9AcrRO38ePxEGkt7ICuXMkNTVYAQL6g7Sa5Da', 'user', 0, 'default-profile.jpg', 'Bachelor in Computer Science (Honours)', 'March 2025', 'Ethiopia', 'Female', 'School Of Computing and Informatics', '2028', '2026-04-28 06:06:31', NULL, NULL),
(377, 'Amalia Hikmatul Hirosi', '2003-09-24', '01159554396', 'amalia.hirosi@student.aiu.edu.my', '$2y$10$jQ88pa5LjBkqolx90KVEh.UEjrkFARmfXsXxPyfUYvfvTcACxTlIG', 'user', 0, 'default-profile.jpg', 'Bachelor of Media and Communication (Honours)', 'March 2024', 'Indonesia', 'Female', 'School Of Education & Human Sciences', '2028', '2026-04-28 06:08:59', NULL, NULL),
(378, 'FARIA ISLAM FARJANA', '2007-02-01', '+60172792440', 'aiu25102104@student.aiu.edu.my', '$2y$10$4g12AB/lZdrl/17wulQ85OP1mpgAR6ZVB93kF8m7hzYctlHp7xya.', 'user', 0, 'default-profile.jpg', 'Bachelor in Computer Science (Honours)', 'March 2026', 'Bangladesh', 'Female', 'School Of Computing and Informatics', '2029', '2026-04-28 06:10:57', NULL, NULL),
(379, 'Aung Zay Yan Phyo', '2005-01-30', '01172665329', 'zayyan.phyo@student.aiu.edu.my', '$2y$10$L9JwFZ5bTe5EFPAMFn5BaegFrJ/5wzOFkEiVnd1UXQUfJEIo7a4FO', 'user', 0, 'default-profile.jpg', 'Bachelor in Computer Science (Honours)', 'October 2024', 'Myanmar', 'Male', 'School Of Computing and Informatics', '2027', '2026-04-28 06:15:47', NULL, NULL),
(380, 'Abdullah Faqih Bin Ahmed', '2003-01-26', '01157783197', 'faqih.ahmed@student.aiu.edu.my', '$2y$10$dWho0dkEo6h/pGYwj8QY8.//dCEGmIyo7JS5mMuSpuP7.uKRp0YWu', 'user', 0, 'default-profile.jpg', 'Bachelor of Media and Communication (Honours)', 'March 2025', 'Indonesia', 'Male', 'School Of Education & Human Sciences', '2028', '2026-04-28 06:17:27', NULL, NULL),
(381, 'Ibrahim Osman Sheikh Hussein', '2003-01-11', '01169791826', 'ibrahim.hussein@student.aiu.edu.my', '$2y$10$idJk1F0UO0rPZcroGJhLHe9sYuIMYYt2mpA0OgjPOVAtLFGDrNWOG', 'user', 0, 'default-profile.jpg', 'Bachelor in Computer Science (Honours)', 'October 2022', 'Somalia', 'Male', 'School Of Computing and Informatics', '2026', '2026-04-28 06:17:47', NULL, NULL),
(382, 'Afriliand candra', '2003-04-03', '0103046442', 'afriliand.dinata@student.aiu.edu.my', '$2y$10$K4CtX1/TY.GWxYJXPPg0bOELUVyWN97sCc7dpL5T5SNihlkP4ERz6', 'user', 0, 'default-profile.jpg', 'Bachelor of Media and Communication (Honours)', 'October 2024', 'Indonesia', 'Male', 'School Of Education & Human Sciences', '2027', '2026-04-28 06:23:54', NULL, NULL),
(383, 'Siti Arma Ismawanda Putri', '2001-07-19', '01159590643', 'arma.ismawanda@student.aiu.edu.my', '$2y$10$I.RKRSa/fXU7V/U.8et7c.y2jHmrRzIvmqPZ2h4gXndGj/sDj.FUC', 'user', 0, 'default-profile.jpg', 'Bachelor of Politics and International Relations (Honours)', 'October 2024', 'Indonesia', 'Female', 'School Of Business & Social Sciences', '2027', '2026-04-28 06:25:38', NULL, NULL),
(384, 'Hafizullah Mohammadi', '2001-10-20', '01169856484', 'hafizullah.mohammadi@student.aiu.edu.my', '$2y$10$STGMrPwpuP0diH14Ab5zBOamwx8FgsubMrtnyhs5J4W.I1MXG3ynm', 'user', 0, 'default-profile.jpg', 'Bachelor in Computer Science (Honours)', 'October 2022', 'Afghanistan', 'Male', 'School Of Computing and Informatics', '2026', '2026-04-28 06:37:35', NULL, NULL),
(385, 'Khadija Nasiru', '2000-04-14', '+2349061404607', 'khadija.nasiru@student.aiu.edu.my', '$2y$10$RnPLWjPBue4ofJizc/XTlefOBwczFD1.VEORjSxn/SwpcFcc2rhtC', 'user', 0, 'default-profile.jpg', 'Bachelor in Computer Science (Honours)', 'October 2023', 'Nigeria', 'Female', 'School Of Computing and Informatics', '2026', '2026-04-28 06:42:07', NULL, NULL),
(386, 'Calvin Mukwena', '2006-05-07', '1168168862', 'aiu25102314@student.aiu.edu.my', '$2y$10$wkn6.4ICPMW8UO0V77GjEOvgJv6ljPWrBb2wg0G04Ol/5uiBabh6e', 'user', 0, 'default-profile.jpg', 'Bachelor of Economics (Honours)', 'October 2025', 'Zimbabwe', 'Male', 'School Of Business & Social Sciences', '2028', '2026-04-28 06:42:48', NULL, NULL),
(387, 'Fanji Maulana Arrifkani', '2002-08-05', '+6283853318598', 'fanji.arrifkani@student.aiu.edu.my', '$2y$10$9b9k7cN8.uqdgcltE2hfGON0B7G6Er2QWMF7b340bfZucE4BA.heC', 'user', 0, 'default-profile.jpg', 'Bachelor of Politics and International Relations (Honours)', 'October 2024', 'Indonesia', 'Male', 'School Of Business & Social Sciences', '2027', '2026-04-28 06:45:12', NULL, NULL),
(388, 'Selman Karakas', '2005-03-15', '01140606550', 'selman.karakas@student.aiu.edu.my', '$2y$10$5Ep.5fXM6sj2VgFHlByHdux1lwfD1SX4liQoC44uXu2FTcaYe4boe', 'user', 0, 'default-profile.jpg', 'Bachelor of Media and Communication (Honours)', 'March 2025', 'Turkey', 'Male', 'School Of Education & Human Sciences', '2028', '2026-04-28 06:45:25', NULL, NULL),
(389, 'Umar Umar Gidado', '2002-08-30', '1128994055', 'umar.gidado@student.aiu.edu.my', '$2y$10$sbmaaBihx.o0inwdT7R8FecJP/G8AnJWZexU8VL7bcoExq4yCbPqy', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours)', 'October 2023', 'Nigeria', 'Male', 'School Of Business & Social Sciences', '2026', '2026-04-28 06:46:50', NULL, NULL),
(390, 'Rehana Nuru Mohammed', '2005-06-29', '+601137868412', 'aiu25102525@student.aiu.edu.my', '$2y$10$pAGEIXiayf7RYjiMTal8Q.E.RAyGB5Icc2PvqGuhmZ2StIcqhVDEu', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours) (Human Resource Management)', 'March 2026', 'Ethiopia', 'Female', 'School Of Business & Social Sciences', '2029', '2026-04-28 06:46:58', NULL, NULL),
(391, 'MABIN', '2003-12-21', '1166312219', 'AIU25102501@student.aiu.edu.my', '$2y$10$0A07lsYCBueKwlHeDO8vvOv5uNwuW/lG134xtY/PE6RC/Nq3Lz7W.', 'user', 0, 'default-profile.jpg', 'Language Center', 'October 2025', 'CHINA', 'Male', 'Language Center (LC)', '2029', '2026-04-28 06:49:43', NULL, NULL),
(392, 'MA NING', '2007-07-08', '01175670807', 'AIU25102499@student.aiu.edu.my', '$2y$10$Mj1NiMji4HyS44bXXE83d.PlAAcco5BL3CiykGBBGpdEc5gAym2c2', 'user', 0, 'default-profile.jpg', 'Language Center', 'October 2025', 'China', 'Male', 'Language Center (LC)', '2029', '2026-04-28 06:50:30', NULL, NULL),
(393, 'BAKOURA SAMARI KINGSLEY', '2004-12-02', '+60 1162583790', 'aiu25102508@student.aiu.edu.my', '$2y$10$qnkOTPFbHwxS/.gN59Fj4ORAE.VcDLZoj5hzoGcDrT6xEjY82zZwa', 'user', 0, 'profile_69f05a7cacd856.66579168.jpeg', 'Language Center', 'October 2025', 'Chad', 'Male', 'Language Center (LC)', '2029', '2026-04-28 06:52:47', NULL, NULL),
(394, 'Osama Mohammed Saeed Ahmed Alhasani', '2003-05-11', '0138608745', 'osama.hasani@student.aiu.edu.my', '$2y$10$28QiJccjWZaLxLTXjWBtZ.daYV6KqJ.FhmiR9ROHN8bP5t9tLW3py', 'user', 0, 'default-profile.jpg', 'Bachelor in Computer Science (Honours)', 'October 2023', 'Yemen', 'Male', 'School Of Computing and Informatics', '2026', '2026-04-28 06:59:20', NULL, NULL),
(395, 'Ali Abdulhamid Ambwa', '2005-10-01', '+60173319648', 'AIU25102115@student.aiu.edu.my', '$2y$10$HfA23IOfi3mjOl1plKCP2Or/sERehIDEs19b/8SyvXGjMSbN6oauC', 'user', 0, 'default-profile.jpg', 'Bachelor in Computer Science (Honours)', 'October 2025', 'Kenya', 'Male', 'School Of Computing and Informatics', '2028', '2026-04-28 07:02:07', NULL, NULL),
(396, 'Jafer Yenus Kemal', '2005-03-01', '01162219233', 'aiu25102528@student.aiu.edu.my', '$2y$10$uzTIU6nvdK53woubPGJe.urKp1MlPJLHn3asICQ0zQ8xvEtT6VPU2', 'user', 0, 'default-profile.jpg', 'Language Center', 'October 2025', 'Ethiopia', 'Male', 'Language Center (LC)', '2029', '2026-04-28 07:03:54', NULL, NULL),
(397, 'Abdalfatah S. A. Salhab', '2005-03-31', '0187761561', 'abdalfatah.salhab@student.aiu.edu.my', '$2y$10$WS0xHzoHBVZv5v1wk3IfS.2xmOQwX5q80ZBzQRrgTMGx6X.yw56oG', 'user', 0, 'default-profile.jpg', 'Bachelor in Computer Science (Honours)', 'October 2024', 'abdalfatah.salhab@student.aiu.edu.my', 'Male', 'School Of Computing and Informatics', '2027', '2026-04-28 07:05:39', NULL, NULL),
(398, 'Mohamed Omar Hassan', '2002-08-13', '0175801143', 'momar.hassan@student.aiu.edu.my', '$2y$10$7zGdNrQWZRd2LjUsl1ZIaOIaF1bCqnEx.KaVA4esed6IgjoaC7aKC', 'user', 0, 'default-profile.jpg', 'Bachelor in Computer Science (Honours)', 'October 2023', 'Somalia', 'Male', 'School Of Computing and Informatics', '2027', '2026-04-28 07:06:40', NULL, NULL),
(399, 'Dana Ammar Abukarsh', '2005-09-04', '1111638089', 'aiu25102099@student.aiu.edu.my', '$2y$10$IWioDKWbLFEEcjbdUf3cP.P1leZZevXgF54H79bvt6QHpW4dgpJx6', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours)', 'March 2026', 'Palestine', 'Female', 'School Of Business & Social Sciences', '2029', '2026-04-28 07:10:39', NULL, NULL),
(400, 'SITI NUR DINI BINTI MOHAMAD YUSOFF', '2006-07-18', '0198729554', 'aiu25102183@student.aiu.edu.my', '$2y$10$pb6Dr31cyy0kACZN6YOFu.gYPPJ7IHVOQYSZlvgihN29Oo.3lDmOK', 'user', 0, 'default-profile.jpg', 'Bachelor in Early Childhood Education (Honours)', 'October 2025', 'Malaysia', 'Female', 'School Of Education & Human Sciences', '2028', '2026-04-28 07:12:57', NULL, NULL),
(401, 'Seydouba Yabara Camara', '2005-10-18', '+60 1164140192', 'aiu25102486@student.aiu.edu.my', '$2y$10$RYPWePKxSKSXRaR6PYPePuCLBqDEX/7VxRKGKSgXDz4bTsKKit08G', 'user', 0, 'default-profile.jpg', 'Bachelor in Computer Science (Honours)', 'March 2026', 'aiu25102486@student.aiu.edu.my', 'Male', 'School Of Computing and Informatics', '2029', '2026-04-28 07:18:21', NULL, NULL),
(402, 'Sakina Skandari', '2003-11-30', '0103445518', 'aiu25102373@student.aiu.edu.my', '$2y$10$73EpLgJ/UmyyyIux07B8sO3d35Ea.ITFbxCsoCaPsdA4t560F8BjW', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours)', 'March 2026', 'Afghanistan', 'Female', 'School Of Business & Social Sciences', '2029', '2026-04-28 07:26:13', NULL, NULL),
(403, 'Sanan Kamal', '2006-03-30', '01128992930', 'sanan.kamal@student.aiu.edu.my', '$2y$10$BxyO5V2gVkdIrPfxlZu2fOJ3wkChvmNjYjVm.WScqdSBDOP87PAia', 'user', 0, 'profile_69f062cfef5129.57991410.jpeg', 'Bachelor of Business Administration (Honours)', 'October 2024', 'Pakistan', 'Male', 'School Of Business & Social Sciences', '2028', '2026-04-28 07:32:12', NULL, NULL),
(404, 'Aisha Abdulkadir yahaya', '2001-10-03', '+2347044313997', 'aisha.abdulkadir@student.aiu.edu.my', '$2y$10$OjMG6o8gF0HW1kLQuJ3u0.fWX/z9.u8KsXPm6UbpAhAzPQxC1ifKu', 'user', 0, 'default-profile.jpg', 'Bachelor in Computer Science (Honours)', 'March 2023', 'Nigeria', 'Female', 'School Of Computing and Informatics', '2027', '2026-04-28 07:38:33', NULL, NULL),
(405, 'Endah Nur Diana', '2006-04-06', '+6289654736525', 'endah.nurdiana@student.aiu.edu.my', '$2y$10$xGyxpUkeHnpSeiGDWh6Mz.83Cd4cD9X.HN9Y.K7nTL9qlKikQbFry', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours) (Marketing)', 'October 2025', 'Indonesia', 'Female', 'School Of Business & Social Sciences', '2028', '2026-04-28 07:44:05', NULL, NULL),
(406, 'NUR ALEESA IRDINA BINTI IRWAN', '2006-12-02', '017-465-2834', 'aiu25102168@student.aiu.edu.my', '$2y$10$u45t6hwxa4DdBQVHvCi4e.wOeBUiiBFwxzT1Et9IPCBH7/89b55wC', 'user', 0, 'default-profile.jpg', 'Bachelor in Early Childhood Education (Honours)', 'October 2025', 'Malaysian ', 'Female', 'School Of Education & Human Sciences', '2028', '2026-04-28 07:44:29', NULL, NULL),
(407, 'Fathima Rusda Mohammadu Nipasi', '2003-11-02', '01133002954', 'fathima.nipasi@student.aiu.edu.my', '$2y$10$mrQ.VkhlOUJfjov7sNj44OlHeFWxcD/kvnxsJijy5CynTEldYDPUi', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours)', 'March 2025', 'Sri Lanka', 'Female', 'School Of Business & Social Sciences', '2028', '2026-04-28 07:45:11', NULL, NULL),
(408, 'Yi Mon Thein', '2003-03-19', '01139270346', 'aiu25101095@student.aiu.edu.my', '$2y$10$Wnq1SdFnf4FTRArTLWi1buq8DTf1wpPHSTPsCzY.nVRypMC9yPdH6', 'user', 0, 'default-profile.jpg', 'Foundation in Computing', 'October 2025', 'Myanmar', 'Female', 'Centre for Foundation and General Studies', '2029', '2026-04-28 08:17:16', NULL, NULL),
(409, 'ATUHIRE BUSHIRAH', '2005-02-24', '01139066329', 'aiu25102581@student.aiu.edu.my', '$2y$10$Zz/U8b0pIibnoxBQknSPsOuh9aGiljG.pC37IQywadQv13phXYowS', 'user', 0, 'default-profile.jpg', 'Bachelor of Elementary Education (Honours)', 'March 2026', 'Uganda', 'Female', 'School Of Education & Human Sciences', '2029', '2026-04-28 08:19:07', NULL, NULL),
(410, 'Abdur Rahaman Fahim', '2004-02-14', '+601137251757', 'aiu25102030@student.aiu.edu.my', '$2y$10$9hO.m50Ibm4QpbsddGqMqOyxc3bZRrNab.6ZWA4x.6bGXjiYW9z16', 'user', 0, 'default-profile.jpg', 'Bachelor of Business Administration (Honours) (Human Resource Management)', 'October 2025', 'Bangladesh', 'Male', 'School Of Business & Social Sciences', '2028', '2026-04-28 08:20:15', NULL, NULL),
(411, 'Shohzodbek Omonboev', '2004-07-03', '+601170124564', 'Aiu25102250@student.aiu.edu.my', '$2y$10$Q3gypXWwNfYVFGAw/65om.Db1/pENoZLnXenHjRWCrQGlXJxEfQ4q', 'user', 0, 'default-profile.jpg', 'Bachelor of Finance (Islamic Finance) (Honours)', 'October 2025', 'Uzbekistan', 'Male', 'School Of Business & Social Sciences', '2028', '2026-04-28 08:23:34', NULL, NULL),
(412, 'Aishwarya Anumanthan', '2006-11-07', '0174075637', 'aishwarya.anumanthan@student.aiu.edu.my', '$2y$10$WYIRHMBBGiKmIiapyUBJf.DckN6J4MxAYY4ZyddLC7Yz2X5hU8j6O', 'user', 0, 'default-profile.jpg', 'Bachelor of Politics and International Relations (Honours)', 'October 2024', 'India ', 'Female', 'School Of Business & Social Sciences', '2028', '2026-04-28 08:24:06', NULL, NULL),
(413, 'Musa Abdulkodir Olayiwola', '2004-04-25', '+60 19-344 2934', 'aiu25102284@student.aiu.edu.my', '$2y$10$c/cXVz6FGcwgS2h6UaIDg.I6/N4J0SuQFiQ9NRtI3/pQJM00iWoTG', 'user', 0, 'default-profile.jpg', 'Bachelor in Computer Science (Honours)', 'October 2025', 'Nigeria', 'Male', 'School Of Computing and Informatics', '2028', '2026-04-28 08:27:57', NULL, NULL),
(414, 'Ruth Deva Malar A p Murugan', '2005-05-12', '010-2484936', 'aiu25102064@student.aiu.edu.my', '$2y$10$vpcQE15PzIDGbQ/9uwfbqOFwbtlhYmqPE3TuMb6rn0RbqKl.Zti8i', 'user', 0, 'default-profile.jpg', 'Bachelor of Social Development (Honours)', 'October 2025', 'Malaysia', 'Female', 'School Of Business & Social Sciences', '2028', '2026-04-28 08:29:26', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_activity`
--

CREATE TABLE `user_activity` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `activity_type` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_activity_log`
--
ALTER TABLE `admin_activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `admin_user_actions`
--
ALTER TABLE `admin_user_actions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_message` (`email`,`message`(100),`created_at`);

--
-- Indexes for table `leaderboard`
--
ALTER TABLE `leaderboard`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `news_events`
--
ALTER TABLE `news_events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `news_images`
--
ALTER TABLE `news_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `news_id` (`news_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `rewards`
--
ALTER TABLE `rewards`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reward_point_adjustments`
--
ALTER TABLE `reward_point_adjustments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `reward_redemption_history`
--
ALTER TABLE `reward_redemption_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `reward_id` (`reward_id`);

--
-- Indexes for table `submissions`
--
ALTER TABLE `submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `submission_feedback`
--
ALTER TABLE `submission_feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `submission_id` (`submission_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `submission_team_members`
--
ALTER TABLE `submission_team_members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `submission_id` (`submission_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `sustainability_scores`
--
ALTER TABLE `sustainability_scores`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transaction_logs`
--
ALTER TABLE `transaction_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_activity`
--
ALTER TABLE `user_activity`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_activity_log`
--
ALTER TABLE `admin_activity_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin_user_actions`
--
ALTER TABLE `admin_user_actions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=506;

--
-- AUTO_INCREMENT for table `leaderboard`
--
ALTER TABLE `leaderboard`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `news_events`
--
ALTER TABLE `news_events`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `news_images`
--
ALTER TABLE `news_images`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rewards`
--
ALTER TABLE `rewards`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reward_point_adjustments`
--
ALTER TABLE `reward_point_adjustments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reward_redemption_history`
--
ALTER TABLE `reward_redemption_history`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `submissions`
--
ALTER TABLE `submissions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=223;

--
-- AUTO_INCREMENT for table `submission_feedback`
--
ALTER TABLE `submission_feedback`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `submission_team_members`
--
ALTER TABLE `submission_team_members`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `sustainability_scores`
--
ALTER TABLE `sustainability_scores`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `transaction_logs`
--
ALTER TABLE `transaction_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=415;

--
-- AUTO_INCREMENT for table `user_activity`
--
ALTER TABLE `user_activity`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_activity_log`
--
ALTER TABLE `admin_activity_log`
  ADD CONSTRAINT `admin_activity_log_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `admin_user_actions`
--
ALTER TABLE `admin_user_actions`
  ADD CONSTRAINT `admin_user_actions_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `admin_user_actions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `leaderboard`
--
ALTER TABLE `leaderboard`
  ADD CONSTRAINT `leaderboard_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `news_images`
--
ALTER TABLE `news_images`
  ADD CONSTRAINT `news_images_ibfk_1` FOREIGN KEY (`news_id`) REFERENCES `news_events` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reward_point_adjustments`
--
ALTER TABLE `reward_point_adjustments`
  ADD CONSTRAINT `reward_point_adjustments_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reward_point_adjustments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reward_redemption_history`
--
ALTER TABLE `reward_redemption_history`
  ADD CONSTRAINT `reward_redemption_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reward_redemption_history_ibfk_2` FOREIGN KEY (`reward_id`) REFERENCES `rewards` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `submissions`
--
ALTER TABLE `submissions`
  ADD CONSTRAINT `submissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `submission_feedback`
--
ALTER TABLE `submission_feedback`
  ADD CONSTRAINT `submission_feedback_ibfk_1` FOREIGN KEY (`submission_id`) REFERENCES `submissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `submission_feedback_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `submission_team_members`
--
ALTER TABLE `submission_team_members`
  ADD CONSTRAINT `submission_team_members_ibfk_1` FOREIGN KEY (`submission_id`) REFERENCES `submissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `submission_team_members_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `transaction_logs`
--
ALTER TABLE `transaction_logs`
  ADD CONSTRAINT `transaction_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_activity`
--
ALTER TABLE `user_activity`
  ADD CONSTRAINT `user_activity_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
