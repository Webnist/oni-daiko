=== Plugin Name ===
Contributors: Webnist
Donate link: 
Tags: multi, site, recent, posts, widget, rss
Requires at least: 3.0.4
Tested up to: 3.1
Stable tag: 0.5.3

Shows a list of the latest posts from all blogs under your WordPress Multisite.

== Description ==
鬼太鼓は、５００年の歴史を持つ日本の佐渡島に伝わる能の舞です。佐渡島で暮らしていた人は、悪魔払い、商売繁盛、五穀豊穣のために、この踊りを踊っていたそうです。

WordPressプラグインの鬼太鼓は、能の鬼太鼓とは、残念ながら全く関係がありません。
そのかわり、プラグインの鬼太鼓は、マルチサイトのWordPressに、全子サイトから統合された最新投稿のリストを表示する機能を追加します。
このほかにも、ウィジェットへの対応やRSSの出力機能も有しています。

== Thanks ==
このリードミーを書いてくれた大曲さん有難う御座います。
 [Hitoshi Omagari](http://www.warna.info/)

== Installation ==

1. 解凍して、WordPressのプラグインディレクトリにアップロードします。もしくは、管理画面のプラグイン > 新規追加 より、インストールをすることも可能です。
2. プラグイン > インストール済み のリストから、Oni Daikoを有効にしてください。
3. 管理画面のメニューに鬼太鼓が追加されていますので、タイトル、表示数、本文の表示、本文の表示文字数を設定してください。（ウィジェットはウィジェット毎に設定が可能です。）
4. 鬼太鼓のウィジェットをサイドバーに追加するか、テーマファイルに鬼太鼓のテンプレートタグを追加してください。テンプレートタグは下記の通りです。
    `<?php echo oni_daiko_template_tag(); ?>`

== Frequently Asked Questions ==

= 新しい投稿が反映されません =
プラグインが有効になっていない、テンプレートタグの前にechoがないなど基本的な部分を見直してみて下さい。
また、ネットワークサイトは、鬼太鼓の新着には含まれません。

= 鬼太鼓の能を教えて下さい。 =
残念ながら、作者は鬼太鼓を踊ることができません。Youtubeの映像を見てご自分で習得してください。

http://www.youtube.com/watch?v=mm0Rr6T8rPg

== Screenshots ==
1. Administration interface of Oni Daiko
2. Oni Daiko Widget

== Changelog ==
* **0.5.3**
* Public release

== Upgrade Notice ==
念のためバックアップを行ってからにしてね。