-- =========================================
-- HOME INTERIOR DATABASE SETUP
-- Run this SQL in phpMyAdmin or MySQL
-- =========================================

-- Step 1: Create the database
CREATE DATABASE IF NOT EXISTS home_interior;

-- Step 2: Use the database
USE home_interior;

-- Step 3: Create the designs table
-- This table stores every AI design that is generated
CREATE TABLE IF NOT EXISTS designs (

    id              INT AUTO_INCREMENT PRIMARY KEY,   -- Unique ID for each design
    original_image  VARCHAR(255) NOT NULL,            -- Path to the uploaded room image
    generated_image VARCHAR(255) NOT NULL,            -- Path to the AI generated image
    prompt          TEXT,                             -- The custom prompt the user typed
    is_saved        TINYINT(1) DEFAULT 0,             -- 0 = not saved, 1 = saved by user
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP  -- When was it generated

);
