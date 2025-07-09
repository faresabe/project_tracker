@@ .. @@
 CREATE TABLE `tasks` (
   `id` int(11) NOT NULL,
   `project_id` int(11) DEFAULT NULL,
   `name` varchar(255) DEFAULT NULL,
   `type` varchar(50) DEFAULT NULL,
   `description` text DEFAULT NULL,
   `start_date` date DEFAULT NULL,
   `end_date` date DEFAULT NULL,
-  `status` enum('pending','ongoing','done') DEFAULT 'pending'
+  `status` enum('pending','ongoing','done') DEFAULT 'pending',
+  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;