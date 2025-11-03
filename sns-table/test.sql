USE cp_problem2;
SET NAMES utf8mb4;

-- シード＆確認
INSERT IGNORE INTO members (name, account_name, status) VALUES ('テスト太郎','test_taro','active');
INSERT IGNORE INTO posts (member_id, content) VALUES (1,'はじめての投稿'),(1,'2件目の投稿');
INSERT IGNORE INTO tags (name) VALUES ('挨拶'),('日常');

INSERT IGNORE INTO post_tags (post_id, tag_id)
SELECT 1,(SELECT id FROM tags WHERE name='挨拶')
UNION ALL SELECT 1,(SELECT id FROM tags WHERE name='日常')
UNION ALL SELECT 2,(SELECT id FROM tags WHERE name='日常');

SELECT 'tables' AS what, COUNT(*) AS n FROM information_schema.tables
 WHERE table_schema='cp_problem2'
UNION ALL SELECT 'posts', COUNT(*) FROM posts
UNION ALL SELECT 'replies', COUNT(*) FROM replies
UNION ALL SELECT 'tags', COUNT(*) FROM tags
UNION ALL SELECT 'reactions', COUNT(*) FROM reactions;
