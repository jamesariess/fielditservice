-- ============================================================
-- Brand enhancements for the Equipment Database page
-- Adds: description, image_url, device_types to `manufacturers`
-- Run once against the fieldit_hub database.
-- ============================================================

ALTER TABLE manufacturers
  ADD COLUMN description TEXT NULL AFTER name,
  ADD COLUMN image_url VARCHAR(500) NULL AFTER description,
  ADD COLUMN device_types VARCHAR(500) NULL AFTER image_url;
-- device_types: comma-separated list of device categories this brand
-- offers, e.g. "Laptop,Desktop,Server,Printer". Used for filtering.
