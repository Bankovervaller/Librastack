-- Align mvc_boeken.user_id type with users.id (BIGINT UNSIGNED) and ensure FK is correct

-- 1) Drop existing foreign key if present
ALTER TABLE mvc_boeken DROP FOREIGN KEY mvc_boeken_ibfk_1;

-- 2) Modify column type to BIGINT UNSIGNED, keep nullable for legacy rows
ALTER TABLE mvc_boeken MODIFY COLUMN user_id BIGINT UNSIGNED NULL;

-- 3) Recreate foreign key referencing users(id)
ALTER TABLE mvc_boeken
  ADD CONSTRAINT mvc_boeken_ibfk_1
  FOREIGN KEY (user_id) REFERENCES users(id)
  ON DELETE CASCADE
  ON UPDATE RESTRICT;

-- Optional: If you want to enforce non-null for new rows, update application logic accordingly (already enforced in PHP).

