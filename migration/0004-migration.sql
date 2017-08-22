-- phpMyAdmin SQL Dump
-- version 4.7.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jul 29, 2017 at 06:16 PM
-- Server version: 5.7.18
-- PHP Version: 5.6.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `OpalDB_Qi_Sandbox`
--

-- --------------------------------------------------------

--
-- Table structure for table `Questiongroup`
--
DROP TABLE IF EXISTS `Questiongroup`;
CREATE TABLE `Questiongroup` (
  `serNum` bigint(20) UNSIGNED NOT NULL,
  `name_EN` varchar(128) NOT NULL,
  `name_FR` varchar(128) NOT NULL,
  `category_EN` varchar(128) NOT NULL,
  `category_FR` varchar(128) NOT NULL,
  `private` tinyint(1) NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created` timestamp NOT NULL,
  `last_updated_by` int(11) UNSIGNED DEFAULT NULL,
  `created_by` int(11) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `Questiongroup`
--

INSERT INTO `Questiongroup` (`serNum`, `name_EN`, `name_FR`, `category_EN`, `category_FR`, `private`, `last_updated`, `created`, `last_updated_by`, `created_by`) VALUES
(1, 'Dry mouth', 'Dry mouth', 'Oral', 'Oral', 0, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(2, 'Difficulty swallowing', 'Difficulty swallowing', 'Oral', 'Oral', 0, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(3, 'Mouth/throat sores', 'Mouth/throat sores', 'Oral', 'Oral', 0, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(4, 'Cracking at the corners of the mouth (cheilosis/cheilitis)', 'Cracking at the corners of the mouth (cheilosis/cheilitis)', 'Oral', 'Oral', 0, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(5, 'Voice quality changes', 'Voice quality changes', 'Oral', 'Oral', 0, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(6, 'Hoarseness', 'Hoarseness', 'Oral', 'Oral', 0, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(7, 'Taste changes', 'Taste changes', 'Gastrointestinal', 'Gastrointestinal', 0, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(8, 'Decreased appetite', 'Decreased appetite', 'Gastrointestinal', 'Gastrointestinal', 0, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(9, 'Nausea', 'Nausea', 'Gastrointestinal', 'Gastrointestinal', 0, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(10, 'Vomiting', 'Vomiting', 'Gastrointestinal', 'Gastrointestinal', 0, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(11, 'Heartburn', 'Heartburn', 'Gastrointestinal', 'Gastrointestinal', 0, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(12, 'Gas', 'Gas', 'Gastrointestinal', 'Gastrointestinal', 0, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(13, 'Bloating', 'Bloating', 'Gastrointestinal', 'Gastrointestinal', 0, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(14, 'Hiccups', 'Hiccups', 'Gastrointestinal', 'Gastrointestinal', 0, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(15, 'Constipation', 'Constipation', 'Gastrointestinal', 'Gastrointestinal', 0, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(16, 'Diarrhea', 'Diarrhea', 'Gastrointestinal', 'Gastrointestinal', 0, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(17, 'Abdominal pain', 'Abdominal pain', 'Gastrointestinal', 'Gastrointestinal', 0, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(18, 'Fecal incontinence', 'Fecal incontinence', 'Gastrointestinal', 'Gastrointestinal', 0, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(19, 'Shortness of breath', 'Shortness of breath', 'Respiratory', 'Respiratory', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(20, 'Cough', 'Cough', 'Respiratory', 'Respiratory', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(21, 'Wheezing', 'Wheezing', 'Respiratory', 'Respiratory', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(22, 'Swelling', 'Swelling', 'Cardio/Circulatory', 'Cardio/Circulatory', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(23, 'Heart palpitations', 'Heart palpitations', 'Cardio/Circulatory', 'Cardio/Circulatory', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(24, 'Rash', 'Rash', 'Cutaneous', 'Cutaneous', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(25, 'Skin dryness', 'Skin dryness', 'Cutaneous', 'Cutaneous', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(26, 'Acne', 'Acne', 'Cutaneous', 'Cutaneous', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(27, 'Hair loss', 'Hair loss', 'Cutaneous', 'Cutaneous', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(28, 'Itching', 'Itching', 'Cutaneous', 'Cutaneous', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(29, 'Hives', 'Hives', 'Cutaneous', 'Cutaneous', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(30, 'Hand-foot syndrome', 'Hand-foot syndrome', 'Cutaneous', 'Cutaneous', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(31, 'Nail loss', 'Nail loss', 'Cutaneous', 'Cutaneous', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(32, 'Nail ridging', 'Nail ridging', 'Cutaneous', 'Cutaneous', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(33, 'Nail discoloration', 'Nail discoloration', 'Cutaneous', 'Cutaneous', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(34, 'Sensitivity to sunlight', 'Sensitivity to sunlight', 'Cutaneous', 'Cutaneous', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(35, 'Bed/pressure sores', 'Bed/pressure sores', 'Cutaneous', 'Cutaneous', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(36, 'Radiation skin reaction', 'Radiation skin reaction', 'Cutaneous', 'Cutaneous', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(37, 'Skin darkening', 'Skin darkening', 'Cutaneous', 'Cutaneous', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(38, 'Stretch marks', 'Stretch marks', 'Cutaneous', 'Cutaneous', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(39, 'Numbness and tingling', 'Numbness and tingling', 'Neurological', 'Neurological', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(40, 'Dizziness', 'Dizziness', 'Neurological', 'Neurological', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(41, 'Blurred vision', 'Blurred vision', 'Visual/Perception', 'Visual/Perception', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(42, 'Flashing lights', 'Flashing lights', 'Visual/Perception', 'Visual/Perception', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(43, 'Visual floaters', 'Visual floaters', 'Visual/Perception', 'Visual/Perception', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(44, 'Watery eyes', 'Watery eyes', 'Visual/Perception', 'Visual/Perception', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(45, 'Ringing in ears', 'Ringing in ears', 'Visual/Perception', 'Visual/Perception', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(46, 'Concentration', 'Concentration', 'Attention/Memory', 'Attention/Memory', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(47, 'Memory', 'Memory', 'Attention/Memory', 'Attention/Memory', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(48, 'General pain', 'General pain', 'Pain', 'Pain', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(49, 'Headache', 'Headache', 'Pain', 'Pain', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(50, 'Muscle pain', 'Muscle pain', 'Pain', 'Pain', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(51, 'Joint pain', 'Joint pain', 'Pain', 'Pain', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(52, 'Insomnia', 'Insomnia', 'Sleep/Wake', 'Sleep/Wake', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(53, 'Fatigue', 'Fatigue', 'Sleep/Wake', 'Sleep/Wake', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(54, 'Anxious', 'Anxious', 'Mood', 'Mood', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(55, 'Discouraged', 'Discouraged', 'Mood', 'Mood', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(56, 'Sad', 'Sad', 'Mood', 'Mood', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(57, 'Irregular periods/vaginal bleeding', 'Irregular periods/vaginal bleeding', 'Gynecologic/Urinary', 'Gynecologic/Urinary', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(58, 'Missed expected menstrual period', 'Missed expected menstrual period', 'Gynecologic/Urinary', 'Gynecologic/Urinary', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(59, 'Vaginal discharge', 'Vaginal discharge', 'Gynecologic/Urinary', 'Gynecologic/Urinary', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(60, 'Vaginal dryness', 'Vaginal dryness', 'Gynecologic/Urinary', 'Gynecologic/Urinary', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(61, 'Painful urination', 'Painful urination', 'Gynecologic/Urinary', 'Gynecologic/Urinary', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(62, 'Urinary urgency', 'Urinary urgency', 'Gynecologic/Urinary', 'Gynecologic/Urinary', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(63, 'Urinary frequency', 'Urinary frequency', 'Gynecologic/Urinary', 'Gynecologic/Urinary', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(64, 'Change in usual urine color', 'Change in usual urine color', 'Gynecologic/Urinary', 'Gynecologic/Urinary', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(65, 'Urinary incontinence', 'Urinary incontinence', 'Gynecologic/Urinary', 'Gynecologic/Urinary', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(66, 'Achieve and maintain erection', 'Achieve and maintain erection', 'Sexual', 'Sexual', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(67, 'Ejaculation', 'Ejaculation', 'Sexual', 'Sexual', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(68, 'Decreased libido', 'Decreased libido', 'Sexual', 'Sexual', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(69, 'Delayed orgasm', 'Delayed orgasm', 'Sexual', 'Sexual', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(70, 'Unable to have orgasm', 'Unable to have orgasm', 'Sexual', 'Sexual', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(71, 'Pain with sexual intercourse', 'Pain with sexual intercourse', 'Sexual', 'Sexual', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(72, 'Breast swelling and tenderness', 'Breast swelling and tenderness', 'Miscellaneous', 'Miscellaneous', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(73, 'Bruising', 'Bruising', 'Miscellaneous', 'Miscellaneous', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(74, 'Chills', 'Chills', 'Miscellaneous', 'Miscellaneous', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(75, 'Increased sweating', 'Increased sweating', 'Miscellaneous', 'Miscellaneous', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(76, 'Decreased sweating', 'Decreased sweating', 'Miscellaneous', 'Miscellaneous', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(77, 'Hot flashes', 'Hot flashes', 'Miscellaneous', 'Miscellaneous', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(78, 'Nosebleed', 'Nosebleed', 'Miscellaneous', 'Miscellaneous', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(79, 'Pain and swelling at injection site', 'Pain and swelling at injection site', 'Miscellaneous', 'Miscellaneous', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(80, 'Body odor', 'Body odor', 'Miscellaneous', 'Miscellaneous', 0, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(81, 'Leaking urine', 'Leaking urine', 'Urinary Function', 'Urinary Function', 0, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(82, 'Urinating blood', 'Urinating blood', 'Urinary Function', 'Urinary Function', 0, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(83, 'Pain or burning with urination', 'Pain or burning with urination', 'Urinary Function', 'Urinary Function', 0, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(84, 'Urinary control', 'Urinary control', 'Urinary Function', 'Urinary Function', 0, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(85, 'Pads or adult diapers', 'Pads or adult diapers', 'Urinary Function', 'Urinary Function', 0, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(86, 'Weak urine stream or incomplete emptying', 'Weak urine stream or incomplete emptying', 'Urinary Function', 'Urinary Function', 0, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(87, 'Waking up to urinate', 'Waking up to urinate', 'Urinary Function', 'Urinary Function', 0, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(88, 'Need to urinate frequently during the day', 'Need to urinate frequently during the day', 'Urinary Function', 'Urinary Function', 0, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(89, 'Urinary function', 'Urinary function', 'Urinary Function', 'Urinary Function', 0, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(90, 'Rectal urgency', 'Rectal urgency', 'Bowel Habits', 'Bowel Habits', 0, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(91, 'Uncontrolled leakage of stool or feces', 'Uncontrolled leakage of stool or feces', 'Bowel Habits', 'Bowel Habits', 0, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(92, 'Loose or liquid stools', 'Loose or liquid stools', 'Bowel Habits', 'Bowel Habits', 0, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(93, 'Bloody stools', 'Bloody stools', 'Bowel Habits', 'Bowel Habits', 0, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(94, 'Painful bowel movements', 'Painful bowel movements', 'Bowel Habits', 'Bowel Habits', 0, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(95, 'Number of bowel movements', 'Number of bowel movements', 'Bowel Habits', 'Bowel Habits', 0, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(96, 'Crampy pain in abdomen, pelvis or rectum', 'Crampy pain in abdomen, pelvis or rectum', 'Bowel Habits', 'Bowel Habits', 0, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(97, 'Urgency to have a bowel movement', 'Urgency to have a bowel movement', 'Bowel Habits', 'Bowel Habits', 0, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(98, 'Increased frequency of bowel movements', 'Increased frequency of bowel movements', 'Bowel Habits', 'Bowel Habits', 0, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(99, 'Watery bowel movements', 'Watery bowel movements', 'Bowel Habits', 'Bowel Habits', 0, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(100, 'Losing control of your stools', 'Losing control of your stools', 'Bowel Habits', 'Bowel Habits', 0, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(101, 'Bloody stools', 'Bloody stools', 'Bowel Habits', 'Bowel Habits', 0, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(102, 'Abdominal/Pelvic/Rectal pain', 'Abdominal/Pelvic/Rectal pain', 'Bowel Habits', 'Bowel Habits', 0, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(103, 'Bowel habits', 'Bowel habits', 'Bowel Habits', 'Bowel Habits', 0, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(104, 'Sexual desire', 'Sexual desire', 'Sexual Function', 'Sexual Function', 0, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(105, 'Ability to have an erection', 'Ability to have an erection', 'Sexual Function', 'Sexual Function', 0, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(106, 'Ability to reach orgasm', 'Ability to reach orgasm', 'Sexual Function', 'Sexual Function', 0, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(107, 'Quality of erections', 'Quality of erections', 'Sexual Function', 'Sexual Function', 0, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(108, 'Frequency of erections', 'Frequency of erections', 'Sexual Function', 'Sexual Function', 0, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(109, 'Awaken with erection', 'Awaken with erection', 'Sexual Function', 'Sexual Function', 0, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(110, 'Sexual activity frequency', 'Sexual activity frequency', 'Sexual Function', 'Sexual Function', 0, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(111, 'Sexual intercourse frequency', 'Sexual intercourse frequency', 'Sexual Function', 'Sexual Function', 0, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(112, 'Ability to function sexually', 'Ability to function sexually', 'Sexual Function', 'Sexual Function', 0, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(113, 'Sexual function', 'Sexual function', 'Sexual Function', 'Sexual Function', 0, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(114, 'Hot flashes', 'Hot flashes', 'Hormonal Function', 'Hormonal Function', 0, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(115, 'Breast tenderness', 'Breast tenderness', 'Hormonal Function', 'Hormonal Function', 0, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(116, 'Depression', 'Depression', 'Hormonal Function', 'Hormonal Function', 0, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(117, 'Lack of energy', 'Lack of energy', 'Hormonal Function', 'Hormonal Function', 0, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(118, 'Change in weight', 'Change in weight', 'Hormonal Function', 'Hormonal Function', 0, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(119, 'Loss of body hair', 'Loss of body hair', 'Hormonal Function', 'Hormonal Function', 0, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(120, 'Overall satisfaction', 'Overall satisfaction', 'Overall Satisfaction', 'Overall Satisfaction', 0, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(121, 'Pain', 'Pain', 'ESAS-r', 'ESAS-r', 0, '2017-07-13 14:16:20', '2017-07-13 14:16:20', NULL, NULL),
(122, 'Tiredness', 'Tiredness', 'ESAS-r', 'ESAS-r', 0, '2017-07-13 14:16:20', '2017-07-13 14:16:20', NULL, NULL),
(123, 'Drowsiness', 'Drowsiness', 'ESAS-r', 'ESAS-r', 0, '2017-07-13 14:16:20', '2017-07-13 14:16:20', NULL, NULL),
(124, 'Nausea', 'Nausea', 'ESAS-r', 'ESAS-r', 0, '2017-07-13 14:16:20', '2017-07-13 14:16:20', NULL, NULL),
(125, 'Lack of appetite', 'Lack of appetite', 'ESAS-r', 'ESAS-r', 0, '2017-07-13 14:16:20', '2017-07-13 14:16:20', NULL, NULL),
(126, 'Shortness of breath', 'Shortness of breath', 'ESAS-r', 'ESAS-r', 0, '2017-07-13 14:16:20', '2017-07-13 14:16:20', NULL, NULL),
(127, 'Depression', 'Depression', 'ESAS-r', 'ESAS-r', 0, '2017-07-13 14:16:20', '2017-07-13 14:16:20', NULL, NULL),
(128, 'Anxiety', 'Anxiety', 'ESAS-r', 'ESAS-r', 0, '2017-07-13 14:16:20', '2017-07-13 14:16:20', NULL, NULL),
(129, 'Wellbeing', 'Wellbeing', 'ESAS-r', 'ESAS-r', 0, '2017-07-13 14:16:20', '2017-07-13 14:16:20', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `Questiongroup_library`
--
DROP TABLE IF EXISTS `Questiongroup_library`;
CREATE TABLE `Questiongroup_library` (
  `questiongroup_serNum` bigint(20) UNSIGNED NOT NULL,
  `library_serNum` int(11) UNSIGNED NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created` timestamp NOT NULL,
  `last_updated_by` int(11) UNSIGNED DEFAULT NULL,
  `created_by` int(11) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `Questiongroup_library`
--

INSERT INTO `Questiongroup_library` (`questiongroup_serNum`, `library_serNum`, `last_updated`, `created`, `last_updated_by`, `created_by`) VALUES
(1, 1, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(2, 1, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(3, 1, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(4, 1, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(5, 1, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(6, 1, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(7, 1, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(8, 1, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(9, 1, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(10, 1, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(11, 1, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(12, 1, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(13, 1, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(14, 1, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(15, 1, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(16, 1, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(17, 1, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(18, 1, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(19, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(20, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(21, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(22, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(23, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(24, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(25, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(26, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(27, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(28, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(29, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(30, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(31, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(32, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(33, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(34, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(35, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(36, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(37, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(38, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(39, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(40, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(41, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(42, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(43, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(44, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(45, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(46, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(47, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(48, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(49, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(50, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(51, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(52, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(53, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(54, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(55, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(56, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(57, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(58, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(59, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(60, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(61, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(62, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(63, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(64, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(65, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(66, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(67, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(68, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(69, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(70, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(71, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(72, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(73, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(74, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(75, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(76, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(77, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(78, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(79, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(80, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(81, 2, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(82, 2, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(83, 2, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(84, 2, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(85, 2, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(86, 2, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(87, 2, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(88, 2, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(89, 2, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(90, 2, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(91, 2, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(92, 2, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(93, 2, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(94, 2, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(95, 2, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(96, 2, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(97, 2, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(98, 2, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(99, 2, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(100, 2, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(101, 2, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(102, 2, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(103, 2, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(104, 2, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(105, 2, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(106, 2, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(107, 2, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(108, 2, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(109, 2, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(110, 2, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(111, 2, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(112, 2, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(113, 2, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(114, 2, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(115, 2, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(116, 2, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(117, 2, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(118, 2, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(119, 2, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(120, 2, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(121, 3, '2017-07-13 14:16:20', '2017-07-13 14:16:20', NULL, NULL),
(122, 3, '2017-07-13 14:16:20', '2017-07-13 14:16:20', NULL, NULL),
(123, 3, '2017-07-13 14:16:20', '2017-07-13 14:16:20', NULL, NULL),
(124, 3, '2017-07-13 14:16:20', '2017-07-13 14:16:20', NULL, NULL),
(125, 3, '2017-07-13 14:16:20', '2017-07-13 14:16:20', NULL, NULL),
(126, 3, '2017-07-13 14:16:20', '2017-07-13 14:16:20', NULL, NULL),
(127, 3, '2017-07-13 14:16:20', '2017-07-13 14:16:20', NULL, NULL),
(128, 3, '2017-07-13 14:16:20', '2017-07-13 14:16:20', NULL, NULL),
(129, 3, '2017-07-13 14:16:20', '2017-07-13 14:16:20', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `Questiongroup_tag`
--
DROP TABLE IF EXISTS `Questiongroup_tag`;
CREATE TABLE `Questiongroup_tag` (
  `questiongroup_serNum` bigint(20) UNSIGNED NOT NULL,
  `tag_serNum` int(11) UNSIGNED NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created` timestamp NOT NULL,
  `last_updated_by` int(11) UNSIGNED DEFAULT NULL,
  `created_by` int(11) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `Questiongroup_tag`
--

INSERT INTO `Questiongroup_tag` (`questiongroup_serNum`, `tag_serNum`, `last_updated`, `created`, `last_updated_by`, `created_by`) VALUES
(1, 1, '2017-07-25 20:11:16', '2017-07-25 20:11:16', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `Questionnaire`
--
DROP TABLE IF EXISTS `Questionnaire`;
CREATE TABLE `Questionnaire` (
  `serNum` int(11) UNSIGNED NOT NULL,
  `name_EN` varchar(128) NOT NULL,
  `name_FR` varchar(128) NOT NULL,
  `private` tinyint(1) NOT NULL,
  `publish` tinyint(1) NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created` timestamp NOT NULL,
  `last_updated_by` int(11) UNSIGNED NOT NULL,
  `created_by` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `QuestionnaireAnswer`
--
DROP TABLE IF EXISTS `QuestionnaireAnswer`;
CREATE TABLE `QuestionnaireAnswer` (
  `serNum` bigint(20) UNSIGNED NOT NULL,
  `question_serNum` bigint(20) UNSIGNED NOT NULL,
  `answeroption_serNum` bigint(20) UNSIGNED NOT NULL,
  `questionnaire_patient_serNum` bigint(20) UNSIGNED NOT NULL,
  `answered` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `QuestionnaireAnswerOption`
--
DROP TABLE IF EXISTS `QuestionnaireAnswerOption`;
CREATE TABLE `QuestionnaireAnswerOption` (
  `serNum` bigint(20) UNSIGNED NOT NULL,
  `text_EN` varchar(256) NOT NULL,
  `text_FR` varchar(256) NOT NULL,
  `answertype_serNum` int(11) UNSIGNED NOT NULL,
  `position` int(4) UNSIGNED NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created` timestamp NOT NULL,
  `last_updated_by` int(11) UNSIGNED DEFAULT NULL,
  `created_by` int(11) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `QuestionnaireAnswerOption`
--

INSERT INTO `QuestionnaireAnswerOption` (`serNum`, `text_EN`, `text_FR`, `answertype_serNum`, `position`, `last_updated`, `created`, `last_updated_by`, `created_by`) VALUES
(1, 'None', 'None', 1, 1, '2017-07-13 14:15:36', '2017-07-13 14:15:36', NULL, NULL),
(2, 'Mild', 'Mild', 1, 2, '2017-07-13 14:15:36', '2017-07-13 14:15:36', NULL, NULL),
(3, 'Moderate', 'Moderate', 1, 3, '2017-07-13 14:15:36', '2017-07-13 14:15:36', NULL, NULL),
(4, 'Severe', 'Severe', 1, 4, '2017-07-13 14:15:36', '2017-07-13 14:15:36', NULL, NULL),
(5, 'Very severe', 'Very severe', 1, 5, '2017-07-13 14:15:36', '2017-07-13 14:15:36', NULL, NULL),
(6, 'None', 'None', 2, 1, '2017-07-13 14:15:36', '2017-07-13 14:15:36', NULL, NULL),
(7, 'Mild', 'Mild', 2, 2, '2017-07-13 14:15:36', '2017-07-13 14:15:36', NULL, NULL),
(8, 'Moderate', 'Moderate', 2, 3, '2017-07-13 14:15:36', '2017-07-13 14:15:36', NULL, NULL),
(9, 'Severe', 'Severe', 2, 4, '2017-07-13 14:15:36', '2017-07-13 14:15:36', NULL, NULL),
(10, 'Very severe', 'Very severe', 2, 5, '2017-07-13 14:15:36', '2017-07-13 14:15:36', NULL, NULL),
(11, 'Not applicable', 'Not applicable', 2, 6, '2017-07-13 14:15:36', '2017-07-13 14:15:36', NULL, NULL),
(12, 'None', 'None', 3, 1, '2017-07-13 14:15:36', '2017-07-13 14:15:36', NULL, NULL),
(13, 'Mild', 'Mild', 3, 2, '2017-07-13 14:15:36', '2017-07-13 14:15:36', NULL, NULL),
(14, 'Moderate', 'Moderate', 3, 3, '2017-07-13 14:15:36', '2017-07-13 14:15:36', NULL, NULL),
(15, 'Severe', 'Severe', 3, 4, '2017-07-13 14:15:36', '2017-07-13 14:15:36', NULL, NULL),
(16, 'Very severe', 'Very severe', 3, 5, '2017-07-13 14:15:36', '2017-07-13 14:15:36', NULL, NULL),
(17, 'Not sexually active', 'Not sexually active', 3, 6, '2017-07-13 14:15:36', '2017-07-13 14:15:36', NULL, NULL),
(18, 'Prefer not to answer', 'Prefer not to answer', 3, 7, '2017-07-13 14:15:36', '2017-07-13 14:15:36', NULL, NULL),
(19, 'Not at all', 'Not at all', 4, 1, '2017-07-13 14:15:36', '2017-07-13 14:15:36', NULL, NULL),
(20, 'A little bit', 'A little bit', 4, 2, '2017-07-13 14:15:36', '2017-07-13 14:15:36', NULL, NULL),
(21, 'Somewhat', 'Somewhat', 4, 3, '2017-07-13 14:15:36', '2017-07-13 14:15:36', NULL, NULL),
(22, 'Quite a bit', 'Quite a bit', 4, 4, '2017-07-13 14:15:36', '2017-07-13 14:15:36', NULL, NULL),
(23, 'Very much', 'Very much', 4, 5, '2017-07-13 14:15:36', '2017-07-13 14:15:36', NULL, NULL),
(24, 'Yes', 'Yes', 5, 1, '2017-07-13 14:15:36', '2017-07-13 14:15:36', NULL, NULL),
(25, 'No', 'No', 5, 2, '2017-07-13 14:15:36', '2017-07-13 14:15:36', NULL, NULL),
(26, 'Yes', 'Yes', 6, 1, '2017-07-13 14:15:36', '2017-07-13 14:15:36', NULL, NULL),
(27, 'No', 'No', 6, 2, '2017-07-13 14:15:36', '2017-07-13 14:15:36', NULL, NULL),
(28, 'Not applicable', 'Not applicable', 6, 3, '2017-07-13 14:15:36', '2017-07-13 14:15:36', NULL, NULL),
(29, 'Yes', 'Yes', 7, 1, '2017-07-13 14:15:36', '2017-07-13 14:15:36', NULL, NULL),
(30, 'No', 'No', 7, 2, '2017-07-13 14:15:36', '2017-07-13 14:15:36', NULL, NULL),
(31, 'Not sexually active', 'Not sexually active', 7, 3, '2017-07-13 14:15:36', '2017-07-13 14:15:36', NULL, NULL),
(32, 'Prefer not to answer', 'Prefer not to answer', 7, 4, '2017-07-13 14:15:36', '2017-07-13 14:15:36', NULL, NULL),
(33, 'Never', 'Never', 8, 1, '2017-07-13 14:15:36', '2017-07-13 14:15:36', NULL, NULL),
(34, 'Rarely', 'Rarely', 8, 2, '2017-07-13 14:15:36', '2017-07-13 14:15:36', NULL, NULL),
(35, 'Occasionally', 'Occasionally', 8, 3, '2017-07-13 14:15:36', '2017-07-13 14:15:36', NULL, NULL),
(36, 'Frequently', 'Frequently', 8, 4, '2017-07-13 14:15:36', '2017-07-13 14:15:36', NULL, NULL),
(37, 'Almost constantly', 'Almost constantly', 8, 5, '2017-07-13 14:15:36', '2017-07-13 14:15:36', NULL, NULL),
(38, 'Never', 'Never', 9, 1, '2017-07-13 14:15:36', '2017-07-13 14:15:36', NULL, NULL),
(39, 'Rarely', 'Rarely', 9, 2, '2017-07-13 14:15:36', '2017-07-13 14:15:36', NULL, NULL),
(40, 'Occasionally', 'Occasionally', 9, 3, '2017-07-13 14:15:36', '2017-07-13 14:15:36', NULL, NULL),
(41, 'Frequently', 'Frequently', 9, 4, '2017-07-13 14:15:36', '2017-07-13 14:15:36', NULL, NULL),
(42, 'Almost constantly', 'Almost constantly', 9, 5, '2017-07-13 14:15:36', '2017-07-13 14:15:36', NULL, NULL),
(43, 'Not sexually active', 'Not sexually active', 9, 6, '2017-07-13 14:15:36', '2017-07-13 14:15:36', NULL, NULL),
(44, 'Prefer not to answer', 'Prefer not to answer', 9, 7, '2017-07-13 14:15:36', '2017-07-13 14:15:36', NULL, NULL),
(45, 'Rarely or never', 'Rarely or never', 10, 1, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(46, 'About once a week', 'About once a week', 10, 2, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(47, 'More than once a week', 'More than once a week', 10, 3, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(48, 'About once a day', 'About once a day', 10, 4, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(49, 'More than once a day', 'More than once a day', 10, 5, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(50, 'Never', 'Never', 11, 1, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(51, 'Rarely', 'Rarely', 11, 2, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(52, 'About half the time', 'About half the time', 11, 3, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(53, 'Usually', 'Usually', 11, 4, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(54, 'Always', 'Always', 11, 5, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(55, 'Total control', 'Total control', 12, 1, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(56, 'Occasional dribbling', 'Occasional dribbling', 12, 2, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(57, 'Frequent dribbling', 'Frequent dribbling', 12, 3, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(58, 'No urinary control whatsoever', 'No urinary control whatsoever', 12, 4, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(59, 'None', 'None', 13, 1, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(60, '1 pad per day', '1 pad per day', 13, 2, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(61, '2 pads per day', '2 pads per day', 13, 3, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(62, '3 or more pads per day', '3 or more pads per day', 13, 4, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(63, 'No problem', 'No problem', 14, 1, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(64, 'Very small problem', 'Very small problem', 14, 2, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(65, 'Small problem', 'Small problem', 14, 3, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(66, 'Moderate problem', 'Moderate problem', 14, 4, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(67, 'Big problem', 'Big problem', 14, 5, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(68, 'Two or less', 'Two or less', 15, 1, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(69, 'Three to four', 'Three to four', 15, 2, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(70, 'Five or more', 'Five or more', 15, 3, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(71, 'Very poor to none', 'Very poor to none', 16, 1, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(72, 'Poor', 'Poor', 16, 2, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(73, 'Fair', 'Fair', 16, 3, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(74, 'Good', 'Good', 16, 4, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(75, 'Very good', 'Very good', 16, 5, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(76, 'None at all', 'None at all', 17, 1, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(77, 'Not firm enough for any sexual activity', 'Not firm enough for any sexual activity', 17, 2, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(78, 'Firm enough for masturbation and foreplay only', 'Firm enough for masturbation and foreplay only', 17, 3, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(79, 'Firm enough for intercourse', 'Firm enough for intercourse', 17, 4, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(80, 'I never had an erection when I wanted one', 'I never had an erection when I wanted one', 18, 1, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(81, 'I had an erection less than half the time I wanted one', 'I had an erection less than half the time I wanted one', 18, 2, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(82, 'I had an erection about half the time I wanted one', 'I had an erection about half the time I wanted one', 18, 3, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(83, 'I had an erection more than half the time I wanted one', 'I had an erection more than half the time I wanted one', 18, 4, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(84, 'I had an erection whenever I wanted one', 'I had an erection whenever I wanted one', 18, 5, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(85, 'Not at all', 'Not at all', 19, 1, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(86, 'Less than once a week', 'Less than once a week', 19, 2, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(87, 'About once a week', 'About once a week', 19, 3, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(88, 'Several times a week', 'Several times a week', 19, 4, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(89, 'Daily', 'Daily', 19, 5, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(90, 'Gained 10 pounds or more', 'Gained 10 pounds or more', 20, 1, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(91, 'Gained less than 10 pounds', 'Gained less than 10 pounds', 20, 2, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(92, 'No change in weight', 'No change in weight', 20, 3, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(93, 'Lost less than 10 pounds', 'Lost less than 10 pounds', 20, 4, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(94, 'Lost 10 pounds or more', 'Lost 10 pounds or more', 20, 5, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(95, 'Extremely dissatisfied', 'Extremely dissatisfied', 21, 1, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(96, 'Dissatisfied', 'Dissatisfied', 21, 2, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(97, 'Uncertain', 'Uncertain', 21, 3, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(98, 'Satisfied', 'Satisfied', 21, 4, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(99, 'Extremely satisfied', 'Extremely satisfied', 21, 5, '2017-07-13 14:15:45', '2017-07-13 14:15:45', NULL, NULL),
(100, '0', '0', 22, 1, '2017-07-13 14:15:55', '2017-07-13 14:15:55', NULL, NULL),
(101, '1', '1', 22, 2, '2017-07-13 14:15:55', '2017-07-13 14:15:55', NULL, NULL),
(102, '2', '2', 22, 3, '2017-07-13 14:15:55', '2017-07-13 14:15:55', NULL, NULL),
(103, '3', '3', 22, 4, '2017-07-13 14:15:55', '2017-07-13 14:15:55', NULL, NULL),
(104, '4', '4', 22, 5, '2017-07-13 14:15:55', '2017-07-13 14:15:55', NULL, NULL),
(105, '5', '5', 22, 6, '2017-07-13 14:15:55', '2017-07-13 14:15:55', NULL, NULL),
(106, '6', '6', 22, 7, '2017-07-13 14:15:55', '2017-07-13 14:15:55', NULL, NULL),
(107, '7', '7', 22, 8, '2017-07-13 14:15:55', '2017-07-13 14:15:55', NULL, NULL),
(108, '8', '8', 22, 9, '2017-07-13 14:15:55', '2017-07-13 14:15:55', NULL, NULL),
(109, '9', '9', 22, 10, '2017-07-13 14:15:55', '2017-07-13 14:15:55', NULL, NULL),
(110, '10', '10', 22, 11, '2017-07-13 14:15:55', '2017-07-13 14:15:55', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `QuestionnaireAnswerText`
--
DROP TABLE IF EXISTS `QuestionnaireAnswerText`;
CREATE TABLE `QuestionnaireAnswerText` (
  `serNum` bigint(20) UNSIGNED NOT NULL,
  `answer_serNum` bigint(20) UNSIGNED NOT NULL,
  `answer_text` text NOT NULL,
  `answered` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `QuestionnaireAnswerType`
--
DROP TABLE IF EXISTS `QuestionnaireAnswerType`;
CREATE TABLE `QuestionnaireAnswerType` (
  `serNum` int(11) UNSIGNED NOT NULL,
  `name_EN` varchar(128) NOT NULL,
  `name_FR` varchar(128) NOT NULL,
  `category_EN` varchar(128) DEFAULT NULL,
  `category_FR` varchar(128) DEFAULT NULL,
  `private` tinyint(1) NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created` timestamp NOT NULL,
  `last_updated_by` int(11) UNSIGNED DEFAULT NULL,
  `created_by` int(11) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `QuestionnaireAnswerType`
--

INSERT INTO `QuestionnaireAnswerType` (`serNum`, `name_EN`, `name_FR`, `category_EN`, `category_FR`, `private`, `last_updated`, `created`, `last_updated_by`, `created_by`) VALUES
(1, 'Severity', 'Severity', 'Multiple Choice', 'Multiple Choice', 0, '2017-07-13 15:34:25', '2017-07-13 14:15:36', NULL, NULL),
(2, 'SeverityNA', 'SeverityNA', 'Multiple Choice', 'Multiple Choice', 0, '2017-07-13 15:34:28', '2017-07-13 14:15:36', NULL, NULL),
(3, 'SeverityS', 'SeverityS', 'Multiple Choice', 'Multiple Choice', 0, '2017-07-13 15:34:30', '2017-07-13 14:15:36', NULL, NULL),
(4, 'Amount', 'Amount', 'Multiple Choice', 'Multiple Choice', 0, '2017-07-13 15:34:18', '2017-07-13 14:15:36', NULL, NULL),
(5, 'Boolean', 'Boolean', 'Multiple Choice', 'Multiple Choice', 0, '2017-07-13 15:34:13', '2017-07-13 14:15:36', NULL, NULL),
(6, 'BooleanNA', 'BooleanNA', 'Multiple Choice', 'Multiple Choice', 0, '2017-07-13 15:34:16', '2017-07-13 14:15:36', NULL, NULL),
(7, 'BooleanS', 'BooleanS', 'Multiple Choice', 'Multiple Choice', 0, '2017-07-13 15:01:10', '2017-07-13 14:15:36', NULL, NULL),
(8, 'Frequency', 'Frequency', 'Multiple Choice', 'Multiple Choice', 0, '2017-07-13 15:01:12', '2017-07-13 14:15:36', NULL, NULL),
(9, 'FrequencyS', 'FrequencyS', 'Multiple Choice', 'Multiple Choice', 0, '2017-07-13 15:01:16', '2017-07-13 14:15:36', NULL, NULL),
(10, 'Frequency Over 4 Weeks', 'Frequency Over 4 Weeks', 'Multiple Choice', 'Multiple Choice', 0, '2017-07-13 15:01:25', '2017-07-13 14:15:45', NULL, NULL),
(11, 'Frequency Over 4 Weeks - 2', 'Frequency Over 4 Weeks - 2', 'Multiple Choice', 'Multiple Choice', 0, '2017-07-13 15:01:19', '2017-07-13 14:15:45', NULL, NULL),
(12, 'Urinary control', 'Urinary control', 'Multiple Choice', 'Multiple Choice', 0, '2017-07-13 15:00:23', '2017-07-13 14:15:45', NULL, NULL),
(13, 'Pads', 'Pads', 'Multiple Choice', 'Multiple Choice', 0, '2017-07-13 15:00:27', '2017-07-13 14:15:45', NULL, NULL),
(14, 'Size of problem', 'Size of problem', 'Multiple Choice', 'Multiple Choice', 0, '2017-07-13 15:01:06', '2017-07-13 14:15:45', NULL, NULL),
(15, 'Number of bowel movements', 'Number of bowel movements', 'Multiple Choice', 'Multiple Choice', 0, '2017-07-13 15:01:02', '2017-07-13 14:15:45', NULL, NULL),
(16, 'Rating', 'Rating', 'Multiple Choice', 'Multiple Choice', 0, '2017-07-13 15:01:00', '2017-07-13 14:15:45', NULL, NULL),
(17, 'Quality of erections', 'Quality of erections', 'Multiple Choice', 'Multiple Choice', 0, '2017-07-13 15:00:57', '2017-07-13 14:15:45', NULL, NULL),
(18, 'Frequency of erections', 'Frequency of erections', 'Multiple Choice', 'Multiple Choice', 0, '2017-07-13 15:00:55', '2017-07-13 14:15:45', NULL, NULL),
(19, 'Frequency for sexual habits', 'Frequency for sexual habits', 'Multiple Choice', 'Multiple Choice', 0, '2017-07-13 15:00:52', '2017-07-13 14:15:45', NULL, NULL),
(20, 'Weight change', 'Weight change', 'Multiple Choice', 'Multiple Choice', 0, '2017-07-13 15:00:50', '2017-07-13 14:15:45', NULL, NULL),
(21, 'Satisfaction', 'Satisfaction', 'Multiple Choice', 'Multiple Choice', 0, '2017-07-13 15:00:47', '2017-07-13 14:15:45', NULL, NULL),
(22, 'Scale (0-10)', 'Scale (0-10)', 'Linear Scale', 'Linear Scale', 0, '2017-07-13 14:55:57', '2017-07-13 14:15:55', NULL, NULL),
(23, 'Short Answer', 'Short Answer', 'Short Answer', 'Short Answer', 0, '2017-07-13 15:41:31', '2017-07-13 15:41:31', NULL, NULL),
(24, 'Date', 'Date', 'Date', 'Date', 0, '2017-07-13 15:41:31', '2017-07-13 15:41:31', NULL, NULL),
(25, 'Time', 'Time', 'Time', 'Time', 0, '2017-07-13 15:42:00', '2017-07-13 15:42:00', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `QuestionnaireLibrary`
--
DROP TABLE IF EXISTS `QuestionnaireLibrary`;
CREATE TABLE `QuestionnaireLibrary` (
  `serNum` int(11) UNSIGNED NOT NULL,
  `name_EN` varchar(128) NOT NULL,
  `name_FR` varchar(128) NOT NULL,
  `private` tinyint(1) NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created` timestamp NOT NULL,
  `last_updated_by` int(11) UNSIGNED DEFAULT NULL,
  `created_by` int(11) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `QuestionnaireLibrary`
--

INSERT INTO `QuestionnaireLibrary` (`serNum`, `name_EN`, `name_FR`, `private`, `last_updated`, `created`, `last_updated_by`, `created_by`) VALUES
(1, 'CTCAE', 'CTCAE', 0, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(2, 'EPIC', 'EPIC', 0, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(3, 'ESAS', 'ESAS', 0, '2017-07-13 14:16:20', '2017-07-13 14:16:20', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `QuestionnaireQuestion`
--
DROP TABLE IF EXISTS `QuestionnaireQuestion`;
CREATE TABLE `QuestionnaireQuestion` (
  `serNum` bigint(20) UNSIGNED NOT NULL,
  `text_EN` varchar(1024) NOT NULL,
  `text_FR` varchar(1024) NOT NULL,
  `questiongroup_serNum` bigint(20) UNSIGNED NOT NULL,
  `answertype_serNum` int(11) UNSIGNED NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created` timestamp NOT NULL,
  `last_updated_by` int(11) UNSIGNED DEFAULT NULL,
  `created_by` int(11) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `QuestionnaireQuestion`
--

INSERT INTO `QuestionnaireQuestion` (`serNum`, `text_EN`, `text_FR`, `questiongroup_serNum`, `answertype_serNum`, `last_updated`, `created`, `last_updated_by`, `created_by`) VALUES
(1, 'In the last 7 days, what was the SEVERITY of your DRY MOUTH at its WORST?', 'In the last 7 days, what was the SEVERITY of your DRY MOUTH at its WORST?', 1, 1, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(2, 'In the last 7 days, what was the SEVERITY of your DIFFICULTY SWALLOWING at its WORST?', 'In the last 7 days, what was the SEVERITY of your DIFFICULTY SWALLOWING at its WORST?', 2, 1, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(3, 'In the last 7 days, what was the SEVERITY of your MOUTH OR THROAT SORES at their WORST?', 'In the last 7 days, what was the SEVERITY of your MOUTH OR THROAT SORES at their WORST?', 3, 1, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(4, 'In the last 7 days, how much did MOUTH OR THROAT SORES INTERFERE with your usual or daily activities?', 'In the last 7 days, how much did MOUTH OR THROAT SORES INTERFERE with your usual or daily activities?', 3, 4, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(5, 'In the last 7 days, what was the SEVERITY of SKIN CRACKING AT THE CORNERS OF YOUR MOUTH at its WORST?', 'In the last 7 days, what was the SEVERITY of SKIN CRACKING AT THE CORNERS OF YOUR MOUTH at its WORST?', 4, 1, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(6, 'In the last 7 days, did you have any VOICE CHANGES?', 'In the last 7 days, did you have any VOICE CHANGES?', 5, 5, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(7, 'In the last 7 days, what was the SEVERITY of your HOARSE VOICE at its WORST?', 'In the last 7 days, what was the SEVERITY of your HOARSE VOICE at its WORST?', 6, 1, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(8, 'In the last 7 days, what was the SEVERITY of your PROBLEMS WITH TASTING FOOD OR DRINK at their WORST?', 'In the last 7 days, what was the SEVERITY of your PROBLEMS WITH TASTING FOOD OR DRINK at their WORST?', 7, 1, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(9, 'In the last 7 days, what was the SEVERITY of your DECREASED APPETITE at its WORST?', 'In the last 7 days, what was the SEVERITY of your DECREASED APPETITE at its WORST?', 8, 1, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(10, 'In the last 7 days, how much did DECREASED APPETITE INTERFERE with your usual or daily activities?', 'In the last 7 days, how much did DECREASED APPETITE INTERFERE with your usual or daily activities?', 8, 4, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(11, 'In the last 7 days, how OFTEN did you have NAUSEA?', 'In the last 7 days, how OFTEN did you have NAUSEA?', 9, 8, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(12, 'In the last 7 days, what was the SEVERITY of your NAUSEA at its WORST?', 'In the last 7 days, what was the SEVERITY of your NAUSEA at its WORST?', 9, 1, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(13, 'In the last 7 days, how OFTEN did you have VOMITING?', 'In the last 7 days, how OFTEN did you have VOMITING?', 10, 8, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(14, 'In the last 7 days, what was the SEVERITY of your VOMITING at its WORST?', 'In the last 7 days, what was the SEVERITY of your VOMITING at its WORST?', 10, 1, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(15, 'In the last 7 days, how OFTEN did you have HEARTBURN?', 'In the last 7 days, how OFTEN did you have HEARTBURN?', 11, 8, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(16, 'In the last 7 days, what was the SEVERITY of your HEARTBURN at its WORST?', 'In the last 7 days, what was the SEVERITY of your HEARTBURN at its WORST?', 11, 1, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(17, 'In the last 7 days, did you have any INCREASED PASSING OF GAS (FLATULENCE)?', 'In the last 7 days, did you have any INCREASED PASSING OF GAS (FLATULENCE)?', 12, 5, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(18, 'In the last 7 days, how OFTEN did you have BLOATING OF THE ABDOMEN (BELLY)?', 'In the last 7 days, how OFTEN did you have BLOATING OF THE ABDOMEN (BELLY)?', 13, 8, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(19, 'In the last 7 days, what was the SEVERITY of your BLOATING OF THE ABDOMEN (BELLY) at its WORST?', 'In the last 7 days, what was the SEVERITY of your BLOATING OF THE ABDOMEN (BELLY) at its WORST?', 13, 1, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(20, 'In the last 7 days, how OFTEN did you have HICCUPS?', 'In the last 7 days, how OFTEN did you have HICCUPS?', 14, 8, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(21, 'In the last 7 days, what was the SEVERITY of your HICCUPS at their WORST?', 'In the last 7 days, what was the SEVERITY of your HICCUPS at their WORST?', 14, 1, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(22, 'In the last 7 days, what was the SEVERITY of your CONSTIPATION at its WORST?', 'In the last 7 days, what was the SEVERITY of your CONSTIPATION at its WORST?', 15, 1, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(23, 'In the last 7 days, how OFTEN did you have LOOSE OR WATERY STOOLS (DIARRHEA)?', 'In the last 7 days, how OFTEN did you have LOOSE OR WATERY STOOLS (DIARRHEA)?', 16, 8, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(24, 'In the last 7 days, how OFTEN did you have PAIN IN THE ABDOMEN (BELLY AREA)?', 'In the last 7 days, how OFTEN did you have PAIN IN THE ABDOMEN (BELLY AREA)?', 17, 8, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(25, 'In the last 7 days, what was the SEVERITY of your PAIN IN THE ABDOMEN (BELLY AREA) at its WORST?', 'In the last 7 days, what was the SEVERITY of your PAIN IN THE ABDOMEN (BELLY AREA) at its WORST?', 17, 1, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(26, 'In the last 7 days, how much did PAIN IN THE ABDOMEN (BELLY AREA) INTERFERE with your usual or daily activities?', 'In the last 7 days, how much did PAIN IN THE ABDOMEN (BELLY AREA) INTERFERE with your usual or daily activities?', 17, 4, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(27, 'In the last 7 days, how OFTEN did you LOSE CONTROL OF BOWEL MOVEMENTS?', 'In the last 7 days, how OFTEN did you LOSE CONTROL OF BOWEL MOVEMENTS?', 18, 8, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(28, 'In the last 7 days, how much did LOSS OF CONTROL OF BOWEL MOVEMENTS INTERFERE with your usual or daily activities?', 'In the last 7 days, how much did LOSS OF CONTROL OF BOWEL MOVEMENTS INTERFERE with your usual or daily activities?', 18, 4, '2017-07-13 14:16:05', '2017-07-13 14:16:05', NULL, NULL),
(29, 'In the last 7 days, what was the SEVERITY of your SHORTNESS OF BREATH at its WORST?', 'In the last 7 days, what was the SEVERITY of your SHORTNESS OF BREATH at its WORST?', 19, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(30, 'In the last 7 days, how much did your SHORTNESS OF BREATH INTERFERE with your usual or daily activities?', 'In the last 7 days, how much did your SHORTNESS OF BREATH INTERFERE with your usual or daily activities?', 19, 4, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(31, 'In the last 7 days, what was the SEVERITY of your COUGH at its WORST?', 'In the last 7 days, what was the SEVERITY of your COUGH at its WORST?', 20, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(32, 'In the last 7 days, how much did COUGH INTERFERE with your usual or daily activities?', 'In the last 7 days, how much did COUGH INTERFERE with your usual or daily activities?', 20, 4, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(33, 'In the last 7 days, what was the SEVERITY of your WHEEZING (WHISTLING NOISE IN THE CHEST WITH BREATHING) at its WORST?', 'In the last 7 days, what was the SEVERITY of your WHEEZING (WHISTLING NOISE IN THE CHEST WITH BREATHING) at its WORST?', 21, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(34, 'In the last 7 days, how OFTEN did you have ARM OR LEG SWELLING?', 'In the last 7 days, how OFTEN did you have ARM OR LEG SWELLING?', 22, 8, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(35, 'In the last 7 days, what was the SEVERITY of your ARM OR LEG SWELLING at its WORST?', 'In the last 7 days, what was the SEVERITY of your ARM OR LEG SWELLING at its WORST?', 22, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(36, 'In the last 7 days, how much did ARM OR LEG SWELLING INTERFERE with your usual or daily activities?', 'In the last 7 days, how much did ARM OR LEG SWELLING INTERFERE with your usual or daily activities?', 22, 4, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(37, 'In the last 7 days, how OFTEN did you feel a POUNDING OR RACING HEARTBEAT (PALPITATIONS)?', 'In the last 7 days, how OFTEN did you feel a POUNDING OR RACING HEARTBEAT (PALPITATIONS)?', 23, 8, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(38, 'In the last 7 days, what was the SEVERITY of your POUNDING OR RACING HEARTBEAT (PALPITATIONS) at its WORST?', 'In the last 7 days, what was the SEVERITY of your POUNDING OR RACING HEARTBEAT (PALPITATIONS) at its WORST?', 23, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(39, 'In the last 7 days, did you have any RASH?', 'In the last 7 days, did you have any RASH?', 24, 5, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(40, 'In the last 7 days, what was the SEVERITY of your DRY SKIN at its WORST?', 'In the last 7 days, what was the SEVERITY of your DRY SKIN at its WORST?', 25, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(41, 'In the last 7 days, what was the SEVERITY of your ACNE OR PIMPLES ON THE FACE OR CHEST at its WORST?', 'In the last 7 days, what was the SEVERITY of your ACNE OR PIMPLES ON THE FACE OR CHEST at its WORST?', 26, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(42, 'In the last 7 days, did you have any HAIR LOSS?', 'In the last 7 days, did you have any HAIR LOSS?', 27, 4, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(43, 'In the last 7 days, what was the SEVERITY of your ITCHY SKIN at its WORST?', 'In the last 7 days, what was the SEVERITY of your ITCHY SKIN at its WORST?', 28, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(44, 'In the last 7 days, did you have any HIVES (ITCHY RED BUMPS ON THE SKIN)?', 'In the last 7 days, did you have any HIVES (ITCHY RED BUMPS ON THE SKIN)?', 29, 5, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(45, 'In the last 7 days, what was the SEVERITY of your HAND-FOOT SYNDROME (A RASH OF THE HANDS OR FEET THAT CAN CAUSE CRACKING, PEELING, REDNESS OR PAIN) at its WORST?', 'In the last 7 days, what was the SEVERITY of your HAND-FOOT SYNDROME (A RASH OF THE HANDS OR FEET THAT CAN CAUSE CRACKING, PEELING, REDNESS OR PAIN) at its WORST?', 30, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(46, 'In the last 7 days, did you LOSE ANY FINGERNAILS OR TOENAILS?', 'In the last 7 days, did you LOSE ANY FINGERNAILS OR TOENAILS?', 31, 5, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(47, 'In the last 7 days, did you have any RIDGES OR BUMPS ON YOUR FINGERNAILS OR TOENAILS?', 'In the last 7 days, did you have any RIDGES OR BUMPS ON YOUR FINGERNAILS OR TOENAILS?', 32, 5, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(48, 'In the last 7 days, did you have any CHANGE IN THE COLOR OF YOUR FINGERNAILS OR TOENAILS?', 'In the last 7 days, did you have any CHANGE IN THE COLOR OF YOUR FINGERNAILS OR TOENAILS?', 33, 5, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(49, 'In the last 7 days, did you have any INCREASED SKIN SENSIVITY TO SUNLIGHT?', 'In the last 7 days, did you have any INCREASED SKIN SENSIVITY TO SUNLIGHT?', 34, 5, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(50, 'In the last 7 days, did you have any BED SORES?', 'In the last 7 days, did you have any BED SORES?', 35, 5, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(51, 'In the last 7 days, what was the SEVERITY of your SKIN BURNS FROM RADIATION at their WORST?', 'In the last 7 days, what was the SEVERITY of your SKIN BURNS FROM RADIATION at their WORST?', 36, 2, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(52, 'In the last 7 days, did you have any UNUSUAL DARKENING OF THE SKIN?', 'In the last 7 days, did you have any UNUSUAL DARKENING OF THE SKIN?', 37, 5, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(53, 'In the last 7 days, did you have any STRETCH MARKS?', 'In the last 7 days, did you have any STRETCH MARKS?', 38, 5, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(54, 'In the last 7 days, what was the SEVERITY of your NUMBNESS OR TINGLING IN YOUR HANDS OR FEET at its WORST?', 'In the last 7 days, what was the SEVERITY of your NUMBNESS OR TINGLING IN YOUR HANDS OR FEET at its WORST?', 39, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(55, 'In the last 7 days, how much did NUMBNESS OR TINGLING IN YOUR HANDS OR FEET INTERFERE with your usual or daily activities?', 'In the last 7 days, how much did NUMBNESS OR TINGLING IN YOUR HANDS OR FEET INTERFERE with your usual or daily activities?', 39, 4, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(56, 'In the last 7 days, what was the SEVERITY of your DIZZINESS at its WORST?', 'In the last 7 days, what was the SEVERITY of your DIZZINESS at its WORST?', 40, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(57, 'In the last 7 days, how much did DIZZINESS INTERFERE with your usual or daily activities?', 'In the last 7 days, how much did DIZZINESS INTERFERE with your usual or daily activities?', 40, 4, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(58, 'In the last 7 days, what was the SEVERITY of your BLURRY VISION at its WORST?', 'In the last 7 days, what was the SEVERITY of your BLURRY VISION at its WORST?', 41, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(59, 'In the last 7 days, how much did BLURRY VISION INTERFERE with your usual or daily activities?', 'In the last 7 days, how much did BLURRY VISION INTERFERE with your usual or daily activities?', 41, 4, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(60, 'In the last 7 days, did you have any FLASHING LIGHTS IN FRONT OF YOUR EYES?', 'In the last 7 days, did you have any FLASHING LIGHTS IN FRONT OF YOUR EYES?', 42, 5, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(61, 'In the last 7 days, did you have any SPOTS OR LINES (FLOATERS) THAT DRIFT IN FRONT OF YOUR EYES?', 'In the last 7 days, did you have any SPOTS OR LINES (FLOATERS) THAT DRIFT IN FRONT OF YOUR EYES?', 43, 4, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(62, 'In the last 7 days, what was the SEVERITY of your WATERY EYES (TEARING) at their WORST?', 'In the last 7 days, what was the SEVERITY of your WATERY EYES (TEARING) at their WORST?', 44, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(63, 'In the last 7 days, how much did WATERY EYES (TEARING) INTERFERE with your usual or daily activities?', 'In the last 7 days, how much did WATERY EYES (TEARING) INTERFERE with your usual or daily activities?', 44, 4, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(64, 'In the last 7 days, what was the SEVERITY of RINGING IN YOUR EARS at its WORST?', 'In the last 7 days, what was the SEVERITY of RINGING IN YOUR EARS at its WORST?', 45, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(65, 'In the last 7 days, what was the SEVERITY of your PROBLEMS WITH CONCENTRATION at their WORST?', 'In the last 7 days, what was the SEVERITY of your PROBLEMS WITH CONCENTRATION at their WORST?', 46, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(66, 'In the last 7 days, how much did PROBLEMS WITH CONCENTRATION INTERFERE with your usual or daily activities?', 'In the last 7 days, how much did PROBLEMS WITH CONCENTRATION INTERFERE with your usual or daily activities?', 46, 4, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(67, 'In the last 7 days, what was the SEVERITY of your PROBLEMS WITH MEMORY at their WORST?', 'In the last 7 days, what was the SEVERITY of your PROBLEMS WITH MEMORY at their WORST?', 47, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(68, 'In the last 7 days, how much did PROBLEMS WITH MEMORY INTERFERE with your usual or daily activities?', 'In the last 7 days, how much did PROBLEMS WITH MEMORY INTERFERE with your usual or daily activities?', 47, 4, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(69, 'In the last 7 days, how OFTEN did you have PAIN?', 'In the last 7 days, how OFTEN did you have PAIN?', 48, 8, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(70, 'In the last 7 days, what was the SEVERITY of your PAIN at its WORST?', 'In the last 7 days, what was the SEVERITY of your PAIN at its WORST?', 48, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(71, 'In the last 7 days, how much did PAIN INTERFERE with your usual or daily activities?', 'In the last 7 days, how much did PAIN INTERFERE with your usual or daily activities?', 48, 4, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(72, 'In the last 7 days, how OFTEN did you have a HEADACHE?', 'In the last 7 days, how OFTEN did you have a HEADACHE?', 49, 8, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(73, 'In the last 7 days, what was the SEVERITY of your HEADACHE at its WORST?', 'In the last 7 days, what was the SEVERITY of your HEADACHE at its WORST?', 49, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(74, 'In the last 7 days, how much did your HEADACHE INTERFERE with your usual or daily activities?', 'In the last 7 days, how much did your HEADACHE INTERFERE with your usual or daily activities?', 49, 4, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(75, 'In the last 7 days, how OFTEN did you have ACHING MUSCLES?', 'In the last 7 days, how OFTEN did you have ACHING MUSCLES?', 50, 8, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(76, 'In the last 7 days, what was the SEVERITY of your ACHING MUSCLES at their WORST?', 'In the last 7 days, what was the SEVERITY of your ACHING MUSCLES at their WORST?', 50, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(77, 'In the last 7 days, how much did ACHING MUSCLES INTERFERE with your usual or daily activities?', 'In the last 7 days, how much did ACHING MUSCLES INTERFERE with your usual or daily activities?', 50, 4, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(78, 'In the last 7 days, how OFTEN did you have ACHING JOINTS (SUCH AS ELBOWS, KNEES, SHOULDERS)?', 'In the last 7 days, how OFTEN did you have ACHING JOINTS (SUCH AS ELBOWS, KNEES, SHOULDERS)?', 51, 8, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(79, 'In the last 7 days, what was the SEVERITY of your ACHING JOINTS (SUCH AS ELBOWS, KNEES, SHOULDERS) at their WORST?', 'In the last 7 days, what was the SEVERITY of your ACHING JOINTS (SUCH AS ELBOWS, KNEES, SHOULDERS) at their WORST?', 51, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(80, 'In the last 7 days, how much did ACHING JOINTS (SUCH AS ELBOWS, KNEES, SHOULDERS) INTERFERE with your usual or daily activities?', 'In the last 7 days, how much did ACHING JOINTS (SUCH AS ELBOWS, KNEES, SHOULDERS) INTERFERE with your usual or daily activities?', 51, 4, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(81, 'In the last 7 days, what was the SEVERITY of your INSOMNIA (INCLUDING DIFFICULTY FALLING ASLEEP, STAYING ASLEEP, OR WAKING UP EARLY) at its WORST?', 'In the last 7 days, what was the SEVERITY of your INSOMNIA (INCLUDING DIFFICULTY FALLING ASLEEP, STAYING ASLEEP, OR WAKING UP EARLY) at its WORST?', 52, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(82, 'In the last 7 days, how much did INSOMNIA (INCLUDING DIFFICULTY FALLING ASLEEP, STAYING ASLEEP, OR WAKING UP EARLY) INTERFERE with your usual or daily activities?', 'In the last 7 days, how much did INSOMNIA (INCLUDING DIFFICULTY FALLING ASLEEP, STAYING ASLEEP, OR WAKING UP EARLY) INTERFERE with your usual or daily activities?', 52, 4, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(83, 'In the last 7 days, what was the SEVERITY of your FATIGUE, TIREDNESS, OR LACK OF ENERGY at its WORST?', 'In the last 7 days, what was the SEVERITY of your FATIGUE, TIREDNESS, OR LACK OF ENERGY at its WORST?', 53, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(84, 'In the last 7 days, how much did FATIGUE, TIREDNESS, OR LACK OF ENERGY INTERFERE with your usual or daily activities?', 'In the last 7 days, how much did FATIGUE, TIREDNESS, OR LACK OF ENERGY INTERFERE with your usual or daily activities?', 53, 4, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(85, 'In the last 7 days, how OFTEN did you feel ANXIETY?', 'In the last 7 days, how OFTEN did you feel ANXIETY?', 54, 8, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(86, 'In the last 7 days, what was the SEVERITY of your ANXIETY at its WORST?', 'In the last 7 days, what was the SEVERITY of your ANXIETY at its WORST?', 54, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(87, 'In the last 7 days, how much did ANXIETY INTERFERE with your usual or daily activities?', 'In the last 7 days, how much did ANXIETY INTERFERE with your usual or daily activities?', 54, 4, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(88, 'In the last 7 days, how OFTEN did you FEEL THAT NOTHING COULD CHEER YOU UP?', 'In the last 7 days, how OFTEN did you FEEL THAT NOTHING COULD CHEER YOU UP?', 55, 8, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(89, 'In the last 7 days, what was the SEVERITY of your FEELINGS THAT NOTHING COULD CHEER YOU UP at their WORST?', 'In the last 7 days, what was the SEVERITY of your FEELINGS THAT NOTHING COULD CHEER YOU UP at their WORST?', 55, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(90, 'In the last 7 days, how much did FEELING THAT NOTHING COULD CHEER YOU UP INTERFERE with your usual or daily activities?', 'In the last 7 days, how much did FEELING THAT NOTHING COULD CHEER YOU UP INTERFERE with your usual or daily activities?', 55, 4, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(91, 'In the last 7 days, how OFTEN did you have SAD OR UNHAPPY FEELINGS?', 'In the last 7 days, how OFTEN did you have SAD OR UNHAPPY FEELINGS?', 56, 8, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(92, 'In the last 7 days, what was the SEVERITY of your SAD OR UNHAPPY FEELINGS at their WORST?', 'In the last 7 days, what was the SEVERITY of your SAD OR UNHAPPY FEELINGS at their WORST?', 56, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(93, 'In the last 7 days, how much did SAD OR UNHAPPY FEELINGS INTERFERE with your usual or daily activities?', 'In the last 7 days, how much did SAD OR UNHAPPY FEELINGS INTERFERE with your usual or daily activities?', 56, 4, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(94, 'In the last 7 days, did you have any IRREGULAR MENSTRUAL PERIODS?', 'In the last 7 days, did you have any IRREGULAR MENSTRUAL PERIODS?', 57, 6, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(95, 'In the last 7 days, did you MISS AN EXPECTED MENSTRUAL PERIOD?', 'In the last 7 days, did you MISS AN EXPECTED MENSTRUAL PERIOD?', 58, 6, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(96, 'In the last 7 days, did you have any UNUSUAL VAGINAL DISCHARGE?', 'In the last 7 days, did you have any UNUSUAL VAGINAL DISCHARGE?', 59, 4, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(97, 'In the last 7 days, what was the SEVERITY of your VAGINAL DRYNESS at its WORST?', 'In the last 7 days, what was the SEVERITY of your VAGINAL DRYNESS at its WORST?', 60, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(98, 'In the last 7 days, what was the SEVERITY of your PAIN OR BURNING WITH URINATION at its WORST?', 'In the last 7 days, what was the SEVERITY of your PAIN OR BURNING WITH URINATION at its WORST?', 61, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(99, 'In the last 7 days, how OFTEN did you feel an URGE TO URINATE ALL OF A SUDDEN?', 'In the last 7 days, how OFTEN did you feel an URGE TO URINATE ALL OF A SUDDEN?', 62, 8, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(100, 'In the last 7 days, how much did SUDDEN URGES TO URINATE INTERFERE with your usual or daily activities?', 'In the last 7 days, how much did SUDDEN URGES TO URINATE INTERFERE with your usual or daily activities?', 62, 4, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(101, 'In the last 7 days, were there times when you had to URINATE FREQUENTLY?', 'In the last 7 days, were there times when you had to URINATE FREQUENTLY?', 63, 8, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(102, 'In the last 7 days, how much did FREQUENT URINATION INTERFERE with your usual or daily activities?', 'In the last 7 days, how much did FREQUENT URINATION INTERFERE with your usual or daily activities?', 63, 4, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(103, 'In the last 7 days, did you have any URINE COLOR CHANGE?', 'In the last 7 days, did you have any URINE COLOR CHANGE?', 64, 5, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(104, 'In the last 7 days, how OFTEN did you have LOSS OF CONTROL OF URINE (LEAKAGE)?', 'In the last 7 days, how OFTEN did you have LOSS OF CONTROL OF URINE (LEAKAGE)?', 65, 8, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(105, 'In the last 7 days, how much did LOSS OF CONTROL OF URINE (LEAKAGE) INTERFERE with your usual or daily activities?', 'In the last 7 days, how much did LOSS OF CONTROL OF URINE (LEAKAGE) INTERFERE with your usual or daily activities?', 65, 4, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(106, 'In the last 7 days, what was the SEVERITY of your DIFFICULTY GETTING OR KEEPING AN ERECTION at its WORST?', 'In the last 7 days, what was the SEVERITY of your DIFFICULTY GETTING OR KEEPING AN ERECTION at its WORST?', 66, 3, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(107, 'In the last 7 days, how OFTEN did you have EJACULATION PROBLEMS?', 'In the last 7 days, how OFTEN did you have EJACULATION PROBLEMS?', 67, 9, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(108, 'In the last 7 days, what was the SEVERITY of your DECREASED SEXUAL INTEREST at its WORST?', 'In the last 7 days, what was the SEVERITY of your DECREASED SEXUAL INTEREST at its WORST?', 68, 3, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(109, 'In the last 7 days, did you feel that it TOOK TOO LONG TO HAVE AN ORGASM OR CLIMAX?', 'In the last 7 days, did you feel that it TOOK TOO LONG TO HAVE AN ORGASM OR CLIMAX?', 69, 7, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(110, 'In the last 7 days, were you UNABLE TO HAVE AN ORGASM OR CLIMAX?', 'In the last 7 days, were you UNABLE TO HAVE AN ORGASM OR CLIMAX?', 70, 7, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(111, 'In the last 7 days, what was the SEVERITY of your PAIN DURING VAGINAL SEX at its WORST?', 'In the last 7 days, what was the SEVERITY of your PAIN DURING VAGINAL SEX at its WORST?', 71, 3, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(112, 'In the last 7 days, what was the SEVERITY of your BREAST AREA ENLARGEMENT OR TENDERNESS at its WORST?', 'In the last 7 days, what was the SEVERITY of your BREAST AREA ENLARGEMENT OR TENDERNESS at its WORST?', 72, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(113, 'In the last 7 days, did you BRUISE EASILY (BLACK AND BLUE MARKS)?', 'In the last 7 days, did you BRUISE EASILY (BLACK AND BLUE MARKS)?', 73, 5, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(114, 'In the last 7 days, how OFTEN did you have SHIVERING OR SHAKING CHILLS?', 'In the last 7 days, how OFTEN did you have SHIVERING OR SHAKING CHILLS?', 74, 8, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(115, 'In the last 7 days, what was the SEVERITY of your SHIVERING OR SHAKING CHILLS at their WORST?', 'In the last 7 days, what was the SEVERITY of your SHIVERING OR SHAKING CHILLS at their WORST?', 74, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(116, 'In the last 7 days, how OFTEN did you have UNEXPECTED OR EXCESSIVE SWEATING DURING THE DAY OR NIGHTIME (NOT RELATED TO HOT FLASHES)?', 'In the last 7 days, how OFTEN did you have UNEXPECTED OR EXCESSIVE SWEATING DURING THE DAY OR NIGHTIME (NOT RELATED TO HOT FLASHES)?', 75, 8, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(117, 'In the last 7 days, what was the SEVERITY of your UNEXPECTED OR EXCESSIVE SWEATING DURING THE DAY OR NIGHTIME (NOT RELATED TO HOT FLASHES) at its WORST?', 'In the last 7 days, what was the SEVERITY of your UNEXPECTED OR EXCESSIVE SWEATING DURING THE DAY OR NIGHTIME (NOT RELATED TO HOT FLASHES) at its WORST?', 75, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(118, 'In the last 7 days, did you have an UNEXPECTED DECREASE IN SWEATING?', 'In the last 7 days, did you have an UNEXPECTED DECREASE IN SWEATING?', 76, 5, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(119, 'In the last 7 days, how OFTEN did you have HOT FLASHES?', 'In the last 7 days, how OFTEN did you have HOT FLASHES?', 77, 8, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(120, 'In the last 7 days, what was the SEVERITY of your HOT FLASHES at their WORST?', 'In the last 7 days, what was the SEVERITY of your HOT FLASHES at their WORST?', 77, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(121, 'In the last 7 days, how OFTEN did you have NOSEBLEEDS?', 'In the last 7 days, how OFTEN did you have NOSEBLEEDS?', 78, 8, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(122, 'In the last 7 days, what was the SEVERITY of your NOSEBLEEDS at their WORST?', 'In the last 7 days, what was the SEVERITY of your NOSEBLEEDS at their WORST?', 78, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(123, 'In the last 7 days, did you HAVE ANY PAIN, SWELLING, OR REDNESS AT A SITE OF DRUG INJECTION OR IV?', 'In the last 7 days, did you HAVE ANY PAIN, SWELLING, OR REDNESS AT A SITE OF DRUG INJECTION OR IV?', 79, 6, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(124, 'In the last 7 days, what was the SEVERITY of your BODY ODOR at its WORST?', 'In the last 7 days, what was the SEVERITY of your BODY ODOR at its WORST?', 80, 1, '2017-07-13 14:16:06', '2017-07-13 14:16:06', NULL, NULL),
(125, 'Over the past 4 weeks, how often have you leaked urine?', 'Over the past 4 weeks, how often have you leaked urine?', 81, 10, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(126, 'How big a problem, if any, has dripping or leaking urine been for you during the last 4 weeks?', 'How big a problem, if any, has dripping or leaking urine been for you during the last 4 weeks?', 81, 14, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(127, 'Over the past 4 weeks, how often have you urinated blood?', 'Over the past 4 weeks, how often have you urinated blood?', 82, 10, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(128, 'How big a problem, if any, has bleeding with urination been for you during the last 4 weeks?', 'How big a problem, if any, has bleeding with urination been for you during the last 4 weeks?', 82, 14, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(129, 'Over the past 4 weeks, how often have you had pain or burning with urination?', 'Over the past 4 weeks, how often have you had pain or burning with urination?', 83, 10, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(130, 'How big a problem, if any, has pain or burning on urination been for you during the last 4 weeks?', 'How big a problem, if any, has pain or burning on urination been for you during the last 4 weeks?', 83, 14, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(131, 'Which of the following best describes your urinary control during the last 4 weeks?', 'Which of the following best describes your urinary control during the last 4 weeks?', 84, 12, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(132, 'How many pads or adult diapers per day did you usually use to control leakage during the last 4 weeks?', 'How many pads or adult diapers per day did you usually use to control leakage during the last 4 weeks?', 85, 13, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(133, 'How big a problem, if any, has weak urine stream or incomplete emptying been for you during the last 4 weeks?', 'How big a problem, if any, has weak urine stream or incomplete emptying been for you during the last 4 weeks?', 86, 14, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(134, 'How big a problem, if any, has waking up to urinate been for you during the last 4 weeks?', 'How big a problem, if any, has waking up to urinate been for you during the last 4 weeks?', 87, 14, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(135, 'How big a problem, if any, has the need to urinate frequently during the day been for you during the last 4 weeks?', 'How big a problem, if any, has the need to urinate frequently during the day been for you during the last 4 weeks?', 88, 14, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(136, 'Overall, how big a problem has your urinary function been for during the last 4 weeks?', 'Overall, how big a problem has your urinary function been for during the last 4 weeks?', 89, 14, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(137, 'How often have you had rectal urgency (felt like I had to pass stool, but did not) during the last 4 weeks?', 'How often have you had rectal urgency (felt like I had to pass stool, but did not) during the last 4 weeks?', 90, 10, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(138, 'How often have you had uncontrolled leakage of stool or feces during the last 4 weeks?', 'How often have you had uncontrolled leakage of stool or feces during the last 4 weeks?', 91, 10, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(139, 'How often have you had stools (bowel movements) that were loose or liquid (no form, watery, mushy) during the last 4 weeks?', 'How often have you had stools (bowel movements) that were loose or liquid (no form, watery, mushy) during the last 4 weeks?', 92, 11, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(140, 'How often have you had bloody stools during the last 4 weeks?', 'How often have you had bloody stools during the last 4 weeks?', 93, 11, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(141, 'How often have your bowel movements been painful during the last 4 weeks?', 'How often have your bowel movements been painful during the last 4 weeks?', 94, 11, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(142, 'How many bowel movements have you had on a typical day during the last 4 weeks?', 'How many bowel movements have you had on a typical day during the last 4 weeks?', 95, 15, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(143, 'How often have you had crampy pain in your abdomen, pelvis or rectum during the last 4 weeks?', 'How often have you had crampy pain in your abdomen, pelvis or rectum during the last 4 weeks?', 96, 10, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(144, 'How big a problem, if any, has urgency to have a bowel movement been for you during the last 4 weeks?', 'How big a problem, if any, has urgency to have a bowel movement been for you during the last 4 weeks?', 97, 14, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(145, 'How big a problem, if any, has increased frequency of bowel movements been for you during the last 4 weeks?', 'How big a problem, if any, has increased frequency of bowel movements been for you during the last 4 weeks?', 98, 14, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(146, 'How big a problem, if any, has watery bowel movements been for you during the last 4 weeks?', 'How big a problem, if any, has watery bowel movements been for you during the last 4 weeks?', 99, 14, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(147, 'How big a problem, if any, has losing control of your stools been for you during the last 4 weeks?', 'How big a problem, if any, has losing control of your stools been for you during the last 4 weeks?', 100, 14, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(148, 'How big a problem, if any, has bloody stools been for you during the last 4 weeks?', 'How big a problem, if any, has bloody stools been for you during the last 4 weeks?', 101, 14, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(149, 'How big a problem, if any, has abdominal/pelvic/rectal pain been for you during the last 4 weeks?', 'How big a problem, if any, has abdominal/pelvic/rectal pain been for you during the last 4 weeks?', 102, 14, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(150, 'Overall, how big a problem have your bowel habits been for during the last 4 weeks?', 'Overall, how big a problem have your bowel habits been for during the last 4 weeks?', 103, 14, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(151, 'How would you rate your level of sexual desire during the last 4 weeks?', 'How would you rate your level of sexual desire during the last 4 weeks?', 104, 16, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(152, 'How big a problem during the last 4 weeks, if any, has your level of sexual desire been for you?', 'How big a problem during the last 4 weeks, if any, has your level of sexual desire been for you?', 104, 14, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(153, 'How would you rate your ability to have an erection during the last 4 weeks?', 'How would you rate your ability to have an erection during the last 4 weeks?', 105, 16, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(154, 'How big a problem during the last 4 weeks, if any, has your ability to have an erection been for you?', 'How big a problem during the last 4 weeks, if any, has your ability to have an erection been for you?', 105, 14, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(155, 'How would you rate your ability to reach orgasm (climax) during the last 4 weeks?', 'How would you rate your ability to reach orgasm (climax) during the last 4 weeks?', 106, 16, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(156, 'How big a problem during the last 4 weeks, if any, has your ability to reach an orgasm been for you?', 'How big a problem during the last 4 weeks, if any, has your ability to reach an orgasm been for you?', 106, 14, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(157, 'How would you describe the usual quality of your erections during the last 4 weeks?', 'How would you describe the usual quality of your erections during the last 4 weeks?', 107, 17, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(158, 'How would you describe the prequency of your erections during the last 4 weeks?', 'How would you describe the prequency of your erections during the last 4 weeks?', 108, 18, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(159, 'How often have you awakened in the morning or night with an erection during the last 4 weeks?', 'How often have you awakened in the morning or night with an erection during the last 4 weeks?', 109, 19, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(160, 'During the last 4 weeks, how often did you have sexual activity?', 'During the last 4 weeks, how often did you have sexual activity?', 110, 19, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(161, 'During the last 4 weeks, how often did you have sexual intercourse?', 'During the last 4 weeks, how often did you have sexual intercourse?', 111, 19, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(162, 'Overall, how would you rate your ability to function sexually during the last 4 weeks?', 'Overall, how would you rate your ability to function sexually during the last 4 weeks?', 112, 16, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(163, 'Overall, how big a problem has your sexual function or lack of sexual function been for during the last 4 weeks?', 'Overall, how big a problem has your sexual function or lack of sexual function been for during the last 4 weeks?', 113, 14, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(164, 'Over the last 4 weeks, how often have you experienced hot flashes?', 'Over the last 4 weeks, how often have you experienced hot flashes?', 114, 10, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(165, 'How big a problem during the last 4 weeks, if any, have hot flashes been for you?', 'How big a problem during the last 4 weeks, if any, have hot flashes been for you?', 114, 14, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(166, 'How often have you had breast tenderness during the last 4 weeks?', 'How often have you had breast tenderness during the last 4 weeks?', 115, 10, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(167, 'How big a problem during the last 4 weeks, if any, has breast tenderness/enlargement been for you?', 'How big a problem during the last 4 weeks, if any, has breast tenderness/enlargement been for you?', 115, 14, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(168, 'During the last 4 weeks, how often have you felt depressed?', 'During the last 4 weeks, how often have you felt depressed?', 116, 10, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(169, 'How big a problem during the last 4 weeks, if any, has feeling depressed been for you?', 'How big a problem during the last 4 weeks, if any, has feeling depressed been for you?', 116, 14, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(170, 'During the last 4 weeks, how often have you felt a lack of energy?', 'During the last 4 weeks, how often have you felt a lack of energy?', 117, 10, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(171, 'How big a problem during the last 4 weeks, if any, has lack of energy been for you?', 'How big a problem during the last 4 weeks, if any, has lack of energy been for you?', 117, 14, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(172, 'How much change in your weight have you experienced during the last 4 weeks, if any?', 'How much change in your weight have you experienced during the last 4 weeks, if any?', 118, 20, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(173, 'How big a problem during the last 4 weeks, if any, has change in body weight been for you?', 'How big a problem during the last 4 weeks, if any, has change in body weight been for you?', 118, 14, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(174, 'How big a problem during the last 4 weeks, if any, has loss of body hair been for you?', 'How big a problem during the last 4 weeks, if any, has loss of body hair been for you?', 119, 14, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(175, 'Overall, how satisfied are you with the treatment you received for your prostate cancer?', 'Overall, how satisfied are you with the treatment you received for your prostate cancer?', 120, 21, '2017-07-13 14:16:15', '2017-07-13 14:16:15', NULL, NULL),
(176, 'What number best describes your PAIN now, from no pain (0) to worst possible pain (10)?', 'What number best describes your PAIN now, from no pain (0) to worst possible pain (10)?', 121, 22, '2017-07-13 14:16:20', '2017-07-13 14:16:20', NULL, NULL),
(177, 'What number best describes your TIREDNESS now, from no tiredness (0) to worst possible tiredness (10)?', 'What number best describes your TIREDNESS now, from no tiredness (0) to worst possible tiredness (10)?', 122, 22, '2017-07-13 14:16:20', '2017-07-13 14:16:20', NULL, NULL),
(178, 'What number best describes your DROWSINESS now, from no drowsiness (0) to worst possible drowsiness (10)?', 'What number best describes your DROWSINESS now, from no drowsiness (0) to worst possible drowsiness (10)?', 123, 22, '2017-07-13 14:16:20', '2017-07-13 14:16:20', NULL, NULL),
(179, 'What number best describes your NAUSEA now, from no nausea (0) to worst possible nausea (10)?', 'What number best describes your NAUSEA now, from no nausea (0) to worst possible nausea (10)?', 124, 22, '2017-07-13 14:16:20', '2017-07-13 14:16:20', NULL, NULL),
(180, 'What number best describes your LACK OF APPETITE now, from no lack of appetite (0) to worst possible lack of appetite (10)?', 'What number best describes your LACK OF APPETITE now, from no lack of appetite (0) to worst possible lack of appetite (10)?', 125, 22, '2017-07-13 14:16:20', '2017-07-13 14:16:20', NULL, NULL),
(181, 'What number best describes your SHORTNESS OF BREATH now, from no shortness of breath (0) to worst possible shortness of breath (10)?', 'What number best describes your SHORTNESS OF BREATH now, from no shortness of breath (0) to worst possible shortness of breath (10)?', 126, 22, '2017-07-13 14:16:20', '2017-07-13 14:16:20', NULL, NULL),
(182, 'What number best describes your DEPRESSION now, from no depression (0) to worst possible depression (10)?', 'What number best describes your DEPRESSION now, from no depression (0) to worst possible depression (10)?', 127, 22, '2017-07-13 14:16:20', '2017-07-13 14:16:20', NULL, NULL),
(183, 'What number best describes your ANXIETY now, from no anxiety (0) to worst possible anxiety (10)?', 'What number best describes your ANXIETY now, from no anxiety (0) to worst possible anxiety (10)?', 128, 22, '2017-07-13 14:16:20', '2017-07-13 14:16:20', NULL, NULL),
(184, 'What number best describes your WELLBEING now, from best wellbeing (0) to worst possible wellbeing (10)?', 'What number best describes your WELLBEING now, from best wellbeing (0) to worst possible wellbeing (10)?', 129, 22, '2017-07-13 14:16:20', '2017-07-13 14:16:20', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `QuestionnaireTag`
--
DROP TABLE IF EXISTS `QuestionnaireTag`;
CREATE TABLE `QuestionnaireTag` (
  `serNum` int(11) UNSIGNED NOT NULL,
  `level` smallint(5) UNSIGNED NOT NULL,
  `name_EN` varchar(256) NOT NULL,
  `name_FR` varchar(256) NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created` timestamp NOT NULL,
  `last_updated_by` int(11) UNSIGNED DEFAULT NULL,
  `created_by` int(11) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `QuestionnaireTag`
--

INSERT INTO `QuestionnaireTag` (`serNum`, `level`, `name_EN`, `name_FR`, `last_updated`, `created`, `last_updated_by`, `created_by`) VALUES
(1, 1, 'Oncology', 'Oncology', '2017-07-25 20:10:41', '2017-07-25 20:10:41', NULL, NULL),
(2, 2, 'Medical Oncology', 'Medical Oncology', '2017-07-27 19:16:22', '2017-07-27 19:16:22', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `Questionnaire_patient`
--
DROP TABLE IF EXISTS `Questionnaire_patient`;
CREATE TABLE `Questionnaire_patient` (
  `serNum` bigint(20) UNSIGNED NOT NULL,
  `questionnaire_serNum` int(11) UNSIGNED NOT NULL,
  `patient_serNum` int(11) UNSIGNED NOT NULL,
  `user_serNum` int(11) UNSIGNED NOT NULL,
  `submitted` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Questionnaire_questiongroup`
--
DROP TABLE IF EXISTS `Questionnaire_questiongroup`;
CREATE TABLE `Questionnaire_questiongroup` (
  `questionnaire_serNum` int(11) UNSIGNED NOT NULL,
  `questiongroup_serNum` bigint(20) UNSIGNED NOT NULL,
  `position` int(11) NOT NULL,
  `optional` tinyint(1) NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created` timestamp NOT NULL,
  `last_updated_by` int(11) UNSIGNED DEFAULT NULL,
  `created_by` int(11) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Questionnaire_tag`
--
DROP TABLE IF EXISTS `Questionnaire_tag`;
CREATE TABLE `Questionnaire_tag` (
  `questionnaire_serNum` int(11) UNSIGNED NOT NULL,
  `tag_serNum` int(11) UNSIGNED NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created` timestamp NOT NULL,
  `last_updated_by` int(11) UNSIGNED DEFAULT NULL,
  `created_by` int(11) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Questionnaire_user`
--
DROP TABLE IF EXISTS `Questionnaire_user`;
CREATE TABLE `Questionnaire_user` (
  `questionnaire_serNum` int(11) UNSIGNED NOT NULL,
  `user_serNum` int(11) NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_updated_by` int(11) UNSIGNED NOT NULL,
  `created_by` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Questiongroup`
--
ALTER TABLE `Questiongroup`
  ADD PRIMARY KEY (`serNum`);

--
-- Indexes for table `Questiongroup_library`
--
ALTER TABLE `Questiongroup_library`
  ADD PRIMARY KEY (`questiongroup_serNum`,`library_serNum`),
  ADD KEY `library_serNum` (`library_serNum`);

--
-- Indexes for table `Questiongroup_tag`
--
ALTER TABLE `Questiongroup_tag`
  ADD PRIMARY KEY (`questiongroup_serNum`,`tag_serNum`),
  ADD KEY `tag_serNum` (`tag_serNum`);

--
-- Indexes for table `Questionnaire`
--
ALTER TABLE `Questionnaire`
  ADD PRIMARY KEY (`serNum`);

--
-- Indexes for table `QuestionnaireAnswer`
--
ALTER TABLE `QuestionnaireAnswer`
  ADD PRIMARY KEY (`serNum`),
  ADD KEY `question_serNum` (`question_serNum`),
  ADD KEY `answeroption_serNum` (`answeroption_serNum`),
  ADD KEY `questionnaire_patient_serNum` (`questionnaire_patient_serNum`);

--
-- Indexes for table `QuestionnaireAnswerOption`
--
ALTER TABLE `QuestionnaireAnswerOption`
  ADD PRIMARY KEY (`serNum`),
  ADD KEY `answertype_serNum` (`answertype_serNum`);

--
-- Indexes for table `QuestionnaireAnswerText`
--
ALTER TABLE `QuestionnaireAnswerText`
  ADD PRIMARY KEY (`serNum`),
  ADD KEY `answer_serNum` (`answer_serNum`);

--
-- Indexes for table `QuestionnaireAnswerType`
--
ALTER TABLE `QuestionnaireAnswerType`
  ADD PRIMARY KEY (`serNum`);

--
-- Indexes for table `QuestionnaireLibrary`
--
ALTER TABLE `QuestionnaireLibrary`
  ADD PRIMARY KEY (`serNum`);

--
-- Indexes for table `QuestionnaireQuestion`
--
ALTER TABLE `QuestionnaireQuestion`
  ADD PRIMARY KEY (`serNum`),
  ADD KEY `questiongroup_serNum` (`questiongroup_serNum`),
  ADD KEY `answertype_serNum` (`answertype_serNum`);

--
-- Indexes for table `QuestionnaireTag`
--
ALTER TABLE `QuestionnaireTag`
  ADD PRIMARY KEY (`serNum`);

--
-- Indexes for table `Questionnaire_patient`
--
ALTER TABLE `Questionnaire_patient`
  ADD PRIMARY KEY (`serNum`),
  ADD KEY `questionnaire_serNum` (`questionnaire_serNum`);

--
-- Indexes for table `Questionnaire_questiongroup`
--
ALTER TABLE `Questionnaire_questiongroup`
  ADD PRIMARY KEY (`questionnaire_serNum`,`questiongroup_serNum`),
  ADD KEY `questiongroup_serNum` (`questiongroup_serNum`);

--
-- Indexes for table `Questionnaire_tag`
--
ALTER TABLE `Questionnaire_tag`
  ADD PRIMARY KEY (`questionnaire_serNum`,`tag_serNum`),
  ADD KEY `tag_serNum` (`tag_serNum`);

--
-- Indexes for table `Questionnaire_user`
--
ALTER TABLE `Questionnaire_user`
  ADD PRIMARY KEY (`questionnaire_serNum`,`user_serNum`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Questiongroup`
--
ALTER TABLE `Questiongroup`
  MODIFY `serNum` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=145;
--
-- AUTO_INCREMENT for table `Questionnaire`
--
ALTER TABLE `Questionnaire`
  MODIFY `serNum` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;
--
-- AUTO_INCREMENT for table `QuestionnaireAnswer`
--
ALTER TABLE `QuestionnaireAnswer`
  MODIFY `serNum` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `QuestionnaireAnswerOption`
--
ALTER TABLE `QuestionnaireAnswerOption`
  MODIFY `serNum` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=122;
--
-- AUTO_INCREMENT for table `QuestionnaireAnswerText`
--
ALTER TABLE `QuestionnaireAnswerText`
  MODIFY `serNum` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `QuestionnaireAnswerType`
--
ALTER TABLE `QuestionnaireAnswerType`
  MODIFY `serNum` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;
--
-- AUTO_INCREMENT for table `QuestionnaireLibrary`
--
ALTER TABLE `QuestionnaireLibrary`
  MODIFY `serNum` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT for table `QuestionnaireQuestion`
--
ALTER TABLE `QuestionnaireQuestion`
  MODIFY `serNum` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=188;
--
-- AUTO_INCREMENT for table `QuestionnaireTag`
--
ALTER TABLE `QuestionnaireTag`
  MODIFY `serNum` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
--
-- AUTO_INCREMENT for table `Questionnaire_patient`
--
ALTER TABLE `Questionnaire_patient`
  MODIFY `serNum` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `Questiongroup_library`
--
ALTER TABLE `Questiongroup_library`
  ADD CONSTRAINT `Questiongroup_library_ibfk_1` FOREIGN KEY (`questiongroup_serNum`) REFERENCES `Questiongroup` (`serNum`) ON UPDATE CASCADE,
  ADD CONSTRAINT `Questiongroup_library_ibfk_2` FOREIGN KEY (`library_serNum`) REFERENCES `QuestionnaireLibrary` (`serNum`) ON UPDATE CASCADE;

--
-- Constraints for table `Questiongroup_tag`
--
ALTER TABLE `Questiongroup_tag`
  ADD CONSTRAINT `Questiongroup_tag_ibfk_1` FOREIGN KEY (`questiongroup_serNum`) REFERENCES `Questiongroup` (`serNum`) ON UPDATE CASCADE,
  ADD CONSTRAINT `Questiongroup_tag_ibfk_2` FOREIGN KEY (`tag_serNum`) REFERENCES `QuestionnaireTag` (`serNum`) ON UPDATE CASCADE;

--
-- Constraints for table `QuestionnaireAnswer`
--
ALTER TABLE `QuestionnaireAnswer`
  ADD CONSTRAINT `QuestionnaireAnswer_ibfk_1` FOREIGN KEY (`question_serNum`) REFERENCES `QuestionnaireQuestion` (`serNum`) ON UPDATE CASCADE,
  ADD CONSTRAINT `QuestionnaireAnswer_ibfk_2` FOREIGN KEY (`answeroption_serNum`) REFERENCES `QuestionnaireAnswerOption` (`serNum`) ON UPDATE CASCADE,
  ADD CONSTRAINT `QuestionnaireAnswer_ibfk_3` FOREIGN KEY (`questionnaire_patient_serNum`) REFERENCES `Questionnaire_patient` (`serNum`) ON UPDATE CASCADE;

--
-- Constraints for table `QuestionnaireAnswerOption`
--
ALTER TABLE `QuestionnaireAnswerOption`
  ADD CONSTRAINT `QuestionnaireAnswerOption_ibfk_1` FOREIGN KEY (`answertype_serNum`) REFERENCES `QuestionnaireAnswerType` (`serNum`) ON UPDATE CASCADE;

--
-- Constraints for table `QuestionnaireQuestion`
--
ALTER TABLE `QuestionnaireQuestion`
  ADD CONSTRAINT `QuestionnaireQuestion_ibfk_1` FOREIGN KEY (`questiongroup_serNum`) REFERENCES `Questiongroup` (`serNum`) ON UPDATE CASCADE,
  ADD CONSTRAINT `QuestionnaireQuestion_ibfk_2` FOREIGN KEY (`answertype_serNum`) REFERENCES `QuestionnaireAnswerType` (`serNum`) ON UPDATE CASCADE;

--
-- Constraints for table `Questionnaire_patient`
--
ALTER TABLE `Questionnaire_patient`
  ADD CONSTRAINT `questionnaire_patient_ibfk_1` FOREIGN KEY (`questionnaire_serNum`) REFERENCES `Questionnaire` (`serNum`);

--
-- Constraints for table `Questionnaire_questiongroup`
--
ALTER TABLE `Questionnaire_questiongroup`
  ADD CONSTRAINT `questionnaire_questiongroup_ibfk_1` FOREIGN KEY (`questionnaire_serNum`) REFERENCES `Questionnaire` (`serNum`) ON UPDATE CASCADE,
  ADD CONSTRAINT `questionnaire_questiongroup_ibfk_2` FOREIGN KEY (`questiongroup_serNum`) REFERENCES `Questiongroup` (`serNum`) ON UPDATE CASCADE;

--
-- Constraints for table `Questionnaire_tag`
--
ALTER TABLE `Questionnaire_tag`
  ADD CONSTRAINT `questionnaire_tag_ibfk_1` FOREIGN KEY (`questionnaire_serNum`) REFERENCES `Questionnaire` (`serNum`) ON UPDATE CASCADE,
  ADD CONSTRAINT `questionnaire_tag_ibfk_2` FOREIGN KEY (`tag_serNum`) REFERENCES `QuestionnaireTag` (`serNum`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;