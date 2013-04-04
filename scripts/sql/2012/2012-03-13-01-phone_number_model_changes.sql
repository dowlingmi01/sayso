/**
 * Phone Number Model Changes
 *
 * Telephone numbers previously had their own table in the model.
 * This is unneccessary, as phone numbers are only used to verify shipping
 * information. 
 * This script moves the phone numbers column to the user_address table
 * and then drops the phone number table
 */
 
ALTER TABLE user
    DROP FOREIGN KEY user_primary_phone_id;

ALTER TABLE user
    DROP COLUMN primary_phone_id;

ALTER TABLE user_address
    ADD phone varchar(100) COMMENT 'Delivery address' AFTER country;

DROP TABLE user_phone;
