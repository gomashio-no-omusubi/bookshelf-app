# 模擬案件\_書籍レビューアプリ BookShelf

## プロジェクト概要

未完成

## 作成者

- [あなたの名前、またはGitHubユーザー名]

## 使用技術（技術スタック）

- **言語**: PHP 8.5
- **フレームワーク**: Laravel 10.x
- **データベース**: MySQL 8.4
- **フロントエンド**: Vite, Tailwind CSS ^3.4.0, @tailwindcss/forms
- **開発ツール**: Docker, Laravel Sail, phpMyAdmin
- **構成管理**: Docker / Docker Compose

## 開発環境URL

- **アプリケーション**: http://localhost
- **phpMyAdmin**: http://localhost:8080

## ER図

[※ 基本機能が完成した段階、またはER図を作成したタイミングで、ここにMermaid記法や画像のリンクを貼り付けてください]

---

## 開発環境構築手順

### 1.Laravelプロジェクトの作成 (Laravel 10.x)

以下のDockerコマンドを実行して、Laravel 10.xを明示的に指定してプロジェクトを作成します。

```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    -e COMPOSER_CACHE_DIR=/tmp/composer_cache \
    laravelsail/php82-composer:latest \
    composer create-project laravel/laravel:^10.0 task-manager-app

```

_※ **Windowsをお使いの方へ**：以下のコマンドは `WSL（Ubuntu）` のターミナルで実行してください（`PowerShell` では動きません）。_

### 2. Laravel Sailのインストール

プロジェクト作成後、`bookshelf-app` ディレクトリに移動し、Laravel Sailをインストールします。

```bash
# プロジェクトディレクトリに移動
cd bookshelf-app

# Laravel Sailをインストール
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    -e COMPOSER_CACHE_DIR=/tmp/composer_cache \
    laravelsail/php82-composer:latest \
    composer require laravel/sail --dev

# Sailの設定ファイルをパブリッシュ (MySQLを選択)
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    -e COMPOSER_CACHE_DIR=/tmp/composer_cache \
    laravelsail/php82-composer:latest \
    php artisan sail:install --with=mysql
```

_※ **M1/M2/M3 Mac (Apple Silicon) をお使いの方へ**：Apple Silicon搭載のMacでは、`sail up -d` 実行時に `no matching manifest for linux/arm64/v8` エラーが発生する場合があります。その際は、`compose.yaml` を開き、 `mysql` サービスに `platform: 'linux/amd64'` を追加してください。_

```yaml
mysql:
    image: "mysql/mysql-server:8.0"
    platform: "linux/amd64" # ← この行を追加
    ports: ...
```

_編集後、保存してから `sail up -d` を実行してください。_

### 3. .env ファイルの設定

`.env` ファイルを開き、データベース接続情報が以下と一致していることを確認します。

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=bookshelf_app
DB_USERNAME=sail
DB_PASSWORD=password
```

_**【重要】**：`DB_HOST` には `localhost` や `127.0.0.1` ではなく、必ずDockerのコンテナ名である **`mysql`** を指定してください。これを間違えると、データベースへの接続エラーが発生し、アプリケーションが正常に動作しません。_

### 4. フロントエンドのセットアップ (Vite & Tailwind CSS)

本プロジェクトでは、フロントエンドのスタイリングにTailwind CSSを使用します。  
以下の手順でセットアップを行ってください。

#### 4-1. NPM依存パッケージのインストール

```bash
sail npm install
```

_※ Sailコンテナが起動していることを確認。起動していない場合は `./vendor/bin/sail up -d`を実行_

#### 4-2. Alpine.jsのインストール

```bash
sail npm install alpinejs
```

#### 4-3. Tailwind CSSと @tailwindcss/forms プラグインのインストール

```bash
sail npm install -D tailwindcss@^3.4.0 @tailwindcss/forms postcss autoprefixer
```

_※ `@tailwindcss/forms` はフォーム要素のスタイルをリセットするLaravel標準プラグインです。_

#### 4-4. 設定ファイルの生成

```bash
sail npx tailwindcss init -p
```

#### 4-5. Tailwind CSSのテンプレートパス設定とforms プラグインの有効化

`tailwind.config.js` を開き、中身を以下の内容に書き換えて保存してください。

```javascript
import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ["Figtree", ...defaultTheme.fontFamily.sans],
            },
        },
    },
    plugins: [forms],
};
```

#### 4-6. Vite開発サーバーの起動

デザイン（`CSS/JavaScript`）をリアルタイムで反映させるため、以下のコマンドを実行して開発サーバーを起動します。

```bash
sail npm run dev
```

_**【重要】**：アプリケーションのデザインを正しく表示させるため、 **開発中は常にこのコマンドを実行した状態（ターミナルを起動したまま）** にしておいてください。_

### 5. phpMyAdminの追加

`compose.yaml` を開き、`mysql` サービスの後に以下の設定を追加してください。

```yaml
phpmyadmin:
    image: "phpmyadmin:latest"
    ports:
        - "${FORWARD_PHPMYADMIN_PORT:-8080}:80"
    environment:
        PMA_HOST: mysql
        PMA_USER: "${DB_USERNAME}"
        PMA_PASSWORD: "${DB_PASSWORD}"
    networks:
        - sail
    depends_on:
        - mysql
```

_**【重要】**：YAMLファイルはインデント（字下げ）がずれると正しく動作しません。`phpmyadmin:` の左側のスペース数を、既にある `mysql:` と同じに揃えてください。_

### 6. Sailの起動とエイリアス設定

#### 6-1. Sailをバックグラウンドで起動

```bash
./vendor/bin/sail up -d
```

#### 6-2. エイリアスを設定

毎回 `./vendor/bin/sail` と入力するのは面倒なので、エイリアスを設定します。

**Zsh（Mac）の場合：**

```bash
# エイリアスを設定して 'sail' だけでコマンドを実行できるようにする
echo "alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail'" >> ~/.zshrc

# シェルを再起動するか、新しいターミナルを開いてエイリアスを有効にする
exec $SHELL
```

**Bash（Linux）の場合：**

```bash
# エイリアスを設定して 'sail' だけでコマンドを実行できるようにする
echo "alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail'" >> ~/.bashrc

# シェルを再起動するか、新しいターミナルを開いてエイリアスを有効にする
exec $SHELL
```

### 7. アプリケーションキーの生成

```bash
sail artisan key:generate
```

### 8. データベースのマイグレーションと初期データ投入

以下のコマンドでテーブルを作成し、初期データを投入します。

```bash
sail artisan migrate --seed
```

_※ 既存のデータベースをリセットしたい場合は以下を実行してください。_

```bash
sail artisan migrate:fresh --seed
```

_**※ 日本語化（バリデーション・認証メッセージ）について（基本）**： `config/app.php` の `locale` を `ja` にし、`lang/ja/` にメッセージファイルを手動配置して行います。`laravel-lang/lang` などの `laravel-lang/*` 系パッケージ（`composer require laravel-lang/...`）は導入しないでください。同系パッケージは 2026年5月のサプライチェーン攻撃でマルウェア配布に悪用された経緯があります。_

## 使用技術(実行環境)

- **PHP** : 8.1.34
- **Laravel** : 8.83.8
- **MySQL** : 8.0.26
- **nginx** : 1.21.1

## ER図

![ER図](flea-market-app.drawio.png)

## 開発環境

### アクセスURL

- **商品一覧画面（トップ）** : http://localhost/
- **会員登録画面** : http://localhost/register
- **ログイン画面** : http://localhost/login
- **phpMyAdmin** : http://localhost:8080/
- **MailHog（受信用ダッシュボード）** : http://localhost:8025/

### テスト用ログインアカウント

マイグレーションおよびシーダー（`php artisan db:seed`）の実行後、以下のテスト用アカウントを使用してすぐに各機能の挙動を確認いただけます。
（効率的な動作確認のため、会員登録の手間を省く目的であらかじめ用意しています）

#### 一般ユーザー（購入テスト用）

会員登録なしでログインし、出品されている商品の閲覧・購入の挙動を確認できます。
※プロフィール画像は、要件である「ローカルからのアップロードおよびストレージ（storageディレクトリ）への保存機能」を実際にテストしていただくため、初期状態では未設定（空）としています。

- **メールアドレス**: `test@example.com`
- **パスワード**: `password`

#### 出品者ユーザー

要件に基づき生成された「商品情報」「商品カテゴリー情報」を持つ、10件のダミー商品を出品しているアカウントです。

- **メールアドレス**: `seller@example.com`
- **パスワード**: `password`

### メール認証機能（FN012・FN013）の確認手順

上記のテスト用アカウントはすべて認証済み状態となっています。
新規登録時のメール認証や、認証メール再送機能の挙動を確認する際は、以下の手順で行ってください。

1. トップページの「会員登録」から、任意のメールアドレスで新規アカウントを作成する。
2. 登録完了後、自動的にメール認証待ち画面に遷移する。
3. ブラウザで [MailHog](http://localhost:8025/) を開く。
4. 送信された「Verify Email Address（メールアドレスを確認する）」というメールを開き、本文内の認証リンクをクリックする。
5. 認証が完了し、プロフィール設定画面に遷移することを確認する。

_※ `.env` ファイルのメール設定（MAIL_HOST=mailhog, MAIL_PORT=1025 等）は、docker-composeの起動時点で自動的に適用されるようになっています。_

```

```
