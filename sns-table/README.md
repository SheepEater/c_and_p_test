# C&P Question 2（簡易ER・DDL）

SNSの一部機能を想定したテーブル定義です。  

会員登録、投稿、返信、タグ、スタンプ（リアクション）を扱います。

文字コードは utf8mb4 です。

## 使い方（ローカル MySQL で試す）

1. MySQL 8 を用意する（Dockerでも可）
2. 空の DB を作る（例：`cp_question2`）
3. このリポジトリの `schema.sql` を実行する
4. このリポジトリの `test.sql` を実行する

```sql
-- 例
CREATE DATABASE cp_question2 CHARACTER SET utf8mb4;
USE cp_question2;
SOURCE ./schema.sql;


## テーブルの概要

### members
会員。account_name はユニーク。status は provisional / active / withdrawn を想定。

### member_emails
会員のメールアドレス。email はユニーク。

is_primary（主アドレス）は1人につき1件を想定

### posts
投稿本文。

### replies
投稿への返信（返信への返信はしない想定）。

### tags
タグのマスタ。name はユニーク。

### post_tags
投稿とタグの中間テーブル。複合主キー（post_id,tag_id）。

### stamp_types
リアクション（スタンプ）の種類マスタ。name はユニーク。

### reactions
だれがどのスタンプをどの投稿 or 返信に付けたか。

## インデックス/削除ポリシー（最低限）

各 FK は基本 ON DELETE CASCADE（親が消えたら子も消える）

検索でよく使いそうな列にシンプルな INDEX を追加（created_at など）