<?php

// Migration to create lecturers table
return [
    'up' => "
        CREATE TABLE IF NOT EXISTS lecturers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nip VARCHAR(50) UNIQUE NOT NULL,
            name VARCHAR(100) NOT NULL,
            prodi VARCHAR(100) NULL,
            is_external TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ",
    'down' => "
        DROP TABLE IF EXISTS lecturers;
    "
];