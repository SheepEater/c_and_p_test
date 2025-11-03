# C&P様　テスト提出（問1・問2）
各問の回答は専用フォルダにまとめ、フォルダ内に実行手順などを記した README.md を置いています。

## 問1
### フォルダ
`src/manga-cafe-pricing`

- マンガ喫茶の料金計算 CLI
- 実行方法はフォルダ内の `README.md` を参照してください。

## 問2
### フォルダ
`sns-table`

- SNSのテーブル定義（ER図 / MySQL DDL）
- 利用手順・概要はフォルダ内の `README.md` を参照してください。

## セットアップ（共通）
このリポジトリでは、環境変数は src/.env に記載します。機密情報はコミットしません。

1. ひな形をコピー
cp src/.env.example src/.env

2. エディタで src/.env を開き、パスワード等を設定
 - DB_PASSWORD, MYSQL_ROOT_PASSWORD など
 - ローカル利用時はダミーでOK

3. コンテナ起動（ビルド込み）
docker compose up -d --build