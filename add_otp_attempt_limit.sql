-- Run this in phpMyAdmin's SQL tab if you already have a working database.
-- Adds one column to otp_codes — nothing existing is touched or deleted.

ALTER TABLE otp_codes
    ADD COLUMN attempts INT NOT NULL DEFAULT 0 AFTER used;
