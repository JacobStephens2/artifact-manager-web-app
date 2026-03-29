-- Add birth_year column and backfill from Age
-- Run this migration once, then deploy the code changes

-- Step 1: Add birth_year column
ALTER TABLE players ADD COLUMN birth_year INT NULL AFTER G;

-- Step 2: Backfill birth_year from existing Age values
-- Uses current year minus Age for rows where Age is a plain integer
UPDATE players
SET birth_year = YEAR(CURDATE()) - CAST(Age AS UNSIGNED)
WHERE Age IS NOT NULL
  AND Age REGEXP '^[0-9]+$';

-- Step 3: Drop the Age column
ALTER TABLE players DROP COLUMN Age;
