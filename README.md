= mmm_ddns_agent

mmm_ddns_agentは、 MySQL Master Master Replication cluster toolとmmm_ns_agentをDynamicDNS環境で使用するためのツールです。
(Amazon EC2環境のようなIPアドレスを制御できないばあいは特に)

NsupdateのようなツールでDNS recordを更新可能なBINDのようなDNSサーバを構築している場合に使用可能です。
設定ファイルを直接書き換える訳ではないので、DNSサーバとは別サーバで動作させることが可能です。


* Multi-Master Replication Manager for MySQLとmmm_ns_agentの使い方は下記を参考にしてください

[Multi-Master Replication Manager for MySQL](http://mysql-mmm.org/)

[README - mysql-master-master - Multi-Master Replication Manager for MySQL - Google Project Hosting](http://code.google.com/p/mysql-master-master/source/browse/trunk/contrib/ns_agent/README?r=280)

== 事前準備

* MySQL Master Master Replication cluster toolのセットアップ
* DynamicDNSの設定
* php, php extensionのインストール

    $ sudo yum install php php-paer.noarch
    $ pear list-upgrades
    $ pear install Net_DNS2

== 使い方

1.任意の場所にcheckoutする

    $ cd ~/utils
    $ git clone git://github.com/roothybrid7/mmm_ddns_agent.git

2.サンプルの設定ファイルをコピーして環境にあうように編集

    $ cd mmm_ddns_agent
    $ cp mmm_ddns_agent.conf.sample mmm_ddns_agent.conf
    $ vim mmm_ddns_agent.conf
    # mmm_ns_agent.conf
    [nameserver_agent]
    port = 9994                             # ns_agent用起動ポート
    dns_server = 10.1.2.3                   # DynamicDNSサーバ(Bindなど)
    dns_port = 53                           # DynamicDNSのポート
    ttl = 10                                # 登録するMySQL MasterレコードのTTLを指定(短めに)
    type = A                                # DNSレコードの種別(今のところAレコードのみ:監視しているMySQLのInterfaceのIPaddress)
    zone = nsupdate.example.com             # MySQLサーバの所属ゾーンを指定(Domain名)
    # TSIGkey認証の場合はキー名とキーの内容を記述
    tsig_key_name = nsupdate.example.com.
    tsig_key_value = "WImSObCU+ClK7Ol8wWSDokjfdljfkldsafjklsdfdsjlGp8CUPSBpKBSc1yA2ODpcye7vryKzIMqBjELzRsHjWJACyfgs+b7qUnA=="

3. ddns_agentを起動

    $ php ddns_agent.php


== Tips

* DNSサーバ自体の死活確認はしていないので別にした方がいいでしょう。
* ddns_agent.php自体にもDaemonToolsの使用やinitスクリプトの作成を検討した方がいいでしょう。

== 参考

* ["EC2上でMySQL Multi-masterフェイルオーバー - 田中慎司のログ"](http://d.hatena.ne.jp/stanaka/20100223/1266922665)
* ["HowTo update DNS hostnames automatically for your Amazon EC2 instances | MDLog:/sysadmin"](http://www.ducea.com/2009/06/01/howto-update-dns-hostnames-automatically-for-your-amazon-ec2-instances/)
* [Multi-Master Replication Manager for MySQL](http://mysql-mmm.org/)
* [README - mysql-master-master - Multi-Master Replication Manager for MySQL - Google Project Hosting](http://code.google.com/p/mysql-master-master/source/browse/trunk/contrib/ns_agent/README?r=280)
