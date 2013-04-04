ALTER TABLE user_gaming_transaction_history ADD COLUMN starbar_id int(10) DEFAULT NULL;

ALTER TABLE user_gaming_transaction_history ADD CONSTRAINT user_gaming_transaction_history_starbar_id FOREIGN KEY (starbar_id) REFERENCES starbar (id) ON DELETE SET NULL ON UPDATE CASCADE;
