-- Add per-user controls for the Capacitor app's local notifications.
-- native_notify_enabled       master on/off (default on)
-- native_notify_hour          0-23, device-local hour to fire notifications (default 9am)
-- native_notify_lead_days     0-14, days before due to fire the "due soon" notification (0 disables)
-- native_notify_past_due      whether to fire the D+1 / morning-after reminder for overdue items

ALTER TABLE users
  ADD COLUMN native_notify_enabled TINYINT(1) NOT NULL DEFAULT 1 AFTER daily_email_hour,
  ADD COLUMN native_notify_hour TINYINT UNSIGNED NOT NULL DEFAULT 9 AFTER native_notify_enabled,
  ADD COLUMN native_notify_lead_days TINYINT UNSIGNED NOT NULL DEFAULT 3 AFTER native_notify_hour,
  ADD COLUMN native_notify_past_due TINYINT(1) NOT NULL DEFAULT 1 AFTER native_notify_lead_days;
