<?php

/*
 * ▼ このファイルの使い方
 * GitHubの Settings > Webhooks に以下のように設定する
 * Payload URL:     http://xxxxx.heteml.net/deploy.php
 * Content type:    application/json_decode
 * Secret:          $SECRET_KEY
 * Which events...: Just the push event.
 */

# ──────────────────────────────
# 設定(基本的にここだけ環境に合わせて変更する)
# ──────────────────────────────

# ログファイル定義
$LOG_FILE = dirname(__FILE__).'/hook.log';

# エラーログファイル定義
$LOG_FILE_ERR = dirname(__FILE__).'/hook-error.log';

# GitHubに設定するパスワード的な物(お好きな文字列)
$SECRET_KEY = 'actions-test';

# git pullしたいブランチ(配列)
$BRANCHS = array('develop','main');

# ──────────────────────────────

# 全てのHTTPリクエストヘッダを取得
$header = getallheaders();

# POSTの生データを取得
$post_data = file_get_contents( 'php://input' );

# ハッシュ値を生成
$hmac = hash_hmac('sha1', $post_data, $SECRET_KEY);

# 'X-Hub-Signature'はGitHubのWebhooksで設定したSecret項目
# リクエストヘッダで受け取ったSecretとconfig.phpの$SECRET_KEYが同一であれば認証成功
if ( isset($header['X-Hub-Signature']) && $header['X-Hub-Signature'] === 'sha1='.$hmac ) {

    # 受け取ったJSONデータ
    $payload = json_decode($post_data, true);

    foreach ($BRANCHS as $branch) {

        # ブランチ判断
        if($payload['ref'] == 'refs/heads/'.$branch){

            # 各サイトのブランチフォルダに移動
            chdir($payload['repository']['name'].'/'.$branch);

            # pull実行
            // exec('git pull origin '.$branch.' 2>&1', $output, $return);
            exec('ls', $output, $return);

            # ログ記録
            file_put_contents($LOG_FILE,
                date("[Y-m-d H:i:s]")." ".
                $_SERVER['REMOTE_ADDR']." ".
                $payload['repository']['name']."/".$branch." ".
                $payload['commits'][0]['message']." ".
                $output[0]." ".$return."\n",
                FILE_APPEND|LOCK_EX
            );
        }
    }

# 認証失敗
} else {

    # エラーログ記録
    file_put_contents($LOG_FILE_ERR,
        date("[Y-m-d H:i:s]")." ".
        $_SERVER['REMOTE_ADDR']." 認証失敗"."\n",
        FILE_APPEND|LOCK_EX
    );
}
?>