<?php
/**
 * 設定ファイル読み込み
 */
require_once( dirname( __FILE__ ) . '/config.php' );
file_put_contents( LOG_FILE, date( "[Y-m-d H:i:s]" ) . " Start Deploy\n", FILE_APPEND | LOCK_EX );
/**
 * データ取得
 */
$post_data = file_get_contents( 'php://input' );
$hmac      = hash_hmac( 'sha1', $post_data, SECRET_KEY );
$payload   = json_decode( $post_data, true );
/**
 * 認証＆処理実行
 */
if ( isset( $_SERVER['HTTP_X_HUB_SIGNATURE'] ) && $_SERVER['HTTP_X_HUB_SIGNATURE'] === 'sha1=' . $hmac ) {

  foreach ( $commands as $branch => $command ) {
    /**
     * ブランチ判断
     */
    if ( $payload['ref'] == 'refs/heads/' . $branch ) {
      if ( $command !== '' ) {
        /**
         * コマンド実行
         */
        exec( $command );
        file_put_contents( LOG_FILE, date( "[Y-m-d H:i:s]" ) . " " . $_SERVER['REMOTE_ADDR'] . " " . $branch . " " . $payload['commits'][0]['message'] . "\n", FILE_APPEND | LOCK_EX );
      }
    }
  }
} else {
  /**
   * 認証失敗
   */
  file_put_contents( LOG_ERR, date( "[Y-m-d H:i:s]" ) . " invalid access: " . $_SERVER['REMOTE_ADDR'] . "\n", FILE_APPEND | LOCK_EX );
}