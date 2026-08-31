-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 31, 2026 at 05:06 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fieldit_hub`
--

-- --------------------------------------------------------

--
-- Table structure for table `ai_conversations`
--

CREATE TABLE `ai_conversations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(200) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ai_feedback`
--

CREATE TABLE `ai_feedback` (
  `id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` enum('helpful','not_helpful') NOT NULL,
  `solved` enum('yes','partial','no') DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ai_messages`
--

CREATE TABLE `ai_messages` (
  `id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `role` enum('user','assistant') NOT NULL,
  `content` text NOT NULL,
  `source` varchar(100) DEFAULT NULL,
  `tokens_used` int(11) DEFAULT 0,
  `response_time_ms` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attachments`
--

CREATE TABLE `attachments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `related_type` varchar(50) DEFAULT NULL,
  `related_id` int(11) DEFAULT NULL,
  `original_name` varchar(255) NOT NULL,
  `stored_name` varchar(255) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `file_size` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `resource_type` varchar(50) DEFAULT NULL,
  `resource_id` int(11) DEFAULT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `resource_type`, `resource_id`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, NULL, 'LOGIN_FAILED', 'auth', NULL, '{\"email\":\"admin@fieldit.local\"}', '::1', NULL, '2026-08-23 20:56:39'),
(2, NULL, 'LOGIN_FAILED', 'auth', NULL, '{\"email\":\"admin@fieldit.local\"}', '::1', NULL, '2026-08-23 20:56:43'),
(3, 1, 'LOGIN', 'auth', NULL, '{\"method\":\"password\"}', '::1', NULL, '2026-08-23 20:58:50'),
(4, 1, 'LOGIN', 'auth', NULL, '{\"method\":\"password\"}', '::1', NULL, '2026-08-29 09:20:36'),
(5, 1, 'LOGIN', 'auth', NULL, '{\"method\":\"password\"}', '::1', NULL, '2026-08-29 09:38:50'),
(6, 1, 'LOGIN', 'auth', NULL, '{\"method\":\"password\"}', '::1', NULL, '2026-08-29 10:00:51'),
(7, 1, 'LOGIN', 'auth', NULL, '{\"method\":\"password\"}', '::1', NULL, '2026-08-29 10:03:42'),
(8, 1, 'LOGIN', 'auth', NULL, '{\"method\":\"password\"}', '::1', NULL, '2026-08-29 10:06:41'),
(9, 1, 'LOGIN', 'auth', NULL, '{\"method\":\"password\"}', '::1', NULL, '2026-08-29 10:11:50'),
(10, 1, 'LOGIN', 'auth', NULL, '{\"method\":\"password\"}', '::1', NULL, '2026-08-29 10:21:33'),
(11, 1, 'LOGIN', 'auth', NULL, '{\"method\":\"password\"}', '::1', NULL, '2026-08-29 10:21:53'),
(12, 1, 'LOGIN', 'auth', NULL, '{\"method\":\"password\"}', '::1', NULL, '2026-08-29 10:55:05'),
(13, 1, 'LOGIN', 'auth', NULL, '{\"method\":\"password\"}', '::1', NULL, '2026-08-29 10:57:28'),
(14, 1, 'LOGIN', 'auth', NULL, '{\"method\":\"password\"}', '::1', NULL, '2026-08-29 10:57:54'),
(15, 1, 'LOGIN', 'auth', NULL, '{\"method\":\"password\"}', '::1', NULL, '2026-08-29 11:04:39'),
(16, 1, 'LOGIN', 'auth', NULL, '{\"method\":\"password\"}', '::1', NULL, '2026-08-29 11:17:40'),
(17, 1, 'LOGIN', 'auth', NULL, '{\"method\":\"password\"}', '::1', NULL, '2026-08-29 23:47:56'),
(18, 1, 'LOGIN', 'auth', NULL, '{\"method\":\"password\"}', '::1', NULL, '2026-08-29 23:54:47'),
(19, 1, 'LOGIN', 'auth', NULL, '{\"method\":\"password\"}', '::1', NULL, '2026-08-30 19:32:54'),
(20, 1, 'LOGIN', 'auth', NULL, '{\"method\":\"password\"}', '::1', NULL, '2026-08-31 02:37:32');

-- --------------------------------------------------------

--
-- Table structure for table `chat_conversations`
--

CREATE TABLE `chat_conversations` (
  `id` int(11) NOT NULL,
  `type` enum('direct','group','channel') DEFAULT 'direct',
  `name` varchar(100) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `attachment_url` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat_participants`
--

CREATE TABLE `chat_participants` (
  `id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `commands`
--

CREATE TABLE `commands` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `command` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `when_to_use` text DEFAULT NULL,
  `example` text DEFAULT NULL,
  `expected_output` text DEFAULT NULL,
  `common_errors` text DEFAULT NULL,
  `next_steps` text DEFAULT NULL,
  `risk_level` enum('safe','caution','danger') DEFAULT 'safe',
  `is_powershell` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `commands`
--

INSERT INTO `commands` (`id`, `category_id`, `command`, `description`, `when_to_use`, `example`, `expected_output`, `common_errors`, `next_steps`, `risk_level`, `is_powershell`, `sort_order`, `created_at`) VALUES
(2, 1, 'ipconfig /release', 'Releases current DHCP IP address', 'Network issues, IP conflict', 'ipconfig /release', NULL, NULL, NULL, 'safe', 0, 0, '2026-08-29 10:17:52'),
(3, 1, 'ipconfig /renew', 'Requests new IP from DHCP', 'After releasing IP', 'ipconfig /renew', NULL, NULL, NULL, 'safe', 0, 0, '2026-08-29 10:17:52'),
(4, 1, 'ipconfig /flushdns', 'Clears DNS resolver cache', 'DNS issues, website not loading', 'ipconfig /flushdns', NULL, NULL, NULL, 'safe', 0, 0, '2026-08-29 10:17:52'),
(5, 1, 'netsh winsock reset', 'Resets Winsock catalog', 'Network not working, can connect but no internet', 'netsh winsock reset', NULL, NULL, NULL, 'safe', 0, 0, '2026-08-29 10:17:52'),
(6, 1, 'ping 8.8.8.8', 'Tests internet connectivity', 'Checking if internet works', 'ping 8.8.8.8', NULL, NULL, NULL, 'safe', 0, 0, '2026-08-29 10:17:52'),
(7, 2, 'sfc /scannow', 'Scans and repairs system files', 'BSOD, crashes, missing DLLs', 'sfc /scannow', NULL, NULL, NULL, 'safe', 0, 0, '2026-08-29 10:17:52'),
(8, 2, 'DISM /Online /Cleanup-Image /RestoreHealth', 'Repairs Windows image', 'SFC cannot fix issues', 'DISM /Online /Cleanup-Image /RestoreHealth', NULL, NULL, NULL, 'safe', 0, 0, '2026-08-29 10:17:52'),
(9, 2, 'shutdown /r /t 0', 'Immediate restart', 'Need quick restart', 'shutdown /r /t 0', NULL, NULL, NULL, 'safe', 0, 0, '2026-08-29 10:17:52'),
(10, 2, 'msconfig', 'System Configuration utility', 'Manage startup, clean boot', 'msconfig', NULL, NULL, NULL, 'safe', 0, 0, '2026-08-29 10:17:52'),
(11, 3, 'chkdsk C: /f /r', 'Checks and repairs disk errors', 'Disk errors, slow access', 'chkdsk C: /f /r', NULL, NULL, NULL, 'safe', 0, 0, '2026-08-29 10:17:52'),
(12, 3, 'wmic diskdrive get status', 'Checks hard drive health', 'Suspected disk issues', 'wmic diskdrive get status', NULL, NULL, NULL, 'safe', 0, 0, '2026-08-29 10:17:52'),
(13, 4, 'net stop spooler && net start spooler', 'Restarts print spooler', 'Printer offline, stuck jobs', 'net stop spooler && net start spooler', NULL, NULL, NULL, 'safe', 0, 0, '2026-08-29 10:17:52'),
(14, 5, 'net user username newpassword', 'Resets Windows password', 'User locked out', 'net user john P@ss123', NULL, NULL, NULL, 'safe', 0, 0, '2026-08-29 10:17:52'),
(15, 2, 'taskkill /F /IM process.exe', 'Force kills a process', 'Unresponsive app', 'taskkill /F /IM chrome.exe', NULL, NULL, NULL, 'safe', 0, 0, '2026-08-29 10:17:52'),
(16, 2, 'powercfg /batteryreport', 'Generates battery health report', 'Laptop battery issues', 'powercfg /batteryreport', NULL, NULL, NULL, 'safe', 0, 0, '2026-08-29 10:17:52'),
(17, 1, 'ipconfig /release', 'Releases current DHCP IP address', 'Network issues, IP conflict', 'ipconfig /release', NULL, NULL, NULL, 'safe', 0, 0, '2026-08-29 10:19:28'),
(18, 1, 'ipconfig /renew', 'Requests new IP from DHCP', 'After releasing IP', 'ipconfig /renew', NULL, NULL, NULL, 'safe', 0, 0, '2026-08-29 10:19:28'),
(19, 1, 'ipconfig /flushdns', 'Clears DNS resolver cache', 'DNS issues, website not loading', 'ipconfig /flushdns', NULL, NULL, NULL, 'safe', 0, 0, '2026-08-29 10:19:28'),
(20, 1, 'netsh winsock reset', 'Resets Winsock catalog', 'Network not working, can connect but no internet', 'netsh winsock reset', NULL, NULL, NULL, 'safe', 0, 0, '2026-08-29 10:19:28'),
(21, 1, 'ping 8.8.8.8', 'Tests internet connectivity', 'Checking if internet works', 'ping 8.8.8.8', NULL, NULL, NULL, 'safe', 0, 0, '2026-08-29 10:19:28'),
(22, 2, 'sfc /scannow', 'Scans and repairs system files', 'BSOD, crashes, missing DLLs', 'sfc /scannow', NULL, NULL, NULL, 'safe', 0, 0, '2026-08-29 10:19:28'),
(23, 2, 'DISM /Online /Cleanup-Image /RestoreHealth', 'Repairs Windows image', 'SFC cannot fix issues', 'DISM /Online /Cleanup-Image /RestoreHealth', NULL, NULL, NULL, 'safe', 0, 0, '2026-08-29 10:19:28'),
(24, 2, 'shutdown /r /t 0', 'Immediate restart', 'Need quick restart', 'shutdown /r /t 0', NULL, NULL, NULL, 'safe', 0, 0, '2026-08-29 10:19:28'),
(25, 2, 'msconfig', 'System Configuration utility', 'Manage startup, clean boot', 'msconfig', NULL, NULL, NULL, 'safe', 0, 0, '2026-08-29 10:19:28'),
(26, 3, 'chkdsk C: /f /r', 'Checks and repairs disk errors', 'Disk errors, slow access', 'chkdsk C: /f /r', NULL, NULL, NULL, 'safe', 0, 0, '2026-08-29 10:19:28'),
(27, 3, 'wmic diskdrive get status', 'Checks hard drive health', 'Suspected disk issues', 'wmic diskdrive get status', NULL, NULL, NULL, 'safe', 0, 0, '2026-08-29 10:19:28'),
(28, 4, 'net stop spooler && net start spooler', 'Restarts print spooler', 'Printer offline, stuck jobs', 'net stop spooler && net start spooler', NULL, NULL, NULL, 'safe', 0, 0, '2026-08-29 10:19:28'),
(29, 5, 'net user username newpassword', 'Resets Windows password', 'User locked out', 'net user john P@ss123', NULL, NULL, NULL, 'safe', 0, 0, '2026-08-29 10:19:28'),
(30, 2, 'taskkill /F /IM process.exe', 'Force kills a process', 'Unresponsive app', 'taskkill /F /IM chrome.exe', NULL, NULL, NULL, 'safe', 0, 0, '2026-08-29 10:19:28'),
(31, 2, 'powercfg /batteryreport', 'Generates battery health report', 'Laptop battery issues', 'powercfg /batteryreport', NULL, NULL, NULL, 'safe', 0, 0, '2026-08-29 10:19:28'),
(32, 1, 'ipconfig /release', 'Releases current DHCP IP address', 'Network issues, IP conflict', 'ipconfig /release', NULL, NULL, NULL, 'safe', 0, 0, '2026-08-29 10:20:27'),
(33, 1, 'ipconfig /renew', 'Requests new IP from DHCP', 'After releasing IP', 'ipconfig /renew', NULL, NULL, NULL, 'safe', 0, 0, '2026-08-29 10:20:27'),
(34, 1, 'ipconfig /flushdns', 'Clears DNS resolver cache', 'DNS issues, website not loading', 'ipconfig /flushdns', NULL, NULL, NULL, 'safe', 0, 0, '2026-08-29 10:20:27'),
(35, 1, 'netsh winsock reset', 'Resets Winsock catalog', 'Network not working, can connect but no internet', 'netsh winsock reset', NULL, NULL, NULL, 'safe', 0, 0, '2026-08-29 10:20:27'),
(36, 1, 'ping 8.8.8.8', 'Tests internet connectivity', 'Checking if internet works', 'ping 8.8.8.8', NULL, NULL, NULL, 'safe', 0, 0, '2026-08-29 10:20:27'),
(37, 2, 'sfc /scannow', 'Scans and repairs system files', 'BSOD, crashes, missing DLLs', 'sfc /scannow', NULL, NULL, NULL, 'safe', 0, 0, '2026-08-29 10:20:27'),
(38, 2, 'DISM /Online /Cleanup-Image /RestoreHealth', 'Repairs Windows image', 'SFC cannot fix issues', 'DISM /Online /Cleanup-Image /RestoreHealth', NULL, NULL, NULL, 'safe', 0, 0, '2026-08-29 10:20:27'),
(39, 2, 'shutdown /r /t 0', 'Immediate restart', 'Need quick restart', 'shutdown /r /t 0', NULL, NULL, NULL, 'safe', 0, 0, '2026-08-29 10:20:27'),
(40, 2, 'msconfig', 'System Configuration utility', 'Manage startup, clean boot', 'msconfig', NULL, NULL, NULL, 'safe', 0, 0, '2026-08-29 10:20:27'),
(41, 3, 'chkdsk C: /f /r', 'Checks and repairs disk errors', 'Disk errors, slow access', 'chkdsk C: /f /r', NULL, NULL, NULL, 'safe', 0, 0, '2026-08-29 10:20:27'),
(42, 3, 'wmic diskdrive get status', 'Checks hard drive health', 'Suspected disk issues', 'wmic diskdrive get status', NULL, NULL, NULL, 'safe', 0, 0, '2026-08-29 10:20:27'),
(43, 4, 'net stop spooler && net start spooler', 'Restarts print spooler', 'Printer offline, stuck jobs', 'net stop spooler && net start spooler', NULL, NULL, NULL, 'safe', 0, 0, '2026-08-29 10:20:27'),
(44, 5, 'net user username newpassword', 'Resets Windows password', 'User locked out', 'net user john P@ss123', NULL, NULL, NULL, 'safe', 0, 0, '2026-08-29 10:20:27'),
(45, 2, 'taskkill /F /IM process.exe', 'Force kills a process', 'Unresponsive app', 'taskkill /F /IM chrome.exe', NULL, NULL, NULL, 'safe', 0, 0, '2026-08-29 10:20:27'),
(46, 2, 'powercfg /batteryreport', 'Generates battery health report', 'Laptop battery issues', 'powercfg /batteryreport', NULL, NULL, NULL, 'safe', 0, 0, '2026-08-29 10:20:27');

-- --------------------------------------------------------

--
-- Table structure for table `command_categories`
--

CREATE TABLE `command_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `command_categories`
--

INSERT INTO `command_categories` (`id`, `name`, `slug`, `created_at`) VALUES
(1, 'Network', 'network', '2026-08-29 10:14:40'),
(2, 'System', 'system', '2026-08-29 10:14:40'),
(3, 'Disk', 'disk', '2026-08-29 10:14:40'),
(4, 'Printer', 'printer', '2026-08-29 10:14:40'),
(5, 'Security', 'security', '2026-08-29 10:14:40');

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `role` varchar(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `viber` varchar(100) DEFAULT NULL,
  `is_supervisor` tinyint(1) DEFAULT 0,
  `is_manager` tinyint(1) DEFAULT 0,
  `visibility` enum('department','organization') DEFAULT 'department',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `decision_nodes`
--

CREATE TABLE `decision_nodes` (
  `id` int(11) NOT NULL,
  `issue_id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `yes_next` int(11) DEFAULT NULL,
  `no_next` int(11) DEFAULT NULL,
  `question` text NOT NULL,
  `description` text DEFAULT NULL,
  `risk` varchar(20) DEFAULT 'safe',
  `node_type` varchar(30) DEFAULT 'question',
  `step_order` int(11) DEFAULT 10,
  `visual_guide` text DEFAULT NULL,
  `expected_result` text DEFAULT NULL,
  `tools_needed` text DEFAULT NULL,
  `why_answer` text DEFAULT NULL,
  `device_type` varchar(50) DEFAULT 'all',
  `visibility_mode` varchar(30) DEFAULT 'always',
  `visible_for_question_id` int(11) DEFAULT NULL,
  `is_terminal` tinyint(1) DEFAULT 0,
  `result_type` varchar(50) DEFAULT NULL,
  `result_solution` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `decision_nodes`
--

INSERT INTO `decision_nodes` (`id`, `issue_id`, `parent_id`, `yes_next`, `no_next`, `question`, `description`, `risk`, `node_type`, `step_order`, `visual_guide`, `expected_result`, `tools_needed`, `why_answer`, `device_type`, `visibility_mode`, `visible_for_question_id`, `is_terminal`, `result_type`, `result_solution`, `created_at`) VALUES
(1, 1, NULL, NULL, NULL, 'Is the monitor power light on?', 'Check if the monitor has power. Look at the power LED on the front.', 'safe', 'question', 1, NULL, NULL, NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(2, 1, NULL, NULL, NULL, 'Does the PC show any signs of life?', 'Check if fans spin, power LED is on, any beeps when you press power.', 'safe', 'question', 2, NULL, NULL, NULL, NULL, 'all', 'always', 1, 0, NULL, NULL, '2026-08-29 09:37:21'),
(3, 1, NULL, NULL, NULL, 'Does the monitor show No Signal or completely black?', 'Look closely — No Signal message or just black screen with power light on?', 'safe', 'question', 3, NULL, NULL, NULL, NULL, 'all', 'always', 2, 0, NULL, NULL, '2026-08-29 09:37:21'),
(4, 1, 1, NULL, NULL, 'Check monitor power cable', 'Ensure monitor power cable is firmly plugged into both monitor and wall outlet.', 'safe', 'step', 4, NULL, 'Power light turns on', NULL, NULL, 'all', 'no', 1, 0, NULL, NULL, '2026-08-29 09:37:21'),
(5, 1, 1, NULL, NULL, 'Test with different outlet', 'Plug monitor into a different outlet to rule out bad outlet.', 'safe', 'step', 5, NULL, 'Monitor powers on', NULL, NULL, 'all', 'no', 1, 0, NULL, NULL, '2026-08-29 09:37:21'),
(6, 1, 1, NULL, NULL, 'Try a different monitor', 'Connect a spare monitor to the same PC. If it works, original monitor is faulty.', 'safe', 'step', 6, NULL, 'Spare monitor shows display', 'Spare monitor', NULL, 'all', 'no', 1, 0, NULL, NULL, '2026-08-29 09:37:21'),
(7, 1, 2, NULL, NULL, 'Check video cable connection', 'Unplug video cable from both PC and monitor. Plug back firmly. Make sure it clicks.', 'safe', 'step', 7, NULL, 'Display appears or cable visibly damaged', 'Video cable', NULL, 'all', 'always', 2, 0, NULL, NULL, '2026-08-29 09:37:21'),
(8, 1, 2, NULL, NULL, 'Try different video cable', 'Swap with a spare cable if available. Old cables can cause no display.', 'safe', 'step', 8, NULL, 'Display works with new cable', 'Spare cable', NULL, 'all', 'always', 2, 0, NULL, NULL, '2026-08-29 09:37:21'),
(9, 1, 2, NULL, NULL, 'Try different GPU port', 'Connect to a different video output (HDMI, DP, VGA) on the GPU.', 'safe', 'step', 9, NULL, 'Display works on different port', 'Video cable', NULL, 'desktop', 'always', 2, 0, NULL, NULL, '2026-08-29 09:37:21'),
(10, 1, 2, NULL, NULL, 'Press monitor input/source button', 'Cycle through inputs (HDMI1, HDMI2, DP) using the monitor buttons.', 'safe', 'step', 10, NULL, 'Monitor shows correct input', NULL, NULL, 'all', 'always', 2, 0, NULL, NULL, '2026-08-29 09:37:21'),
(11, 1, 2, NULL, NULL, 'Reseat the RAM', 'Power off, unplug, open case. Remove RAM sticks and reinsert firmly until they click.', 'low', 'step', 11, NULL, 'PC boots normally with display', 'Screwdriver', NULL, 'desktop', 'always', 2, 0, NULL, NULL, '2026-08-29 09:37:21'),
(12, 1, 2, NULL, NULL, 'Reseat the GPU', 'Remove GPU from PCIe slot and reinsert. Check GPU power connectors.', 'low', 'step', 12, NULL, 'Display works after reseating GPU', 'Screwdriver', NULL, 'desktop', 'always', 2, 0, NULL, NULL, '2026-08-29 09:37:21'),
(13, 1, 2, NULL, NULL, 'Try integrated graphics', 'Connect monitor to motherboard video output (if CPU supports it).', 'safe', 'step', 13, NULL, 'Display works via integrated graphics', 'Video cable', NULL, 'desktop', 'always', 2, 0, NULL, NULL, '2026-08-29 09:37:21'),
(14, 1, NULL, NULL, NULL, 'Monitor needs replacement', 'Monitor is not getting power or is internally faulty.', 'escalate', '', 20, NULL, NULL, NULL, NULL, 'all', 'no', NULL, 0, 'escalate', 'Replace the monitor. Check warranty status.', '2026-08-29 09:37:21'),
(15, 1, NULL, NULL, NULL, 'GPU needs replacement', 'GPU not outputting video. Try spare GPU or escalate.', 'escalate', '', 21, NULL, NULL, NULL, NULL, 'desktop', 'always', NULL, 0, 'escalate', 'Test with spare GPU. If none available, escalate for replacement.', '2026-08-29 09:37:21'),
(16, 1, NULL, NULL, NULL, 'Motherboard issue', 'RAM reseat did not help. Motherboard or CPU may be faulty.', 'escalate', '', 22, NULL, NULL, NULL, NULL, 'desktop', 'always', NULL, 0, 'escalate', 'Escalate to hardware team.', '2026-08-29 09:37:21'),
(17, 1, NULL, NULL, NULL, 'Display issue resolved', 'Display is now working.', 'safe', '', 23, NULL, NULL, NULL, NULL, 'all', 'always', NULL, 0, 'solved', 'Document the fix.', '2026-08-29 09:37:21'),
(18, 2, NULL, NULL, NULL, 'Does flickering happen everywhere or just desktop?', 'Open Task Manager. If it also flickers, likely hardware. If only desktop, likely driver.', 'safe', 'question', 1, NULL, NULL, NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(19, 2, 18, NULL, NULL, 'Update or reinstall GPU driver', 'Download latest driver from NVIDIA/AMD/Intel. Install and restart.', 'safe', 'step', 2, NULL, 'Flickering stops', 'Internet, GPU driver', NULL, 'all', 'no', 18, 0, NULL, NULL, '2026-08-29 09:37:21'),
(20, 2, 18, NULL, NULL, 'Check video cable', 'Unplug and reconnect cable at both ends. Try different cable.', 'safe', 'step', 3, NULL, 'Flickering stops', 'Video cable', NULL, 'all', 'always', 18, 0, NULL, NULL, '2026-08-29 09:37:21'),
(21, 2, 18, NULL, NULL, 'Check refresh rate', 'Right-click desktop > Display > Advanced > Set recommended refresh rate.', 'safe', 'step', 4, NULL, 'Flickering stops', NULL, NULL, 'all', 'always', 18, 0, NULL, NULL, '2026-08-29 09:37:21'),
(22, 2, NULL, NULL, NULL, 'GPU hardware issue', 'Driver reinstall and cable swap did not help.', 'escalate', '', 10, NULL, NULL, NULL, NULL, 'all', 'always', NULL, 0, 'escalate', 'Escalate for GPU replacement.', '2026-08-29 09:37:21'),
(23, 3, NULL, NULL, NULL, 'Is the power outlet working?', 'Plug a phone charger or lamp into the same outlet. Does it work?', 'safe', 'question', 1, NULL, NULL, NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(24, 3, NULL, NULL, NULL, 'Is the power cable firmly connected?', 'Check both ends of the power cable — wall outlet and PC.', 'safe', 'question', 2, NULL, NULL, NULL, NULL, 'all', 'always', 23, 0, NULL, NULL, '2026-08-29 09:37:21'),
(25, 3, NULL, NULL, NULL, 'Is the PSU switch on? (Desktop)', 'Back of desktop — PSU switch should be in the I position.', 'safe', 'question', 3, NULL, NULL, NULL, NULL, 'desktop', 'always', 24, 0, NULL, NULL, '2026-08-29 09:37:21'),
(26, 3, 23, NULL, NULL, 'Try a different outlet', 'Plug computer into a different outlet.', 'safe', 'step', 4, NULL, 'Computer turns on', NULL, NULL, 'all', 'no', 23, 0, NULL, NULL, '2026-08-29 09:37:21'),
(27, 3, 24, NULL, NULL, 'Reseat power cable at PC end', 'Unplug and replug power cable firmly.', 'safe', 'step', 5, NULL, 'Cable firmly connected', NULL, NULL, 'all', 'always', 24, 0, NULL, NULL, '2026-08-29 09:37:21'),
(28, 3, 25, NULL, NULL, 'Flip PSU switch to I', 'Make sure PSU switch is in I (on) position, not O.', 'safe', 'step', 6, NULL, 'Computer powers on', NULL, NULL, 'desktop', 'always', 25, 0, NULL, NULL, '2026-08-29 09:37:21'),
(29, 3, NULL, NULL, NULL, 'Perform power drain', 'Unplug power cable. Hold power button 30 seconds. Plug back in and try.', 'safe', 'step', 7, NULL, 'Computer turns on after drain', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(30, 3, NULL, NULL, NULL, 'Check internal power connections', 'Open case. Check 24-pin motherboard connector and 8-pin CPU power connector are seated.', 'low', 'step', 8, NULL, 'Connectors firmly seated', 'Screwdriver', NULL, 'desktop', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(31, 3, NULL, NULL, NULL, 'Test PSU with multimeter', 'Test PSU voltages on 24-pin connector: 12V (yellow), 5V (red), 3.3V (orange).', 'low', 'step', 9, NULL, 'Voltages within spec', 'Multimeter', NULL, 'desktop', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(32, 3, NULL, NULL, NULL, 'Test with spare PSU', 'Swap PSU with known-good unit.', 'low', 'step', 10, NULL, 'Computer boots with new PSU', 'Spare PSU, Screwdriver', NULL, 'desktop', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(33, 3, NULL, NULL, NULL, 'PSU is dead', 'PSU not providing power.', 'escalate', '', 15, NULL, NULL, NULL, NULL, 'desktop', 'always', NULL, 0, 'escalate', 'Replace PSU. Check wattage requirements.', '2026-08-29 09:37:21'),
(34, 3, NULL, NULL, NULL, 'Motherboard is dead', 'PSU is fine but motherboard does not respond.', 'escalate', '', 16, NULL, NULL, NULL, NULL, 'desktop', 'always', NULL, 0, 'escalate', 'Escalate for motherboard replacement.', '2026-08-29 09:37:21'),
(35, 3, NULL, NULL, NULL, 'Laptop battery issue', 'Laptop does not turn on even when plugged in.', 'escalate', '', 17, NULL, NULL, NULL, NULL, 'laptop', 'always', NULL, 0, 'escalate', 'Try running without battery. If works, replace battery. Otherwise escalate.', '2026-08-29 09:37:21'),
(36, 3, NULL, NULL, NULL, 'Power issue resolved', 'Computer turns on normally.', 'safe', '', 18, NULL, NULL, NULL, NULL, 'all', 'always', NULL, 0, 'solved', 'Document the root cause.', '2026-08-29 09:37:21'),
(37, 4, NULL, NULL, NULL, 'Check CPU fan connection', 'Make sure CPU fan is connected to CPU_FAN header on motherboard.', 'low', 'step', 2, NULL, 'Fan connected and spins', NULL, NULL, 'desktop', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(38, 4, NULL, NULL, NULL, 'Reapply thermal paste', 'Remove cooler, clean old paste with alcohol, apply new pea-sized dot, remount.', 'medium', 'step', 3, NULL, 'CPU temp normal', 'Thermal paste, Alcohol', NULL, 'desktop', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(39, 4, NULL, NULL, NULL, 'Reseat RAM', 'Remove all RAM. Power on with no RAM — listen for beeps. Insert one stick at a time.', 'low', 'step', 4, NULL, 'PC boots with good RAM', NULL, NULL, 'desktop', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(40, 4, NULL, NULL, NULL, 'Clear CMOS', 'Remove CMOS battery for 30 seconds. Or use CLR_CMOS jumper.', 'safe', 'step', 5, NULL, 'PC boots with reset BIOS', 'Screwdriver', NULL, 'desktop', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(41, 4, NULL, NULL, NULL, 'Test with spare PSU', 'Swap PSU with known-good unit.', 'low', 'step', 6, NULL, 'PC boots with new PSU', 'Spare PSU', NULL, 'desktop', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(42, 4, NULL, NULL, NULL, 'CPU or motherboard failure', 'Nothing worked. Hardware failure.', 'escalate', '', 12, NULL, NULL, NULL, NULL, 'desktop', 'always', NULL, 0, 'escalate', 'Escalate for hardware inspection.', '2026-08-29 09:37:21'),
(43, 5, NULL, NULL, NULL, 'Is the volume muted or very low?', 'Check speaker icon in system tray. Make sure not muted and volume at least 30%.', 'safe', 'question', 1, NULL, NULL, NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(44, 5, 43, NULL, NULL, 'Unmute and increase volume', 'Click speaker icon. Remove mute. Drag volume to 50%.', 'safe', 'step', 2, NULL, 'Volume is audible', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(45, 5, 43, NULL, NULL, 'Check correct output device', 'Right-click speaker > Sound settings > Output. Select correct device.', 'safe', 'step', 3, NULL, 'Correct device selected', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(46, 5, 43, NULL, NULL, 'Test different speakers/headphones', 'Plug in known-working speakers or headphones.', 'safe', 'step', 4, NULL, 'Audio works with different device', 'Working speakers', NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(47, 5, 43, NULL, NULL, 'Run Windows Audio Troubleshooter', 'Right-click speaker > Troubleshoot sound problems.', 'safe', 'step', 5, NULL, 'Audio restored', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(48, 5, 43, NULL, NULL, 'Reinstall audio driver', 'Device Manager > Sound > Right-click > Uninstall. Restart to reinstall.', 'safe', 'step', 6, NULL, 'Audio restored after reboot', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(49, 5, NULL, NULL, NULL, 'Audio hardware failure', 'No sound after all software fixes.', 'escalate', '', 10, NULL, NULL, NULL, NULL, 'all', 'always', NULL, 0, 'escalate', 'Try USB audio adapter. If works, escalate for internal repair.', '2026-08-29 09:37:21'),
(50, 6, NULL, NULL, NULL, 'Check app mute button', 'Make sure microphone is not muted in the app (Zoom, Teams, etc.).', 'safe', 'step', 1, NULL, 'Microphone unmuted', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(51, 6, NULL, NULL, NULL, 'Check Windows privacy settings', 'Settings > Privacy > Microphone > Allow apps to use microphone.', 'safe', 'step', 2, NULL, 'Microphone access enabled', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(52, 6, NULL, NULL, NULL, 'Set correct input device', 'Right-click speaker > Sound settings > Input. Select correct mic.', 'safe', 'step', 3, NULL, 'Correct mic selected', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(53, 6, NULL, NULL, NULL, 'Test different microphone', 'Plug in known-working mic.', 'safe', 'step', 4, NULL, 'Working mic produces audio', 'Working mic', NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(54, 6, NULL, NULL, NULL, 'Microphone hardware failure', 'No mic audio after all checks.', 'escalate', '', 8, NULL, NULL, NULL, NULL, 'all', 'always', NULL, 0, 'escalate', 'Replace microphone or escalate.', '2026-08-29 09:37:21'),
(55, 7, NULL, NULL, NULL, 'Are other devices on the network working?', 'Check if a phone or another computer on the same network can access internet.', 'safe', 'question', 1, NULL, NULL, NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(56, 7, 55, NULL, NULL, 'Run network troubleshooter', 'Right-click network icon > Troubleshoot problems.', 'safe', 'step', 2, NULL, 'Issue identified and fixed', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(57, 7, 55, NULL, NULL, 'Renew IP address', 'CMD admin: ipconfig /release then ipconfig /renew.', 'safe', 'step', 3, NULL, 'New IP assigned', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(58, 7, 55, NULL, NULL, 'Flush DNS cache', 'CMD admin: ipconfig /flushdns. Then try a website.', 'safe', 'step', 4, NULL, 'Websites load', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(59, 7, 55, NULL, NULL, 'Ping test', 'CMD: ping 8.8.8.8. Works = DNS issue. Fails = connection issue.', 'safe', 'step', 5, NULL, 'Ping replies', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(60, 7, 55, NULL, NULL, 'Change DNS server', 'Network adapter > IPv4 > DNS: 8.8.8.8 and 8.8.4.4.', 'safe', 'step', 6, NULL, 'Websites load with new DNS', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(61, 7, 55, NULL, NULL, 'Reset network adapter', 'CMD admin: netsh winsock reset. Restart PC.', 'safe', 'step', 7, NULL, 'Network works after restart', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(62, 7, 55, NULL, NULL, 'Check cable or WiFi', 'Ethernet: replug cable, try different port. WiFi: forget and reconnect.', 'safe', 'step', 8, NULL, 'Connection restored', 'Ethernet cable', NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(63, 7, NULL, NULL, NULL, 'Network issue resolved', 'Internet working.', 'safe', '', 12, NULL, NULL, NULL, NULL, 'all', 'always', NULL, 0, 'solved', 'Document the fix.', '2026-08-29 09:37:21'),
(64, 7, NULL, NULL, NULL, 'Router/modem issue', 'Other devices also cannot connect.', 'escalate', '', 13, NULL, NULL, NULL, NULL, 'all', 'always', NULL, 0, 'escalate', 'Escalate to network team. May need ISP contact.', '2026-08-29 09:37:21'),
(65, 8, NULL, NULL, NULL, 'Is WiFi enabled?', 'Check WiFi switch on laptop or press Fn + WiFi key (usually F12).', 'safe', 'step', 1, NULL, 'WiFi is enabled', NULL, NULL, 'laptop', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(66, 8, NULL, NULL, NULL, 'Toggle WiFi off and on', 'WiFi icon in taskbar. Turn off, wait 10 sec, turn on.', 'safe', 'step', 2, NULL, 'WiFi networks appear', NULL, NULL, 'laptop', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(67, 8, NULL, NULL, NULL, 'Forget and reconnect', 'Settings > Network > WiFi > Manage known networks > Forget. Reconnect with password.', 'safe', 'step', 3, NULL, 'Connected successfully', NULL, NULL, 'laptop', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(68, 8, NULL, NULL, NULL, 'Restart WiFi adapter', 'Device Manager > Network > WiFi adapter > Disable > wait 10s > Enable.', 'safe', 'step', 4, NULL, 'WiFi adapter re-enabled', NULL, NULL, 'laptop', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(69, 8, NULL, NULL, NULL, 'Update WiFi driver', 'Download from laptop manufacturer. Install and restart.', 'safe', 'step', 5, NULL, 'WiFi connects', 'Internet', NULL, 'laptop', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(70, 8, NULL, NULL, NULL, 'Reset network stack', 'CMD admin: netsh winsock reset, netsh int ip reset, ipconfig /flushdns. Restart.', 'safe', 'step', 6, NULL, 'WiFi connects after restart', NULL, NULL, 'laptop', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(71, 8, NULL, NULL, NULL, 'WiFi adapter hardware failure', 'WiFi not detected or cannot connect after all fixes.', 'escalate', '', 10, NULL, NULL, NULL, NULL, 'laptop', 'always', NULL, 0, 'escalate', 'Try USB WiFi adapter as workaround.', '2026-08-29 09:37:21'),
(72, 9, NULL, NULL, NULL, 'Can you ping 8.8.8.8?', 'CMD: ping 8.8.8.8. This tests connection without DNS.', 'safe', 'question', 1, NULL, NULL, NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(73, 9, 72, NULL, NULL, 'Flush DNS cache', 'CMD admin: ipconfig /flushdns. Try website.', 'safe', 'step', 2, NULL, 'Websites load', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(74, 9, 72, NULL, NULL, 'Change DNS servers', 'IPv4 settings > DNS: 8.8.8.8 and 8.8.4.4.', 'safe', 'step', 3, NULL, 'Websites load', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(75, 9, 72, NULL, NULL, 'Restart DNS client service', 'Services.msc > DNS Client > Restart.', 'safe', 'step', 4, NULL, 'DNS resolves', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(76, 9, NULL, NULL, NULL, 'DNS resolved', 'Domain names resolve.', 'safe', '', 8, NULL, NULL, NULL, NULL, 'all', 'always', NULL, 0, 'solved', 'Document which fix worked.', '2026-08-29 09:37:21'),
(77, 10, NULL, NULL, NULL, 'Is there a link light?', 'Check LED on Ethernet port on PC and switch/router.', 'safe', 'question', 1, NULL, NULL, NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(78, 10, 77, NULL, NULL, 'Replug Ethernet cable', 'Unplug from both ends. Plug back firmly until click.', 'safe', 'step', 2, NULL, 'Link light appears', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(79, 10, 77, NULL, NULL, 'Try different cable', 'Swap with known-working Ethernet cable.', 'safe', 'step', 3, NULL, 'Link light appears', 'Spare cable', NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(80, 10, 77, NULL, NULL, 'Try different switch port', 'Plug into different port on switch/router.', 'safe', 'step', 4, NULL, 'Link light appears', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(81, 10, 77, NULL, NULL, 'Reset network adapter', 'CMD admin: netsh winsock reset && netsh int ip reset. Restart.', 'safe', 'step', 5, NULL, 'Ethernet connects', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(82, 10, NULL, NULL, NULL, 'NIC hardware failure', 'No link after all cable/port swaps.', 'escalate', '', 10, NULL, NULL, NULL, NULL, 'all', 'always', NULL, 0, 'escalate', 'Try USB Ethernet adapter. If works, escalate for NIC replacement.', '2026-08-29 09:37:21'),
(83, 11, NULL, NULL, NULL, 'Is the printer powered on?', 'Check power light or display panel.', 'safe', 'question', 1, NULL, NULL, NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(84, 11, 83, NULL, NULL, 'Restart print spooler', 'CMD admin: net stop spooler then net start spooler.', 'safe', 'step', 2, NULL, 'Print queue clears', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(85, 11, 83, NULL, NULL, 'Check printer status in Windows', 'Settings > Devices > Printers. Right-click > uncheck Use Printer Offline.', 'safe', 'step', 3, NULL, 'Printer shows online', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(86, 11, 83, NULL, NULL, 'Clear print queue', 'CMD admin: del /Q /F /S %systemroot%\\System32\\spool\\PRINTERS\\*. Restart spooler.', 'safe', 'step', 4, NULL, 'Queue is empty', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(87, 11, 83, NULL, NULL, 'Reinstall printer driver', 'Remove printer. Download driver from manufacturer. Reinstall.', 'safe', 'step', 5, NULL, 'Printer prints', 'USB cable', NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(88, 11, 83, NULL, NULL, 'Test with direct USB connection', 'Connect printer directly via USB. If works, issue is network.', 'safe', 'step', 6, NULL, 'Printer works via USB', 'USB cable', NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(89, 11, NULL, NULL, NULL, 'Printer needs service', 'Printer does not work after all fixes.', 'escalate', '', 10, NULL, NULL, NULL, NULL, 'all', 'always', NULL, 0, 'escalate', 'Check for hardware errors. Escalate for repair.', '2026-08-29 09:37:21'),
(90, 12, NULL, NULL, NULL, 'Where is the paper stuck?', 'Open covers. Look at input tray, output tray, inside printer.', 'safe', 'step', 1, NULL, 'Stuck paper located', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(91, 12, NULL, NULL, NULL, 'Remove stuck paper gently', 'Pull paper slowly in feed direction. If it tears, remove all pieces.', 'low', 'step', 2, NULL, 'Paper removed intact', 'Tweezers', NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(92, 12, NULL, NULL, NULL, 'Check for torn pieces', 'Use flashlight. Even small pieces cause recurring jams.', 'safe', 'step', 3, NULL, 'No torn pieces found', 'Flashlight', NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(93, 12, NULL, NULL, NULL, 'Check paper quality', 'Paper not wrinkled/folded. Fan stack before loading. Do not overfill.', 'safe', 'step', 4, NULL, 'Paper feeds correctly', 'Fresh paper', NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(94, 12, NULL, NULL, NULL, 'Reset printer', 'Turn off, wait 30 sec, turn on.', 'safe', 'step', 5, NULL, 'Error clears', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(95, 12, NULL, NULL, NULL, 'Printer roller issue', 'Paper jams keep happening.', 'escalate', '', 8, NULL, NULL, NULL, NULL, 'all', 'always', NULL, 0, 'escalate', 'Check pickup rollers for wear. Escalate for replacement.', '2026-08-29 09:37:21'),
(96, 13, NULL, NULL, NULL, 'Can you access printer web interface?', 'Open browser and type printer IP address.', 'safe', 'question', 1, NULL, NULL, NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(97, 13, 96, NULL, NULL, 'Uncheck Use Printer Offline', 'Settings > Devices > Printers > Right-click > See what is printing > Printer > uncheck Use Printer Offline.', 'safe', 'step', 2, NULL, 'Printer shows online', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(98, 13, 96, NULL, NULL, 'Restart print spooler', 'CMD admin: net stop spooler && net start spooler.', 'safe', 'step', 3, NULL, 'Printer shows online', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(99, 13, 96, NULL, NULL, 'Remove and re-add printer', 'Remove from Settings. Re-add with correct IP address.', 'safe', 'step', 4, NULL, 'Printer added and online', 'Printer IP', NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(100, 13, NULL, NULL, NULL, 'Printer offline resolved', 'Printer is online.', 'safe', '', 8, NULL, NULL, NULL, NULL, 'all', 'always', NULL, 0, 'solved', 'Document the fix.', '2026-08-29 09:37:21'),
(101, 14, NULL, NULL, NULL, 'Is the camera getting power?', 'Check camera LED. For PoE, check port link light on switch/NVR.', 'safe', 'question', 1, NULL, NULL, NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(102, 14, 101, NULL, NULL, 'Check NVR/DVR storage', 'Log into NVR. Check disk space. Full disk = no recording.', 'safe', 'step', 2, NULL, 'Disk has space', 'Monitor', NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(103, 14, 101, NULL, NULL, 'Restart NVR/DVR', 'Power cycle — unplug, wait 30 sec, plug back.', 'safe', 'step', 3, NULL, 'Recording resumes', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(104, 14, 101, NULL, NULL, 'Check camera network', 'Ping camera IP from PC. If fails, check cable and port.', 'safe', 'step', 4, NULL, 'Camera responds to ping', 'Network cable', NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(105, 14, 101, NULL, NULL, 'Re-add camera to NVR', 'NVR settings > Remove channel > Re-add with IP and credentials.', 'safe', 'step', 5, NULL, 'Camera shows live feed', 'NVR interface', NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(106, 14, NULL, NULL, NULL, 'Camera hardware failure', 'Not detected after all checks.', 'escalate', '', 10, NULL, NULL, NULL, NULL, 'all', 'always', NULL, 0, 'escalate', 'Try different port. If still not detected, replace camera.', '2026-08-29 09:37:21'),
(107, 15, NULL, NULL, NULL, 'Can you access NVR locally?', 'Connect monitor to NVR. Check local access.', 'safe', 'question', 1, NULL, NULL, NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(108, 15, 107, NULL, NULL, 'Check NVR network settings', 'Verify IP, gateway, DNS. Same subnet as router.', 'safe', 'step', 2, NULL, 'NVR has valid config', 'NVR interface', NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(109, 15, 107, NULL, NULL, 'Check port forwarding', 'Router > forward NVR ports (80, 8000, 554).', 'safe', 'step', 3, NULL, 'Port forwarding configured', 'Router access', NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(110, 15, 107, NULL, NULL, 'Check DDNS or cloud', 'If DDNS, verify hostname resolves. If P2P, check cloud settings.', 'safe', 'step', 4, NULL, 'Remote access works', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(111, 15, NULL, NULL, NULL, 'Remote access resolved', 'NVR accessible remotely.', 'safe', '', 8, NULL, NULL, NULL, NULL, 'all', 'always', NULL, 0, 'solved', 'Document port forwarding settings.', '2026-08-29 09:37:21'),
(112, 16, NULL, NULL, NULL, 'Can you note the BSOD error code?', 'Blue screen shows error like IRQL_NOT_LESS_OR_EQUAL. Write it down.', 'safe', 'question', 1, NULL, NULL, NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(113, 16, 112, NULL, NULL, 'Boot into Safe Mode', 'Restart > hold Shift > Troubleshoot > Advanced > Startup Settings > Safe Mode.', 'safe', 'step', 2, NULL, 'Windows boots in Safe Mode', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(114, 16, 112, NULL, NULL, 'Run SFC', 'CMD admin: sfc /scannow. Wait for completion.', 'safe', 'step', 3, NULL, 'Corrupted files repaired', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(115, 16, 112, NULL, NULL, 'Run DISM', 'CMD admin: DISM /Online /Cleanup-Image /RestoreHealth. Restart.', 'safe', 'step', 4, NULL, 'Windows image repaired', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(116, 16, 112, NULL, NULL, 'Check recent changes', 'New hardware or software? Try removing it.', 'safe', 'step', 5, NULL, 'BSOD stops after removal', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(117, 16, 112, NULL, NULL, 'Update all drivers', 'GPU, chipset, network drivers from manufacturer.', 'safe', 'step', 6, NULL, 'BSOD stops', 'Internet', NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(118, 16, 112, NULL, NULL, 'Test RAM', 'MemTest86 full test.', 'safe', 'step', 7, NULL, 'No memory errors', 'USB drive', NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(119, 16, NULL, NULL, NULL, 'BSOD resolved', 'No more crashes.', 'safe', '', 12, NULL, NULL, NULL, NULL, 'all', 'always', NULL, 0, 'solved', 'Document error code and fix.', '2026-08-29 09:37:21'),
(120, 16, NULL, NULL, NULL, 'Hardware failure (RAM/HDD)', 'BSOD persists after all software fixes.', 'escalate', '', 13, NULL, NULL, NULL, NULL, 'all', 'always', NULL, 0, 'escalate', 'Run MemTest86. Replace RAM/HDD as needed.', '2026-08-29 09:37:21'),
(121, 17, NULL, NULL, NULL, 'What is using high resources?', 'Open Task Manager (Ctrl+Shift+Esc). Check Performance tab — CPU, RAM, Disk at 90%+?', 'safe', 'question', 1, NULL, NULL, NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(122, 17, 121, NULL, NULL, 'Close unnecessary programs', 'End tasks using high CPU/RAM but not needed.', 'safe', 'step', 2, NULL, 'CPU/RAM below 80%', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(123, 17, 121, NULL, NULL, 'Restart the computer', 'Simple restart clears memory.', 'safe', 'step', 3, NULL, 'PC runs faster', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(124, 17, 121, NULL, NULL, 'Run disk cleanup', 'Disk Cleanup in Start menu. Select all categories.', 'safe', 'step', 4, NULL, 'Disk space freed', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(125, 17, 121, NULL, NULL, 'Run SFC and DISM', 'CMD admin: sfc /scannow then DISM restorehealth.', 'safe', 'step', 5, NULL, 'No corrupted files', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(126, 17, 121, NULL, NULL, 'Disable startup programs', 'Task Manager > Startup > disable unnecessary programs.', 'safe', 'step', 6, NULL, 'Boot time improved', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(127, 17, 121, NULL, NULL, 'Check for malware', 'Run full Windows Defender scan.', 'safe', 'step', 7, NULL, 'No malware found', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(128, 17, NULL, NULL, NULL, 'Slow issue resolved', 'PC running normally.', 'safe', '', 12, NULL, NULL, NULL, NULL, 'all', 'always', NULL, 0, 'solved', 'Document what caused slowness.', '2026-08-29 09:37:21'),
(129, 17, NULL, NULL, NULL, 'Hardware upgrade needed', 'Still slow after all software fixes.', 'escalate', '', 13, NULL, NULL, NULL, NULL, 'all', 'always', NULL, 0, 'escalate', 'Recommend SSD/RAM upgrade.', '2026-08-29 09:37:21'),
(130, 18, NULL, NULL, NULL, 'Run as administrator', 'Right-click app > Run as administrator.', 'safe', 'step', 1, NULL, 'App opens', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(131, 18, NULL, NULL, NULL, 'Repair or reinstall app', 'Settings > Apps > Find app > Modify/Repair. Or uninstall and reinstall.', 'safe', 'step', 2, NULL, 'App works after reinstall', 'Installer', NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(132, 18, NULL, NULL, NULL, 'Update Windows and drivers', 'Check for Windows updates. Update GPU driver.', 'safe', 'step', 3, NULL, 'App works', 'Internet', NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(133, 18, NULL, NULL, NULL, 'Check Event Viewer', 'Event Viewer > Windows Logs > Application. Look for Error entries.', 'safe', 'step', 4, NULL, 'Error details found', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(134, 18, NULL, NULL, NULL, 'App issue resolved', 'Application no longer crashes.', 'safe', '', 8, NULL, NULL, NULL, NULL, 'all', 'always', NULL, 0, 'solved', 'Document the fix.', '2026-08-29 09:37:21'),
(135, 19, NULL, NULL, NULL, 'What error code does it show?', 'Note error code: 0x80070002, 0x800f0922, 0x8000ffff, etc.', 'safe', 'question', 1, NULL, NULL, NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(136, 19, 135, NULL, NULL, 'Run Update Troubleshooter', 'Settings > Update > Troubleshoot > Windows Update.', 'safe', 'step', 2, NULL, 'Update succeeds', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(137, 19, 135, NULL, NULL, 'Reset Update components', 'CMD admin: stop wuauserv, cryptSvc, bits, msiserver. Rename SoftwareDistribution. Restart services.', 'safe', 'step', 3, NULL, 'Components reset', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(138, 19, 135, NULL, NULL, 'Run SFC and DISM', 'CMD admin: sfc /scannow then DISM restorehealth.', 'safe', 'step', 4, NULL, 'Files repaired', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(139, 19, 135, NULL, NULL, 'Download update manually', 'Microsoft Update Catalog > search KB number > download and install.', 'safe', 'step', 5, NULL, 'Update installed', 'Internet', NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(140, 19, NULL, NULL, NULL, 'Update issue resolved', 'Windows Update completes.', 'safe', '', 10, NULL, NULL, NULL, NULL, 'all', 'always', NULL, 0, 'solved', 'Document error code and fix.', '2026-08-29 09:37:21'),
(141, 20, NULL, NULL, NULL, 'Is Caps Lock on?', 'Check if Caps Lock is accidentally on.', 'safe', 'step', 1, NULL, 'Caps Lock is off', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(142, 20, NULL, NULL, NULL, 'Try on-screen keyboard', 'Login screen > Ease of Access > On-Screen Keyboard. Type carefully.', 'safe', 'step', 2, NULL, 'Login successful', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(143, 20, NULL, NULL, NULL, 'Restart computer', 'Hold Shift + Restart, or hold power button.', 'safe', 'step', 3, NULL, 'Login works after restart', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(144, 20, NULL, NULL, NULL, 'Boot into Safe Mode', 'Restart > Shift > Troubleshoot > Advanced > Safe Mode with Command Prompt.', 'safe', 'step', 4, NULL, 'Can log into Safe Mode', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(145, 20, NULL, NULL, NULL, 'Reset password via admin', 'Safe Mode CMD: net user username newpassword.', 'safe', 'step', 5, NULL, 'Password reset', 'Admin account', NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(146, 20, NULL, NULL, NULL, 'Login issue resolved', 'User can log in.', 'safe', '', 10, NULL, NULL, NULL, NULL, 'all', 'always', NULL, 0, 'solved', 'Document the cause.', '2026-08-29 09:37:21'),
(147, 20, NULL, NULL, NULL, 'Profile corruption', 'Profile cannot load.', 'escalate', '', 11, NULL, NULL, NULL, NULL, 'all', 'always', NULL, 0, 'escalate', 'Create new profile. Copy data from old.', '2026-08-29 09:37:21'),
(148, 21, NULL, NULL, NULL, 'Check CPU temperature', 'Open HWMonitor or Task Manager Performance. What is CPU temp idle and under load?', 'safe', 'question', 1, NULL, NULL, NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(149, 21, 148, NULL, NULL, 'Clean dust from vents and fans', 'Use compressed air on all vents, fans, CPU cooler. Hold fans while blowing.', 'safe', 'step', 2, NULL, 'Vents and fans clean', 'Compressed air', NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(150, 21, 148, NULL, NULL, 'Ensure proper ventilation', 'PC not on carpet or enclosed. 4-6 inches clearance around vents.', 'safe', 'step', 3, NULL, 'Airflow improved', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(151, 21, 148, NULL, NULL, 'Check fan operation', 'Open case. Are all fans spinning? Replace dead fans.', 'low', 'step', 4, NULL, 'All fans spinning', 'Screwdriver', NULL, 'desktop', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(152, 21, 148, NULL, NULL, 'Reapply thermal paste', 'Remove cooler, clean with alcohol, apply new paste, remount.', 'medium', 'step', 5, NULL, 'CPU temp normal', 'Thermal paste, Alcohol', NULL, 'desktop', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(153, 21, NULL, NULL, NULL, 'Overheating resolved', 'Temperature normal.', 'safe', '', 10, NULL, NULL, NULL, NULL, 'all', 'always', NULL, 0, 'solved', 'Document cause. Schedule regular cleaning.', '2026-08-29 09:37:21'),
(154, 21, NULL, NULL, NULL, 'Hardware failure', 'Still overheating after all fixes.', 'escalate', '', 11, NULL, NULL, NULL, NULL, 'desktop', 'always', NULL, 0, 'escalate', 'Check heatsink, cooler mount, CPU. Escalate.', '2026-08-29 09:37:21'),
(155, 22, NULL, NULL, NULL, 'Is drive detected in BIOS?', 'Enter BIOS (Del/F2). Check storage devices list.', 'safe', 'question', 1, NULL, NULL, NULL, NULL, 'desktop', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(156, 22, 155, NULL, NULL, 'Check SATA/power cables', 'Unplug and reconnect SATA data and power cables. Try different SATA port.', 'safe', 'step', 2, NULL, 'Drive detected', 'SATA cable', NULL, 'desktop', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(157, 22, 155, NULL, NULL, 'Run chkdsk', 'CMD admin: chkdsk C: /f /r. Restart when prompted.', 'safe', 'step', 3, NULL, 'Disk errors repaired', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(158, 22, 155, NULL, NULL, 'Check S.M.A.R.T. status', 'CrystalDiskInfo or wmic diskdrive get status.', 'safe', 'step', 4, NULL, 'S.M.A.R.T. OK', 'CrystalDiskInfo', NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(159, 22, 155, NULL, NULL, 'Backup data immediately', 'If clicking or S.M.A.R.T. bad, copy data to external drive NOW.', 'low', 'step', 5, NULL, 'Data backed up', 'External drive', NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(160, 22, NULL, NULL, NULL, 'Drive is failing', 'S.M.A.R.T. errors, clicking, or not detected.', 'escalate', '', 10, NULL, NULL, NULL, NULL, 'all', 'always', NULL, 0, 'escalate', 'Replace drive immediately. Restore from backup.', '2026-08-29 09:37:21'),
(161, 23, NULL, NULL, NULL, 'Does USB device work on another PC?', 'Test device on different computer.', 'safe', 'question', 1, NULL, NULL, NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(162, 23, 161, NULL, NULL, 'Try different USB port', 'Unplug and try different port. Front and back.', 'safe', 'step', 2, NULL, 'Device detected', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(163, 23, 161, NULL, NULL, 'Restart USB controller', 'Device Manager > USB Root Hub > Disable > wait > Enable.', 'safe', 'step', 3, NULL, 'USB ports work', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(164, 23, 161, NULL, NULL, 'Uninstall USB drivers', 'Device Manager > USB controllers > Uninstall all. Restart.', 'safe', 'step', 4, NULL, 'USB ports work', NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(165, 23, NULL, NULL, NULL, 'USB hardware failure', 'Port not working after all software fixes.', 'escalate', '', 8, NULL, NULL, NULL, NULL, 'all', 'always', NULL, 0, 'escalate', 'Try USB hub as workaround.', '2026-08-29 09:37:21'),
(166, 24, NULL, NULL, NULL, 'Is the charger working?', 'Check charger LED. Try different charger if available.', 'safe', 'question', 1, NULL, NULL, NULL, NULL, 'all', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(167, 24, 166, NULL, NULL, 'Check charger connection', 'Firmly plugged into both laptop and outlet.', 'safe', 'step', 2, NULL, 'Charger firmly connected', NULL, NULL, 'laptop', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(168, 24, 166, NULL, NULL, 'Try different outlet', 'Different wall outlet.', 'safe', 'step', 3, NULL, 'Charger LED on', NULL, NULL, 'laptop', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(169, 24, 166, NULL, NULL, 'Run battery report', 'CMD admin: powercfg /batteryreport. Check design vs full charge capacity.', 'safe', 'step', 4, NULL, 'Battery health > 60%', NULL, NULL, 'laptop', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(170, 24, 166, NULL, NULL, 'Recalibrate battery', 'Charge to 100%, drain completely, charge back to 100% uninterrupted.', 'safe', 'step', 5, NULL, 'Battery charges correctly', NULL, NULL, 'laptop', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(171, 24, 166, NULL, NULL, 'Update BIOS and drivers', 'Manufacturer website for BIOS and power driver updates.', 'safe', 'step', 6, NULL, 'Battery charges', 'Internet', NULL, 'laptop', 'always', NULL, 0, NULL, NULL, '2026-08-29 09:37:21'),
(172, 24, NULL, NULL, NULL, 'Battery needs replacement', 'Health below 60% or not charging after all fixes.', 'escalate', '', 10, NULL, NULL, NULL, NULL, 'laptop', 'always', NULL, 0, 'escalate', 'Replace battery. Check warranty.', '2026-08-29 09:37:22');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `organization_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `organization_id`, `name`, `description`, `created_at`) VALUES
(1, 1, 'Field IT', 'On-site technical support and maintenance', '2026-08-23 20:56:33'),
(2, 1, 'Network Operations', 'Network infrastructure and connectivity', '2026-08-23 20:56:33'),
(3, 1, 'Asset & Deployment', 'Device staging, inventory and deployment', '2026-08-23 20:56:33'),
(4, 2, 'Operations', 'Business operations users and support requests', '2026-08-23 20:56:33');

-- --------------------------------------------------------

--
-- Table structure for table `device_guides`
--

CREATE TABLE `device_guides` (
  `id` int(11) NOT NULL,
  `model_id` int(11) NOT NULL,
  `guide_type` enum('disassembly','assembly','repair') NOT NULL,
  `title` varchar(200) NOT NULL,
  `steps` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`steps`)),
  `tools_needed` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tools_needed`)),
  `safety_notes` text DEFAULT NULL,
  `author_id` int(11) DEFAULT NULL,
  `status` enum('draft','published') DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `device_models`
--

CREATE TABLE `device_models` (
  `id` int(11) NOT NULL,
  `manufacturer_id` int(11) NOT NULL,
  `device_type_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `generation` varchar(50) DEFAULT NULL,
  `specifications` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`specifications`)),
  `service_manual_url` varchar(500) DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `known_issues` text DEFAULT NULL,
  `common_failures` text DEFAULT NULL,
  `required_tools` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `device_model_issues`
--

CREATE TABLE `device_model_issues` (
  `id` int(11) NOT NULL,
  `model_id` int(11) NOT NULL,
  `issue_id` int(11) NOT NULL,
  `frequency` enum('common','occasional','rare') DEFAULT 'common',
  `notes` text DEFAULT NULL,
  `verified_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `device_parts`
--

CREATE TABLE `device_parts` (
  `id` int(11) NOT NULL,
  `model_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `part_number` varchar(100) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `device_types`
--

CREATE TABLE `device_types` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `equipment`
--

CREATE TABLE `equipment` (
  `id` int(11) NOT NULL,
  `manufacturer` varchar(100) NOT NULL,
  `model_name` varchar(255) NOT NULL,
  `device_type` varchar(50) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `year` varchar(10) DEFAULT NULL,
  `serial_number` varchar(100) DEFAULT NULL,
  `cpu` varchar(255) DEFAULT NULL,
  `ram` varchar(255) DEFAULT NULL,
  `storage` varchar(255) DEFAULT NULL,
  `display_spec` varchar(255) DEFAULT NULL,
  `ports` text DEFAULT NULL,
  `known_issues` text DEFAULT NULL,
  `tools_needed` text DEFAULT NULL,
  `repair_guides` text DEFAULT NULL,
  `specs_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`specs_json`)),
  `notes` text DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `disassembly_guide` text DEFAULT NULL,
  `assembly_guide` text DEFAULT NULL,
  `guide_videos` text DEFAULT NULL,
  `asset_tag` varchar(100) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'active',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `equipment`
--

INSERT INTO `equipment` (`id`, `manufacturer`, `model_name`, `device_type`, `category`, `year`, `serial_number`, `cpu`, `ram`, `storage`, `display_spec`, `ports`, `known_issues`, `tools_needed`, `repair_guides`, `specs_json`, `notes`, `image_url`, `disassembly_guide`, `assembly_guide`, `guide_videos`, `asset_tag`, `location`, `status`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Lenovo', 'ThinkPad T14 Gen 3', 'Laptop', 'laptop', '2022', NULL, 'AMD Ryzen 5 Pro 6650U', '16GB DDR4-3200', '512GB NVMe SSD', '14\" FHD IPS Anti-glare', '2x USB-A 3.2, 2x USB-C 3.2 (PD+DP), HDMI 2.0b, RJ-45, 3.5mm', 'Battery swelling (check during service), WiFi disconnects (update driver), Thunderbolt firmware update needed', 'Phillips PH0, Plastic Pry Tool, ESD Wrist Strap', 'Back Cover Removal:Remove 7 screws and slide cover off.||Battery Disconnect:Disconnect battery connector before working on internals.||RAM Reseat:RAM is soldered - check for error codes.||SSD Replacement:M.2 2280 slot - remove single screw to replace.', NULL, 'lenovo-thinkpad-t14', 'https://p4-ofp.static.pub/fes/cms/2022/04/14/rjlz459d5w8cx9gm4s2r1w9v7s5o3q960159.png', '1. Power off and disconnect all cables [video:https://www.youtube.com/watch?v=dQw4w9WgXcQ]|2. Flip laptop over, remove 7 captive Phillips screws [video:https://www.youtube.com/watch?v=dQw4w9WgXcQ]|3. Use plastic pry tool to release bottom panel clips starting from hinges|4. Slide bottom panel toward you to remove|5. DISCONNECT BATTERY FIRST - pull cable from motherboard [video:https://www.youtube.com/watch?v=dQw4w9WgXcQ]|6. To remove fan: disconnect fan cable, remove 2 screws, lift out [video:https://www.youtube.com/watch?v=dQw4w9WgXcQ]|7. To access SSD: remove 1 screw, slide M.2 out at 30░ angle [video:https://www.youtube.com/watch?v=dQw4w9WgXcQ]|8. To access WiFi card: remove 1 screw, disconnect 2 antenna cables|9. RAM is soldered - not user-replaceable|10. To remove keyboard: remove 3 screws from bottom, push through to release [video:https://www.youtube.com/watch?v=dQw4w9WgXcQ]|11. To access display cable: remove hinge screws (2 per side), carefully lift display assembly', '1. Reconnect display cable to motherboard [video:https://www.youtube.com/watch?v=dQw4w9WgXcQ]|2. Align hinges and secure with 2 screws per side|3. Place keyboard back, align tabs, press down until clips engage|4. Secure keyboard with 3 screws from bottom|5. Insert M.2 SSD at 30░ angle, press down and secure with screw [video:https://www.youtube.com/watch?v=dQw4w9WgXcQ]|6. Connect WiFi antenna cables (white=Main, black=Aux) [video:https://www.youtube.com/watch?v=dQw4w9WgXcQ]|7. Reconnect fan cable and secure with 2 screws|8. Reconnect battery cable to motherboard [video:https://www.youtube.com/watch?v=dQw4w9WgXcQ]|9. Align bottom panel, slide into place, press clips around edges|10. Secure with 7 captive screws (hand-tight only)|11. Power on and verify all components detected in BIOS', 'https://www.youtube.com/watch?v=example1|https://www.youtube.com/watch?v=example2', NULL, 'Room 201', 'active', 1, '2026-08-30 00:29:38', '2026-08-30 00:52:56', NULL),
(2, 'Lenovo', 'ThinkPad T14s Gen 4', 'Laptop', 'laptop', '2023', NULL, 'AMD Ryzen 7 Pro 7840U', '32GB LPDDR5x', '1TB NVMe SSD', '14\" 2.8K OLED', '2x USB4, 2x USB-A 3.2, HDMI 2.1, 3.5mm', 'OLED burn-in after extended static display, Fan noise under load (clean dust regularly)', 'Phillips PH0, Plastic Pry Tool, ESD Wrist Strap', 'Back Panel:Remove 5 captive screws.||Battery:Disconnect before internal work.||Thermal Paste:Reapply every 2 years for optimal cooling.', NULL, 'lenovo-thinkpad-t14s', 'https://p4-ofp.static.pub/fes/cms/2022/04/14/ix9r5n6k8t2m7b1v4x0c3f5a2d8e6w90159.png', '1. Power off and disconnect all cables|2. Remove 5 captive screws from bottom panel|3. Use plastic pry tool to release clips|4. Slide panel off|5. Disconnect battery immediately|6. Fan: 2 screws + cable|7. SSD: 1 screw, M.2 2280|8. WiFi: 1 screw, 2 antenna cables', '1. Connect WiFi antennas (White=Main, Black=Aux)|2. Secure WiFi card with screw|3. Insert SSD at 30°, press and screw|4. Connect fan cable, secure 2 screws|5. Connect battery|6. Align panel, press clips, secure 5 screws', NULL, NULL, 'Room 201', 'active', 1, '2026-08-30 00:29:38', '2026-08-30 00:47:37', NULL),
(3, 'Dell', 'Latitude 5520', 'Laptop', 'laptop', '2021', NULL, 'Intel Core i5-1145G7', '8GB DDR4', '256GB NVMe SSD', '15.6\" FHD IPS', '2x USB-A 3.2, 1x USB-C Thunderbolt 4, HDMI 2.0, RJ-45, SD card, 3.5mm', 'Hinge looseness on some units, Thunderbolt dock recognition issues, Battery drain in sleep mode', 'Phillips PH0, Plastic Spudger, ESD Wrist Strap', 'Bottom Cover:Remove 10 screws, pry clips around edge.||Battery:6 screws, slide out.||Keyboard:3 screws from bottom, push through to release.||HDD Bay:Accessible after removing bottom cover.', NULL, 'dell-latitude-5520', 'https://images.dell.com/images/dell/en/media/ldmedia/5520/laptop-latitude-5520-hero-gray.webp', '1. Power off, disconnect power|2. Remove 10 bottom screws (3 longer near hinges)|3. Pry from front edge|4. Release clips around perimeter|5. Lift bottom cover|6. DISCONNECT BATTERY FIRST|7. RAM: spread clips, pull at 30°|8. SSD: 1 screw, slide out|9. WiFi: 1 screw, pop antenna connectors straight up|10. Keyboard: 3 screws from bottom marked K|11. Fan: 1 screw + cable', '1. Apply thermal paste (pea-sized dot)|2. Align heatsink, tighten 4 screws diagonal|3. Connect fan cable|4. Insert WiFi card + antennas (White=Main)|5. Insert RAM at 30°, press to click|6. Insert SSD + screw|7. Connect battery|8. Place keyboard, secure 3 screws|9. Align panel, press clips|10. Secure 10 screws', NULL, NULL, 'Room 202', 'active', 1, '2026-08-30 00:29:38', '2026-08-30 00:47:37', NULL),
(4, 'Dell', 'Latitude 7430', 'Laptop', 'laptop', '2022', NULL, 'Intel Core i7-1265U', '16GB LPDDR5', '512GB NVMe SSD', '14\" FHD+ Anti-glare', '2x Thunderbolt 4, 1x USB-A 3.2, HDMI 2.0, 3.5mm', 'Limited upgradeability (RAM soldered), Touchpad intermittently unresponsive (firmware fix)', 'Phillips PH0, Torx T5, Plastic Pry Tool', 'Bottom Panel:Remove 7 screws (3 captive).||M.2 SSD:Single screw under bottom panel.||WiFi Card:Located near battery, 1 antenna cable each.', NULL, 'dell-latitude-7430', 'https://cdn.mos.cms.futurecdn.net/jg9RkNxFKhLYvUQ4gYqQBH-1200-80.jpg', '1. Power off, unplug|2. Remove 7 screws (3 captive near hinges)|3. Pry bottom panel from front|4. Disconnect battery cable|5. SSD: 1 screw under panel|6. WiFi: 1 screw, 2 pop connectors', '1. WiFi card + antennas + screw|2. SSD + screw|3. Battery cable|4. Panel clips + 7 screws', NULL, NULL, 'Room 202', 'active', 1, '2026-08-30 00:29:38', '2026-08-30 00:47:37', NULL),
(5, 'HP', 'ProBook 450 G9', 'Laptop', 'laptop', '2022', NULL, 'Intel Core i5-1235U', '8GB DDR4-3200', '512GB NVMe SSD', '15.6\" FHD IPS', '3x USB-A 3.2, 1x USB-C 3.2, HDMI 2.0, RJ-45, SD card, 3.5mm', 'Fan error after dust buildup, USB-C charging intermittent, BIOS update needed', 'Phillips PH0, Plastic Pry Tool', 'Bottom Cover:5 captive screws, rubber feet hide 2 more.||RAM:2 SO-DIMM slots, upgradable to 64GB.||Battery:Connected via cable, 4 screws.||Fan:Single fan, accessible after removing bottom cover.', NULL, 'hp-probook-450-g9', 'https://ssl-product-images.www8-hp.com/digmedialib/prodimg/lowres/c08504650.png', '1. Power off and unplug|2. Remove rubber feet to access 2 hidden screws (total 7)|3. Remove all screws|4. Pry from rear hinge area|5. Disconnect battery cable|6. RAM: 2 SO-DIMM slots|7. SSD: M.2, 1 screw|8. WiFi: 1 screw, 2 antenna cables|9. Fan: 2 screws + cable', '1. WiFi card + antennas + screw|2. RAM at 30°, press to click|3. SSD + screw|4. Battery cable|5. Panel clips + screws + rubber feet', NULL, NULL, 'Room 203', 'active', 1, '2026-08-30 00:29:38', '2026-08-30 00:39:12', NULL),
(6, 'HP', 'EliteBook 840 G10', 'Laptop', 'laptop', '2023', NULL, 'Intel Core i7-1355U', '16GB DDR5-5200', '512GB NVMe SSD', '14\" WUXGA IPS Anti-glare', '2x Thunderbolt 4, 2x USB-A 3.2, HDMI 2.0, Nano SIM, 3.5mm', 'Thunderbolt dock hot-plug issues, Power button intermittent', 'Phillips PH0, Torx T5', 'Bottom Cover:5 captive screws.||Battery:Internal, 4 screws + cable.||SSD:M.2 2280 single screw.||WiFi:AX211 module, 2 antenna cables.', NULL, 'hp-elitebook-840-g10', 'https://ssl-product-images.www8-hp.com/digmedialib/prodimg/lowres/c08750849.png', '1. Power off, disconnect|2. Remove 5 captive screws|3. Pry bottom panel|4. Disconnect battery|5. SSD: 2x M.2 slots|6. WiFi: AX211, 2 antennas', '1. WiFi module + antennas + screw|2. SSD + screw|3. Battery cable|4. Panel + 5 screws', NULL, NULL, 'Room 203', 'active', 1, '2026-08-30 00:29:38', '2026-08-30 00:39:12', NULL),
(7, 'Lenovo', 'ThinkCentre M70s', 'Desktop', 'desktop', '2022', NULL, 'Intel Core i5-12400', '8GB DDR4-3200', '512GB NVMe SSD', 'Integrated Intel UHD 730', '4x USB-A 3.2, 2x USB-A 2.0, 1x USB-C, 1x DisplayPort, 1x HDMI, RJ-45', 'Front panel USB loose on some units, Fan warning after dust buildup', 'Phillips PH1, Phillips PH0', 'Side Panel:Slide release latch, remove panel.||RAM:2 DIMM slots, up to 64GB.||Storage:M.2 + 3.5\" SATA bay.||Fan:Single CPU fan, clip release.', NULL, 'lenovo-m70s', 'https://p3-ofp.static.pub/fes/cms/2021/09/08/k1x0l0r94t3s8b6v2c5a7d0e9w8f3q150159.png', '1. Power off, disconnect all cables|2. Pull side panel release latch (rear of case)|3. Slide side panel off|4. Ground yourself with ESD strap|5. RAM: push clips outward, module pops up|6. SSD M.2: remove 1 screw, slide out|7. GPU: remove PCIe bracket screws, press PCIe latch, pull card straight out|8. Fan: single CPU fan, 4 screws on heatsink|9. PSU: 4 screws on rear, disconnect all motherboard connectors|10. Front panel: note connector positions before unplugging', '1. Install RAM (align notch, press until clips click)|2. Insert M.2 SSD at angle, secure screw|3. Apply thermal paste if reseating cooler|4. Lower heatsink evenly, tighten 4 screws in diagonal|5. Connect fan cable to CPU_FAN header|6. Connect PSU 24-pin + 8-pin CPU + PCIe power|7. Install GPU in PCIe x16 slot, press until latch clicks|8. Connect front panel headers (check manual for pinout)|9. Replace side panel, reconnect cables|10. Enter BIOS to verify all components', NULL, NULL, 'Room 204', 'active', 1, '2026-08-30 00:29:38', '2026-08-30 00:47:37', NULL),
(8, 'Dell', 'OptiPlex 7090', 'Desktop', 'desktop', '2021', NULL, 'Intel Core i5-11500', '16GB DDR4-3200', '256GB NVMe SSD', 'Integrated Intel UHD 750', '4x USB-A 3.2, 2x USB-A 2.0, 1x DisplayPort, 1x HDMI, RJ-45, 3.5mm', 'CMOS battery failure after 3+ years, Thermal throttling when dusty', 'Phillips PH1, Flathead', 'Side Panel:Thumbscrew or key lock, slide off.||RAM:4 DIMM slots.||GPU:PCIe x16 slot.||Power Supply:Standard ATX, tool-less removal.', NULL, 'dell-optiplex-7090', 'https://i.dell.com/is/image/dellcontent/content/dam/ss2/product-images/dell-client-products/desktops/optiplex/desktops-optiplex-7090-sff-hero-504x350.psd', '1. Power off, unplug|2. Remove rear thumbscrew|3. Slide side panel off|4. RAM: 4 DIMM slots|5. GPU: PCIe x16, bracket screws|6. M.2 SSD: 1 screw|7. PSU: 4 screws + all connectors', '1. RAM modules (align notch)|2. M.2 SSD + screw|3. GPU in PCIe slot|4. PSU + all connectors|5. Side panel + thumbscrew', NULL, NULL, 'Room 204', 'active', 1, '2026-08-30 00:29:38', '2026-08-30 00:47:37', NULL),
(9, 'Dell', 'OptiPlex 7010 SFF', 'Desktop', 'desktop', '2023', NULL, 'Intel Core i5-13400', '16GB DDR5-4800', '512GB NVMe SSD', 'Integrated Intel UHD 730', '4x USB-A 3.2, 2x USB-A 2.0, 1x DisplayPort 1.4a, 1x HDMI 2.1, RJ-45', 'DDR5 compatibility with older modules, PCIe lane sharing with NVMe', 'Phillips PH1', 'Chassis:Pull release latch.||RAM:2 DDR5 DIMM slots.||Storage:2x M.2 slots + 1x 3.5\" bay.||CPU Cooler:Clip-style retention bracket.', NULL, 'dell-optiplex-7010', 'https://i.dell.com/is/image/dellcontent/content/dam/ss2/product-images/dell-client-products/desktops/optiplex/desktops-optiplex-7010-sff-hero-504x350.psd', '1. Power off, unplug|2. Pull release latch|3. Slide panel off|4. RAM: 2 DDR5 DIMM slots|5. SSD: 2x M.2 slots|6. CPU cooler: clip retention', '1. RAM in DDR5 slots|2. M.2 SSDs|3. Cooler + paste|4. Panel + latch', NULL, NULL, 'Room 204', 'active', 1, '2026-08-30 00:29:38', '2026-08-30 00:47:37', NULL),
(10, 'HP', 'LaserJet Pro M404dn', 'Printer', 'printer', '2020', NULL, 'N/A', 'N/A', 'N/A', 'N/A', '1x USB-B 2.0, 1x Ethernet RJ-45, Wireless optional', 'Paper jam in fuser area, 50.0 fuser error, Toner low warning early', 'Phillips PH1, Long-nose pliers, Cleaning cloth', 'Fuser Access:Open rear door, pull fuser by green handles.||Paper Path:Remove rear cover to access paper path.||Toner:Pull cartridge straight out.||Pickup Roller:Remove tray, pull roller off shaft.', NULL, 'hp-m404dn', 'https://ssl-product-images.www8-hp.com/digmedialib/prodimg/lowres/c06579555.png', '1. Turn off, unplug|2. Open rear door|3. Remove fuser (green handles)|4. Remove toner (pull straight)|5. Check pickup roller|6. Clean rollers with lint-free cloth|7. Inspect transfer belt', '1. Clean rollers|2. Reinstall pickup roller|3. Insert toner (click)|4. Align fuser (green handles lock)|5. Close rear door|6. Print test page', NULL, NULL, 'Server Room', 'active', 1, '2026-08-30 00:29:38', '2026-08-30 00:39:12', NULL),
(11, 'HP', 'Color LaserJet Pro MFP M283fdw', 'Printer', 'printer', '2021', NULL, 'N/A', 'N/A', 'N/A', 'N/A', '1x USB-B 2.0, 1x Ethernet, Wireless, NFC touch-to-print', 'Color calibration drift, Fuser error 50.1, Scanner jam on ADF', 'Phillips PH0, PH1, Lint-free cloth', 'Fuser:Open rear door, release 2 green levers.||Transfer Belt:Under toner cartridges, handle by edges.||Scanner:ADF roller clean with damp cloth.||Toner:4 cartridges - CMYK, pull straight out.', NULL, 'hp-m283fdw', 'https://ssl-product-images.www8-hp.com/digmedialib/prodimg/lowres/c06596707.png', '1. Power off, unplug|2. Open rear door|3. Release fuser (2 green levers)|4. Remove 4 toner cartridges|5. Handle transfer belt by edges|6. Clean ADF roller|7. Check scanner glass', '1. Clean transfer belt|2. Reinstall fuser (2 levers)|3. Insert 4 cartridges (K-C-M-Y)|4. Close rear door|5. Clean scanner glass|6. Power on, run calibration', NULL, NULL, 'Server Room', 'active', 1, '2026-08-30 00:29:38', '2026-08-30 00:39:12', NULL),
(12, 'Brother', 'HL-L2350DW', 'Printer', 'printer', '2021', NULL, 'N/A', 'N/A', 'N/A', 'N/A', '1x USB-B, 1x Ethernet, Wireless, NFC', 'Toner sensor false alarm, Paper feed roller wear, Sleep mode wake issues', 'Phillips PH1, Cotton gloves', 'Fuser:Open back panel, pull fuser assembly.||Paper Feed:Remove tray, clean pickup roller with alcohol.||Toner:Slide out drum unit, replace toner separately.||Waste Box:Located inside front panel.', NULL, 'brother-hl-l2350dw', 'https://assets.brother.com/en/v1/articleimages/large/HLL2350DW_main%402x.png', '1. Power off, unplug|2. Open front cover|3. Pull drum + toner assembly|4. Separate toner from drum (green lever)|5. Clean pickup roller with alcohol|6. Remove waste toner box', '1. Insert toner into drum (click)|2. Slide assembly into printer|3. Close front cover|4. Reinstall waste toner box|5. Print test page', NULL, NULL, 'Room 105', 'active', 1, '2026-08-30 00:29:38', '2026-08-30 00:39:12', NULL),
(13, 'Cisco', 'Catalyst 2960-X', 'Switch', 'network', '2019', NULL, 'N/A', 'N/A', 'N/A', 'N/A', '24x Gigabit Ethernet, 4x SFP+, Console port', 'Spanning tree topology change alerts, Power supply fan failure, IOS upgrade needed', 'Console cable, Phillips PH1, Anti-static mat', 'Console:Rollover cable to RJ-45 console port.||Power Supply:Redundant PSU bay, hot-swappable.||Fan Module:Rear-mounted, single fan unit.||Flash:4GB internal, upgradeable via USB.', NULL, 'cisco-2960x', 'https://www.cisco.com/c/dam/en/us/products/collateral/switches/catalyst-2960-x-series-switches/cat2960-x-switch?.avif', '1. Console: connect rollover cable|2. Check IOS: show version|3. Fan module: pull tab|4. PSU: slide out|5. SFP: pull tab, slide out|6. Flash: dir flash:', '1. SFP modules (click to lock)|2. PSU into bay|3. Fan module|4. Verify ports (green LED)|5. Write memory', NULL, NULL, 'Server Room', 'active', 1, '2026-08-30 00:29:38', '2026-08-30 00:47:37', NULL),
(14, 'Ubiquiti', 'UniFi AP AC Pro', 'Access Point', 'network', '2020', NULL, 'N/A', 'N/A', 'N/A', 'N/A', '1x GbE PoE In, 1x GbE Passthrough', 'Firmware upgrade failures, WiFi clients not roaming, LED stuck solid white', 'PoE injector or PoE switch, Phillips PH0, Ladder', 'Mounting:Twist-lock ceiling mount bracket.||Reset:Hold recessed button 10+ seconds.||Adopt:Connect to UniFi controller, click Adopt.||PoE:Requires 802.3af PoE (48V).', NULL, 'ubiquiti-uap-ac-pro', 'https://store.ui.com/cdn/shop/products/UAP-AC-PRO_large.png', '1. Disconnect PoE cable|2. Twist counter-clockwise from bracket|3. Reset: hold 10+ seconds|4. Check Ethernet port|5. Clean exterior', '1. Align bracket tabs|2. Twist clockwise to lock|3. Connect PoE Ethernet|4. Wait for LED status|5. Verify in UniFi controller', NULL, NULL, 'Building A', 'active', 1, '2026-08-30 00:29:38', '2026-08-30 00:47:37', NULL),
(15, 'TP-Link', 'TL-SG2008', 'Switch', 'network', '2021', NULL, 'N/A', 'N/A', 'N/A', 'N/A', '8x Gigabit Ethernet, 2x SFP', 'Loop detection false positives, Web UI slow after extended uptime', 'Phillips PH1', 'Reset:Hold button 10+ seconds for factory reset.||Management:Access via 192.168.0.1 default.||Firmware:Update via web admin panel.||Mounting:Desktop or rack mount with bracket.', NULL, 'tplink-sg2008', 'https://static.tp-link.com/2022/202211/20221109/TL-SG2008_normal_1731020420103w.png', '1. Power off, unplug|2. Remove 4 bottom screws|3. Lift top cover|4. Check port connections|5. Reset: hold 10+ seconds', '1. Align cover|2. Secure 4 screws|3. Connect power and network|4. Access web UI at 192.168.0.1', NULL, NULL, 'Room 105', 'active', 1, '2026-08-30 00:29:38', '2026-08-30 00:39:12', NULL),
(16, 'Hikvision', 'DS-2CD2143G2-I', 'CCTV Camera', 'cctv', '2022', NULL, 'N/A', 'N/A', 'N/A', 'N/A', '1x RJ-45 PoE (802.3af), microSD slot (up to 256GB)', 'No IR at night, Camera offline, Image freezing, Time sync drift', 'PoE switch/injector, Ladder, Phillips PH0', 'Power:Requires PoE 802.3af (15.4W).||Reset:Hold button inside weatherproof housing 15s.||Lens:Fixed 2.8mm/4mm/6mm options.||Mounting:3-axis bracket, 3 screws + anchors.', NULL, 'hikvision-ds2cd2143g2', 'https://www.hikvision.com/content/dam/hikvision/products/ACS-Products/IP-Intercom-HX/Hikvision-logo.png', '1. Disconnect PoE|2. Remove 3 bracket screws|3. Lower camera|4. Open housing (4 screws)|5. Access microSD|6. Reset: hold 15s|7. Check RJ-45|8. Inspect IR LEDs|9. Clean lens', '1. Verify PoE power|2. Mount bracket (3 screws + anchors)|3. Twist-lock camera|4. Secure housing|5. Verify LED|6. Check live feed|7. Adjust angle|8. Enable motion detection', NULL, NULL, 'Building B', 'active', 1, '2026-08-30 00:29:38', '2026-08-30 00:47:37', NULL),
(17, 'Hikvision', 'DS-7608NI-K2/8P', 'NVR', 'cctv', '2022', NULL, 'N/A', 'N/A', 'N/A', 'N/A', '8x PoE ports, 2x SATA, 1x HDMI, 1x VGA, 2x USB, RJ-45', 'HDD full not recording, PoE port dead, Playback not working, Remote access fail', 'Phillips PH1, SATA cable (spare), Hard drive (surveillance grade)', 'HDD Bay:Remove top cover, 4 screws per drive.||PoE Ports:8 ports, 60W total budget.||Network:Config via browser or NVR screen.||Reset:Rear pinhole button, hold 15s.', NULL, 'hikvision-ds7608ni', 'https://www.hikvision.com/content/dam/hikvision/products/ACS-Products/IP-Intercom-HX/Hikvision-logo.png', '1. Power off, unplug|2. Remove 4 top cover screws|3. Slide cover back|4. Check HDD connections|5. Verify RAM|6. Check PoE LEDs|7. Clean fan|8. Reset: hold 15s', '1. Verify HDD connections|2. Secure HDD in bay|3. Replace cover + 4 screws|4. Connect PoE cameras|5. Connect Ethernet|6. Power on, setup wizard|7. Verify all channels', NULL, NULL, 'Server Room', 'active', 1, '2026-08-30 00:29:38', '2026-08-30 00:47:37', NULL),
(18, 'Dahua', 'IPC-HDW5442T-AS', 'CCTV Camera', 'cctv', '2023', NULL, 'N/A', 'N/A', 'N/A', 'N/A', '1x RJ-45 PoE+, microSD, Alarm I/O', 'Audio not recording, Smart motion events not triggering, Firmware compatibility', 'PoE+ switch, Ladder, Phillips PH0', 'Power:PoE+ 802.3at (25.4W max).||Audio:Built-in mic, enable in Web UI.||Reset:Hold reset button 15 seconds.||SD Card:Waterproof slot under housing.', NULL, 'dahua-ipchdw5442t', 'https://www.dahuasecurity.com/content/dam/dahua/images/logo/dahua-logo.png', '1. Disconnect PoE|2. Remove from bracket|3. Open housing|4. Reset: hold 15s|5. Check microSD|6. Verify audio|7. Clean lens|8. Check RJ-45', '1. Mount bracket|2. Lock camera|3. Secure housing|4. Connect PoE|5. Verify live feed|6. Enable smart events|7. Enable audio|8. Sync time', NULL, NULL, 'Building B', 'active', 1, '2026-08-30 00:29:38', '2026-08-30 00:47:37', NULL),
(19, 'Dell', 'PowerEdge T350', 'Server', 'server', '2023', NULL, 'Intel Xeon E-2336', '32GB DDR4 ECC UDIMM', '2x 1TB SAS 10K RAID1', 'N/A', '2x USB-A 3.2, 2x USB-A 2.0, 1x iDRAC, 1x VGA, 2x GbE, 1x serial', 'RAID controller battery warning, iDRAC license expired, Fan noise after PSU swap', 'Torx T10, Phillips PH1, Anti-static mat', 'Top Cover:2 thumbscrews, slide back.||RAID Card:PCIe slot, battery on card.||HDD Caddy:Tool-less latch on each bay.||iDRAC:Default login root/calvin.', NULL, 'dell-poweredge-t350', 'https://i.dell.com/is/image/dellcontent/content/dam/ss2/product-images/dell-client-products/servers/poweredge-servers/towers/pe-t350/pei-t350-702x702.psd', '1. Power off from front or iDRAC|2. Disconnect power cables|3. Remove top cover (2 thumbscrews)|4. Ground with ESD strap|5. Check RAID battery|6. Remove HDD caddy|7. RAM: push clips|8. Check fans|9. Verify iDRAC port', '1. Install RAM (ECC, match pairs)|2. Insert HDD into caddy + bay|3. Verify RAID card seated|4. Connect power cables|5. Verify fans|6. Connect iDRAC cable|7. Replace cover|8. Power on, check iDRAC|9. Check PERC RAID|10. Monitor temps', NULL, NULL, 'Server Room', 'active', 1, '2026-08-30 00:29:38', '2026-08-30 00:47:37', NULL),
(20, 'HPE', 'ProLiant ML30 Gen10+', 'Server', 'server', '2022', NULL, 'Intel Xeon E-2324G', '16GB DDR4 ECC UDIMM', '2x 480GB SATA SSD RAID1', 'N/A', '4x USB-A 3.2, 2x GbE, 1x iLO5, 1x VGA, 1x serial', 'iLO5 default password not changed, Smart Array cache module, ECC error log', 'Torx T10, Phillips PH0', 'Top Cover:Rear latch, slide off.||HDD Bays:4x LFF or 8x SFF caddies.||RAM:4 DIMM slots, max 128GB.||iLO5:Access via dedicated port or shared NIC.', NULL, 'hpe-ml30-gen10', 'https://www.hpe.com/content/dam/hpe/servers/proliant-ml-servers/product-images/ML30_Gen10_plus.png', '1. Power off, disconnect|2. Remove latch, slide cover off|3. Check iLO5 status|4. RAM: 4 DIMM slots|5. HDD: 4x LFF caddies|6. Check Smart Array cache|7. Check IML for errors|8. Verify PSU LED', '1. Install ECC RAM (matched pairs)|2. Insert HDD caddies|3. Verify Smart Array seated|4. Connect all cables|5. Replace cover|6. Access iLO5|7. Check IML|8. Run Smart Storage Admin', NULL, NULL, 'Server Room', 'active', 1, '2026-08-30 00:29:38', '2026-08-30 00:39:12', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `error_codes`
--

CREATE TABLE `error_codes` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `category` enum('bsod','windows','network','hardware','printer','driver','update','other') DEFAULT 'other',
  `description` text DEFAULT NULL,
  `common_causes` text DEFAULT NULL,
  `fix_steps` text DEFAULT NULL,
  `severity` enum('critical','high','medium','low') DEFAULT 'medium',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `error_codes`
--

INSERT INTO `error_codes` (`id`, `code`, `title`, `category`, `description`, `common_causes`, `fix_steps`, `severity`, `created_at`) VALUES
(1, 'CRITICAL_PROCESS_DIED', 'Critical Process Died', 'bsod', 'Windows critical process crashed. This is a BSOD error that indicates a core system process terminated unexpectedly.', 'Corrupted system files, failing RAM, outdated drivers, malware infection, failing hard drive.', '1. Boot into Safe Mode. 2. Run sfc /scannow. 3. Run DISM /Online /Cleanup-Image /RestoreHealth. 4. Update all drivers. 5. Check RAM with MemTest86. 6. If persists, check Event Viewer for the faulting process.', 'critical', '2026-08-29 10:10:42'),
(2, 'IRQL_NOT_LESS_OR_EQUAL', 'IRQL Not Less Or Equal', 'bsod', 'A kernel-mode process or driver attempted to access a memory address without proper permissions.', 'Faulty RAM, incompatible drivers, corrupted system files, overclocking instability.', '1. Boot Safe Mode. 2. Update/remove recently installed drivers. 3. Run sfc /scannow. 4. Test RAM with MemTest86. 5. Reset BIOS to defaults if overclocked.', 'critical', '2026-08-29 10:10:42'),
(3, 'KERNEL_DATA_INPAGE_ERROR', 'Kernel Data Inpage Error', 'bsod', 'Windows failed to read/write data from the page file. Usually indicates a failing hard drive.', 'Failing hard drive, bad sectors, corrupted NTFS file system, failing RAM.', '1. Run chkdsk C: /f /r. 2. Check S.M.A.R.T. status with CrystalDiskInfo. 3. Backup data immediately. 4. Test RAM. 5. If HDD is failing, replace with SSD.', 'critical', '2026-08-29 10:10:42'),
(4, 'SYSTEM_SERVICE_EXCEPTION', 'System Service Exception', 'bsod', 'A system service process encountered an unexpected exception. Often caused by driver issues.', 'Outdated or corrupt drivers, Windows update issues, third-party software conflicts.', '1. Note which driver caused it (shown in error). 2. Update that specific driver. 3. Run sfc /scannow. 4. Uninstall recent software. 5. Check Windows Update.', 'high', '2026-08-29 10:10:42'),
(5, 'PAGE_FAULT_IN_NONPAGED_AREA', 'Page Fault In Nonpaged Area', 'bsod', 'Windows tried to access data in memory that was not available. Often RAM-related.', 'Faulty RAM, corrupted drivers, disk errors, antivirus conflicts.', '1. Run sfc /scannow. 2. Test RAM with MemTest86. 3. Check disk with chkdsk. 4. Update/remove recently changed drivers. 5. Disable antivirus temporarily to test.', 'critical', '2026-08-29 10:10:42'),
(6, 'WHEA_UNCORRECTABLE_ERROR', 'WHEA Uncorrectable Error', 'bsod', 'Hardware error detected by Windows Hardware Error Architecture. Usually indicates a serious hardware problem.', 'Failing CPU, failing motherboard, unstable overclock, failing RAM, overheating.', '1. Reset BIOS to defaults (remove overclock). 2. Check CPU temperature. 3. Test RAM. 4. Check motherboard capacitors. 5. Escalate for hardware inspection.', 'critical', '2026-08-29 10:10:42'),
(7, 'KERNEL_SECURITY_CHECK_FAILURE', 'Kernel Security Check Failure', 'bsod', 'A kernel security check failed, indicating corruption of a critical kernel data structure.', 'Corrupted system files, incompatible drivers, failed Windows update, failing RAM.', '1. Boot Safe Mode. 2. Run sfc /scannow and DISM. 3. Uninstall recent driver updates. 4. Check RAM. 5. Restore from System Restore point.', 'critical', '2026-08-29 10:10:42'),
(8, '0x80070002', 'Windows Update Error 0x80070002', 'windows', 'Windows Update cannot find the specified file. Common Windows Update failure.', 'Corrupted Windows Update cache, incorrect system date/time, disk space issues.', '1. Run Windows Update Troubleshooter. 2. Clear SoftwareDistribution folder. 3. Reset Windows Update components. 4. Check disk space. 5. Verify system date/time.', 'medium', '2026-08-29 10:10:42'),
(9, '0x800f0922', 'Windows Update Error 0x800f0922', 'windows', 'Windows Update failed. The server timed out or did not respond.', 'VPN interference, firewall blocking, insufficient disk space in system partition.', '1. Disconnect VPN if active. 2. Run DISM. 3. Check disk space on C: drive. 4. Disable firewall temporarily. 5. Try manual update from Microsoft Catalog.', 'medium', '2026-08-29 10:10:42'),
(10, 'WIFI_AUTH_ERROR', 'WiFi Authentication Error', 'network', 'Device cannot authenticate with the WiFi network. Password may be incorrect or security mismatch.', 'Wrong WiFi password, security protocol mismatch, router authentication settings changed.', '1. Forget network and reconnect with correct password. 2. Check security type matches (WPA2/WPA3). 3. Restart router. 4. Try connecting with another device. 5. Reset network settings on device.', 'medium', '2026-08-29 10:10:42'),
(11, 'DHCP_ERROR', 'DHCP Configuration Error', 'network', 'Device cannot obtain an IP address from the DHCP server.', 'DHCP server down, IP pool exhausted, cable loose, network adapter issue.', '1. ipconfig /release then /renew. 2. Restart network adapter. 3. Check cable connection. 4. Restart router/DHCP server. 5. Set static IP temporarily.', 'medium', '2026-08-29 10:10:42'),
(12, 'DNS_PROBE_FINISHED_NXDOMAIN', 'DNS Probe Finished NXDOMAIN', 'network', 'Browser cannot resolve the domain name. DNS lookup failed.', 'DNS server down, DNS cache corrupted, DNS misconfigured, domain does not exist.', '1. ipconfig /flushdns. 2. Change DNS to 8.8.8.8 and 8.8.4.4. 3. Try ping 8.8.8.8 to test connection. 4. Restart DNS client service. 5. Check hosts file for redirects.', 'medium', '2026-08-29 10:10:42'),
(13, 'PRINTER_OFFLINE_0x803C010B', 'Printer Offline Error', 'printer', 'Printer shows as offline in Windows even though it has power.', 'Network connectivity issue, IP address changed, print spooler stuck, driver issue.', '1. Restart print spooler. 2. Check printer IP matches configured IP. 3. Re-add printer. 4. Update printer driver. 5. Ping printer IP to verify network connectivity.', 'medium', '2026-08-29 10:10:42'),
(14, 'PAPER_JAM_ERR', 'Paper Jam Error', 'printer', 'Printer reports paper jam. Paper is stuck inside the printer mechanism.', 'Paper loaded incorrectly, worn pickup rollers, wrong paper type, torn paper pieces inside.', '1. Open all covers and remove visible paper. 2. Check for torn pieces with flashlight. 3. Fan paper stack before loading. 4. Check paper type matches printer specs. 5. Reset printer after clearing jam.', 'low', '2026-08-29 10:10:42'),
(15, 'CPU_FAN_ERROR', 'CPU Fan Error', 'hardware', 'BIOS reports CPU fan is not spinning or not detected. System may shut down to prevent overheating.', 'Fan disconnected, fan failure, dust buildup, fan header loose.', '1. Open case and check CPU fan connection. 2. Clean dust from fan. 3. Try spinning fan manually. 4. Test with known-good fan. 5. Check BIOS fan speed settings.', 'high', '2026-08-29 10:10:42'),
(16, 'NO_BOOT_DEVICE', 'No Boot Device Found', 'hardware', 'BIOS cannot find a bootable device. The operating system cannot be found.', 'Hard drive disconnected, boot order wrong, failing hard drive, corrupted bootloader.', '1. Enter BIOS and check boot order. 2. Check SATA cable connection. 3. Listen for hard drive sounds. 4. Try booting from USB recovery. 5. Check S.M.A.R.T. status.', 'critical', '2026-08-29 10:10:42'),
(17, 'MEMORY_MANAGEMENT', 'Memory Management BSOD', 'bsod', 'Windows encountered a memory management error. Usually indicates RAM or driver issues.', 'Faulty RAM module, driver conflicts, corrupted system files, too many programs running.', '1. Test RAM with MemTest86 overnight. 2. Run sfc /scannow. 3. Update GPU and chipset drivers. 4. Remove recently installed RAM. 5. Check for memory leaks in Task Manager.', 'critical', '2026-08-29 10:10:42'),
(18, 'DRIVER_IRQL_NOT_LESS_OR_EQUAL', 'Driver IRQL Not Less Or Equal', 'bsod', 'A driver attempted to access improper memory address at too high IRQL.', 'Faulty driver (usually network or GPU), malware, corrupted driver files.', '1. Boot Safe Mode. 2. Check Event Viewer for faulting driver name. 3. Uninstall that driver. 4. Download and install latest version from manufacturer. 5. If no specific driver, update all drivers.', 'critical', '2026-08-29 10:10:42'),
(19, 'CRITICAL_PROCESS_DIED', 'Critical Process Died', 'bsod', 'Windows critical process crashed. This is a BSOD error that indicates a core system process terminated unexpectedly.', 'Corrupted system files, failing RAM, outdated drivers, malware infection, failing hard drive.', '1. Boot into Safe Mode. 2. Run sfc /scannow. 3. Run DISM /Online /Cleanup-Image /RestoreHealth. 4. Update all drivers. 5. Check RAM with MemTest86. 6. If persists, check Event Viewer for the faulting process.', 'critical', '2026-08-29 10:12:57'),
(20, 'IRQL_NOT_LESS_OR_EQUAL', 'IRQL Not Less Or Equal', 'bsod', 'A kernel-mode process or driver attempted to access a memory address without proper permissions.', 'Faulty RAM, incompatible drivers, corrupted system files, overclocking instability.', '1. Boot Safe Mode. 2. Update/remove recently installed drivers. 3. Run sfc /scannow. 4. Test RAM with MemTest86. 5. Reset BIOS to defaults if overclocked.', 'critical', '2026-08-29 10:12:58'),
(21, 'KERNEL_DATA_INPAGE_ERROR', 'Kernel Data Inpage Error', 'bsod', 'Windows failed to read/write data from the page file. Usually indicates a failing hard drive.', 'Failing hard drive, bad sectors, corrupted NTFS file system, failing RAM.', '1. Run chkdsk C: /f /r. 2. Check S.M.A.R.T. status with CrystalDiskInfo. 3. Backup data immediately. 4. Test RAM. 5. If HDD is failing, replace with SSD.', 'critical', '2026-08-29 10:12:58'),
(22, 'SYSTEM_SERVICE_EXCEPTION', 'System Service Exception', 'bsod', 'A system service process encountered an unexpected exception. Often caused by driver issues.', 'Outdated or corrupt drivers, Windows update issues, third-party software conflicts.', '1. Note which driver caused it (shown in error). 2. Update that specific driver. 3. Run sfc /scannow. 4. Uninstall recent software. 5. Check Windows Update.', 'high', '2026-08-29 10:12:58'),
(23, 'PAGE_FAULT_IN_NONPAGED_AREA', 'Page Fault In Nonpaged Area', 'bsod', 'Windows tried to access data in memory that was not available. Often RAM-related.', 'Faulty RAM, corrupted drivers, disk errors, antivirus conflicts.', '1. Run sfc /scannow. 2. Test RAM with MemTest86. 3. Check disk with chkdsk. 4. Update/remove recently changed drivers. 5. Disable antivirus temporarily to test.', 'critical', '2026-08-29 10:12:58'),
(24, 'WHEA_UNCORRECTABLE_ERROR', 'WHEA Uncorrectable Error', 'bsod', 'Hardware error detected by Windows Hardware Error Architecture. Usually indicates a serious hardware problem.', 'Failing CPU, failing motherboard, unstable overclock, failing RAM, overheating.', '1. Reset BIOS to defaults (remove overclock). 2. Check CPU temperature. 3. Test RAM. 4. Check motherboard capacitors. 5. Escalate for hardware inspection.', 'critical', '2026-08-29 10:12:58'),
(25, 'KERNEL_SECURITY_CHECK_FAILURE', 'Kernel Security Check Failure', 'bsod', 'A kernel security check failed, indicating corruption of a critical kernel data structure.', 'Corrupted system files, incompatible drivers, failed Windows update, failing RAM.', '1. Boot Safe Mode. 2. Run sfc /scannow and DISM. 3. Uninstall recent driver updates. 4. Check RAM. 5. Restore from System Restore point.', 'critical', '2026-08-29 10:12:58'),
(26, '0x80070002', 'Windows Update Error 0x80070002', 'windows', 'Windows Update cannot find the specified file. Common Windows Update failure.', 'Corrupted Windows Update cache, incorrect system date/time, disk space issues.', '1. Run Windows Update Troubleshooter. 2. Clear SoftwareDistribution folder. 3. Reset Windows Update components. 4. Check disk space. 5. Verify system date/time.', 'medium', '2026-08-29 10:12:58'),
(27, '0x800f0922', 'Windows Update Error 0x800f0922', 'windows', 'Windows Update failed. The server timed out or did not respond.', 'VPN interference, firewall blocking, insufficient disk space in system partition.', '1. Disconnect VPN if active. 2. Run DISM. 3. Check disk space on C: drive. 4. Disable firewall temporarily. 5. Try manual update from Microsoft Catalog.', 'medium', '2026-08-29 10:12:58'),
(28, 'WIFI_AUTH_ERROR', 'WiFi Authentication Error', 'network', 'Device cannot authenticate with the WiFi network. Password may be incorrect or security mismatch.', 'Wrong WiFi password, security protocol mismatch, router authentication settings changed.', '1. Forget network and reconnect with correct password. 2. Check security type matches (WPA2/WPA3). 3. Restart router. 4. Try connecting with another device. 5. Reset network settings on device.', 'medium', '2026-08-29 10:12:58'),
(29, 'DHCP_ERROR', 'DHCP Configuration Error', 'network', 'Device cannot obtain an IP address from the DHCP server.', 'DHCP server down, IP pool exhausted, cable loose, network adapter issue.', '1. ipconfig /release then /renew. 2. Restart network adapter. 3. Check cable connection. 4. Restart router/DHCP server. 5. Set static IP temporarily.', 'medium', '2026-08-29 10:12:58'),
(30, 'DNS_PROBE_FINISHED_NXDOMAIN', 'DNS Probe Finished NXDOMAIN', 'network', 'Browser cannot resolve the domain name. DNS lookup failed.', 'DNS server down, DNS cache corrupted, DNS misconfigured, domain does not exist.', '1. ipconfig /flushdns. 2. Change DNS to 8.8.8.8 and 8.8.4.4. 3. Try ping 8.8.8.8 to test connection. 4. Restart DNS client service. 5. Check hosts file for redirects.', 'medium', '2026-08-29 10:12:58'),
(31, 'PRINTER_OFFLINE_0x803C010B', 'Printer Offline Error', 'printer', 'Printer shows as offline in Windows even though it has power.', 'Network connectivity issue, IP address changed, print spooler stuck, driver issue.', '1. Restart print spooler. 2. Check printer IP matches configured IP. 3. Re-add printer. 4. Update printer driver. 5. Ping printer IP to verify network connectivity.', 'medium', '2026-08-29 10:12:58'),
(32, 'PAPER_JAM_ERR', 'Paper Jam Error', 'printer', 'Printer reports paper jam. Paper is stuck inside the printer mechanism.', 'Paper loaded incorrectly, worn pickup rollers, wrong paper type, torn paper pieces inside.', '1. Open all covers and remove visible paper. 2. Check for torn pieces with flashlight. 3. Fan paper stack before loading. 4. Check paper type matches printer specs. 5. Reset printer after clearing jam.', 'low', '2026-08-29 10:12:58'),
(33, 'CPU_FAN_ERROR', 'CPU Fan Error', 'hardware', 'BIOS reports CPU fan is not spinning or not detected. System may shut down to prevent overheating.', 'Fan disconnected, fan failure, dust buildup, fan header loose.', '1. Open case and check CPU fan connection. 2. Clean dust from fan. 3. Try spinning fan manually. 4. Test with known-good fan. 5. Check BIOS fan speed settings.', 'high', '2026-08-29 10:12:58'),
(34, 'NO_BOOT_DEVICE', 'No Boot Device Found', 'hardware', 'BIOS cannot find a bootable device. The operating system cannot be found.', 'Hard drive disconnected, boot order wrong, failing hard drive, corrupted bootloader.', '1. Enter BIOS and check boot order. 2. Check SATA cable connection. 3. Listen for hard drive sounds. 4. Try booting from USB recovery. 5. Check S.M.A.R.T. status.', 'critical', '2026-08-29 10:12:58'),
(35, 'MEMORY_MANAGEMENT', 'Memory Management BSOD', 'bsod', 'Windows encountered a memory management error. Usually indicates RAM or driver issues.', 'Faulty RAM module, driver conflicts, corrupted system files, too many programs running.', '1. Test RAM with MemTest86 overnight. 2. Run sfc /scannow. 3. Update GPU and chipset drivers. 4. Remove recently installed RAM. 5. Check for memory leaks in Task Manager.', 'critical', '2026-08-29 10:12:58'),
(36, 'DRIVER_IRQL_NOT_LESS_OR_EQUAL', 'Driver IRQL Not Less Or Equal', 'bsod', 'A driver attempted to access improper memory address at too high IRQL.', 'Faulty driver (usually network or GPU), malware, corrupted driver files.', '1. Boot Safe Mode. 2. Check Event Viewer for faulting driver name. 3. Uninstall that driver. 4. Download and install latest version from manufacturer. 5. If no specific driver, update all drivers.', 'critical', '2026-08-29 10:12:58'),
(37, 'CRITICAL_PROCESS_DIED', 'Critical Process Died', 'bsod', 'Windows critical process crashed. BSOD error indicating a core system process terminated unexpectedly.', 'Corrupted system files, failing RAM, outdated drivers, malware.', 'Boot Safe Mode. Run sfc /scannow. Run DISM. Update drivers. Test RAM with MemTest86.', 'critical', '2026-08-29 10:17:52'),
(38, 'IRQL_NOT_LESS_OR_EQUAL', 'IRQL Not Less Or Equal', 'bsod', 'Kernel-mode process attempted to access memory without proper permissions.', 'Faulty RAM, incompatible drivers, corrupted system files.', 'Boot Safe Mode. Update/remove recently installed drivers. Run sfc /scannow. Test RAM.', 'critical', '2026-08-29 10:17:52'),
(39, 'KERNEL_DATA_INPAGE_ERROR', 'Kernel Data Inpage Error', 'bsod', 'Windows failed to read/write data from the page file. Usually indicates failing hard drive.', 'Failing hard drive, bad sectors, corrupted NTFS, failing RAM.', 'Run chkdsk C: /f /r. Check S.M.A.R.T. status. Backup data immediately. Test RAM.', 'critical', '2026-08-29 10:17:52'),
(40, 'SYSTEM_SERVICE_EXCEPTION', 'System Service Exception', 'bsod', 'A system service process encountered an unexpected exception.', 'Outdated drivers, Windows update issues, software conflicts.', 'Note faulting driver. Update that driver. Run sfc /scannow. Uninstall recent software.', 'high', '2026-08-29 10:17:52'),
(41, 'PAGE_FAULT_IN_NONPAGED_AREA', 'Page Fault In Nonpaged Area', 'bsod', 'Windows tried to access data in memory that was not available. Often RAM-related.', 'Faulty RAM, corrupted drivers, disk errors.', 'Run sfc /scannow. Test RAM with MemTest86. Check disk with chkdsk. Update drivers.', 'critical', '2026-08-29 10:17:52'),
(42, 'WHEA_UNCORRECTABLE_ERROR', 'WHEA Uncorrectable Error', 'bsod', 'Hardware error detected. Usually indicates serious hardware problem.', 'Failing CPU, motherboard, unstable overclock, failing RAM.', 'Reset BIOS to defaults. Check CPU temperature. Test RAM. Check motherboard. Escalate.', 'critical', '2026-08-29 10:17:52'),
(43, 'KERNEL_SECURITY_CHECK_FAILURE', 'Kernel Security Check Failure', 'bsod', 'Kernel security check failed. Corruption of critical kernel data structure.', 'Corrupted system files, incompatible drivers, failed Windows update.', 'Boot Safe Mode. Run sfc and DISM. Uninstall recent driver updates. Test RAM.', 'critical', '2026-08-29 10:17:52'),
(44, 'MEMORY_MANAGEMENT', 'Memory Management BSOD', 'bsod', 'Windows encountered a memory management error.', 'Faulty RAM, driver conflicts, corrupted system files.', 'Test RAM with MemTest86 overnight. Run sfc /scannow. Update GPU and chipset drivers.', 'critical', '2026-08-29 10:17:52'),
(45, 'DRIVER_IRQL_NOT_LESS_OR_EQUAL', 'Driver IRQL Error', 'bsod', 'A driver attempted to access improper memory address.', 'Faulty driver (network/GPU), malware, corrupted drivers.', 'Boot Safe Mode. Check Event Viewer for faulting driver. Uninstall and reinstall that driver.', 'critical', '2026-08-29 10:17:52'),
(46, '0x80070002', 'Windows Update Error', 'windows', 'Windows Update cannot find specified file.', 'Corrupted update cache, incorrect date/time, disk space.', 'Run Update Troubleshooter. Clear SoftwareDistribution folder. Reset Update components.', 'medium', '2026-08-29 10:17:52'),
(47, '0x800f0922', 'Windows Update Error', 'windows', 'Windows Update failed. Server timed out.', 'VPN interference, firewall blocking, insufficient disk space.', 'Disconnect VPN. Run DISM. Check disk space. Disable firewall temporarily.', 'medium', '2026-08-29 10:17:52'),
(48, 'DNS_PROBE_FINISHED_NXDOMAIN', 'DNS Resolution Failed', 'network', 'Browser cannot resolve the domain name.', 'DNS server down, cache corrupted, DNS misconfigured.', 'ipconfig /flushdns. Change DNS to 8.8.8.8. Test ping 8.8.8.8. Restart DNS client.', 'medium', '2026-08-29 10:17:52'),
(49, 'NO_BOOT_DEVICE', 'No Boot Device Found', 'hardware', 'BIOS cannot find a bootable device.', 'HDD disconnected, boot order wrong, failing drive, corrupted bootloader.', 'Enter BIOS, check boot order. Check SATA cable. Listen for drive sounds. Try USB recovery.', 'critical', '2026-08-29 10:17:52'),
(50, 'CPU_FAN_ERROR', 'CPU Fan Error', 'hardware', 'BIOS reports CPU fan not spinning or not detected.', 'Fan disconnected, failure, dust buildup, header loose.', 'Open case, check fan connection. Clean dust. Test with known-good fan. Check BIOS settings.', 'high', '2026-08-29 10:17:52'),
(51, 'CRITICAL_PROCESS_DIED', 'Critical Process Died', 'bsod', 'Windows critical process crashed. BSOD error indicating a core system process terminated unexpectedly.', 'Corrupted system files, failing RAM, outdated drivers, malware.', 'Boot Safe Mode. Run sfc /scannow. Run DISM. Update drivers. Test RAM with MemTest86.', 'critical', '2026-08-29 10:19:27'),
(52, 'IRQL_NOT_LESS_OR_EQUAL', 'IRQL Not Less Or Equal', 'bsod', 'Kernel-mode process attempted to access memory without proper permissions.', 'Faulty RAM, incompatible drivers, corrupted system files.', 'Boot Safe Mode. Update/remove recently installed drivers. Run sfc /scannow. Test RAM.', 'critical', '2026-08-29 10:19:28'),
(53, 'KERNEL_DATA_INPAGE_ERROR', 'Kernel Data Inpage Error', 'bsod', 'Windows failed to read/write data from the page file. Usually indicates failing hard drive.', 'Failing hard drive, bad sectors, corrupted NTFS, failing RAM.', 'Run chkdsk C: /f /r. Check S.M.A.R.T. status. Backup data immediately. Test RAM.', 'critical', '2026-08-29 10:19:28'),
(54, 'SYSTEM_SERVICE_EXCEPTION', 'System Service Exception', 'bsod', 'A system service process encountered an unexpected exception.', 'Outdated drivers, Windows update issues, software conflicts.', 'Note faulting driver. Update that driver. Run sfc /scannow. Uninstall recent software.', 'high', '2026-08-29 10:19:28'),
(55, 'PAGE_FAULT_IN_NONPAGED_AREA', 'Page Fault In Nonpaged Area', 'bsod', 'Windows tried to access data in memory that was not available. Often RAM-related.', 'Faulty RAM, corrupted drivers, disk errors.', 'Run sfc /scannow. Test RAM with MemTest86. Check disk with chkdsk. Update drivers.', 'critical', '2026-08-29 10:19:28'),
(56, 'WHEA_UNCORRECTABLE_ERROR', 'WHEA Uncorrectable Error', 'bsod', 'Hardware error detected. Usually indicates serious hardware problem.', 'Failing CPU, motherboard, unstable overclock, failing RAM.', 'Reset BIOS to defaults. Check CPU temperature. Test RAM. Check motherboard. Escalate.', 'critical', '2026-08-29 10:19:28'),
(57, 'KERNEL_SECURITY_CHECK_FAILURE', 'Kernel Security Check Failure', 'bsod', 'Kernel security check failed. Corruption of critical kernel data structure.', 'Corrupted system files, incompatible drivers, failed Windows update.', 'Boot Safe Mode. Run sfc and DISM. Uninstall recent driver updates. Test RAM.', 'critical', '2026-08-29 10:19:28'),
(58, 'MEMORY_MANAGEMENT', 'Memory Management BSOD', 'bsod', 'Windows encountered a memory management error.', 'Faulty RAM, driver conflicts, corrupted system files.', 'Test RAM with MemTest86 overnight. Run sfc /scannow. Update GPU and chipset drivers.', 'critical', '2026-08-29 10:19:28'),
(59, 'DRIVER_IRQL_NOT_LESS_OR_EQUAL', 'Driver IRQL Error', 'bsod', 'A driver attempted to access improper memory address.', 'Faulty driver (network/GPU), malware, corrupted drivers.', 'Boot Safe Mode. Check Event Viewer for faulting driver. Uninstall and reinstall that driver.', 'critical', '2026-08-29 10:19:28'),
(60, '0x80070002', 'Windows Update Error', 'windows', 'Windows Update cannot find specified file.', 'Corrupted update cache, incorrect date/time, disk space.', 'Run Update Troubleshooter. Clear SoftwareDistribution folder. Reset Update components.', 'medium', '2026-08-29 10:19:28'),
(61, '0x800f0922', 'Windows Update Error', 'windows', 'Windows Update failed. Server timed out.', 'VPN interference, firewall blocking, insufficient disk space.', 'Disconnect VPN. Run DISM. Check disk space. Disable firewall temporarily.', 'medium', '2026-08-29 10:19:28'),
(62, 'DNS_PROBE_FINISHED_NXDOMAIN', 'DNS Resolution Failed', 'network', 'Browser cannot resolve the domain name.', 'DNS server down, cache corrupted, DNS misconfigured.', 'ipconfig /flushdns. Change DNS to 8.8.8.8. Test ping 8.8.8.8. Restart DNS client.', 'medium', '2026-08-29 10:19:28'),
(63, 'NO_BOOT_DEVICE', 'No Boot Device Found', 'hardware', 'BIOS cannot find a bootable device.', 'HDD disconnected, boot order wrong, failing drive, corrupted bootloader.', 'Enter BIOS, check boot order. Check SATA cable. Listen for drive sounds. Try USB recovery.', 'critical', '2026-08-29 10:19:28'),
(64, 'CPU_FAN_ERROR', 'CPU Fan Error', 'hardware', 'BIOS reports CPU fan not spinning or not detected.', 'Fan disconnected, failure, dust buildup, header loose.', 'Open case, check fan connection. Clean dust. Test with known-good fan. Check BIOS settings.', 'high', '2026-08-29 10:19:28'),
(65, 'CRITICAL_PROCESS_DIED', 'Critical Process Died', 'bsod', 'Windows critical process crashed. BSOD error indicating a core system process terminated unexpectedly.', 'Corrupted system files, failing RAM, outdated drivers, malware.', 'Boot Safe Mode. Run sfc /scannow. Run DISM. Update drivers. Test RAM with MemTest86.', 'critical', '2026-08-29 10:20:27'),
(66, 'IRQL_NOT_LESS_OR_EQUAL', 'IRQL Not Less Or Equal', 'bsod', 'Kernel-mode process attempted to access memory without proper permissions.', 'Faulty RAM, incompatible drivers, corrupted system files.', 'Boot Safe Mode. Update/remove recently installed drivers. Run sfc /scannow. Test RAM.', 'critical', '2026-08-29 10:20:27'),
(67, 'KERNEL_DATA_INPAGE_ERROR', 'Kernel Data Inpage Error', 'bsod', 'Windows failed to read/write data from the page file. Usually indicates failing hard drive.', 'Failing hard drive, bad sectors, corrupted NTFS, failing RAM.', 'Run chkdsk C: /f /r. Check S.M.A.R.T. status. Backup data immediately. Test RAM.', 'critical', '2026-08-29 10:20:27'),
(68, 'SYSTEM_SERVICE_EXCEPTION', 'System Service Exception', 'bsod', 'A system service process encountered an unexpected exception.', 'Outdated drivers, Windows update issues, software conflicts.', 'Note faulting driver. Update that driver. Run sfc /scannow. Uninstall recent software.', 'high', '2026-08-29 10:20:27'),
(69, 'PAGE_FAULT_IN_NONPAGED_AREA', 'Page Fault In Nonpaged Area', 'bsod', 'Windows tried to access data in memory that was not available. Often RAM-related.', 'Faulty RAM, corrupted drivers, disk errors.', 'Run sfc /scannow. Test RAM with MemTest86. Check disk with chkdsk. Update drivers.', 'critical', '2026-08-29 10:20:27'),
(70, 'WHEA_UNCORRECTABLE_ERROR', 'WHEA Uncorrectable Error', 'bsod', 'Hardware error detected. Usually indicates serious hardware problem.', 'Failing CPU, motherboard, unstable overclock, failing RAM.', 'Reset BIOS to defaults. Check CPU temperature. Test RAM. Check motherboard. Escalate.', 'critical', '2026-08-29 10:20:27'),
(71, 'KERNEL_SECURITY_CHECK_FAILURE', 'Kernel Security Check Failure', 'bsod', 'Kernel security check failed. Corruption of critical kernel data structure.', 'Corrupted system files, incompatible drivers, failed Windows update.', 'Boot Safe Mode. Run sfc and DISM. Uninstall recent driver updates. Test RAM.', 'critical', '2026-08-29 10:20:27'),
(72, 'MEMORY_MANAGEMENT', 'Memory Management BSOD', 'bsod', 'Windows encountered a memory management error.', 'Faulty RAM, driver conflicts, corrupted system files.', 'Test RAM with MemTest86 overnight. Run sfc /scannow. Update GPU and chipset drivers.', 'critical', '2026-08-29 10:20:27'),
(73, 'DRIVER_IRQL_NOT_LESS_OR_EQUAL', 'Driver IRQL Error', 'bsod', 'A driver attempted to access improper memory address.', 'Faulty driver (network/GPU), malware, corrupted drivers.', 'Boot Safe Mode. Check Event Viewer for faulting driver. Uninstall and reinstall that driver.', 'critical', '2026-08-29 10:20:27'),
(74, '0x80070002', 'Windows Update Error', 'windows', 'Windows Update cannot find specified file.', 'Corrupted update cache, incorrect date/time, disk space.', 'Run Update Troubleshooter. Clear SoftwareDistribution folder. Reset Update components.', 'medium', '2026-08-29 10:20:27'),
(75, '0x800f0922', 'Windows Update Error', 'windows', 'Windows Update failed. Server timed out.', 'VPN interference, firewall blocking, insufficient disk space.', 'Disconnect VPN. Run DISM. Check disk space. Disable firewall temporarily.', 'medium', '2026-08-29 10:20:27'),
(76, 'DNS_PROBE_FINISHED_NXDOMAIN', 'DNS Resolution Failed', 'network', 'Browser cannot resolve the domain name.', 'DNS server down, cache corrupted, DNS misconfigured.', 'ipconfig /flushdns. Change DNS to 8.8.8.8. Test ping 8.8.8.8. Restart DNS client.', 'medium', '2026-08-29 10:20:27'),
(77, 'NO_BOOT_DEVICE', 'No Boot Device Found', 'hardware', 'BIOS cannot find a bootable device.', 'HDD disconnected, boot order wrong, failing drive, corrupted bootloader.', 'Enter BIOS, check boot order. Check SATA cable. Listen for drive sounds. Try USB recovery.', 'critical', '2026-08-29 10:20:27'),
(78, 'CPU_FAN_ERROR', 'CPU Fan Error', 'hardware', 'BIOS reports CPU fan not spinning or not detected.', 'Fan disconnected, failure, dust buildup, header loose.', 'Open case, check fan connection. Clean dust. Test with known-good fan. Check BIOS settings.', 'high', '2026-08-29 10:20:27');

-- --------------------------------------------------------

--
-- Table structure for table `escalations`
--

CREATE TABLE `escalations` (
  `id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `session_id` int(11) DEFAULT NULL,
  `escalated_by` int(11) NOT NULL,
  `escalated_to` int(11) DEFAULT NULL,
  `reason` text NOT NULL,
  `summary` text DEFAULT NULL,
  `status` enum('pending','acknowledged','in_progress','resolved') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `item_type` varchar(50) NOT NULL,
  `item_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invitations`
--

CREATE TABLE `invitations` (
  `id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `email` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `invited_by` int(11) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `knowledge_articles`
--

CREATE TABLE `knowledge_articles` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `category` varchar(50) NOT NULL,
  `issue` text DEFAULT NULL,
  `symptoms` text DEFAULT NULL,
  `root_cause` text DEFAULT NULL,
  `solution` text NOT NULL,
  `tools_used` text DEFAULT NULL,
  `commands_used` text DEFAULT NULL,
  `device_type` varchar(50) DEFAULT NULL,
  `manufacturer` varchar(100) DEFAULT NULL,
  `model` varchar(100) DEFAULT NULL,
  `author_id` int(11) NOT NULL,
  `reviewer_id` int(11) DEFAULT NULL,
  `status` enum('draft','submitted','under_review','approved','published','rejected','archived') DEFAULT 'draft',
  `version` decimal(3,1) DEFAULT 1.0,
  `quality_score` decimal(5,2) DEFAULT 0.00,
  `success_count` int(11) DEFAULT 0,
  `use_count` int(11) DEFAULT 0,
  `helpful_count` int(11) DEFAULT 0,
  `not_helpful_count` int(11) DEFAULT 0,
  `last_reviewed_at` timestamp NULL DEFAULT NULL,
  `next_review_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `knowledge_articles`
--

INSERT INTO `knowledge_articles` (`id`, `title`, `category`, `issue`, `symptoms`, `root_cause`, `solution`, `tools_used`, `commands_used`, `device_type`, `manufacturer`, `model`, `author_id`, `reviewer_id`, `status`, `version`, `quality_score`, `success_count`, `use_count`, `helpful_count`, `not_helpful_count`, `last_reviewed_at`, `next_review_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2, 'How to Fix No Display Issue', 'Display', 'Computer turns on but monitor shows no image or black screen.', 'Monitor power light on but no image, No Signal message, PC seems running but display is black.', 'Loose video cable, wrong input source on monitor, failed GPU, or dead RAM.', '1. Check monitor power and cable. 2. Try different video port (HDMI/DP/VGA). 3. Press monitor source button. 4. Reseat RAM and GPU. 5. Try integrated graphics. 6. Test with spare monitor.', 'Spare video cable, spare monitor, screwdriver', NULL, NULL, NULL, NULL, 1, NULL, 'published', 1.0, 0.00, 0, 0, 0, 0, NULL, NULL, '2026-08-29 10:12:58', '2026-08-29 23:55:26', '2026-08-29 23:55:26'),
(3, 'Computer Won\'t Turn On - Power Troubleshooting', 'Power', 'Computer does not respond at all when power button is pressed.', 'No lights, no fans, no beeps, completely dead.', 'Bad outlet, loose power cable, PSU switch off, dead PSU, or dead motherboard.', '1. Test outlet with charger. 2. Check PSU switch. 3. Power drain (hold power 30s unplugged). 4. Check internal connections. 5. Test PSU with multimeter. 6. Try spare PSU.', 'Multimeter, spare PSU, screwdriver', NULL, NULL, NULL, NULL, 1, NULL, 'published', 1.0, 0.00, 0, 0, 0, 0, NULL, NULL, '2026-08-29 10:12:58', '2026-08-29 23:55:26', '2026-08-29 23:55:26'),
(4, 'WiFi Not Working - Network Troubleshooting', 'Network', 'Device cannot connect to WiFi or WiFi keeps disconnecting.', 'WiFi networks not showing, connection drops, no internet on WiFi.', 'Disabled WiFi adapter, wrong password, driver issue, interference, or router problem.', '1. Toggle WiFi off/on. 2. Forget and reconnect. 3. Restart WiFi adapter in Device Manager. 4. Update WiFi driver. 5. Reset network stack (netsh winsock reset). 6. Try Ethernet to isolate.', 'Ethernet cable for testing', NULL, NULL, NULL, NULL, 1, NULL, 'published', 1.0, 0.00, 0, 0, 0, 0, NULL, NULL, '2026-08-29 10:12:58', '2026-08-29 23:55:26', '2026-08-29 23:55:26'),
(5, 'Printer Not Printing - Fix Guide', 'Printer', 'Printer does not respond to print jobs or shows as offline.', 'Print jobs stuck, printer offline, nothing happens when printing.', 'Print spooler stuck, wrong printer selected, offline mode, or driver issue.', '1. Restart print spooler (net stop spooler, net start spooler). 2. Check not in Use Printer Offline mode. 3. Clear print queue. 4. Reinstall printer driver. 5. Test with USB direct.', 'USB cable for testing', NULL, NULL, NULL, NULL, 1, NULL, 'published', 1.0, 0.00, 0, 0, 0, 0, NULL, NULL, '2026-08-29 10:12:58', '2026-08-29 23:55:26', '2026-08-29 23:55:26'),
(6, 'BSOD Blue Screen Fix Guide', 'Software', 'Windows shows blue screen with error code and restarts.', 'Blue screen appears, PC restarts, error code shown.', 'Corrupted system files, bad drivers, failing RAM, or hardware failure.', '1. Note error code. 2. Boot Safe Mode. 3. Run sfc /scannow. 4. Run DISM. 5. Update drivers. 6. Test RAM with MemTest86. 7. Check Event Viewer.', 'USB drive for Safe Mode/MemTest86', NULL, NULL, NULL, NULL, 1, NULL, 'published', 1.0, 0.00, 0, 0, 0, 0, NULL, NULL, '2026-08-29 10:12:58', '2026-08-29 23:55:26', '2026-08-29 23:55:26'),
(7, 'Slow Computer Performance Fix', 'Software', 'PC is noticeably slow, takes long to respond.', 'High CPU/RAM usage, slow boot, app freezes.', 'Too many startup programs, malware, full disk, insufficient RAM, or failing hard drive.', '1. Restart PC. 2. Check Task Manager for high usage. 3. Disable startup programs. 4. Run disk cleanup. 5. Run SFC. 6. Check for malware. 7. Consider SSD upgrade.', 'Task Manager', NULL, NULL, NULL, NULL, 1, NULL, 'published', 1.0, 0.00, 0, 0, 0, 0, NULL, NULL, '2026-08-29 10:12:58', '2026-08-29 23:55:26', '2026-08-29 23:55:26'),
(8, 'CCTV Camera Not Recording Fix', 'CCTV', 'NVR/DVR shows camera but is not recording.', 'Live view works but no playback, recording stopped, NVR shows offline camera.', 'Full disk, camera disconnected, IP conflict, or NVR configuration issue.', '1. Check NVR disk space. 2. Restart NVR. 3. Ping camera IP. 4. Check camera power (PoE light). 5. Re-add camera to NVR. 6. Check recording schedule settings.', 'Monitor for NVR, Ethernet cable', NULL, NULL, NULL, NULL, 1, NULL, 'published', 1.0, 0.00, 0, 0, 0, 0, NULL, NULL, '2026-08-29 10:12:58', '2026-08-29 23:55:26', '2026-08-29 23:55:26'),
(9, 'No Sound - Audio Fix Guide', 'Audio', 'No audio output from speakers or headphones.', 'Volume icon muted, no sound from speakers, audio device not detected.', 'Muted audio, wrong output device, driver issue, or hardware failure.', '1. Check volume not muted. 2. Select correct output device. 3. Test with different speakers. 4. Run Audio Troubleshooter. 5. Reinstall audio driver. 6. Try USB audio adapter.', 'Working speakers/headphones', NULL, NULL, NULL, NULL, 1, NULL, 'published', 1.0, 0.00, 0, 0, 0, 0, NULL, NULL, '2026-08-29 10:12:58', '2026-08-29 23:55:26', '2026-08-29 23:55:26'),
(26, 'No Display Troubleshooting', 'Display', 'Computer turns on but monitor shows no image or completely black screen.', 'Black screen, No signal message, Monitor LED on but no image, Flickering display, Monitor shows No Input', 'Most commonly caused by loose display cables, improperly seated RAM, failed GPU, wrong monitor input source, or monitor hardware failure.', 'Check Power:Verify both computer and monitor have power cables connected and powered on.\nCheck Monitor Input Source:Press the input/source button on the monitor to select the correct input (HDMI, DisplayPort, VGA).\nReseat Display Cable:Power off, disconnect and firmly reconnect HDMI/DisplayPort/VGA cable at both ends.\nTest Different Cable:Try a known-good display cable to rule out cable failure.\nTest Different Monitor:Connect a known-good monitor to isolate whether it is the monitor or PC.\nReseat RAM:Power off, open case, remove and reseat RAM modules firmly.\nReseat GPU:Power off, remove and reinsert the graphics card, check PCIe power connectors.\nTest with Integrated Graphics:Remove GPU and connect monitor to motherboard video output.', 'Known-good monitor, Known-good display cable, Phillips screwdriver, ESD wrist strap', 'systeminfo,msinfo32', 'Desktop', NULL, NULL, 1, NULL, 'published', 1.0, 0.00, 1, 19, 1, 0, NULL, NULL, '2026-08-29 10:20:27', '2026-08-31 02:53:32', NULL),
(27, 'Power Issues Guide', 'Power', 'Computer does not respond at all when power button is pressed - completely dead.', 'No power at all, No LEDs, No fan spin, No beep codes, Completely dead when pressing power button', 'Caused by failed PSU, loose power cable, tripped surge protector, failed power button, or motherboard failure.', 'Check Power Outlet:Plug another device into the outlet to verify it works.\nCheck PSU Switch:Make sure the PSU switch on the back of the PC is in the ON position (I).\nReseat Power Cable:Disconnect and reconnect the power cable at both PSU and wall outlet.\nTest Different Cable:Try a different power cable if available.\nCheck Surge Protector:Make sure surge protector is on and working.\nTest PSU with Multimeter:Use a multimeter to test PSU voltages on the 24-pin connector (should show 12V on yellow, 5V on red).\nCheck Power Button:Open case and short the power button pins on the motherboard header to test.\nReseat 24-pin and CPU Power:Disconnect and firmly reconnect the 24-pin ATX and 4/8-pin CPU power connectors.\nTry Spare PSU:If possible, test with a known-good power supply.\nCheck Motherboard:Look for bulging capacitors or burnt components.', 'Multimeter, Spare PSU, Phillips screwdriver, ESD wrist strap', '', 'Desktop', NULL, NULL, 1, NULL, 'published', 1.0, 0.00, 0, 0, 0, 0, NULL, NULL, '2026-08-29 10:20:27', '2026-08-29 23:57:39', NULL),
(28, 'WiFi Troubleshooting', 'Network', 'Device cannot connect to WiFi or WiFi keeps disconnecting.', 'Cannot connect to WiFi, WiFi keeps disconnecting, Connected but no internet, Slow WiFi speeds, Limited connectivity', 'Caused by incorrect WiFi password, router issues, driver problems, DNS configuration, or signal interference.', 'Check WiFi is ON:Make sure WiFi switch on laptop is on or airplane mode is off.\nForget and Reconnect:Forget the WiFi network and reconnect with the correct password.\nRestart Router:Unplug router for 30 seconds, plug back in, wait for full boot.\nCheck Other Devices:See if other devices can connect to rule out device-specific issue.\nUpdate WiFi Driver:Open Device Manager, expand Network adapters, right-click WiFi adapter, Update driver.\nRun Network Troubleshooter:Settings > Network & Internet > Status > Network troubleshooter.\nReset Network Settings:Settings > Network & Internet > Advanced network settings > Network reset.\nFlush DNS:Open CMD as admin, run: ipconfig /flushdns then ipconfig /registerdns.\nRelease and Renew IP:Run: ipconfig /release then ipconfig /renew in CMD as admin.\nCheck DNS Settings:Set DNS to 8.8.8.8 and 8.8.4.4 in adapter IPv4 settings.', 'None for basic steps, USB WiFi adapter for testing', 'ipconfig /flushdns,ipconfig /release,ipconfig /renew,netsh winsock reset', 'Desktop', NULL, NULL, 1, NULL, 'published', 1.0, 0.00, 0, 0, 0, 0, NULL, NULL, '2026-08-29 10:20:27', '2026-08-29 23:57:39', NULL),
(29, 'Printer Fix Guide', 'Printer', 'Printer does not respond to print jobs or shows as offline.', 'Printer shows offline, Print jobs stuck in queue, Documents not printing, Printer not responding, Error lights on printer', 'Caused by loose USB cable, wrong default printer, spooler service stopped, driver issues, or network connectivity problems for network printers.', 'Check Physical Connection:Ensure USB cable is firmly connected or network cable is plugged in.\nPower Cycle Printer:Turn printer off, wait 30 seconds, turn back on.\nClear Print Queue:Open Settings > Devices > Printers, open queue, cancel all documents.\nRestart Print Spooler:Open CMD as admin, run: net stop spooler then net start spooler.\nSet as Default Printer:Go to Settings > Devices > Printers, right-click the printer, select Set as default.\nUpdate Printer Driver:Download latest driver from manufacturer website.\nCheck Network:For network printers, ping the printer IP address from CMD.\nRemove and Re-add Printer:Remove the printer from Settings > Devices, then add it back.\nCheck Printer Display:Look at printer LCD for any error messages or warning lights.\nRun Printer Troubleshooter:Settings > Update & Security > Troubleshoot > Printer.', 'USB cable, Ethernet cable (for network printers), Phillips screwdriver', 'net stop spooler,net start spooler,ping,ipconfig', 'Printer', NULL, NULL, 1, NULL, 'published', 1.0, 0.00, 0, 0, 0, 0, NULL, NULL, '2026-08-29 10:20:27', '2026-08-29 23:57:39', NULL),
(30, 'BSOD Fix Guide', 'Software', 'Windows shows blue screen with error code and automatically restarts.', 'Blue Screen of Death, BSOD, System crash with error code, Automatic restart, Stop error', 'Caused by faulty RAM, failing hard drive, incompatible drivers, overheating, or hardware failure.', 'Note the Stop Code:Write down the BSOD error code (e.g., IRQL_NOT_LESS_OR_EQUAL).\nBoot into Safe Mode:Restart, hold Shift, select Troubleshoot > Advanced > Startup Settings > Safe Mode.\nUninstall Recent Driver:If BSOD started after driver install, boot Safe Mode and uninstall it.\nRun SFC Scan:Open CMD as admin, run: sfc /scannow to repair system files.\nRun DISM:Run: DISM /Online /Cleanup-Image /RestoreHealth in CMD as admin.\nCheck RAM:Run Windows Memory Diagnostic (mdsched.exe) or use MemTest86 bootable USB.\nCheck Disk:Run: chkdsk C: /f /r in CMD as admin (may require restart).\nCheck Event Viewer:Open eventvwr.msc, check System and Application logs for critical errors.\nUpdate Windows:Settings > Update & Security > Windows Update > Check for updates.\nCheck Temps:Use HWMonitor to check CPU/GPU temperatures for overheating.', 'MemTest86 bootable USB, Event Viewer, HWMonitor', 'sfc /scannow,DISM /Online /Cleanup-Image /RestoreHealth,chkdsk C: /f /r,mdsched.exe', 'Desktop', NULL, NULL, 1, NULL, 'published', 1.0, 0.00, 0, 0, 0, 0, NULL, NULL, '2026-08-29 10:20:27', '2026-08-29 23:57:39', NULL),
(31, 'Slow PC Fix', 'Software', 'PC is noticeably slow, takes long to respond and load applications.', 'Slow boot time, Applications take long to open, System freezes, High disk usage, High CPU usage, Laggy mouse', 'Caused by too many startup programs, full hard drive, fragmented disk, malware, insufficient RAM, or failing hardware.', 'Check Task Manager:Press Ctrl+Shift+Esc, check CPU, Memory, and Disk usage percentages.\nDisable Startup Apps:Task Manager > Startup tab, disable unnecessary programs.\nClean Disk Space:Run Disk Cleanup (cleanmgr) or delete temp files.\nCheck for Malware:Run a full scan with Windows Defender or Malwarebytes.\nUpgrade to SSD:If using HDD, upgrading to SSD dramatically improves performance.\nAdd More RAM:If RAM usage is consistently above 80%, consider adding more RAM.\nRun Disk Defrag:For HDD only (not SSD): Optimize Drives from Start menu.\nCheck for Windows Updates:Install all pending Windows updates.\nUninstall Unused Programs:Control Panel > Programs > Uninstall unused software.\nCheck for Background Processes:Task Manager > Details tab, sort by CPU or Memory.', 'Malwarebytes, CrystalDiskInfo (check disk health)', 'cleanmgr,defrag C:', 'Desktop', NULL, NULL, 1, NULL, 'published', 1.0, 0.00, 0, 3, 0, 0, NULL, NULL, '2026-08-29 10:20:27', '2026-08-31 02:55:28', NULL),
(32, 'CCTV Recording Issues', 'CCTV', 'NVR/DVR shows camera but is not recording or camera shows offline.', 'Camera shows no recording, No live feed, Playback empty, Camera offline in NVR, Motion detection not working', 'Caused by network connectivity issues, full hard drive, power supply failure, or configuration problems.', 'Check Camera Power:Verify PoE injector/switch is powered and cable is connected.\nCheck Network Cable:Ensure Ethernet cable is securely connected at both ends.\nPing Camera IP:Open CMD and ping the camera IP to verify network connectivity.\nCheck NVR Storage:NVR/DVR interface > check if hard drive is full or failed.\nFormat Recording Disk:If disk is corrupted, format it through NVR settings (WARNING: erases footage).\nCheck Recording Schedule:NVR settings > make sure recording schedule is set to continuous or motion-triggered.\nRestart NVR and Camera:Power cycle both the NVR and camera.\nUpdate Camera Firmware:Download latest firmware from manufacturer website.\nCheck Camera Settings:Log into camera web interface, verify RTSP stream and recording settings.\nCheck Power Supply:Test PoE switch/injector output voltage.', 'Network cable tester, PoE tester, PC for camera web interface access', 'ping,ping -t', 'CCTV', NULL, NULL, 1, NULL, 'published', 1.0, 0.00, 0, 1, 0, 0, NULL, NULL, '2026-08-29 10:20:27', '2026-08-31 03:00:36', NULL),
(33, 'No Sound Fix', 'Audio', 'No audio output from speakers or headphones.', 'No audio output, Crackling sound, Audio cuts out, Speakers not detected, Headphone jack not working', 'Caused by muted audio, wrong output device, disabled audio service, driver issues, or hardware failure.', 'Check Volume:Click speaker icon in taskbar, make sure not muted and volume is up.\nCheck Output Device:Right-click speaker icon > Sound settings > select correct output device.\nTest Different Audio:Try playing different audio (YouTube, local file) to rule out app-specific issue.\nRestart Audio Service:Open CMD as admin, run: net stop Audiosrv then net start Audiosrv.\nUpdate Audio Driver:Device Manager > Sound controllers > right-click > Update driver.\nReinstall Audio Driver:Device Manager > Sound controllers > right-click > Uninstall, restart PC to reinstall.\nCheck Audio Connections:Make sure speakers/headphones are plugged into the correct jack (usually green).\nTest Different Port:Try front and back audio jacks on the PC.\nDisable Audio Enhancements:Right-click speaker icon > Sound settings > select device > Properties > Disable enhancements.\nCheck BIOS:Enter BIOS setup and verify onboard audio is enabled.', 'Known-good speakers or headphones, USB audio adapter', 'net stop Audiosrv,net start Audiosrv,sndvol', 'Desktop', NULL, NULL, 1, NULL, 'published', 1.0, 0.00, 0, 1, 0, 0, NULL, NULL, '2026-08-29 10:20:27', '2026-08-31 02:42:38', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `knowledge_ratings`
--

CREATE TABLE `knowledge_ratings` (
  `id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` enum('helpful','not_helpful') NOT NULL,
  `solved` enum('yes','partial','no') DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `knowledge_ratings`
--

INSERT INTO `knowledge_ratings` (`id`, `article_id`, `user_id`, `rating`, `solved`, `feedback`, `created_at`) VALUES
(2, 26, 1, '', NULL, '', '2026-08-30 18:46:31');

-- --------------------------------------------------------

--
-- Table structure for table `knowledge_requests`
--

CREATE TABLE `knowledge_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `status` enum('pending','in_progress','fulfilled','dismissed') DEFAULT 'pending',
  `vote_count` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `knowledge_versions`
--

CREATE TABLE `knowledge_versions` (
  `id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `version` decimal(3,1) NOT NULL,
  `title` varchar(255) NOT NULL,
  `solution` text NOT NULL,
  `changed_by` int(11) NOT NULL,
  `change_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `id` int(11) NOT NULL,
  `organization_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`id`, `organization_id`, `name`, `address`, `created_at`) VALUES
(1, 1, 'Main Office', 'Makati, Metro Manila', '2026-08-23 20:56:33'),
(2, 1, 'North Service Site', 'Quezon City, Metro Manila', '2026-08-23 20:56:33'),
(3, 1, 'South Service Site', 'Dasmariñas, Cavite', '2026-08-23 20:56:33');

-- --------------------------------------------------------

--
-- Table structure for table `manufacturers`
--

CREATE TABLE `manufacturers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `logo_url` varchar(500) DEFAULT NULL,
  `website` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text DEFAULT NULL,
  `url` varchar(500) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `organizations`
--

CREATE TABLE `organizations` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `organizations`
--

INSERT INTO `organizations` (`id`, `name`, `created_at`) VALUES
(1, 'Field IT Services', '2026-08-23 20:56:33'),
(2, 'Customer Support Operations', '2026-08-23 20:56:33');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `permission_key` varchar(100) NOT NULL,
  `module` varchar(50) NOT NULL,
  `action` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `permission_key`, `module`, `action`, `description`, `created_at`) VALUES
(1, 'dashboard.view', 'dashboard', 'view', 'View dashboard', '2026-08-23 20:56:33'),
(2, 'troubleshooting.view', 'troubleshooting', 'view', 'View troubleshooting guides', '2026-08-23 20:56:33'),
(3, 'troubleshooting.create', 'troubleshooting', 'create', 'Create troubleshooting sessions', '2026-08-23 20:56:33'),
(4, 'troubleshooting.edit', 'troubleshooting', 'edit', 'Edit troubleshooting flows', '2026-08-23 20:56:33'),
(5, 'troubleshooting.delete', 'troubleshooting', 'delete', 'Delete troubleshooting flows', '2026-08-23 20:56:33'),
(6, 'knowledge.view', 'knowledge', 'view', 'View knowledge', '2026-08-23 20:56:33'),
(7, 'knowledge.create', 'knowledge', 'create', 'Submit knowledge', '2026-08-23 20:56:33'),
(8, 'knowledge.edit', 'knowledge', 'edit', 'Edit knowledge', '2026-08-23 20:56:33'),
(9, 'knowledge.delete', 'knowledge', 'delete', 'Delete knowledge', '2026-08-23 20:56:33'),
(10, 'knowledge.approve', 'knowledge', 'approve', 'Approve knowledge', '2026-08-23 20:56:33'),
(11, 'knowledge.publish', 'knowledge', 'publish', 'Publish knowledge', '2026-08-23 20:56:33'),
(12, 'knowledge.manage', 'knowledge', 'manage', 'Manage knowledge', '2026-08-23 20:56:33'),
(13, 'equipment.view', 'equipment', 'view', 'View equipment', '2026-08-23 20:56:33'),
(14, 'equipment.create', 'equipment', 'create', 'Create equipment', '2026-08-23 20:56:33'),
(15, 'equipment.edit', 'equipment', 'edit', 'Edit equipment', '2026-08-23 20:56:33'),
(16, 'equipment.delete', 'equipment', 'delete', 'Delete equipment', '2026-08-23 20:56:33'),
(17, 'equipment.manage', 'equipment', 'manage', 'Manage equipment', '2026-08-23 20:56:33'),
(18, 'commands.view', 'commands', 'view', 'View commands', '2026-08-23 20:56:33'),
(19, 'tools.view', 'tools', 'view', 'View tools', '2026-08-23 20:56:33'),
(20, 'tickets.view', 'tickets', 'view', 'View tickets', '2026-08-23 20:56:33'),
(21, 'tickets.create', 'tickets', 'create', 'Create tickets', '2026-08-23 20:56:33'),
(22, 'tickets.escalate', 'tickets', 'escalate', 'Escalate tickets', '2026-08-23 20:56:33'),
(23, 'documentation.create', 'documentation', 'create', 'Submit field documentation', '2026-08-23 20:56:33'),
(24, 'documentation.review', 'documentation', 'review', 'Review documentation', '2026-08-23 20:56:33'),
(25, 'users.manage', 'users', 'manage', 'Manage users', '2026-08-23 20:56:33'),
(26, 'roles.manage', 'roles', 'manage', 'Manage roles', '2026-08-23 20:56:33'),
(27, 'departments.manage', 'departments', 'manage', 'Manage departments', '2026-08-23 20:56:33'),
(28, 'contacts.view', 'contacts', 'view', 'View authorized contacts', '2026-08-23 20:56:33'),
(29, 'contacts.manage', 'contacts', 'manage', 'Manage contacts', '2026-08-23 20:56:33'),
(30, 'ai.use', 'ai', 'use', 'Use IT Support AI', '2026-08-23 20:56:33'),
(31, 'ai.train', 'ai', 'train', 'Manage AI training', '2026-08-23 20:56:33'),
(32, 'ai.web_search', 'ai', 'web_search', 'Use approved web research', '2026-08-23 20:56:33'),
(33, 'chat.use', 'chat', 'use', 'Use team chat', '2026-08-23 20:56:33'),
(34, 'audit.view', 'audit', 'view', 'View audit logs', '2026-08-23 20:56:33'),
(35, 'system.settings', '', '', 'Manage system settings', '2026-08-29 10:51:50'),
(36, 'ai.manage', '', '', 'Manage AI settings', '2026-08-29 10:52:00');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `is_system` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`, `is_system`, `created_at`) VALUES
(1, 'Super Admin', 'Full system access', 1, '2026-08-23 20:56:33'),
(2, 'Admin', 'Manage knowledge, users, equipment, troubleshooting', 1, '2026-08-23 20:56:33'),
(3, 'Supervisor', 'Manage assigned department, users, contacts, escalations', 1, '2026-08-23 20:56:33'),
(4, 'Field IT', 'Troubleshoot, document, use AI and chat', 1, '2026-08-23 20:56:33'),
(5, 'Standard User', 'View approved knowledge and create support requests', 1, '2026-08-23 20:56:33');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`) VALUES
(9, 1, 1),
(33, 1, 2),
(30, 1, 3),
(32, 1, 4),
(31, 1, 5),
(24, 1, 6),
(19, 1, 7),
(21, 1, 8),
(20, 1, 9),
(18, 1, 10),
(23, 1, 11),
(22, 1, 12),
(17, 1, 13),
(13, 1, 14),
(15, 1, 15),
(14, 1, 16),
(16, 1, 17),
(6, 1, 18),
(29, 1, 19),
(28, 1, 20),
(26, 1, 21),
(27, 1, 22),
(11, 1, 23),
(12, 1, 24),
(34, 1, 25),
(25, 1, 26),
(10, 1, 27),
(8, 1, 28),
(7, 1, 29),
(2, 1, 30),
(1, 1, 31),
(3, 1, 32),
(5, 1, 33),
(4, 1, 34),
(151, 1, 35),
(152, 1, 36),
(71, 2, 1),
(93, 2, 2),
(90, 2, 3),
(92, 2, 4),
(91, 2, 5),
(85, 2, 6),
(80, 2, 7),
(82, 2, 8),
(81, 2, 9),
(79, 2, 10),
(84, 2, 11),
(83, 2, 12),
(78, 2, 13),
(74, 2, 14),
(76, 2, 15),
(75, 2, 16),
(77, 2, 17),
(69, 2, 18),
(89, 2, 19),
(88, 2, 20),
(86, 2, 21),
(87, 2, 22),
(72, 2, 23),
(73, 2, 24),
(94, 2, 25),
(70, 2, 28),
(65, 2, 30),
(64, 2, 31),
(66, 2, 32),
(68, 2, 33),
(67, 2, 34),
(100, 3, 1),
(112, 3, 2),
(111, 3, 3),
(106, 3, 6),
(105, 3, 7),
(104, 3, 10),
(103, 3, 13),
(98, 3, 18),
(110, 3, 19),
(109, 3, 20),
(107, 3, 21),
(108, 3, 22),
(101, 3, 23),
(102, 3, 24),
(99, 3, 28),
(95, 3, 30),
(97, 3, 33),
(96, 3, 34),
(130, 4, 1),
(140, 4, 2),
(139, 4, 3),
(134, 4, 6),
(133, 4, 7),
(132, 4, 13),
(128, 4, 18),
(138, 4, 19),
(137, 4, 20),
(135, 4, 21),
(136, 4, 22),
(131, 4, 23),
(129, 4, 28),
(126, 4, 30),
(127, 4, 33),
(144, 5, 1),
(150, 5, 2),
(146, 5, 6),
(145, 5, 13),
(143, 5, 18),
(149, 5, 19),
(148, 5, 20),
(147, 5, 21),
(141, 5, 30),
(142, 5, 33);

-- --------------------------------------------------------

--
-- Table structure for table `search_analytics`
--

CREATE TABLE `search_analytics` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `query` varchar(255) NOT NULL,
  `results_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `session_steps`
--

CREATE TABLE `session_steps` (
  `id` bigint(20) NOT NULL,
  `session_id` int(11) NOT NULL,
  `node_id` int(11) NOT NULL,
  `step_order` int(11) DEFAULT 0,
  `answer` varchar(30) NOT NULL,
  `time_spent_seconds` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `session_steps`
--

INSERT INTO `session_steps` (`id`, `session_id`, `node_id`, `step_order`, `answer`, `time_spent_seconds`, `created_at`) VALUES
(1, 1, 4, 4, 'not_worked', 3, '2026-08-29 09:56:15'),
(2, 2, 4, 4, 'not_worked', 3, '2026-08-29 09:56:34'),
(3, 7, 4, 4, 'worked', 1, '2026-08-29 10:06:55');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `key` varchar(120) NOT NULL,
  `value` text DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

CREATE TABLE `teams` (
  `id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `teams`
--

INSERT INTO `teams` (`id`, `department_id`, `name`, `created_at`) VALUES
(1, 1, 'Field Support Alpha', '2026-08-23 20:56:33'),
(2, 1, 'Field Support Beta', '2026-08-23 20:56:33'),
(3, 2, 'Network Team', '2026-08-23 20:56:33'),
(4, 3, 'Deployment Team', '2026-08-23 20:56:33');

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `id` int(11) NOT NULL,
  `ticket_number` varchar(20) DEFAULT NULL,
  `session_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `priority` enum('low','medium','high','critical') DEFAULT 'medium',
  `status` enum('open','in_progress','waiting','resolved','closed','escalated') DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ticket_notes`
--

CREATE TABLE `ticket_notes` (
  `id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `is_internal` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tips`
--

CREATE TABLE `tips` (
  `id` int(11) NOT NULL,
  `category` varchar(50) NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `author_id` int(11) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tools`
--

CREATE TABLE `tools` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `purpose` text NOT NULL,
  `when_to_use` text DEFAULT NULL,
  `how_to_use` text DEFAULT NULL,
  `safety` text DEFAULT NULL,
  `related_issues` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tools`
--

INSERT INTO `tools` (`id`, `name`, `icon`, `purpose`, `when_to_use`, `how_to_use`, `safety`, `related_issues`, `created_at`) VALUES
(1, 'Multimeter', NULL, 'Measure voltage, current, resistance', 'Testing PSU voltages, diagnosing power issues', 'Set to DC voltage. Touch probes to PSU pins.', 'Do not touch PSU internals.', NULL, '2026-08-29 10:19:28'),
(2, 'CrystalDiskInfo', NULL, 'Check drive health via S.M.A.R.T.', 'Checking hard drive/SSD health', 'Open to see health status.', 'Read-only tool.', NULL, '2026-08-29 10:19:28'),
(3, 'MemTest86', NULL, 'Test RAM for errors', 'Diagnosing RAM-related BSOD and crashes', 'Boot from USB. Run 1+ passes.', 'Requires USB boot.', NULL, '2026-08-29 10:19:28'),
(4, 'HWMonitor', NULL, 'Monitor temperatures and voltages', 'Checking for overheating issues', 'Open and check CPU/GPU temps.', 'Read-only.', NULL, '2026-08-29 10:19:28'),
(5, 'Task Manager', NULL, 'Monitor processes and resources', 'Finding high CPU/RAM usage, managing startup', 'Ctrl+Shift+Esc.', 'Built-in Windows tool.', NULL, '2026-08-29 10:19:28'),
(6, 'Event Viewer', NULL, 'View system logs', 'Finding error details for BSOD and crashes', 'Open eventvwr.msc.', 'Read-only.', NULL, '2026-08-29 10:19:28'),
(7, 'Compressed Air', NULL, 'Clean dust from components', 'Fixing overheating, cleaning fans', 'Short bursts on fans/heatsinks.', 'Hold can upright.', NULL, '2026-08-29 10:19:28'),
(8, 'USB Bootable Drive', NULL, 'Recovery and diagnostics', 'Windows will not boot, need recovery', 'Create with Media Creation Tool.', 'Backup before use.', NULL, '2026-08-29 10:19:28'),
(9, 'Multimeter', NULL, 'Measure voltage, current, resistance', 'Testing PSU voltages, diagnosing power issues', 'Set to DC voltage. Touch probes to PSU pins.', 'Do not touch PSU internals.', NULL, '2026-08-29 10:20:27'),
(10, 'CrystalDiskInfo', NULL, 'Check drive health via S.M.A.R.T.', 'Checking hard drive/SSD health', 'Open to see health status.', 'Read-only tool.', NULL, '2026-08-29 10:20:27'),
(11, 'MemTest86', NULL, 'Test RAM for errors', 'Diagnosing RAM-related BSOD and crashes', 'Boot from USB. Run 1+ passes.', 'Requires USB boot.', NULL, '2026-08-29 10:20:27'),
(12, 'HWMonitor', NULL, 'Monitor temperatures and voltages', 'Checking for overheating issues', 'Open and check CPU/GPU temps.', 'Read-only.', NULL, '2026-08-29 10:20:27'),
(13, 'Task Manager', NULL, 'Monitor processes and resources', 'Finding high CPU/RAM usage, managing startup', 'Ctrl+Shift+Esc.', 'Built-in Windows tool.', NULL, '2026-08-29 10:20:27'),
(14, 'Event Viewer', NULL, 'View system logs', 'Finding error details for BSOD and crashes', 'Open eventvwr.msc.', 'Read-only.', NULL, '2026-08-29 10:20:27'),
(15, 'Compressed Air', NULL, 'Clean dust from components', 'Fixing overheating, cleaning fans', 'Short bursts on fans/heatsinks.', 'Hold can upright.', NULL, '2026-08-29 10:20:27'),
(16, 'USB Bootable Drive', NULL, 'Recovery and diagnostics', 'Windows will not boot, need recovery', 'Create with Media Creation Tool.', 'Backup before use.', NULL, '2026-08-29 10:20:27');

-- --------------------------------------------------------

--
-- Table structure for table `troubleshooting_categories`
--

CREATE TABLE `troubleshooting_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `troubleshooting_categories`
--

INSERT INTO `troubleshooting_categories` (`id`, `name`, `slug`, `icon`, `description`, `sort_order`, `created_at`) VALUES
(1, 'Display Issues', 'display', 'monitor', 'Monitor and video output problems', 1, '2026-08-23 20:56:33'),
(2, 'Power Issues', 'power', 'power', 'Power and startup problems', 2, '2026-08-23 20:56:33'),
(3, 'Audio Issues', 'audio', 'volume-x', 'Sound and audio problems', 3, '2026-08-23 20:56:33'),
(4, 'Network Issues', 'network', 'wifi', 'LAN, Wi-Fi, IP and DNS problems', 4, '2026-08-23 20:56:33'),
(5, 'Printer Issues', 'printer', 'printer', 'Printer and print path problems', 5, '2026-08-23 20:56:33'),
(6, 'CCTV Issues', 'cctv', 'camera', 'Authorized CCTV device problems', 6, '2026-08-23 20:56:33'),
(7, 'Software Issues', 'software', 'app-window', 'Windows and application problems', 7, '2026-08-23 20:56:33'),
(8, 'Hardware Issues', 'hardware', 'cpu', 'Component and thermal problems', 8, '2026-08-23 20:56:33');

-- --------------------------------------------------------

--
-- Table structure for table `troubleshooting_issues`
--

CREATE TABLE `troubleshooting_issues` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `severity` enum('low','medium','high','critical') DEFAULT 'medium',
  `estimated_time` varchar(50) DEFAULT NULL,
  `symptoms` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`symptoms`)),
  `tools_needed` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tools_needed`)),
  `safety_warnings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`safety_warnings`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `device_types` varchar(255) DEFAULT NULL,
  `status` varchar(30) DEFAULT 'approved',
  `submitted_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `troubleshooting_issues`
--

INSERT INTO `troubleshooting_issues` (`id`, `category_id`, `title`, `slug`, `description`, `severity`, `estimated_time`, `symptoms`, `tools_needed`, `safety_warnings`, `created_at`, `device_types`, `status`, `submitted_by`) VALUES
(1, 1, 'No Display / Black Screen', 'no-display-black-screen', 'Monitor shows no image when computer is powered on.', 'medium', '15-30 min', '[\"Monitor shows black screen\",\"Monitor shows No Signal\",\"Display is blank but PC seems running\"]', '[\"Video cable (HDMI\\/DP\\/VGA)\",\"Spare monitor\"]', '[\"Always power off before reseating components.\"]', '2026-08-29 09:37:21', 'desktop,laptop', 'approved', NULL),
(2, 1, 'Screen Flickering / Artifacts', 'screen-flickering-artifacts', 'Display shows flickering, visual artifacts, or distorted image.', 'medium', '10-20 min', '[\"Screen flickering\",\"Visual artifacts\",\"Distorted display\",\"Lines on screen\"]', '[\"GPU driver installer\"]', '[\"None for basic checks.\"]', '2026-08-29 09:37:21', 'desktop,laptop', 'approved', NULL),
(3, 2, 'No Power / Computer Wont Turn On', 'no-power-computer-wont-turn-on', 'Computer does not respond when power button is pressed.', 'critical', '15-45 min', '[\"Computer does not turn on\",\"No lights or fans\",\"Completely dead\"]', '[\"Multimeter\",\"Spare PSU\",\"Screwdriver\"]', '[\"Unplug power before opening case.\"]', '2026-08-29 09:37:21', 'desktop,laptop', 'approved', NULL),
(4, 2, 'Computer Turns On Then Immediately Off', 'computer-turns-on-then-off', 'Computer powers on briefly then shuts down within seconds.', 'high', '20-45 min', '[\"PC turns on then off\",\"Fans spin briefly then stop\",\"Keeps rebooting\"]', '[\"Screwdriver\",\"Thermal paste\"]', '[\"Unplug before opening. Handle CPU with care.\"]', '2026-08-29 09:37:21', 'desktop', 'approved', NULL),
(5, 3, 'No Sound / Audio Not Working', 'no-sound-audio-not-working', 'No audio output from speakers or headphones.', 'medium', '10-20 min', '[\"No sound from speakers\",\"Volume icon muted\",\"Audio device not detected\"]', '[\"Working speakers\\/headphones\"]', '[\"None \\u2014 software issue.\"]', '2026-08-29 09:37:21', 'desktop,laptop', 'approved', NULL),
(6, 3, 'Microphone Not Working', 'microphone-not-working', 'Microphone not picking up audio.', 'medium', '10-20 min', '[\"Microphone not detected\",\"No audio input\",\"Mic shows muted\"]', '[\"Working microphone\"]', '[\"None \\u2014 software issue.\"]', '2026-08-29 09:37:21', 'desktop,laptop', 'approved', NULL),
(7, 4, 'No Internet Connection', 'no-internet-connection', 'Computer cannot access the internet.', 'high', '15-30 min', '[\"No internet access\",\"Web pages not loading\",\"Connected but no internet\"]', '[\"Ethernet cable\",\"Router access\"]', '[\"None for basic checks.\"]', '2026-08-29 09:37:21', 'desktop,laptop', 'approved', NULL),
(8, 4, 'WiFi Not Connecting', 'wifi-not-connecting', 'Device cannot connect to WiFi.', 'medium', '10-20 min', '[\"WiFi not showing networks\",\"Cannot connect\",\"WiFi keeps dropping\"]', '[\"WiFi adapter\"]', '[\"None for basic checks.\"]', '2026-08-29 09:37:21', 'laptop', 'approved', NULL),
(9, 4, 'DNS Resolution Issues', 'dns-resolution-issues', 'Internet works for IP addresses but not website names.', 'medium', '10-15 min', '[\"Cannot resolve domain names\",\"Ping works for IP but not domain\"]', '[]', '[\"None \\u2014 software.\"]', '2026-08-29 09:37:21', 'desktop,laptop', 'approved', NULL),
(10, 4, 'LAN Cable Not Working', 'lan-cable-not-working', 'Ethernet connection not working.', 'medium', '10-20 min', '[\"Ethernet not detected\",\"No link light\",\"Cable connected but no internet\"]', '[\"Cable tester\",\"Spare Ethernet cable\"]', '[\"None for basic checks.\"]', '2026-08-29 09:37:21', 'desktop,laptop', 'approved', NULL),
(11, 5, 'Printer Not Printing', 'printer-not-printing', 'Printer does not respond to print jobs.', 'medium', '10-20 min', '[\"Printer shows offline\",\"Print jobs stuck\",\"Not responding\"]', '[\"Ethernet\\/USB cable\"]', '[\"Unplug before clearing jams.\"]', '2026-08-29 09:37:21', 'all', 'approved', NULL),
(12, 5, 'Paper Jam', 'paper-jam', 'Printer shows paper jam error.', 'medium', '10-20 min', '[\"Paper jam error\",\"Paper stuck\",\"Will not feed paper\"]', '[\"Flashlight\",\"Tweezers\"]', '[\"Unplug before opening. Do not force paper out.\"]', '2026-08-29 09:37:21', 'all', 'approved', NULL),
(13, 5, 'Printer Shows Offline', 'printer-shows-offline', 'Printer appears offline in Windows.', 'medium', '10-15 min', '[\"Printer status shows Offline\",\"Cannot send print jobs\"]', '[\"Ethernet cable\",\"Printer IP\"]', '[\"None \\u2014 software\\/config issue.\"]', '2026-08-29 09:37:21', 'all', 'approved', NULL),
(14, 6, 'CCTV Camera Not Recording', 'cctv-camera-not-recording', 'NVR/DVR shows camera but no recording.', 'high', '20-40 min', '[\"No recording on NVR\",\"Camera shows live but no playback\",\"Recording stopped\"]', '[\"Network cable\",\"Monitor for NVR\"]', '[\"Work carefully near camera mounts.\"]', '2026-08-29 09:37:21', 'all', 'approved', NULL),
(15, 6, 'NVR Remote Access Not Working', 'nvr-remote-access-not-working', 'Cannot access NVR remotely.', 'medium', '15-30 min', '[\"Cannot view cameras remotely\",\"Mobile app offline\",\"Port forwarding issues\"]', '[\"Router access\",\"Monitor\"]', '[\"Do not open unnecessary ports.\"]', '2026-08-29 09:37:21', 'all', 'approved', NULL),
(16, 7, 'Blue Screen of Death (BSOD)', 'blue-screen-of-death-bsod', 'Windows shows blue screen with error code.', 'critical', '20-60 min', '[\"Blue screen appears\",\"Computer restarts with error\",\"BSOD error code\"]', '[\"USB drive for Safe Mode\"]', '[\"Backup data before major fixes.\"]', '2026-08-29 09:37:21', 'desktop,laptop', 'approved', NULL),
(17, 7, 'Computer Running Slow', 'computer-running-slow', 'PC is noticeably slow.', 'medium', '15-30 min', '[\"Very slow\",\"Apps take forever\",\"System freezes\",\"High CPU\\/RAM usage\"]', '[\"Task Manager\"]', '[\"Back up data before disk operations.\"]', '2026-08-29 09:37:21', 'desktop,laptop', 'approved', NULL),
(18, 7, 'Application Crashes / Not Responding', 'application-crashes-not-responding', 'App keeps crashing or stops responding.', 'medium', '10-20 min', '[\"App crashes on open\",\"Stops responding\",\"Error on launch\"]', '[\"Application installer\"]', '[\"Back up app data before reinstall.\"]', '2026-08-29 09:37:21', 'desktop,laptop', 'approved', NULL),
(19, 7, 'Windows Update Fails', 'windows-update-fails', 'Windows Update keeps failing.', 'medium', '15-30 min', '[\"Update fails with error\",\"Stuck at percentage\",\"Cannot check for updates\"]', '[\"USB drive for manual update\"]', '[\"Do not force restart during update.\"]', '2026-08-29 09:37:21', 'desktop,laptop', 'approved', NULL),
(20, 7, 'Cannot Log Into Windows', 'cannot-log-into-windows', 'User cannot log into Windows.', 'high', '10-30 min', '[\"Password not accepted\",\"Login loop\",\"Account locked\"]', '[\"Admin account\"]', '[\"Do not guess passwords repeatedly.\"]', '2026-08-29 09:37:21', 'desktop,laptop', 'approved', NULL),
(21, 8, 'Computer Overheating', 'computer-overheating', 'PC shuts down randomly or runs very hot.', 'high', '20-45 min', '[\"Random shutdowns\",\"Fan at full speed\",\"Hot to touch\",\"CPU over 90C\"]', '[\"Compressed air\",\"Thermal paste\",\"Temp monitor\"]', '[\"Unplug before cleaning.\"]', '2026-08-29 09:37:21', 'desktop,laptop', 'approved', NULL),
(22, 8, 'Hard Drive Not Detected / Failing', 'hard-drive-not-detected-failing', 'Hard drive not showing in BIOS or making unusual noises.', 'critical', '20-45 min', '[\"HDD not detected\",\"Clicking\\/grinding noise\",\"Very slow\",\"S.M.A.R.T. errors\"]', '[\"SATA cable\",\"Spare drive\",\"Backup drive\"]', '[\"BACKUP DATA IMMEDIATELY if clicking.\"]', '2026-08-29 09:37:21', 'desktop,laptop', 'approved', NULL),
(23, 8, 'USB Port Not Working', 'usb-port-not-working', 'USB device not recognized or not working.', 'low', '10-20 min', '[\"USB not detected\",\"Device error\",\"Keeps disconnecting\"]', '[\"Working USB device\"]', '[\"None.\"]', '2026-08-29 09:37:21', 'desktop,laptop', 'approved', NULL),
(24, 8, 'Laptop Battery Not Charging', 'laptop-battery-not-charging', 'Laptop battery does not charge.', 'medium', '15-30 min', '[\"Battery not charging\",\"Drains while plugged in\",\"Charging indicator off\"]', '[\"Spare charger\",\"Battery report\"]', '[\"Do not use swollen batteries.\"]', '2026-08-29 09:37:21', 'laptop', 'approved', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `troubleshooting_sessions`
--

CREATE TABLE `troubleshooting_sessions` (
  `id` int(11) NOT NULL,
  `ticket_number` varchar(20) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `issue_id` int(11) DEFAULT NULL,
  `customer_name` varchar(150) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `location` varchar(200) DEFAULT NULL,
  `device_type` varchar(50) DEFAULT NULL,
  `manufacturer` varchar(100) DEFAULT NULL,
  `model` varchar(100) DEFAULT NULL,
  `serial_number` varchar(100) DEFAULT NULL,
  `problem_description` text DEFAULT NULL,
  `priority` enum('low','medium','high','critical') DEFAULT 'medium',
  `status` enum('new','in_progress','solved','partial','escalated','unsolved') DEFAULT 'new',
  `resolution` text DEFAULT NULL,
  `resolution_type` varchar(50) DEFAULT NULL,
  `steps_performed` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`steps_performed`)),
  `parts_replaced` text DEFAULT NULL,
  `tools_used` text DEFAULT NULL,
  `time_spent_minutes` int(11) DEFAULT NULL,
  `started_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ended_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolved_at` timestamp NULL DEFAULT NULL,
  `escalated_at` timestamp NULL DEFAULT NULL,
  `total_questions` int(11) DEFAULT 0,
  `questions_yes` int(11) DEFAULT 0,
  `questions_no` int(11) DEFAULT 0,
  `total_steps` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `troubleshooting_sessions`
--

INSERT INTO `troubleshooting_sessions` (`id`, `ticket_number`, `user_id`, `issue_id`, `customer_name`, `department`, `location`, `device_type`, `manufacturer`, `model`, `serial_number`, `problem_description`, `priority`, `status`, `resolution`, `resolution_type`, `steps_performed`, `parts_replaced`, `tools_used`, `time_spent_minutes`, `started_at`, `ended_at`, `created_at`, `resolved_at`, `escalated_at`, `total_questions`, `questions_yes`, `questions_no`, `total_steps`) VALUES
(1, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'medium', 'new', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-29 09:55:59', NULL, '2026-08-29 09:55:59', NULL, NULL, 3, 2, 1, 10),
(2, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'medium', 'new', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-29 09:56:25', NULL, '2026-08-29 09:56:25', NULL, NULL, 3, 3, 0, 10),
(3, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'medium', 'new', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-29 09:59:35', NULL, '2026-08-29 09:59:35', NULL, NULL, 0, 0, 0, 0),
(4, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'medium', 'new', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-29 10:02:32', NULL, '2026-08-29 10:02:32', NULL, NULL, 0, 0, 0, 0),
(5, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'medium', 'new', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-29 10:02:38', NULL, '2026-08-29 10:02:38', NULL, NULL, 0, 0, 0, 0),
(6, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'medium', 'new', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-29 10:02:39', NULL, '2026-08-29 10:02:39', NULL, NULL, 0, 0, 0, 0),
(7, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'medium', 'new', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-29 10:06:51', NULL, '2026-08-29 10:06:51', NULL, NULL, 3, 1, 2, 10),
(8, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'medium', 'new', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-29 10:07:33', NULL, '2026-08-29 10:07:33', NULL, NULL, 0, 0, 0, 0),
(9, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'medium', 'new', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-29 10:07:41', NULL, '2026-08-29 10:07:41', NULL, NULL, 0, 0, 0, 0),
(10, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'medium', 'new', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-29 10:07:43', NULL, '2026-08-29 10:07:43', NULL, NULL, 0, 0, 0, 0),
(11, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'medium', 'new', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-29 10:07:44', NULL, '2026-08-29 10:07:44', NULL, NULL, 0, 0, 0, 0),
(12, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'medium', 'new', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-29 10:07:44', NULL, '2026-08-29 10:07:44', NULL, NULL, 0, 0, 0, 0),
(13, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'medium', 'new', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-29 11:34:52', NULL, '2026-08-29 11:34:52', NULL, NULL, 0, 0, 0, 0),
(14, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'medium', 'new', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-29 11:34:57', NULL, '2026-08-29 11:34:57', NULL, NULL, 0, 0, 0, 0),
(15, NULL, 1, 19, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'medium', 'new', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-29 11:35:05', NULL, '2026-08-29 11:35:05', NULL, NULL, 0, 0, 0, 0),
(16, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'medium', 'new', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-29 11:35:10', NULL, '2026-08-29 11:35:10', NULL, NULL, 0, 0, 0, 0),
(17, NULL, 1, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'medium', 'new', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-29 11:35:17', NULL, '2026-08-29 11:35:17', NULL, NULL, 0, 0, 0, 0),
(18, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'medium', 'new', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-31 02:42:12', NULL, '2026-08-31 02:42:12', NULL, NULL, 0, 0, 0, 0),
(19, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'medium', 'new', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-31 03:01:32', NULL, '2026-08-31 03:01:32', NULL, NULL, 0, 0, 0, 0),
(20, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'medium', 'new', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-31 03:01:36', NULL, '2026-08-31 03:01:36', NULL, NULL, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `troubleshooting_steps`
--

CREATE TABLE `troubleshooting_steps` (
  `id` int(11) NOT NULL,
  `issue_id` int(11) NOT NULL,
  `step_number` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `instruction` text NOT NULL,
  `why` text DEFAULT NULL,
  `risk_level` enum('safe','caution','danger') DEFAULT 'safe',
  `safety_warning` text DEFAULT NULL,
  `checks` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`checks`)),
  `expected_result` text DEFAULT NULL,
  `if_yes` text DEFAULT NULL,
  `if_no` text DEFAULT NULL,
  `required_tools` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`required_tools`)),
  `commands` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`commands`)),
  `media_url` varchar(500) DEFAULT NULL,
  `is_final` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `troubleshooting_submissions`
--

CREATE TABLE `troubleshooting_submissions` (
  `id` int(11) NOT NULL,
  `submitted_by` int(11) NOT NULL,
  `submission_type` varchar(50) NOT NULL DEFAULT 'new_issue',
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `severity` varchar(30) DEFAULT 'medium',
  `category_id` int(11) DEFAULT NULL,
  `nodes_data` longtext DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `role_id` int(11) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `team_id` int(11) DEFAULT NULL,
  `location_id` int(11) DEFAULT NULL,
  `status` enum('active','inactive','locked') DEFAULT 'active',
  `avatar_url` varchar(500) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password_hash`, `full_name`, `role_id`, `department_id`, `team_id`, `location_id`, `status`, `avatar_url`, `phone`, `last_login`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'admin@fieldit.local', '$2y$10$OhAxJFuDuzKXbiFN169Jh.3W/6XR1eTOBlRnRjeGkdCtkUjtjle9W', 'System Admin', 1, 1, NULL, NULL, 'active', NULL, NULL, '2026-08-30 18:37:32', '2026-08-22 01:52:22', '2026-08-31 02:37:32', NULL),
(2, 'fieldit@fieldit.local', '$2y$10$OhAxJFuDuzKXbiFN169Jh.3W/6XR1eTOBlRnRjeGkdCtkUjtjle9W', 'Juan Dela Cruz', 4, 1, NULL, NULL, 'active', NULL, NULL, NULL, '2026-08-22 01:52:22', '2026-08-22 02:09:25', NULL),
(3, 'supervisor@fieldit.local', '$2y$10$OhAxJFuDuzKXbiFN169Jh.3W/6XR1eTOBlRnRjeGkdCtkUjtjle9W', 'Maria Santos', 3, 1, NULL, NULL, 'active', NULL, NULL, NULL, '2026-08-22 01:52:22', '2026-08-22 02:09:25', NULL),
(4, 'user@fieldit.local', '$2y$10$OhAxJFuDuzKXbiFN169Jh.3W/6XR1eTOBlRnRjeGkdCtkUjtjle9W', 'Carlo Reyes', 5, 2, NULL, NULL, 'active', NULL, NULL, NULL, '2026-08-22 01:52:22', '2026-08-22 02:09:25', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_permissions`
--

CREATE TABLE `user_permissions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `granted` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ai_conversations`
--
ALTER TABLE `ai_conversations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `ai_feedback`
--
ALTER TABLE `ai_feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `message_id` (`message_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `ai_messages`
--
ALTER TABLE `ai_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `conversation_id` (`conversation_id`);

--
-- Indexes for table `attachments`
--
ALTER TABLE `attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_user` (`user_id`),
  ADD KEY `idx_audit_action` (`action`);

--
-- Indexes for table `chat_conversations`
--
ALTER TABLE `chat_conversations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_chat_conv` (`conversation_id`,`created_at`);

--
-- Indexes for table `chat_participants`
--
ALTER TABLE `chat_participants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_conv_user` (`conversation_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `commands`
--
ALTER TABLE `commands`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_commands_cat` (`category_id`);

--
-- Indexes for table `command_categories`
--
ALTER TABLE `command_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `decision_nodes`
--
ALTER TABLE `decision_nodes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_decision_issue` (`issue_id`),
  ADD KEY `idx_decision_parent` (`parent_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `organization_id` (`organization_id`);

--
-- Indexes for table `device_guides`
--
ALTER TABLE `device_guides`
  ADD PRIMARY KEY (`id`),
  ADD KEY `model_id` (`model_id`),
  ADD KEY `author_id` (`author_id`);

--
-- Indexes for table `device_models`
--
ALTER TABLE `device_models`
  ADD PRIMARY KEY (`id`),
  ADD KEY `manufacturer_id` (`manufacturer_id`),
  ADD KEY `device_type_id` (`device_type_id`);

--
-- Indexes for table `device_model_issues`
--
ALTER TABLE `device_model_issues`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_model_issue` (`model_id`,`issue_id`),
  ADD KEY `idx_model_issue_model` (`model_id`),
  ADD KEY `idx_model_issue_issue` (`issue_id`);

--
-- Indexes for table `device_parts`
--
ALTER TABLE `device_parts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `model_id` (`model_id`);

--
-- Indexes for table `device_types`
--
ALTER TABLE `device_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `error_codes`
--
ALTER TABLE `error_codes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `escalations`
--
ALTER TABLE `escalations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_id` (`ticket_id`),
  ADD KEY `escalated_by` (`escalated_by`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_fav` (`user_id`,`item_type`,`item_id`);

--
-- Indexes for table `invitations`
--
ALTER TABLE `invitations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `invited_by` (`invited_by`);

--
-- Indexes for table `knowledge_articles`
--
ALTER TABLE `knowledge_articles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_knowledge_status` (`status`),
  ADD KEY `idx_knowledge_category` (`category`),
  ADD KEY `idx_knowledge_author` (`author_id`);

--
-- Indexes for table `knowledge_ratings`
--
ALTER TABLE `knowledge_ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_article_rating` (`article_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `knowledge_requests`
--
ALTER TABLE `knowledge_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `knowledge_versions`
--
ALTER TABLE `knowledge_versions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `article_id` (`article_id`),
  ADD KEY `changed_by` (`changed_by`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `organization_id` (`organization_id`);

--
-- Indexes for table `manufacturers`
--
ALTER TABLE `manufacturers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_user` (`user_id`,`is_read`);

--
-- Indexes for table `organizations`
--
ALTER TABLE `organizations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permission_key` (`permission_key`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_role_perm` (`role_id`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`);

--
-- Indexes for table `search_analytics`
--
ALTER TABLE `search_analytics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `session_steps`
--
ALTER TABLE `session_steps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_session_steps_session` (`session_id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key` (`key`);

--
-- Indexes for table `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ticket_number` (`ticket_number`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `assigned_to` (`assigned_to`);

--
-- Indexes for table `ticket_notes`
--
ALTER TABLE `ticket_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_id` (`ticket_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tips`
--
ALTER TABLE `tips`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author_id` (`author_id`);

--
-- Indexes for table `tools`
--
ALTER TABLE `tools`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `troubleshooting_categories`
--
ALTER TABLE `troubleshooting_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `troubleshooting_issues`
--
ALTER TABLE `troubleshooting_issues`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `troubleshooting_sessions`
--
ALTER TABLE `troubleshooting_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ticket_number` (`ticket_number`),
  ADD KEY `idx_sessions_user` (`user_id`),
  ADD KEY `idx_sessions_status` (`status`);

--
-- Indexes for table `troubleshooting_steps`
--
ALTER TABLE `troubleshooting_steps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `issue_id` (`issue_id`);

--
-- Indexes for table `troubleshooting_submissions`
--
ALTER TABLE `troubleshooting_submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ts_sub_status` (`status`),
  ADD KEY `idx_ts_sub_submitter` (`submitted_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `team_id` (`team_id`),
  ADD KEY `location_id` (`location_id`),
  ADD KEY `idx_users_email` (`email`),
  ADD KEY `idx_users_status` (`status`),
  ADD KEY `idx_users_dept` (`department_id`);

--
-- Indexes for table `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_perm` (`user_id`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ai_conversations`
--
ALTER TABLE `ai_conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ai_feedback`
--
ALTER TABLE `ai_feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ai_messages`
--
ALTER TABLE `ai_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attachments`
--
ALTER TABLE `attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `chat_conversations`
--
ALTER TABLE `chat_conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chat_participants`
--
ALTER TABLE `chat_participants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `commands`
--
ALTER TABLE `commands`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `command_categories`
--
ALTER TABLE `command_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `decision_nodes`
--
ALTER TABLE `decision_nodes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=173;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `device_guides`
--
ALTER TABLE `device_guides`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `device_models`
--
ALTER TABLE `device_models`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `device_model_issues`
--
ALTER TABLE `device_model_issues`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `device_parts`
--
ALTER TABLE `device_parts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `device_types`
--
ALTER TABLE `device_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `equipment`
--
ALTER TABLE `equipment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `error_codes`
--
ALTER TABLE `error_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT for table `escalations`
--
ALTER TABLE `escalations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invitations`
--
ALTER TABLE `invitations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `knowledge_articles`
--
ALTER TABLE `knowledge_articles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `knowledge_ratings`
--
ALTER TABLE `knowledge_ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `knowledge_requests`
--
ALTER TABLE `knowledge_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `knowledge_versions`
--
ALTER TABLE `knowledge_versions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `manufacturers`
--
ALTER TABLE `manufacturers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `organizations`
--
ALTER TABLE `organizations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=153;

--
-- AUTO_INCREMENT for table `search_analytics`
--
ALTER TABLE `search_analytics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `session_steps`
--
ALTER TABLE `session_steps`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ticket_notes`
--
ALTER TABLE `ticket_notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tips`
--
ALTER TABLE `tips`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tools`
--
ALTER TABLE `tools`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `troubleshooting_categories`
--
ALTER TABLE `troubleshooting_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `troubleshooting_issues`
--
ALTER TABLE `troubleshooting_issues`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `troubleshooting_sessions`
--
ALTER TABLE `troubleshooting_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `troubleshooting_steps`
--
ALTER TABLE `troubleshooting_steps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `troubleshooting_submissions`
--
ALTER TABLE `troubleshooting_submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user_permissions`
--
ALTER TABLE `user_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ai_conversations`
--
ALTER TABLE `ai_conversations`
  ADD CONSTRAINT `ai_conversations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `ai_feedback`
--
ALTER TABLE `ai_feedback`
  ADD CONSTRAINT `ai_feedback_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `ai_messages` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ai_feedback_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `ai_messages`
--
ALTER TABLE `ai_messages`
  ADD CONSTRAINT `ai_messages_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `ai_conversations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `attachments`
--
ALTER TABLE `attachments`
  ADD CONSTRAINT `attachments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `chat_conversations`
--
ALTER TABLE `chat_conversations`
  ADD CONSTRAINT `chat_conversations_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `chat_conversations_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `chat_messages_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `chat_conversations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chat_messages_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `chat_participants`
--
ALTER TABLE `chat_participants`
  ADD CONSTRAINT `chat_participants_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `chat_conversations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chat_participants_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `commands`
--
ALTER TABLE `commands`
  ADD CONSTRAINT `commands_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `command_categories` (`id`);

--
-- Constraints for table `contacts`
--
ALTER TABLE `contacts`
  ADD CONSTRAINT `contacts_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `decision_nodes`
--
ALTER TABLE `decision_nodes`
  ADD CONSTRAINT `decision_nodes_ibfk_1` FOREIGN KEY (`issue_id`) REFERENCES `troubleshooting_issues` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `departments`
--
ALTER TABLE `departments`
  ADD CONSTRAINT `departments_ibfk_1` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `device_guides`
--
ALTER TABLE `device_guides`
  ADD CONSTRAINT `device_guides_ibfk_1` FOREIGN KEY (`model_id`) REFERENCES `device_models` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `device_guides_ibfk_2` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `device_models`
--
ALTER TABLE `device_models`
  ADD CONSTRAINT `device_models_ibfk_1` FOREIGN KEY (`manufacturer_id`) REFERENCES `manufacturers` (`id`),
  ADD CONSTRAINT `device_models_ibfk_2` FOREIGN KEY (`device_type_id`) REFERENCES `device_types` (`id`);

--
-- Constraints for table `device_model_issues`
--
ALTER TABLE `device_model_issues`
  ADD CONSTRAINT `device_model_issues_ibfk_1` FOREIGN KEY (`model_id`) REFERENCES `device_models` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `device_model_issues_ibfk_2` FOREIGN KEY (`issue_id`) REFERENCES `troubleshooting_issues` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `device_parts`
--
ALTER TABLE `device_parts`
  ADD CONSTRAINT `device_parts_ibfk_1` FOREIGN KEY (`model_id`) REFERENCES `device_models` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `escalations`
--
ALTER TABLE `escalations`
  ADD CONSTRAINT `escalations_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`),
  ADD CONSTRAINT `escalations_ibfk_2` FOREIGN KEY (`escalated_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `invitations`
--
ALTER TABLE `invitations`
  ADD CONSTRAINT `invitations_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `invitations_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `invitations_ibfk_3` FOREIGN KEY (`invited_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `knowledge_articles`
--
ALTER TABLE `knowledge_articles`
  ADD CONSTRAINT `knowledge_articles_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `knowledge_ratings`
--
ALTER TABLE `knowledge_ratings`
  ADD CONSTRAINT `knowledge_ratings_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `knowledge_articles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `knowledge_ratings_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `knowledge_requests`
--
ALTER TABLE `knowledge_requests`
  ADD CONSTRAINT `knowledge_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `knowledge_versions`
--
ALTER TABLE `knowledge_versions`
  ADD CONSTRAINT `knowledge_versions_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `knowledge_articles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `knowledge_versions_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `locations`
--
ALTER TABLE `locations`
  ADD CONSTRAINT `locations_ibfk_1` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `search_analytics`
--
ALTER TABLE `search_analytics`
  ADD CONSTRAINT `search_analytics_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `session_steps`
--
ALTER TABLE `session_steps`
  ADD CONSTRAINT `session_steps_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `troubleshooting_sessions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `teams`
--
ALTER TABLE `teams`
  ADD CONSTRAINT `teams_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `tickets_ibfk_2` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`);

--
-- Constraints for table `ticket_notes`
--
ALTER TABLE `ticket_notes`
  ADD CONSTRAINT `ticket_notes_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ticket_notes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `tips`
--
ALTER TABLE `tips`
  ADD CONSTRAINT `tips_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `troubleshooting_issues`
--
ALTER TABLE `troubleshooting_issues`
  ADD CONSTRAINT `troubleshooting_issues_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `troubleshooting_categories` (`id`);

--
-- Constraints for table `troubleshooting_sessions`
--
ALTER TABLE `troubleshooting_sessions`
  ADD CONSTRAINT `troubleshooting_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `troubleshooting_steps`
--
ALTER TABLE `troubleshooting_steps`
  ADD CONSTRAINT `troubleshooting_steps_ibfk_1` FOREIGN KEY (`issue_id`) REFERENCES `troubleshooting_issues` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_ibfk_3` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_ibfk_4` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD CONSTRAINT `user_permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
