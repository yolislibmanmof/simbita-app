USE simbita_db;

CREATE TABLE IF NOT EXISTS notifications (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(200) NOT NULL,
  message TEXT NOT NULL,
  type ENUM('comment','review','approval','deadline','system') NOT NULL DEFAULT 'system',
  related_table VARCHAR(64) NULL,
  related_id BIGINT UNSIGNED NULL,
  link VARCHAR(500) NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  read_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_notification_user FOREIGN KEY (user_id)
    REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_notification_unread (user_id, is_read, created_at)
) ENGINE=InnoDB;

ALTER TABLE notifications
  ADD COLUMN IF NOT EXISTS link VARCHAR(500) NULL AFTER related_id;