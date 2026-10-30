-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 03, 2025 at 05:18 PM
-- Server version: 10.4.17-MariaDB
-- PHP Version: 8.0.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `health_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `notes` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `fee` decimal(10,0) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(50) COLLATE utf8_unicode_ci DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `user_id`, `doctor_id`, `appointment_date`, `appointment_time`, `notes`, `fee`, `created_at`, `status`) VALUES
(1, 2, 3, '2025-12-10', '08:00:00', 'Sick', '1000', '2025-10-27 23:21:42', 'Cancelled'),
(2, 2, 3, '2025-12-15', '09:30:00', 'Sick', '1000', '2025-10-27 23:22:10', 'Confirmed'),
(3, 2, 3, '2025-12-03', '10:30:00', 'Sick', '1000', '2025-10-27 23:22:25', 'Completed'),
(4, 2, 3, '2025-12-25', '14:00:00', 'Sick', '1000', '2025-10-29 13:50:56', 'Pending'),
(5, 2, 3, '2025-12-30', '13:00:00', 'Sick', '1000', '2025-10-29 13:51:22', 'In-Progress'),
(7, 2, 8, '2025-12-17', '12:30:00', 'Patient', '250', '2025-12-03 15:21:24', 'Cancelled'),
(8, 14, 6, '2025-12-03', '18:00:00', 'Illness', '600', '2025-12-03 15:24:50', 'Completed'),
(9, 15, 9, '2025-12-04', '10:00:00', 'Problem', '400', '2025-12-03 15:34:51', 'Completed');

-- --------------------------------------------------------

--
-- Table structure for table `doctors`
--

CREATE TABLE `doctors` (
  `id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `specialization` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `fee` decimal(10,2) NOT NULL DEFAULT 100.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `doctors`
--

INSERT INTO `doctors` (`id`, `name`, `specialization`, `image`, `user_id`, `fee`) VALUES
(1, 'Dr. Ahmed Ali', 'Cardiologist', 'img/doctors/doctor_1.jpg', 3, '1000.00'),
(2, 'Dr. Sarah Mohamed', 'Dermatologist', 'img/doctors/doctor_2.jpg', 4, '200.00'),
(3, 'Dr. Omar Hassan', 'Pediatrician', 'img/doctors/doctor_3.jpg', 5, '300.00'),
(4, 'Dr. Mona Adel', 'Neurologist', 'img/doctors/doctor_4.jpg', 6, '900.00'),
(5, 'Dr. Karim Youssef', 'Orthopedic', 'img/doctors/doctor_5.jpg', 7, '700.00'),
(6, 'Dr. Layla Saeed', 'Ophthalmologist', 'img/doctors/doctor_6.jpg', 8, '800.00'),
(7, 'Dr. Hossam Fathy', 'ENT Specialist', 'img/doctors/doctor_7.jpg', 9, '400.00'),
(8, 'Dr. Rania Khaled', 'Gynecologist', 'img/doctors/doctor_8.jpg', 10, '600.00');

-- --------------------------------------------------------

--
-- Table structure for table `doctor_notes`
--

CREATE TABLE `doctor_notes` (
  `id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `note` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `prescription_image` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `doctor_notes`
--

INSERT INTO `doctor_notes` (`id`, `doctor_id`, `patient_id`, `appointment_id`, `note`, `prescription_image`, `created_at`) VALUES
(1, 3, 2, 3, 'Done!', 'img/prescriptions/Prescription_Sultan_Ali_1764765601.jpg', '2025-12-03 14:40:01'),
(3, 9, 15, 9, 'Done', 'img/prescriptions/Prescription_Soliman_1764778410.jpg', '2025-12-03 18:13:30');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `subject` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `message` text COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `name`, `email`, `subject`, `message`, `created_at`, `status`) VALUES
(1, 'Sultan', 'sultan@gmail.com', 'Urgent', 'Hello, I need Technical Help', '2025-10-26 13:33:10', 'pending'),
(2, 'Sultan', 'sultan@gmail.com', 'Inquiry', 'Hello, I need More Technical Help', '2025-10-26 13:33:26', 'resolved');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text COLLATE utf8_unicode_ci NOT NULL,
  `link` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` enum('unread','read') COLLATE utf8_unicode_ci DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `link`, `status`, `created_at`) VALUES
(1, 2, 'Your doctor has started the video consultation.', 'user_video.php?appointment_id=3', 'read', '2025-12-03 12:37:10'),
(2, 15, 'Your doctor has started the video consultation.', 'user_video.php?appointment_id=9', 'read', '2025-12-03 15:36:41');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 100.00,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(50) COLLATE utf8_unicode_ci DEFAULT 'Paid',
  `method` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `appointment_id`, `user_id`, `amount`, `payment_date`, `status`, `method`) VALUES
(1, 3, 2, '1000.00', '2025-12-03 12:36:48', 'Paid', 'stcpay'),
(2, 4, 2, '1000.00', '2025-12-03 12:41:09', 'Paid', 'paypal'),
(3, 9, 15, '400.00', '2025-12-03 15:35:57', 'Paid', 'paypal');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') COLLATE utf8_unicode_ci DEFAULT 'Other',
  `role` enum('user','doctor','admin','receptionist') COLLATE utf8_unicode_ci DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `image` varchar(255) COLLATE utf8_unicode_ci DEFAULT 'img/users/default-user.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `address`, `phone`, `dob`, `gender`, `role`, `created_at`, `image`) VALUES
(1, 'Admin User', 'admin@health.com', 'admin123', 'Madinah, Saudi Arabia', '0595555555', '1980-01-01', 'Female', 'admin', '2025-10-09 16:03:55', 'img/users/default-user.png'),
(2, 'Sultan Ali', 'sultan@gmail.com', '12345', 'Riyadh, Saudi Arabia', '+966 555345670', '2000-05-12', 'Male', 'user', '2025-10-09 16:03:55', 'img/users/user_2.jpg'),
(3, 'Ahmed Ali', 'doctor1@gmail.com', '12345', 'Dammam, Saudi Arabia', '+966 555345671', '1985-02-14', 'Male', 'doctor', '2025-10-09 16:03:55', 'img/doctors/doctor_1.jpg'),
(4, 'Sarah Mohamed', 'doctor2@gmail.com', '12345', 'Dammam, Saudi Arabia', '+966 555345672', '1997-10-17', 'Male', 'doctor', '2025-10-14 16:24:33', 'img/doctors/doctor_2.jpg'),
(5, 'Omar Hassan', 'doctor3@gmail.com', '12345', 'Dammam, Saudi Arabia', '+966 555345673', '1995-02-02', 'Male', 'doctor', '2025-10-17 23:09:16', 'img/doctors/doctor_3.jpg'),
(6, 'Mona Adel', 'doctor4@gmail.com', '12345', 'Jeddah, Saudi Arabia', '+966 555345674', '1998-08-22', 'Female', 'doctor', '2025-10-09 16:03:55', 'img/doctors/doctor_4.jpg'),
(7, 'Karim Youssef', 'doctor5@gmail.com', '12345', 'Riyadh, Saudi Arabia', '+966 555345675', '1995-05-05', 'Male', 'doctor', '2025-10-17 23:45:36', 'img/doctors/doctor_5.jpg'),
(8, 'Layla Saeed', 'doctor6@gmail.com', '12345', 'Riyadh, Saudi Arabia', '+966 555345676', '1996-06-06', 'Female', 'doctor', '2025-10-17 23:47:14', 'img/doctors/doctor_6.jpg'),
(9, 'Hossam Fathy', 'doctor7@gmail.com', '12345', 'Riyadh, Saudi Arabia', '+966 555345677', '1998-07-07', 'Male', 'doctor', '2025-10-17 23:51:35', 'img/doctors/doctor_7.jpg'),
(10, 'Rania Khaled', 'doctor8@gmail.com', '12345', 'Jeddah, Saudi Arabia', '+966 555345678', '1989-08-08', 'Male', 'doctor', '2025-10-17 23:54:15', 'img/doctors/doctor_8.jpg'),
(11, 'Receptionist', 'receptionist1@gmail.com', '12345', 'Riyadh, Saudi Arabia', '+966 555345699', '1989-06-06', 'Female', 'receptionist', '2025-12-03 13:02:11', 'img/users/default-user.png'),
(14, 'Ali Ahmed', 'ali@gmail.com', '123', 'Cairo', '+966 550545972', '1980-12-11', 'Male', 'user', '2025-12-03 15:23:12', 'img/users/default-user.png'),
(15, 'Soliman', 'soliman@health.com', '123', 'Cairo', '+20 1098244445', '1999-01-02', 'Male', 'user', '2025-12-03 15:33:50', 'img/users/default-user.png');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `doctor_notes`
--
ALTER TABLE `doctor_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `doctor_id` (`doctor_id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `doctors`
--
ALTER TABLE `doctors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `doctor_notes`
--
ALTER TABLE `doctor_notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `doctors`
--
ALTER TABLE `doctors`
  ADD CONSTRAINT `fk_doctor_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `doctor_notes`
--
ALTER TABLE `doctor_notes`
  ADD CONSTRAINT `doctor_notes_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `doctor_notes_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
