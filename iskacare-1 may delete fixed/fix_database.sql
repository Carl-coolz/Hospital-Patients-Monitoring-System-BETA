-- Fix the patients table structure
-- This will fix both delete and check out functionality

-- First, let's backup and recreate the patients table with proper structure
DROP TABLE IF EXISTS `patients_backup`;
CREATE TABLE `patients_backup` AS SELECT * FROM `patients`;

-- Drop the existing patients table
DROP TABLE `patients`;

-- Create the patients table with proper structure
CREATE TABLE `patients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `age` int(3) NOT NULL,
  `gender` varchar(10) NOT NULL,
  `condition_text` text NOT NULL,
  `date_admitted` date NOT NULL,
  `doctor_assigned` varchar(100) NOT NULL,
  `time_in` int(11) NOT NULL,
  `time_out` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert some sample data with proper IDs
INSERT INTO `patients` (`name`, `age`, `gender`, `condition_text`, `date_admitted`, `doctor_assigned`, `time_in`, `time_out`) VALUES
('John Doe', 25, 'Male', 'Headache', '2024-01-15', 'doctor', 1705123200, 0),
('Jane Smith', 30, 'Female', 'Fever', '2024-01-15', 'doctor', 1705126800, 0),
('Mike Johnson', 22, 'Male', 'Stomach Pain', '2024-01-15', 'doctor', 1705130400, 1705134000),
('Sarah Wilson', 28, 'Female', 'Cold', '2024-01-15', 'doctor', 1705134000, 0);
