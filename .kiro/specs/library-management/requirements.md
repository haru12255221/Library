# 図書館管理システム - 要件定義書

## はじめに

学校図書館の蔵書をデジタル化し、効率的な管理と検索を可能にするWebアプリケーションを開発します。2週間のMVP開発として、貸出・返却機能は含めず、「本のデータベース化と検索」に焦点を当てます。

## 要件

### 要件1: ユーザー認証とロール管理

**ユーザーストーリー:** 管理者として、安全にシステムにログインし、一般ユーザーと区別された権限でシステムを利用したい

#### 受入基準
1. WHEN ユーザーがログインページにアクセス THEN システムはメールアドレスとパスワードの入力フォームを表示する
2. WHEN 正しい認証情報が入力される THEN システムはユーザーをダッシュボードにリダイレクトする
3. WHEN 管理者ロール（role=1）のユーザーがアクセス THEN システムは管理者専用機能へのアクセスを許可する
4. WHEN 一般ユーザー（role=2）が管理者専用URLにアクセス THEN システムはアクセスを拒否し、適切なエラーメッセージを表示する

### 要件2: 書籍登録機能

**ユーザーストーリー:** 管理者として、新しい書籍をシステムに登録し、蔵書データベースを構築したい

#### 受入基準
1. WHEN 管理者が書籍登録フォームにアクセス THEN システムはタイトル、著者、ISBNの入力フィールドを表示する
2. WHEN 必須項目（タイトル、著者、ISBN）が入力される THEN システムは書籍をデータベースに保存する
3. WHEN 重複するISBNが入力される THEN システムはエラーメッセージを表示し、登録を拒否する
4. WHEN 書籍登録が成功 THEN システムは成功メッセージを表示し、書籍一覧ページにリダイレクトする
5. WHEN 入力値が不正（空欄など） THEN システムは適切なバリデーションエラーを表示する

### 要件3: 書籍一覧表示機能

**ユーザーストーリー:** 利用者として、登録されている全ての書籍を一覧で確認したい

#### 受入基準
1. WHEN ユーザーが書籍一覧ページにアクセス THEN システムは登録済み書籍の一覧を表示する
2. WHEN 書籍データが存在する THEN システムは各書籍のタイトル、著者、ISBNを表形式で表示する
3. WHEN 書籍データが存在しない THEN システムは「登録された書籍がありません」というメッセージを表示する
4. WHEN 一覧に多数の書籍が存在 THEN システムは見やすい形式で情報を整理して表示する

### 要件4: 書籍検索機能

**ユーザーストーリー:** 利用者として、特定の書籍を素早く見つけるために、キーワードで検索したい

#### 受入基準
1. WHEN ユーザーが検索フォームにキーワードを入力 THEN システムはタイトルと著者名から部分一致で検索する
2. WHEN 検索結果が存在する THEN システムは該当する書籍のみを一覧表示する
3. WHEN 検索結果が存在しない THEN システムは「該当する書籍が見つかりません」というメッセージを表示する
4. WHEN 検索キーワードが空の場合 THEN システムは全ての書籍を表示する
5. WHEN 検索後に検索フォームをクリア THEN システムは全書籍一覧に戻る

### 要件5: ISBN自動入力機能（拡張機能）

**ユーザーストーリー:** 管理者として、ISBNを入力するだけで書籍情報を自動取得し、登録作業を効率化したい

#### 受入基準
1. WHEN 管理者がISBNを入力 THEN システムは外部APIから書籍情報を取得する
2. WHEN 書籍情報の取得が成功 THEN システムはタイトルと著者フィールドを自動入力する
3. WHEN 書籍情報の取得が失敗 THEN システムは手動入力を促すメッセージを表示する
4. WHEN 自動入力された情報が不正確 THEN 管理者は手動で修正できる

## 技術制約

- Laravel 11.x フレームワークを使用
- MySQL データベースを使用
- Tailwind CSS でスタイリング
- Alpine.js でフロントエンド機能
- Laravel Breeze で認証機能
- レスポンシブデザイン対応