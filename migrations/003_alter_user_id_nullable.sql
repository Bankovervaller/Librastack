-- Make user_id nullable to avoid FK violations for legacy rows
ALTER TABLE mvc_boeken MODIFY COLUMN user_id INT NULL;

-- Clean up legacy default values
UPDATE mvc_boeken SET user_id = NULL WHERE user_id = 0;

-- Note: Foreign key constraint to users(id) will accept NULLs; non-NULL values must reference an existing users.id.

