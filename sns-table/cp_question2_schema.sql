-- cp_question2_schema.sql
-- MySQL 8 前提。
-- 先に CREATE DATABASE / USE は別でやってください。
-- 例:
--   CREATE DATABASE cp_question2 CHARACTER SET utf8mb4;
--   USE cp_question2;

SET NAMES utf8mb4;

-- 会員
CREATE TABLE members (
  id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name          VARCHAR(255) NOT NULL,
  account_name  VARCHAR(50)  NOT NULL,
  status        ENUM('provisional','active','withdrawn') NOT NULL DEFAULT 'provisional',
  last_login_at DATETIME NULL,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_members_account_name (account_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 会員のメールアドレス
CREATE TABLE member_emails (
  id                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  member_id          BIGINT UNSIGNED NOT NULL,
  email              VARCHAR(320) NOT NULL,
  is_primary         TINYINT(1) NOT NULL DEFAULT 0,
  is_primary_member_id BIGINT UNSIGNED AS (IF(is_primary = 1, member_id, NULL)) STORED,
  is_verified        TINYINT(1) NOT NULL DEFAULT 0,
  verification_token VARCHAR(255) NULL,
  verified_at        DATETIME NULL,
  created_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_member_emails_email (email),
  UNIQUE KEY uq_member_emails_primary (is_primary_member_id),
  KEY idx_member_emails_member (member_id),
  CONSTRAINT chk_member_emails_primary_flag CHECK (is_primary IN (0,1)),
  CONSTRAINT fk_member_emails_member
    FOREIGN KEY (member_id) REFERENCES members(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 投稿
CREATE TABLE posts (
  id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  member_id  BIGINT UNSIGNED NOT NULL,
  content    TEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_posts_member (member_id),
  KEY idx_posts_created (created_at),
  CONSTRAINT fk_posts_member
    FOREIGN KEY (member_id) REFERENCES members(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 返信（投稿に対してのみ）
CREATE TABLE replies (
  id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  post_id    BIGINT UNSIGNED NOT NULL,
  member_id  BIGINT UNSIGNED NOT NULL,
  content    TEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_replies_post (post_id),
  KEY idx_replies_member (member_id),
  CONSTRAINT fk_replies_post
    FOREIGN KEY (post_id) REFERENCES posts(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_replies_member
    FOREIGN KEY (member_id) REFERENCES members(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- タグ
CREATE TABLE tags (
  id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name       VARCHAR(100) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_tags_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 投稿とタグの中間
CREATE TABLE post_tags (
  post_id BIGINT UNSIGNED NOT NULL,
  tag_id  BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (post_id, tag_id),
  KEY idx_post_tags_tag (tag_id),
  CONSTRAINT fk_post_tags_post
    FOREIGN KEY (post_id) REFERENCES posts(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_post_tags_tag
    FOREIGN KEY (tag_id) REFERENCES tags(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- スタンプ種別
CREATE TABLE stamp_types (
  id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name       VARCHAR(100) NOT NULL,
  image_path TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_stamp_types_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- リアクション（投稿 or 返信のどちらかに付く）
CREATE TABLE reactions (
  id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  member_id     BIGINT UNSIGNED NOT NULL,
  stamp_type_id BIGINT UNSIGNED NOT NULL,
  post_id       BIGINT UNSIGNED NULL,
  reply_id      BIGINT UNSIGNED NULL,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_reactions_member (member_id),
  KEY idx_reactions_post (post_id),
  KEY idx_reactions_reply (reply_id),
  CONSTRAINT fk_reactions_member
    FOREIGN KEY (member_id) REFERENCES members(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_reactions_stamp
    FOREIGN KEY (stamp_type_id) REFERENCES stamp_types(id)
    ON DELETE RESTRICT,
  CONSTRAINT fk_reactions_post
    FOREIGN KEY (post_id) REFERENCES posts(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_reactions_reply
    FOREIGN KEY (reply_id) REFERENCES replies(id)
    ON DELETE CASCADE,
  CONSTRAINT chk_reactions_target CHECK (
    (post_id IS NOT NULL AND reply_id IS NULL)
    OR (post_id IS NULL AND reply_id IS NOT NULL)
  ),
  -- 同じ対象に同じスタンプを何度も付けない想定（NULL の扱いは MySQL 仕様に依存）
  UNIQUE KEY uq_react_post (member_id, stamp_type_id, post_id),
  UNIQUE KEY uq_react_reply (member_id, stamp_type_id, reply_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
