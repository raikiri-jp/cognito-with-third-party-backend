# laravel-cognito-backend

laravel-cognito-backend は、Laravel ベースのアプリケーションで Amazon Cognito を認証サービスとして使用するためのサンプルプロジェクトです。

## 前提条件

このプロジェクトを動作させるには、以下の前提条件を満たす必要があります。

- Amazon Cognito でユーザープールが作成され、アプリケーションクライアントが設定されていること
- アプリケーションクライアントはシークレットが生成されていること

## インストール方法

1. このプロジェクトをクローンするか、ZIP ファイルとしてダウンロードして解凍します。
2. プロジェクトのルートディレクトリに移動して、以下のコマンドを実行します。

   ```
   composer install
   ```

3. `.env.example` ファイルを複製して `.env` ファイルを作成します。

   ```
   cp .env.example .env
   ```

4. `.env` ファイルを開き、以下の値を設定します。

   ```
   COGNITO_OAUTH2_DOMAIN="OAuth および Amazon Cognito エンドポイントのドメイン"
   COGNITO_APP_CLIENT_ID=クライアントID
   COGNITO_APP_SECRET=クライアントのシークレット
   ```

5. データベースを作成します。

   ```
   php artisan migrate
   ```

6. アプリケーションを起動します。

   ```
   php artisan serve
   ```

7. ブラウザで `http://localhost:8000` にアクセスして、アプリケーションが正常に起動することを確認します。

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
